<?php

namespace App\Controllers;

use App\Libraries\ExcelImporter;
use App\Models\AtletModel;
use App\Models\EventModel;
use App\Models\KelompokUmurModel;
use App\Models\NomorLombaModel;
use App\Models\PesertaEventModel;
use App\Models\SekolahModel;

class Atlet extends BaseController
{
    protected AtletModel $atletModel;
    protected SekolahModel $sekolahModel;
    protected KelompokUmurModel $kuModel;
    protected EventModel $eventModel;
    protected PesertaEventModel $pesertaModel;
    protected NomorLombaModel $lombaModel;

    public function __construct()
    {
        $this->atletModel   = new AtletModel();
        $this->sekolahModel = new SekolahModel();
        $this->kuModel      = new KelompokUmurModel();
        $this->eventModel   = new EventModel();
        $this->pesertaModel = new PesertaEventModel();
        $this->lombaModel   = new NomorLombaModel();
    }

    public function index()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $sekolahId   = $this->request->getGet('sekolah_id') ? (int)$this->request->getGet('sekolah_id') : null;
        $search      = $this->request->getGet('search');

        $this->data['title']       = 'Data Atlet Terdaftar';

        // Normalisasi otomatis nomor dada ke format 3 digit (001, 002, dst) jika ada yang kurang dari 3 digit
        if ($kejuaraanId) {
            $unpadded = $this->atletModel->db->table('atlet')
                ->where('kejuaraan_id', $kejuaraanId)
                ->where('no_dada IS NOT NULL')
                ->where('no_dada !=', '')
                ->where('LENGTH(no_dada) <', 3)
                ->get()->getResultArray();

            if (!empty($unpadded)) {
                foreach ($unpadded as $up) {
                    if (ctype_digit(trim($up['no_dada']))) {
                        $this->atletModel->update($up['id'], [
                            'no_dada' => str_pad(trim($up['no_dada']), 3, '0', STR_PAD_LEFT)
                        ]);
                    }
                }
                @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
                @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');
            }

            // Bersihkan nomor dada yang memiliki leading apostrophe dari ekspor Excel lama
            $withQuote = $this->atletModel->db->table('atlet')
                ->where('kejuaraan_id', $kejuaraanId)
                ->where('no_dada IS NOT NULL')
                ->where("no_dada LIKE \"'%\"")
                ->get()->getResultArray();

            foreach ($withQuote as $q) {
                $cleanDada = ltrim($q['no_dada'], "'");
                if ($cleanDada !== $q['no_dada']) {
                    $this->atletModel->update($q['id'], ['no_dada' => $cleanDada]);
                }
            }
        }

        $this->data['atlets']      = $this->atletModel->getAtletWithDetails($kejuaraanId, $sekolahId, $search);
        $this->data['sekolahs']    = $this->sekolahModel->orderBy('nama', 'ASC')->findAll();
        $this->data['selectedSekolah'] = $sekolahId;

        // Ambil daftar event per atlet untuk ditampilkan di tabel
        $atletIds = array_column($this->data['atlets'], 'id');
        $atletEvents = [];
        if (!empty($atletIds)) {
            $eventsRaw = $this->eventModel->db->table('peserta_event pe')
                ->select('pe.atlet_id, e.id as event_id, e.nomor_acara, e.nama_acara, e.babak')
                ->join('event e', 'e.id = pe.event_id')
                ->whereIn('pe.atlet_id', $atletIds)
                ->get()
                ->getResultArray();

            foreach ($eventsRaw as $er) {
                $atletEvents[$er['atlet_id']][] = $er;
            }
        }
        $this->data['atletEvents'] = $atletEvents;

