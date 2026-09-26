<?php

namespace App\Controllers;

use App\Libraries\BukuAcaraSyncService;
use App\Libraries\ExcelExporter;
use App\Models\EventModel;
use App\Models\KejuaraanModel;
use App\Models\PesertaEventModel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BukuAcara extends BaseController
{
    protected EventModel $eventModel;
    protected PesertaEventModel $pesertaModel;
    protected KejuaraanModel $kejuaraanModel;

    public function __construct()
    {
        $this->eventModel     = new EventModel();
        $this->pesertaModel   = new PesertaEventModel();
        $this->kejuaraanModel = new KejuaraanModel();
    }

    public function index()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;

        $sortBy         = $this->request->getGet('sort') ?? 'nomor_acara';
        $sortDir        = (strtolower($this->request->getGet('dir') ?? 'asc') === 'desc') ? 'desc' : 'asc';
        $filterKategori = $this->request->getGet('kategori') ?? '';
        $filterGender   = $this->request->getGet('gender') ?? '';
        $filterBabak    = $this->request->getGet('babak') ?? '';

        $filters = [];
        if (!empty($filterKategori)) $filters['kategori'] = $filterKategori;
        if (!empty($filterGender))   $filters['gender']   = $filterGender;
        if (!empty($filterBabak))    $filters['babak']    = $filterBabak;

        // Ambil data event sesuai pengurutan dan filter
        $events = $this->eventModel->getEventsWithDetails($kejuaraanId, $sortBy, $filters, $sortDir);

        // Ambil semua event untuk statistik filter
        $allEvents = $this->eventModel->getEventsWithDetails($kejuaraanId, 'nomor_acara');
        $counts = [
            'all'     => count($allEvents),
            'lari'    => 0,
            'lompat'  => 0,
            'lempar'  => 0,
            'estafet' => 0,
            'putra'   => 0,
            'putri'   => 0,
            'final'   => 0,
            'penyisihan' => 0,
        ];
        foreach ($allEvents as $aEv) {
            $kat = strtolower($aEv['kategori_lomba'] ?? '');
            if (isset($counts[$kat])) $counts[$kat]++;
            if (($aEv['gender'] ?? '') === 'L') $counts['putra']++;
            if (($aEv['gender'] ?? '') === 'P') $counts['putri']++;
            if (strcasecmp($aEv['babak'] ?? '', 'Final') === 0) $counts['final']++;
            if (strcasecmp($aEv['babak'] ?? '', 'Penyisihan') === 0) $counts['penyisihan']++;
        }

        // Ambil peserta dan hasil untuk setiap event, terapkan aturan Final vs Penyisihan
        $bukuAcaraData = [];
        $pagiEvents = [];
        $soreEvents = [];

        foreach ($events as $ev) {
            $peserta = $this->pesertaModel->getPesertaByEvent((int)$ev['id']);

            // Kelompokkan peserta berdasarkan Heat
            $heats = [];
            foreach ($peserta as $p) {
                $hNum = (int)($p['heat'] ?: 1);
                $heats[$hNum][] = $p;
            }
            ksort($heats);

            // Default 1 Hit = langsung FINAL. Jika babak memang Final, pertahankan sebagai Final
            $isBabakFinal   = (strcasecmp($ev['babak'], 'Final') === 0);
            $namaLower      = strtolower($ev['nama_acara'] ?? '');
            $isField        = in_array(strtolower($ev['kategori_lomba'] ?? ''), ['lompat', 'lempar']) || (bool)preg_match('/(tolak|lempar|lompat|lontar|cakram|peluru|lembing)/i', $namaLower);
            $isLongDistance = (bool)preg_match('/(5\.?000|3\.?000)/i', $namaLower);

            // Jika langsung Final (5000m, nomor lapangan, atau babak Final dengan 1 hit/peserta <= 25):
            if ($isBabakFinal && ($isField || $isLongDistance || count($heats) <= 1)) {
                $isFinal = true;
                $heats = []; // Satukan dalam 1 tabel FINAL tanpa sub-heat terpisah
            } elseif ($isBabakFinal) {
                // Final yang dibagi beberapa seri berimbang (misal 1000m / 1500m putra yang banyak peserta)
                $isFinal = true;
            } else {
                $isFinal = (count($heats) <= 1);
                if ($isFinal) {
                    $heats = [];
                }
            }

            $bukuAcaraData[] = [
                'event'    => $ev,
                'peserta'  => $peserta,
                'is_final' => $isFinal,
                'heats'    => $heats,
            ];

            // Kelompokkan sesi Pagi vs Sore untuk Susunan Acara
            $waktu = strtolower($ev['jadwal_waktu'] ?? '');
            if (strpos($waktu, 'sore') !== false || strpos($waktu, 'siang') !== false || preg_match('/(1[2-9]|20)\./', $waktu)) {
                $soreEvents[] = $ev;
            } else {
                $pagiEvents[] = $ev;
            }
        }

        $this->data['title']          = 'Buku Acara & Susunan Acara Resmi';
        $this->data['bukuAcaraData']  = $bukuAcaraData;
        $this->data['pagiEvents']     = $pagiEvents;
        $this->data['soreEvents']     = $soreEvents;
        $this->data['sortBy']         = $sortBy;
        $this->data['sortDir']        = $sortDir;
        $this->data['filterKategori'] = $filterKategori;
        $this->data['filterGender']   = $filterGender;
        $this->data['filterBabak']    = $filterBabak;
        $this->data['counts']         = $counts;

        // Pastikan file statis Excel selalu terupdate dan siap unduh
        $staticDir  = FCPATH . 'downloads';
        $staticFile = $staticDir . DIRECTORY_SEPARATOR . 'BUKU ACARA ATLETIK KARES 2026.xlsx';
        if (!file_exists($staticFile) || (time() - filemtime($staticFile) > 600)) {
            try {
                $exporter    = new ExcelExporter();
                $spreadsheet = $exporter->generateBukuAcara((int)($kejuaraanId ?: 1));
                if (!is_dir($staticDir)) {
                    @mkdir($staticDir, 0777, true);
                }
                $writer = new Xlsx($spreadsheet);
                $writer->save($staticFile);
            } catch (\Throwable $e) {
                log_message('error', 'Auto generate excel failed: ' . $e->getMessage());
            }
        }

        return view('buku_acara/index', $this->data);
    }

    /**
     * Update cepat pengaturan babak (Final / Penyisihan), jadwal sesi (Pagi / Sore), dan waktu acara
     */
    public function updateJadwal()
    {
        $eventId      = (int)$this->request->getPost('event_id');
        $babak        = trim($this->request->getPost('babak') ?? 'Final');
        $jadwalHari   = trim($this->request->getPost('jadwal_hari') ?? '');
        $sesi         = trim($this->request->getPost('sesi') ?? 'Pagi');
        $jam          = trim($this->request->getPost('jam') ?? '08.00');
        $rearrange    = $this->request->getPost('auto_rearrange') !== null ? (bool)$this->request->getPost('auto_rearrange') : true;
        $lanesPerHeat = (int)($this->request->getPost('lanes_per_heat') ?? 8);
        if ($lanesPerHeat < 2) $lanesPerHeat = 2;
        if ($lanesPerHeat > 8) $lanesPerHeat = 8;

        if ($eventId > 0) {
            $jadwalWaktu = $sesi . ' ' . $jam;
            $updateData = [
                'babak'        => $babak,
                'jadwal_waktu' => $jadwalWaktu,
            ];
            if (!empty($jadwalHari)) {
                $updateData['jadwal_hari'] = $jadwalHari;
            }

            $this->eventModel->update($eventId, $updateData);

            // Jika diminta atur ulang peserta atau penyesuaian babak
            if ($rearrange) {
                $pesertaList = $this->pesertaModel->where('event_id', $eventId)->orderBy('id', 'ASC')->findAll();
                $totalPeserta = count($pesertaList);

                $evDetails = $this->eventModel->getEventWithDetails($eventId);
                $namaAcara = strtolower($evDetails['nama_acara'] ?? '');
                $kat = strtolower($evDetails['kategori_lomba'] ?? '');
                $isField = in_array($kat, ['lompat', 'lempar']) || (bool)preg_match('/(tolak|lempar|lompat|lontar|cakram|peluru|lembing)/i', $namaAcara);
                $isDistance = (bool)preg_match('/(1\.?000|1\.?500|5\.?000|3\.?000)/i', $namaAcara);
                $is5000 = (bool)preg_match('/(5\.?000|3\.?000)/i', $namaAcara);
                $isFinalBabak = (strcasecmp($babak, 'Final') === 0);

                if ($isField || ($isFinalBabak && ($is5000 || $totalPeserta <= 25))) {
                    // Nomor Lapangan & Lari Jarak Jauh (5000m / peserta <= 25): 1 Hit bersama (Langsung FINAL), jangan dibagi hit!
                    $lane = 1;
                    $order = 1;
                    foreach ($pesertaList as $p) {
                        $this->pesertaModel->update($p['id'], [
                            'heat'    => 1,
                            'lane'    => (string)$lane++,
                            'no_urut' => $order++,
                        ]);
                    }
                } elseif ($isFinalBabak && $isDistance) {
                    // Jarak jauh/menengah (1000m, 1500m) dengan peserta banyak (>25 orang) yang langsung Final
                    // Dibagi 2 seri/hit berimbang (misal 42 -> 21 & 21, 39 -> 20 & 19), BUKAN dibagi per 8!
                    $numHeats = 2;
                    $basePerHeat = (int)floor($totalPeserta / $numHeats);
                    $remainder   = $totalPeserta % $numHeats;

                    $curPIdx = 0;
                    $order = 1;
                    for ($h = 1; $h <= $numHeats; $h++) {
                        $heatCap = $basePerHeat + ($h <= $remainder ? 1 : 0);
                        $lane = 1;
                        for ($c = 0; $c < $heatCap; $c++) {
                            if ($curPIdx < $totalPeserta) {
                                $p = $pesertaList[$curPIdx++];
                                $this->pesertaModel->update($p['id'], [
                                    'heat'    => $h,
                                    'lane'    => (string)$lane++,
                                    'no_urut' => $order++,
                                ]);
                            }
                        }
                    }
                } else {
                    // Nomor Lintasan (Sprint / Penyisihan): Bagi rata seimbang ke beberapa Hit
                    $capPerHeat = min(8, $lanesPerHeat);
                    if ($totalPeserta <= $capPerHeat) {
                        $lane = 1;
                        $order = 1;
                        foreach ($pesertaList as $p) {
                            $this->pesertaModel->update($p['id'], [
                                'heat'    => 1,
                                'lane'    => (string)$lane++,
                                'no_urut' => $order++,
                            ]);
                        }
                    } else {
                        $numHeats = (int)ceil($totalPeserta / $capPerHeat);
                        $basePerHeat = (int)floor($totalPeserta / $numHeats);
                        $remainder = $totalPeserta % $numHeats;

                        $curPIdx = 0;
                        $order = 1;
                        for ($h = 1; $h <= $numHeats; $h++) {
                            $heatCap = $basePerHeat + ($h <= $remainder ? 1 : 0);
                            $lane = 1;
                            for ($c = 0; $c < $heatCap; $c++) {
                                if ($curPIdx < $totalPeserta) {
                                    $p = $pesertaList[$curPIdx++];
                                    $this->pesertaModel->update($p['id'], [
                                        'heat'    => $h,
                                        'lane'    => (string)$lane++,
                                        'no_urut' => $order++,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Hapus cache file statis agar langsung di-generate ulang dengan data terbaru
            $staticFile = FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx';
            if (file_exists($staticFile)) {
                @unlink($staticFile);
            }
            @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

            $tab = $this->request->getPost('tab') ?: 'susunan';
            return redirect()->to(base_url('buku-acara?tab=' . $tab))
                ->with('success', "Pengaturan acara berhasil diperbarui (Babak: {$babak}, Sesi: {$sesi} {$jam}).");
        }

        return redirect()->to(base_url('buku-acara'))->with('error', 'Acara tidak valid.');
    }

    /**
     * Tampilan cetak buku acara (print-friendly) lengkap dengan Cover dan Susunan Acara
     */
    public function cetak()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;

        $sortBy         = $this->request->getGet('sort') ?? 'nomor_acara';
        $sortDir        = (strtolower($this->request->getGet('dir') ?? 'asc') === 'desc') ? 'desc' : 'asc';
        $filterKategori = $this->request->getGet('kategori') ?? '';
        $filterGender   = $this->request->getGet('gender') ?? '';
        $filterBabak    = $this->request->getGet('babak') ?? '';

        $filters = [];
        if (!empty($filterKategori)) $filters['kategori'] = $filterKategori;
        if (!empty($filterGender))   $filters['gender']   = $filterGender;
        if (!empty($filterBabak))    $filters['babak']    = $filterBabak;

        $events = $this->eventModel->getEventsWithDetails($kejuaraanId, $sortBy, $filters, $sortDir);

        $bukuAcaraData = [];
        $pagiEvents = [];
        $soreEvents = [];

        foreach ($events as $ev) {
            $peserta = $this->pesertaModel->getPesertaByEvent((int)$ev['id']);
            $isBabakFinal   = (strcasecmp($ev['babak'], 'Final') === 0);
            $namaLower      = strtolower($ev['nama_acara'] ?? '');
            $isField        = in_array(strtolower($ev['kategori_lomba'] ?? ''), ['lompat', 'lempar']) || (bool)preg_match('/(tolak|lempar|lompat|lontar|cakram|peluru|lembing)/i', $namaLower);
            $isLongDistance = (bool)preg_match('/(5\.?000|3\.?000)/i', $namaLower);

            $heats = [];
            foreach ($peserta as $p) {
                $hNum = (int)($p['heat'] ?: 1);
                $heats[$hNum][] = $p;
            }
            ksort($heats);

            if ($isBabakFinal && ($isField || $isLongDistance || count($heats) <= 1)) {
                $isFinal = true;
                $heats = [];
            } elseif ($isBabakFinal) {
                $isFinal = true;
            } else {
                $isFinal = (count($heats) <= 1);
                if ($isFinal) {
                    $heats = [];
                }
            }

            $bukuAcaraData[] = [
                'event'    => $ev,
                'peserta'  => $peserta,
                'is_final' => $isFinal,
                'heats'    => $heats,
            ];

            $waktu = strtolower($ev['jadwal_waktu'] ?? '');
            if (strpos($waktu, 'sore') !== false || strpos($waktu, 'siang') !== false || preg_match('/(1[2-9]|20)\./', $waktu)) {
                $soreEvents[] = $ev;
            } else {
                $pagiEvents[] = $ev;
            }
        }

        $this->data['title']          = 'Cetak Buku Acara — ' . ($this->activeKejuaraan['nama'] ?? 'Kejurwil');
        $this->data['bukuAcaraData']  = $bukuAcaraData;
        $this->data['pagiEvents']     = $pagiEvents;
        $this->data['soreEvents']     = $soreEvents;
        $this->data['sortBy']         = $sortBy;
        $this->data['sortDir']        = $sortDir;
        $this->data['filterKategori'] = $filterKategori;

        return view('buku_acara/cetak', $this->data);
    }

    /**
     * Unduh buku acara dalam format file Excel (.xlsx) dalam SATU SHEET UTUH:
     * - Cover (Halaman 1)
     * - Susunan Acara (Halaman 2)
     * - Buku Acara Peserta (Final & Heat 1, 2, 3...)
     */
    public function exportExcel()
    {
        $kejuaraanId    = $this->activeKejuaraan['id'] ?? 1;
        $sortBy         = $this->request->getGet('sort') ?? 'nomor_acara';
        $sortDir        = (strtolower($this->request->getGet('dir') ?? 'asc') === 'desc') ? 'desc' : 'asc';
        $filterKategori = $this->request->getGet('kategori') ?? '';
        $filterGender   = $this->request->getGet('gender') ?? '';
        $filterBabak    = $this->request->getGet('babak') ?? '';

        $filters = [];
        if (!empty($filterKategori)) $filters['kategori'] = $filterKategori;
        if (!empty($filterGender))   $filters['gender']   = $filterGender;
        if (!empty($filterBabak))    $filters['babak']    = $filterBabak;

        $exporter = new ExcelExporter();
        $spreadsheet = $exporter->generateBukuAcara($kejuaraanId, $sortBy, $filters, $sortDir);

        $suffix = ($sortBy !== 'nomor_acara') ? '_Sort_' . ucfirst($sortBy) : '';
        $filename = 'BUKU ACARA ATLETIK KARES 2026' . $suffix . '.xlsx';

        // Simpan file ke server
        $downloadDir = FCPATH . 'downloads';
        if (!is_dir($downloadDir)) {
            @mkdir($downloadDir, 0777, true);
        }

        $filePath = $downloadDir . DIRECTORY_SEPARATOR . $filename;
        $writer   = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $fileSize = filesize($filePath);

        // Bersihkan seluruh output buffer agar tidak ada byte tersisa
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Header standar resmi untuk unduhan file Excel pada browser Windows / Edge / Chrome
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $fileSize);

        readfile($filePath);
        exit;
    }

    /**
     * Sinkronisasi data resmi dari file template Excel (0FIX BUKU ACARA ATLETIK KARES 2026.xlsx)
     * Mendukung pembacaan file default di root dan opsi unggah file revisi baru.
     */
    public function syncOfficial()
    {
        $kejuaraanId = (int)($this->activeKejuaraan['id'] ?? 1);
        $syncService = new BukuAcaraSyncService();

        $uploadedFile = $this->request->getFile('excel_file');
        $targetPath = '';

        if ($uploadedFile && $uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
            $newName = 'BukuAcara_Uploaded_' . date('Ymd_His') . '.' . $uploadedFile->getExtension();
            $uploadDir = WRITEPATH . 'uploads';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $uploadedFile->move($uploadDir, $newName);
            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;
        } else {
            $targetPath = $syncService->getDefaultFilePath();
        }

        $result = $syncService->sync($targetPath, $kejuaraanId);

        if ($result['success'] ?? false) {
            return redirect()->to(base_url('buku-acara'))
                ->with('success', $result['message']);
        }

        return redirect()->to(base_url('buku-acara'))
            ->with('error', $result['message'] ?? 'Gagal melakukan sinkronisasi file buku acara.');
    }
}
