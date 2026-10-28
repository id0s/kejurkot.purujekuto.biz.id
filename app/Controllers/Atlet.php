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

        $sessionRole = session()->get('role');
        $sessionCaborId = session()->get('cabor_id');
        $filterCaborId = ($sessionRole === 'admin_cabor' && $sessionCaborId) ? (int)$sessionCaborId : null;

        $this->data['atlets']          = $this->atletModel->getAtletWithDetails($kejuaraanId, $sekolahId, $search, $filterCaborId);
        $this->data['sekolahs']        = $this->sekolahModel->orderBy('nama', 'ASC')->findAll();
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

        $aktaUrl = trim((string)$this->request->getPost('akta_url'));
        if (!empty($aktaUrl)) {
            $atletData['akta_url'] = $aktaUrl;
        }

        $fileAkta = $this->request->getFile('file_akta');
        if ($fileAkta && $fileAkta->isValid() && !$fileAkta->hasMoved()) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            if (in_array($fileAkta->getMimeType(), $allowedMimes)) {
                $storageDir = WRITEPATH . 'uploads/akta';
                if (!is_dir($storageDir)) {
                    mkdir($storageDir, 0755, true);
                }
                $ext = strtolower($fileAkta->getClientExtension());
                $safeName = 'akta_' . ($id ?: time()) . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($atletData['nama'])) . '.' . $ext;
                $fileAkta->move($storageDir, $safeName, true);
                $atletData['akta_file'] = $safeName;
            }
        }

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
                // Beri nomor giliran/lintasan berikutnya secara berurutan
                $maxUrut = $this->pesertaModel->where('event_id', $eId)->selectMax('no_urut')->first();
                $nextUrut = ($maxUrut && !empty($maxUrut['no_urut'])) ? ((int)$maxUrut['no_urut'] + 1) : 1;

                $this->pesertaModel->insert([
                    'event_id' => $eId,
                    'atlet_id' => $atletId,
                    'heat'     => 1,
                    'lane'     => (string)$nextUrut,
                    'no_urut'  => $nextUrut,
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

    /**
     * Halaman Pendaftaran Massal Atlet & Multi-Nomor Lomba
     */
    public function batchRegister()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;
        $sessionRole = session()->get('role');
        $sessionCaborId = session()->get('cabor_id');

        $caborModel = new \App\Models\CaborModel();
        $caborList = $caborModel->getActiveCabor();

        $selectedCaborId = ($sessionRole === 'admin_cabor' && $sessionCaborId) ? (int)$sessionCaborId : (int)($this->request->getGet('cabor_id') ?? 1);

        $sekolahs = $this->sekolahModel->orderBy('nama', 'ASC')->findAll();
        $kelompokUmurs = $this->kuModel->orderBy('usia_min', 'ASC')->findAll();

        // Ambil daftar event/nomor lomba untuk kejuaraan ini
        $events = $this->eventModel
            ->select('event.*, nomor_lomba.nama as nama_lomba, nomor_lomba.kategori as kategori_lomba, nomor_lomba.cabor_id, kelompok_umur.nama as nama_ku, kelompok_umur.usia_min, kelompok_umur.usia_max')
            ->join('nomor_lomba', 'nomor_lomba.id = event.nomor_lomba_id')
            ->join('kelompok_umur', 'kelompok_umur.id = event.kelompok_umur_id', 'left')
            ->where('event.kejuaraan_id', $kejuaraanId)
            ->orderBy('event.nomor_acara', 'ASC')
            ->findAll();

        return view('atlet/batch_register', [
            'title'           => 'Pendaftaran Massal Atlet & Multi-Lomba',
            'activeKejuaraan' => $this->activeKejuaraan,
            'sekolahs'        => $sekolahs,
            'kelompokUmurs'   => $kelompokUmurs,
            'events'          => $events,
            'caborList'       => $caborList,
            'selectedCaborId' => $selectedCaborId,
            'sessionRole'     => $sessionRole,
        ]);
    }

    /**
     * Proses Simpan Pendaftaran Massal Atlet & Multi-Lomba
     */
    public function batchRegisterSave()
    {
        $db = \Config\Database::connect();
        $kejuaraanId = (int)($this->activeKejuaraan['id'] ?? 1);

        $sessionRole = session()->get('role');
        $sessionCaborId = session()->get('cabor_id');
        $caborId = ($sessionRole === 'admin_cabor' && $sessionCaborId) ? (int)$sessionCaborId : (int)($this->request->getPost('cabor_id') ?? 1);

        // Kontingen / Sekolah handling
        $sekolahId = $this->request->getPost('sekolah_id');
        $namaSekolahBaru = trim($this->request->getPost('nama_sekolah_baru') ?? '');
        $jenjangBaru = trim($this->request->getPost('jenjang_sekolah_baru') ?? 'SD/MI');

        if ($sekolahId === 'new' || empty($sekolahId)) {
            if (empty($namaSekolahBaru)) {
                return redirect()->back()->withInput()->with('error', 'Nama kontingen / sekolah wajib diisi.');
            }
            $sekolahId = $this->sekolahModel->getOrCreate($namaSekolahBaru, $jenjangBaru);
        } else {
            $sekolahId = (int)$sekolahId;
        }

        $atlets = $this->request->getPost('atlets');
        if (!is_array($atlets) || empty($atlets)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada baris atlet yang dikirim.');
        }

        $uploadedFiles = $this->request->getFiles();

        $insertedAtletCount = 0;
        $insertedEventCount = 0;
        $errors = [];

        $db->transStart();

        foreach ($atlets as $index => $row) {
            $nama = trim($row['nama'] ?? '');
            if (empty($nama)) {
                continue; // Lewati baris kosong
            }

            $gender = in_array($row['gender'] ?? '', ['L', 'P']) ? $row['gender'] : 'L';
            $tglLahir = !empty($row['tanggal_lahir']) ? $row['tanggal_lahir'] : null;

            // Hitung KU
            $kuId = !empty($row['kelompok_umur_id']) ? (int)$row['kelompok_umur_id'] : null;
            if (!$kuId && $tglLahir) {
                $kuId = $this->kuModel->hitungKUOtomatis($tglLahir);
            }

            // No Dada
            $noDada = !empty($row['no_dada']) ? trim($row['no_dada']) : null;
            if ($noDada && is_numeric($noDada)) {
                $noDada = str_pad($noDada, 3, '0', STR_PAD_LEFT);
            }

            $aktaUrl = trim($row['akta_url'] ?? '');
            $aktaFile = null;
            $aktaStatus = !empty($aktaUrl) ? 'menunggu_verifikasi' : 'belum_upload';

            // Cek file upload akta untuk baris ini
            if (isset($uploadedFiles['akta_files'][$index])) {
                $file = $uploadedFiles['akta_files'][$index];
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
                    if (in_array($file->getMimeType(), $allowedMimes)) {
                        $storageDir = WRITEPATH . 'uploads/akta';
                        if (!is_dir($storageDir)) {
                            mkdir($storageDir, 0755, true);
                        }
                        $ext = strtolower($file->getClientExtension());
                        $safeName = 'akta_' . time() . '_' . $index . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($nama)) . '.' . $ext;
                        $file->move($storageDir, $safeName, true);
                        $aktaFile = $safeName;
                        $aktaStatus = 'menunggu_verifikasi';
                    }
                }
            }

            $atletData = [
                'kejuaraan_id'            => $kejuaraanId,
                'cabor_id'                => $caborId,
                'sekolah_id'              => $sekolahId,
                'nama'                    => $nama,
                'gender'                  => $gender,
                'tanggal_lahir'           => $tglLahir,
                'kelompok_umur_id'        => $kuId,
                'no_dada'                 => $noDada,
                'akta_file'               => $aktaFile,
                'akta_url'                => !empty($aktaUrl) ? $aktaUrl : null,
                'akta_status'             => $aktaStatus,
                'catatan'                 => trim($row['catatan'] ?? ''),
            ];

            $atletId = (int)$this->atletModel->insert($atletData);
            if ($atletId) {
                $insertedAtletCount++;

                // Simpan pendaftaran multi-lomba
                $eventIds = $row['events'] ?? [];
                if (is_array($eventIds)) {
                    foreach ($eventIds as $eventId) {
                        $eventId = (int)$eventId;
                        if ($eventId > 0) {
                            $this->pesertaModel->insert([
                                'event_id' => $eventId,
                                'atlet_id' => $atletId,
                            ]);
                            $insertedEventCount++;
                        }
                    }
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false || $insertedAtletCount === 0) {
            return redirect()->back()->withInput()->with('error', 'Pendaftaran gagal disimpan atau tidak ada data atlet yang valid.');
        }

        $successMsg = "Berhasil mendaftarkan {$insertedAtletCount} atlet dengan total {$insertedEventCount} keikutsertaan nomor lomba!";
        return redirect()->to(base_url('atlet'))->with('success', $successMsg);
    }

    /**
     * Halaman Verifikasi Keabsahan Akta Kelahiran
     */
    public function verifikasiAkta()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;
        $sessionRole = session()->get('role');
        $sessionCaborId = session()->get('cabor_id');

        $statusFilter = $this->request->getGet('status');
        $sekolahId = $this->request->getGet('sekolah_id') ? (int)$this->request->getGet('sekolah_id') : null;
        $search = trim($this->request->getGet('search') ?? '');

        $builder = $this->atletModel
            ->select('atlet.*, sekolah.nama as nama_sekolah, kelompok_umur.nama as nama_ku, kelompok_umur.usia_min, kelompok_umur.usia_max, cabor.nama as cabor_nama')
            ->join('sekolah', 'sekolah.id = atlet.sekolah_id', 'left')
            ->join('kelompok_umur', 'kelompok_umur.id = atlet.kelompok_umur_id', 'left')
            ->join('cabor', 'cabor.id = atlet.cabor_id', 'left')
            ->where('atlet.kejuaraan_id', $kejuaraanId);

        if ($sessionRole === 'admin_cabor' && $sessionCaborId) {
            $builder->where('atlet.cabor_id', (int)$sessionCaborId);
        }

        if ($statusFilter && in_array($statusFilter, ['belum_upload', 'menunggu_verifikasi', 'valid', 'perlu_perbaikan'])) {
            $builder->where('atlet.akta_status', $statusFilter);
        }

        if ($sekolahId) {
            $builder->where('atlet.sekolah_id', $sekolahId);
        }

        if ($search) {
            $builder->groupStart()
                    ->like('atlet.nama', $search)
                    ->orLike('atlet.no_dada', $search)
                    ->orLike('sekolah.nama', $search)
                    ->groupEnd();
        }

        $atletList = $builder->orderBy('atlet.id', 'DESC')->findAll();

        // Hitung statistik akta
        $statBuilder = $this->atletModel->where('kejuaraan_id', $kejuaraanId);
        if ($sessionRole === 'admin_cabor' && $sessionCaborId) {
            $statBuilder->where('cabor_id', (int)$sessionCaborId);
        }
        $allStats = $statBuilder->findAll();

        $stats = [
            'total'               => count($allStats),
            'menunggu_verifikasi' => 0,
            'valid'               => 0,
            'perlu_perbaikan'     => 0,
            'belum_upload'        => 0,
        ];

        foreach ($allStats as $a) {
            $st = $a['akta_status'] ?? 'belum_upload';
            if (isset($stats[$st])) {
                $stats[$st]++;
            } else {
                $stats['belum_upload']++;
            }
        }

        $sekolahs = $this->sekolahModel->orderBy('nama', 'ASC')->findAll();

        return view('atlet/verifikasi_akta', [
            'title'        => 'Verifikasi Keabsahan Akta Kelahiran Atlet',
            'atletList'    => $atletList,
            'stats'        => $stats,
            'sekolahs'     => $sekolahs,
            'statusFilter' => $statusFilter,
            'sekolahId'    => $sekolahId,
            'search'       => $search,
        ]);
    }

    /**
     * AJAX/POST Update Status Verifikasi Akta
     */
    public function updateAktaStatus()
    {
        $atletId = (int)$this->request->getPost('atlet_id');
        $status = $this->request->getPost('akta_status');
        $catatan = trim($this->request->getPost('catatan') ?? '');

        if (!in_array($status, ['valid', 'perlu_perbaikan', 'menunggu_verifikasi', 'belum_upload'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Status tidak valid.']);
        }

        $atlet = $this->atletModel->find($atletId);
        if (!$atlet) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Atlet tidak ditemukan.']);
        }

        $this->atletModel->update($atletId, [
            'akta_status'             => $status,
            'akta_catatan_verifikasi' => $catatan,
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Status verifikasi akta berhasil diperbarui.',
            'new_status' => $status,
        ]);
    }

    /**
     * Tampilkan berkas akta kelahiran atlet secara aman (gambar/PDF)
     */
    public function viewAktaFile(int $atletId)
    {
        $atlet = $this->atletModel->find($atletId);
        if (!$atlet || empty($atlet['akta_file'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Dokumen tidak ditemukan.');
        }

        $filename = basename($atlet['akta_file']);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak valid.');
        }

        $baseDir = realpath(WRITEPATH . 'uploads/akta');
        $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . $filename);

        if (!$filePath || strpos($filePath, $baseDir) !== 0 || !is_file($filePath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Dokumen tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if (in_array($ext, ['jpg', 'jpeg'])) {
            $mime = 'image/jpeg';
        } elseif ($ext === 'png') {
            $mime = 'image/png';
        } elseif ($ext === 'webp') {
            $mime = 'image/webp';
        } elseif ($ext === 'pdf') {
            $mime = 'application/pdf';
        }

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody(file_get_contents($filePath));
    }
}