        return view('atlet/index', $this->data);
    }

    public function create()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;

        $this->data['title']    = 'Daftarkan Atlet Baru';
        $this->data['atlet']    = null;
        $this->data['sekolahs'] = $this->sekolahModel->orderBy('nama', 'ASC')->findAll();
        $this->data['kus']      = $this->kuModel->orderBy('usia_min', 'ASC')->findAll();
        $this->data['events']   = $this->eventModel->getEventsWithDetails($kejuaraanId);
        $this->data['registeredEventIds'] = [];

        return view('atlet/form', $this->data);
    }

    public function edit(int $id)
    {
        $atlet = $this->atletModel->find($id);
        if (!$atlet) {
            return redirect()->to(base_url('atlet'))->with('error', 'Data atlet tidak ditemukan.');
        }

        $kejuaraanId = $atlet['kejuaraan_id'];

        $this->data['title']    = 'Edit Data Atlet';
        $this->data['atlet']    = $atlet;
        $this->data['sekolahs'] = $this->sekolahModel->orderBy('nama', 'ASC')->findAll();
        $this->data['kus']      = $this->kuModel->orderBy('usia_min', 'ASC')->findAll();
        $this->data['events']   = $this->eventModel->getEventsWithDetails($kejuaraanId);

        // Ambil event yang saat ini diikuti oleh atlet
        $curEvents = $this->pesertaModel->where('atlet_id', $id)->findAll();
        $this->data['registeredEventIds'] = array_column($curEvents, 'event_id');

        return view('atlet/form', $this->data);
    }

    public function save(?int $id = null)
    {
        $rules = [
            'nama'       => 'required|min_length[2]|max_length[150]',
            'sekolah_id' => 'required',
            'gender'     => 'required|in_list[L,P]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;

        // Tangani input kontingen/sekolah (ID angka atau nama baru jika diketik manual)
        $sekolahInput = trim($this->request->getPost('sekolah_id') ?? '');
        if (is_numeric($sekolahInput)) {
            $sekolahId = (int)$sekolahInput;
        } else {
            $sekolahId = $this->sekolahModel->getOrCreate($sekolahInput);
        }

        $tglLahir = $this->request->getPost('tanggal_lahir') ?: null;
        $kuId     = $this->request->getPost('kelompok_umur_id') ?: null;

        // Hitung KU dinamis otomatis dari tanggal lahir
        if ($tglLahir) {
            $autoKU = $this->kuModel->hitungKUOtomatis($tglLahir, $this->activeKejuaraan['tanggal_mulai'] ?? null);
            if ($autoKU) {
                $kuId = $autoKU;
            }
        }

        $atletData = [
            'kejuaraan_id'     => $kejuaraanId,
            'sekolah_id'       => $sekolahId,
            'no_dada'          => trim($this->request->getPost('no_dada') ?? '') ?: null,
            'nama'             => trim($this->request->getPost('nama')),
            'gender'           => $this->request->getPost('gender'),
            'tanggal_lahir'    => $tglLahir,
            'kelompok_umur_id' => $kuId,
            'catatan'          => $this->request->getPost('catatan'),
        ];

        if ($id) {
            $this->atletModel->update($id, $atletData);
            $atletId = $id;
            $msg = 'Data atlet berhasil diperbarui.';
        } else {
            $atletId = (int)$this->atletModel->insert($atletData);
            $msg = 'Atlet baru berhasil didaftarkan.';
        }

        // Multi-event handling: simpan nomor lomba / acara yang diikuti
        $eventIds = $this->request->getPost('event_ids') ?? [];
        if (!is_array($eventIds)) $eventIds = [];

        // Hapus event lama yang di-uncheck
        $existingRegistrations = $this->pesertaModel->where('atlet_id', $atletId)->findAll();
        foreach ($existingRegistrations as $reg) {
            if (!in_array($reg['event_id'], $eventIds)) {
                $this->pesertaModel->delete($reg['id']);
            }
        }

        // Tambah event baru yang dipilih
        $existingEventIds = array_column($existingRegistrations, 'event_id');
        foreach ($eventIds as $eId) {
            $eId = (int)$eId;
            if ($eId > 0 && !in_array($eId, $existingEventIds)) {
                $this->pesertaModel->insert([
                    'event_id' => $eId,
                    'atlet_id' => $atletId,
                    'heat'     => 1,
                ]);
            }
        }

        return redirect()->to(base_url('atlet'))->with('success', $msg);
    }

    public function delete(int $id)
    {
        // Hapus pendaftaran event terlebih dahulu
        $this->pesertaModel->where('atlet_id', $id)->delete();
        $this->atletModel->delete($id);

        return redirect()->to(base_url('atlet'))->with('success', 'Data atlet berhasil dihapus.');
    }

    public function import()
    {
        $this->data['title'] = 'Import Data Atlet dari Excel';
        return view('atlet/import', $this->data);
    }

    public function processImport()
    {
        $file = $this->request->getFile('excel_file');
        $importType = $this->request->getPost('import_type') ?? 'mentahan';

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau belum dipilih.');
        }

        $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;

        $tempPath = $file->getTempName();
        $importer = new ExcelImporter();

        try {
            if ($importType === 'no_dada') {
                $result = $importer->importNomorDada($tempPath, $kejuaraanId);
                $msg = "Nomor dada berhasil diperbarui untuk {$result['total_updated']} atlet.";
            } else {
                $result = $importer->importMentahan($tempPath, $kejuaraanId);
                $msg = "Import berhasil! {$result['atlet_baru']} atlet baru, {$result['atlet_terdaftar']} atlet update, {$result['pendaftaran_event']} entri nomor lomba dibuat.";
            }
            return redirect()->to(base_url('atlet'))->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Update cepat No Dada atlet via AJAX / modal
     */
    public function updateNoDada()
    {
        $atletId = (int)$this->request->getPost('atlet_id');
        $noDada  = trim($this->request->getPost('no_dada') ?? '');

        if ($atletId > 0) {
            $this->atletModel->update($atletId, [
                'no_dada' => !empty($noDada) ? $noDada : null,
            ]);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'No Dada berhasil diperbarui.',
                'no_dada' => !empty($noDada) ? $noDada : null,
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status'  => 'error',
            'message' => 'Atlet tidak valid.',
        ]);
    }

    /**
     * Generate Nomor Dada Otomatis Berkesinambungan Berdasarkan Sekolah
     * Contoh: SMP 8 dapat nomor 1 - 20, lalu SMP 9 lanjut nomor 21 - 30, dst.
     */
    public function generateNoDada()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        if (!$kejuaraanId) {
            return redirect()->to(base_url('atlet'))->with('error', 'Tidak ada kejuaraan aktif.');
        }

        $startNumber   = max(1, (int)($this->request->getPost('start_number') ?? 1));
        $sortSekolah   = $this->request->getPost('sort_sekolah') ?? 'nama_asc';
        $sortAtlet     = $this->request->getPost('sort_atlet') ?? 'nama_asc';
        $numFormat     = $this->request->getPost('number_format') ?? 'pad3';
        $sekolahScope  = $this->request->getPost('sekolah_scope') ?? 'all';
        $overwriteMode = $this->request->getPost('overwrite_mode') ?? 'all';

        $builder = $this->atletModel->db->table('atlet a')
            ->select('a.id, a.nama as nama_atlet, a.no_dada, a.gender, a.sekolah_id, s.nama as nama_sekolah, s.jenjang, ku.nama as nama_ku')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = a.kelompok_umur_id', 'left')
            ->where('a.kejuaraan_id', $kejuaraanId);

        if ($sekolahScope !== 'all' && is_numeric($sekolahScope) && (int)$sekolahScope > 0) {
            $builder->where('a.sekolah_id', (int)$sekolahScope);
        }

        // Urutan Sekolah
        if ($sortSekolah === 'jenjang_nama') {
            $builder->orderBy("CASE 
                WHEN s.jenjang = 'PAUD/TK' THEN 1 
                WHEN s.jenjang = 'SD/MI' THEN 2 
                WHEN s.jenjang = 'SMP/MTs' THEN 3 
                WHEN s.jenjang = 'SMA/SMK/MA' THEN 4 
                ELSE 5 END", 'ASC');
            $builder->orderBy('s.nama', 'ASC');
        } else {
            $builder->orderBy('s.nama', 'ASC');
        }

        // Urutan Atlet di dalam Sekolah
        if ($sortAtlet === 'gender_nama') {
            $builder->orderBy('a.gender', 'ASC'); // L dulu baru P
            $builder->orderBy('a.nama', 'ASC');
        } elseif ($sortAtlet === 'ku_nama') {
            $builder->orderBy('ku.usia_min', 'ASC');
            $builder->orderBy('a.nama', 'ASC');
        } else {
            $builder->orderBy('a.nama', 'ASC');
        }

        $atlets = $builder->get()->getResultArray();

        if (empty($atlets)) {
            return redirect()->to(base_url('atlet'))->with('error', 'Tidak ada atlet yang ditemukan untuk digenerate nomor dada.');
        }

        $currentNumber = $startNumber;
        $updatedCount  = 0;
        $schoolRanges  = []; // [ sekolah_nama => ['start' => X, 'end' => Y, 'count' => Z] ]

        foreach ($atlets as $a) {
            $schName = !empty($a['nama_sekolah']) ? $a['nama_sekolah'] : 'Independen / Tanpa Sekolah';

            // Jika mode hanya atlet kosong dan atlet sudah punya nomor dada
            if ($overwriteMode === 'empty_only' && !empty($a['no_dada'])) {
                continue;
            }

            // Format nomor dada
            if ($numFormat === 'pad3') {
                $bibStr = str_pad((string)$currentNumber, 3, '0', STR_PAD_LEFT);
            } elseif ($numFormat === 'pad4') {
                $bibStr = str_pad((string)$currentNumber, 4, '0', STR_PAD_LEFT);
            } else {
                $bibStr = (string)$currentNumber;
            }

            $this->atletModel->update($a['id'], ['no_dada' => $bibStr]);
            $updatedCount++;

            if (!isset($schoolRanges[$schName])) {
                $schoolRanges[$schName] = [
                    'start' => $bibStr,
                    'end'   => $bibStr,
                    'count' => 1,
                ];
            } else {
                $schoolRanges[$schName]['end'] = $bibStr;
                $schoolRanges[$schName]['count']++;
            }

            $currentNumber++;
        }

        // Hapus cache file buku acara excel
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

        // Ringkasan alokasi per sekolah
        $summaryParts = [];
        $i = 0;
        foreach ($schoolRanges as $sName => $info) {
            $i++;
            if ($i <= 6) {
                $summaryParts[] = "{$sName}: No. {$info['start']}-{$info['end']} ({$info['count']} atlet)";
            }
        }
        if (count($schoolRanges) > 6) {
            $sisa = count($schoolRanges) - 6;
            $summaryParts[] = "...dan {$sisa} sekolah lainnya";
        }

        $endNumber = $currentNumber - 1;
        $msg = "Nomor dada berhasil digenerate berkesinambungan per sekolah untuk {$updatedCount} atlet (Mulai No. {$startNumber} s/d {$endNumber}). " . implode('; ', $summaryParts);

        return redirect()->to(base_url('atlet'))->with('success', $msg);
    }

    /**
     * Halaman Template Desain & Cetak Nomor Lomba / Nomor Dada Atlet
     */
    public function cetakNomorLomba()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        if (!$kejuaraanId) {
            return redirect()->to(base_url('atlet'))->with('error', 'Tidak ada kejuaraan aktif.');
        }

        $sekolahId = $this->request->getGet('sekolah_id') ? (int)$this->request->getGet('sekolah_id') : null;
        $startNo   = $this->request->getGet('start_no');
        $endNo     = $this->request->getGet('end_no');
        $layout    = $this->request->getGet('layout') ?? '2_per_page'; // 2_per_page (A4 Landscape) or 1_per_page (A5)

        $builder = $this->atletModel->db->table('atlet a')
            ->select('a.*, s.nama as nama_sekolah, s.jenjang, s.alamat as alamat_sekolah, ku.nama as nama_ku')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = a.kelompok_umur_id', 'left')
            ->where('a.kejuaraan_id', $kejuaraanId)
            ->where('a.no_dada IS NOT NULL')
            ->where('a.no_dada !=', '');

        if ($sekolahId) {
            $builder->where('a.sekolah_id', $sekolahId);
        }

        $atlets = $builder->orderBy('a.no_dada', 'ASC')->orderBy('a.nama', 'ASC')->get()->getResultArray();

        // Filter rentang nomor dada jika diinput
        if (!empty($startNo) || !empty($endNo)) {
            $filtered = [];
            $sInt = !empty($startNo) ? (int)$startNo : 1;
            $eInt = !empty($endNo) ? (int)$endNo : 999999;
            foreach ($atlets as $a) {
                $nInt = (int)$a['no_dada'];
                if ($nInt >= $sInt && $nInt <= $eInt) {
                    $filtered[] = $a;
                }
            }
            $atlets = $filtered;
        }

        $this->data['title']           = 'Desain & Cetak Nomor Lomba (Nomor Dada)';
        $this->data['kejuaraan']       = $this->activeKejuaraan;
        $this->data['atlets']          = $atlets;
        $this->data['sekolahs']        = $this->sekolahModel->orderBy('nama', 'ASC')->findAll();
        $this->data['selectedSekolah'] = $sekolahId;
        $this->data['startNo']         = $startNo;
        $this->data['endNo']           = $endNo;
        $this->data['layout']          = $layout;

        return view('atlet/cetak_nomor_lomba', $this->data);
    }

    /**
     * Upload template background nomor dada kustom
     */
    public function uploadTemplateNoDada()
    {
        $file = $this->request->getFile('template_bg');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Format file harus JPG, PNG, atau WebP.']);
            }
            $dir = FCPATH . 'uploads/no_dada/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = 'bib_template_' . time() . '.' . $ext;
            $file->move($dir, $filename);

            return $this->response->setJSON([
                'status'   => 'success',
                'url'      => base_url('uploads/no_dada/' . $filename),
                'filename' => $filename,
            ]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengunggah file. Pastikan file gambar valid.']);
    }
}
