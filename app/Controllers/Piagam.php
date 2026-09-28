<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\HasilModel;
use App\Models\PesertaEventModel;
use App\Models\PiagamTemplateModel;
use App\Models\PiagamCustomModel;

class Piagam extends BaseController
{
    protected EventModel $eventModel;
    protected HasilModel $hasilModel;
    protected PesertaEventModel $pesertaModel;
    protected PiagamTemplateModel $templateModel;
    protected PiagamCustomModel $customModel;

    public function __construct()
    {
        $this->eventModel    = new EventModel();
        $this->hasilModel    = new HasilModel();
        $this->pesertaModel  = new PesertaEventModel();
        $this->templateModel = new PiagamTemplateModel();
        $this->customModel   = new PiagamCustomModel();
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Pastikan tabel dan template bawaan siap
        try {
            $this->templateModel->initializeTable();
            $this->customModel->initializeTable();
            // Pastikan ada template default yang aktif (prioritas: official_federation)
            $hasActiveDef = $this->templateModel->where('is_active', 1)->where('is_default', 1)->first();
            if (!$hasActiveDef) {
                $fedTpl = $this->templateModel->where('tipe', 'official_federation')->first();
                $targetId = $fedTpl ? $fedTpl['id'] : 2;
                $this->templateModel->where('id !=', $targetId)->set(['is_default' => 0])->update();
                $this->templateModel->where('id', $targetId)->set(['is_default' => 1, 'is_active' => 1])->update();
            }
            $this->cleanDefaultTemplate();
        } catch (\Throwable $e) {
            log_message('error', 'Piagam init error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    /**
     * Bersihkan area teks tengah (nama & paragraf Klaten bawaan) pada file template_default_landscape.jpg
     * sehingga menjadi template bersih (clean canvas) untuk dicetak dinamis
     */
    protected function cleanDefaultTemplate(): void
    {
        $file = FCPATH . 'uploads/piagam/templates/template_default_landscape.jpg';
        if (!file_exists($file)) return;

        // Cek flag agar tidak memproses berulang jika sudah bersih
        $flagFile = FCPATH . 'uploads/piagam/templates/.cleaned_v1';
        if (file_exists($flagFile)) return;

        if (!function_exists('imagecreatefromjpeg')) return;

        $im = @imagecreatefromjpeg($file);
        if (!$im) return;

        $w = imagesx($im);
        $h = imagesy($im);

        // Ambil warna latar belakang cream/putih di area aman
        $rgb = imagecolorat($im, (int)($w * 0.15), (int)($h * 0.45));
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        $bgColor = imagecolorallocate($im, $r, $g, $b);

        // Bersihkan seluruh area tengah di bawah logo PASI sampai di atas tanda tangan
        // Dari Y 18.5% sampai Y 69% sehingga tidak ada teks terpotong
        $x1 = (int)($w * 0.04);
        $y1 = (int)($h * 0.185);
        $x2 = (int)($w * 0.96);
        $y2 = (int)($h * 0.69);

        imagefilledrectangle($im, $x1, $y1, $x2, $y2, $bgColor);
        imagejpeg($im, $file, 96);
        imagedestroy($im);

        @file_put_contents($flagFile, date('Y-m-d H:i:s'));
    }

    /**
     * Halaman Utama Piagam: Daftar Acara, Rekap Pemenang & Tombol Cetak / Sesuaikan
     */
    public function index()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $filterEventId = $this->request->getGet('event_id');

        // Ambil semua event di kejuaraan aktif
        $events = $this->eventModel->getEventsWithDetails($kejuaraanId);

        // Ambil pemenang (Juara 1, 2, 3) untuk setiap event
        $pemenangPerEvent = [];
        $totalPemenang = 0;

        foreach ($events as $ev) {
            if (!empty($filterEventId) && $ev['id'] != $filterEventId) {
                continue;
            }

            $winners = $this->getWinnersByEvent($ev['id']);
            $pemenangPerEvent[$ev['id']] = [
                'event'   => $ev,
                'winners' => $winners,
            ];
            $totalPemenang += count($winners);
        }

        // Ambil daftar template yang tersedia (hanya yang aktif)
        $templates = $this->templateModel->where('is_active', 1)->orderBy('is_default', 'DESC')->orderBy('id', 'ASC')->findAll();
        $defaultTemplate = $this->templateModel->getDefaultTemplate();

        $this->data['title']            = 'Piagam Penghargaan — Kejurwil Atletik';
        $this->data['events']           = $events;
        $this->data['pemenangPerEvent'] = $pemenangPerEvent;
        $this->data['totalPemenang']    = $totalPemenang;
        $this->data['templates']        = $templates;
        $this->data['defaultTemplate']  = $defaultTemplate;
        $this->data['filterEventId']    = $filterEventId;

        return view('piagam/index', $this->data);
    }

    /**
     * Cetak Piagam untuk 1 Acara Tertentu (Juara 1-3)
     */
    public function cetakEvent(int $eventId)
    {
        $event = $this->eventModel->getEventWithDetails($eventId);
        if (!$event) {
            $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;
            $evByNo = $this->eventModel->where('kejuaraan_id', $kejuaraanId)->where('nomor_acara', $eventId)->first();
            if ($evByNo) {
                return redirect()->to(base_url('piagam/cetak-event/' . $evByNo['id']));
            }
            return redirect()->to(base_url('piagam'))->with('error', 'Acara tidak ditemukan.');
        }
        $eventId = (int)$event['id'];

        $templateId = (int)($this->request->getGet('template_id') ?? 0);
        $template = $templateId ? $this->templateModel->find($templateId) : $this->templateModel->getDefaultTemplate();
        if (!$template) {
            $template = $this->templateModel->first();
        }

        $winners = $this->getWinnersByEvent($eventId);
        if (empty($winners)) {
            return redirect()->to(base_url('piagam'))->with('error', 'Belum ada data pemenang untuk nomor acara ini. Silakan input hasil lomba terlebih dahulu.');
        }

        // Susun data sertifikat per pemenang (otomatis perorangan untuk estafet)
        $seq = 1;
        $piagamList = $this->formatPiagamItems($winners, $template, $seq, $eventId);

        $this->data['title']      = "Cetak Piagam — Acara {$event['nomor_acara']}: {$event['nama_acara']}";
        $this->data['template']   = $template;
        $this->data['templates']  = $this->templateModel->where('is_active', 1)->orderBy('is_default', 'DESC')->orderBy('id', 'ASC')->findAll();
        $this->data['piagamList'] = $piagamList;
        $this->data['event']      = $event;

        return view('piagam/cetak', $this->data);
    }

    /**
     * Cetak Semua Pemenang (Juara 1, 2, 3) dari Seluruh Acara (Batch Print)
     */
    public function cetakAll()
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $templateId  = (int)($this->request->getGet('template_id') ?? 0);
        $template    = $templateId ? $this->templateModel->find($templateId) : $this->templateModel->getDefaultTemplate();
        if (!$template) {
            $template = $this->templateModel->first();
        }

        $events = $this->eventModel->getEventsWithDetails($kejuaraanId);
        $piagamList = [];
        $seq = 1;

        foreach ($events as $ev) {
            $winners = $this->getWinnersByEvent($ev['id']);
            if (!empty($winners)) {
                $items = $this->formatPiagamItems($winners, $template, $seq, (int)$ev['id']);
                $piagamList = array_merge($piagamList, $items);
            }
        }

        if (empty($piagamList)) {
            return redirect()->to(base_url('piagam'))->with('error', 'Belum ada data pemenang untuk dicetak.');
        }

        $this->data['title']      = 'Cetak Semua Piagam Pemenang (' . count($piagamList) . ' Piagam)';
        $this->data['template']   = $template;
        $this->data['templates']  = $this->templateModel->where('is_active', 1)->orderBy('is_default', 'DESC')->orderBy('id', 'ASC')->findAll();
        $this->data['piagamList'] = $piagamList;
        $this->data['event']      = null;

        return view('piagam/cetak', $this->data);
    }

    /**
     * Cetak Custom: Menerima data yang disesuaikan secara dinamis oleh user
     */
    public function cetakCustom()
    {
        $templateId = (int)($this->request->getPost('template_id') ?? $this->request->getGet('template_id') ?? 0);
        $template = $templateId ? $this->templateModel->find($templateId) : $this->templateModel->getDefaultTemplate();
        if (!$template) {
            $template = $this->templateModel->first();
        }

        $getOrPost = function($key, $default = '') {
            $val = $this->request->getPost($key) ?? $this->request->getGet($key);
            return $val !== null && trim($val) !== '' ? trim($val) : $default;
        };

        $peringkat = (int)($this->request->getPost('peringkat') ?? $this->request->getGet('peringkat') ?? 1);
        $juaraText = $this->getJuaraText($peringkat);

        $sekolahInput = $getOrPost('asal_sekolah', '');
        $kotaInput    = $getOrPost('asal_kota', '');
        if (empty($kotaInput) || $kotaInput === 'Kota Pekalongan') {
            $detected = $this->detectAsalKota($sekolahInput);
            if (!empty($detected) && $detected !== 'Kota Pekalongan') {
                $kotaInput = $detected;
            } elseif (empty($kotaInput)) {
                $kotaInput = 'Kota Pekalongan';
            }
        }

        $customItem = [
            'id'                  => 0,
            'nomor_piagam'        => $getOrPost('nomor_piagam', '001/SERT/PASIJATENG/IX/' . date('Y')),
            'nama_pemenang'       => $getOrPost('nama_pemenang', 'NAMA PESERTA'),
            'asal_sekolah'        => $sekolahInput,
            'asal_kota'           => $kotaInput,
            'nomor_lomba'         => $getOrPost('nomor_lomba', 'Lari 100 M KU 16-18 Putra'),
            'kategori_usia'       => $getOrPost('kategori_usia', 'KU 16-18'),
            'peringkat'           => $peringkat,
            'peringkat_teks'      => $getOrPost('peringkat_teks', $juaraText['teks']),
            'peringkat_romawi'    => $juaraText['romawi'],
            'prestasi'            => $getOrPost('prestasi', '-'),
            'nama_kejuaraan'      => $getOrPost('nama_kejuaraan', $this->activeKejuaraan['nama'] ?? 'KEJURWIL ATLETIK KOTA PEKALONGAN 2026'),
            'tempat'              => $getOrPost('tempat', $this->activeKejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan'),
            'tanggal'             => $getOrPost('tanggal', $this->formatTanggalKejuaraan($this->activeKejuaraan)),
            'satuan'              => '',
        ];

        $paramHanyaPekalongan = $this->request->getPost('hanya_ketua_pekalongan') ?? $this->request->getGet('hanya_ketua_pekalongan');
        if ($paramHanyaPekalongan !== null) {
            $customItem['hanya_ketua_pekalongan'] = ($paramHanyaPekalongan === '1' || $paramHanyaPekalongan === 'true');
        }

        // Jika user ingin override penandatangan khusus untuk cetak ini
        if ($this->request->getPost('override_ttd')) {
            $template['ttd_1_jabatan'] = $this->request->getPost('ttd_1_jabatan') ?: $template['ttd_1_jabatan'];
            $template['ttd_1_nama']    = $this->request->getPost('ttd_1_nama') ?: $template['ttd_1_nama'];
            $template['ttd_2_jabatan'] = $this->request->getPost('ttd_2_jabatan') ?: $template['ttd_2_jabatan'];
            $template['ttd_2_nama']    = $this->request->getPost('ttd_2_nama') ?: $template['ttd_2_nama'];
            $template['ttd_3_jabatan'] = $this->request->getPost('ttd_3_jabatan') ?: $template['ttd_3_jabatan'];
            $template['ttd_3_nama']    = $this->request->getPost('ttd_3_nama') ?: $template['ttd_3_nama'];
        }

        $this->data['title']      = "Cetak Piagam Kustom — " . esc($customItem['nama_pemenang']);
        $this->data['template']   = $template;
        $this->data['templates']  = $this->templateModel->where('is_active', 1)->orderBy('is_default', 'DESC')->orderBy('id', 'ASC')->findAll();
        $this->data['piagamList'] = [$customItem];
        $this->data['event']      = null;

        return view('piagam/cetak', $this->data);
    }

    /**
     * Manajemen & Upload Template Piagam
     */
    public function templates()
    {
        $showAll = (bool)$this->request->getGet('show_all');
        $query = $this->templateModel->orderBy('is_default', 'DESC')->orderBy('id', 'ASC');
        if (!$showAll) {
            $query->where('is_active', 1);
        }
        $templates = $query->findAll();
        $totalAll = $this->templateModel->countAllResults();

        $this->data['title']     = 'Kelola & Upload Template Piagam';
        $this->data['templates'] = $templates;
        $this->data['showAll']   = $showAll;
        $this->data['totalAll']  = $totalAll;
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $this->data['events']    = $this->eventModel->getEventsWithDetails($kejuaraanId);

        return view('piagam/templates', $this->data);
    }

    /**
     * Upload & Simpan Template Piagam Baru
     */
    public function saveTemplate()
    {
        $id = $this->request->getPost('id');
        $nama = trim($this->request->getPost('nama') ?? '');
        $orientasi = $this->request->getPost('orientasi') === 'portrait' ? 'portrait' : 'landscape';
        $tipe = trim($this->request->getPost('tipe') ?? 'portrait_ekskari');
        $isDefault = $this->request->getPost('is_default') ? 1 : 0;
        $isActive  = $this->request->getPost('is_active') !== null ? (int)$this->request->getPost('is_active') : 1;

        if (empty($nama)) {
            return redirect()->back()->with('error', 'Nama template wajib diisi.');
        }

        // Upload file background jika ada (JPG, PNG, WebP, atau PDF)
        $fileBg = null;
        $image = $this->request->getFile('file_background');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $ext = strtolower($image->getClientExtension() ?: $image->guessExtension() ?: $image->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                return redirect()->back()->with('error', 'Format file harus JPG, PNG, WebP, atau PDF.');
            }

            $uploadPath = FCPATH . 'uploads/piagam/templates/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            if ($ext === 'pdf') {
                $tempPdfName = 'temp_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
                $tempPdfPath = $uploadPath . $tempPdfName;
                $image->move($uploadPath, $tempPdfName);

                $pngName = 'template_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
                $pngPath = $uploadPath . $pngName;

                // Konversi halaman 1 PDF ke PNG resolusi tinggi (300 DPI)
                $escPdf = escapeshellarg($tempPdfPath);
                $escPng = escapeshellarg($pngPath);

                // 1. Coba Ghostscript (natively installed & ultra fast di Linux server)
                @exec("gs -dNOPAUSE -dBATCH -sDEVICE=png16m -r300 -dFirstPage=1 -dLastPage=1 -sOutputFile={$escPng} {$escPdf} 2>&1");

                // 2. Fallback: ImageMagick convert
                if (!file_exists($pngPath) || filesize($pngPath) === 0) {
                    @exec("convert -density 300 {$escPdf}[0] {$escPng} 2>&1");
                }

                // 3. Fallback: Python PyMuPDF (python3 / python)
                if (!file_exists($pngPath) || filesize($pngPath) === 0) {
                    @exec("python3 -c \"import pymupdf; doc = pymupdf.open({$escPdf}); page = doc[0]; pix = page.get_pixmap(dpi=300); pix.save({$escPng})\" 2>&1");
                }
                if (!file_exists($pngPath) || filesize($pngPath) === 0) {
                    @exec("python -c \"import pymupdf; doc = pymupdf.open({$escPdf}); page = doc[0]; pix = page.get_pixmap(dpi=300); pix.save({$escPng})\" 2>&1");
                }

                if (file_exists($pngPath) && filesize($pngPath) > 0) {
                    @unlink($tempPdfPath);
                    $fileBg = $pngName;
                } else {
                    $fileBg = $tempPdfName;
                }
            } else {
                $newName = 'template_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $image->move($uploadPath, $newName);
                $fileBg = $newName;
            }
        }

        $data = [
            'nama'                => $nama,
            'orientasi'           => $orientasi,
            'tipe'                => $tipe,
            'hide_header'         => $this->request->getPost('hide_header') ? 1 : 0,
            'hide_title'          => $this->request->getPost('hide_title') ? 1 : 0,
            'offset_top'          => (int)($this->request->getPost('offset_top') ?? 0),
            'offset_ttd'          => (int)($this->request->getPost('offset_ttd') ?? 0),
            'font_size_nama'      => (int)($this->request->getPost('font_size_nama') ?: 32),
            'font_size_detail'    => (int)($this->request->getPost('font_size_detail') ?: 14),
            'instansi_header'     => trim($this->request->getPost('instansi_header') ?? ''),
            'sub_header'          => trim($this->request->getPost('sub_header') ?? ''),
            'judul'               => trim($this->request->getPost('judul') ?? 'PIAGAM PENGHARGAAN'),
            'format_nomor'        => trim($this->request->getPost('format_nomor') ?? '{nomor}/SERT/PASIJATENG/IX/{tahun}'),
            'kalimat_pembuka'     => trim($this->request->getPost('kalimat_pembuka') ?? ''),
            'kalimat_penghargaan' => trim($this->request->getPost('kalimat_penghargaan') ?? ''),
            'ttd_1_jabatan'       => trim($this->request->getPost('ttd_1_jabatan') ?? ''),
            'ttd_1_nama'          => trim($this->request->getPost('ttd_1_nama') ?? ''),
            'ttd_2_jabatan'       => trim($this->request->getPost('ttd_2_jabatan') ?? ''),
            'ttd_2_nama'          => trim($this->request->getPost('ttd_2_nama') ?? ''),
            'ttd_3_jabatan'       => trim($this->request->getPost('ttd_3_jabatan') ?? ''),
            'ttd_3_nama'          => trim($this->request->getPost('ttd_3_nama') ?? ''),
            'is_default'          => $isDefault,
            'is_active'           => $isActive,
        ];

        if ($this->request->getPost('remove_file_background')) {
            $data['file_background'] = null;
        } elseif ($fileBg) {
            $data['file_background'] = $fileBg;
            $data['tipe'] = 'overlay';
        }

        // Upload & simpan file Tanda Tangan 1, 2, 3
        $ttdUploadPath = FCPATH . 'uploads/piagam/ttd/';
        if (!is_dir($ttdUploadPath)) {
            mkdir($ttdUploadPath, 0777, true);
        }

        foreach ([1, 2, 3] as $ttdNum) {
            $field = "ttd_{$ttdNum}_image";

            if ($this->request->getPost("use_default_{$field}")) {
                $defaults = [
                    1 => 'ttd_jumanto_pasi_pekalongan.png',
                    2 => 'ttd_zaini_pasi_pekalongan.png',
                    3 => 'ttd_agus_widodo_pasi_jateng.png',
                ];
                $data[$field] = $defaults[$ttdNum] ?? null;
            } elseif ($this->request->getPost("remove_{$field}")) {
                $data[$field] = null;
            } else {
                $file = $this->request->getFile($field);
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg'])) {
                        $newFileName = "ttd_{$ttdNum}_" . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                        $file->move($ttdUploadPath, $newFileName);
                        $data[$field] = $newFileName;
                    }
                }
            }
        }

        // Jika diset default, non-aktifkan default template lain
        if ($isDefault) {
            $this->templateModel->where('id !=', $id ?: 0)->set(['is_default' => 0])->update();
        }

        if ($id) {
            $this->templateModel->update($id, $data);
            $msg = 'Template piagam & data tanda tangan berhasil diperbarui.';
        } else {
            $this->templateModel->insert($data);
            $msg = 'Template piagam baru berhasil ditambahkan.';
        }

        return redirect()->to(base_url('piagam/templates'))->with('success', $msg);
    }

    /**
     * Toggle status aktif/sembunyikan template
     */
    public function toggleActiveTemplate(int $id)
    {
        $template = $this->templateModel->find($id);
        if (!$template) {
            return redirect()->back()->with('error', 'Template tidak ditemukan.');
        }

        $newActive = empty($template['is_active']) ? 1 : 0;
        $this->templateModel->update($id, ['is_active' => $newActive]);
        $statusMsg = $newActive ? 'Template diaktifkan dan ditampilkan.' : 'Template disembunyikan/diarsipkan.';
        return redirect()->back()->with('success', $statusMsg);
    }

    /**
     * Jadikan template default
     */
    public function setDefaultTemplate(int $id)
    {
        $this->templateModel->where('id !=', $id)->set(['is_default' => 0])->update();
        $this->templateModel->update($id, ['is_default' => 1]);

        return redirect()->to(base_url('piagam/templates'))->with('success', 'Template default berhasil diatur.');
    }

    /**
     * Hapus template
     */
    public function deleteTemplate(int $id)
    {
        $template = $this->templateModel->find($id);
        if (!$template) {
            return redirect()->back()->with('error', 'Template tidak ditemukan.');
        }

        // Hapus file gambar jika ada dan bukan default bawaan
        if (!empty($template['file_background']) && $template['file_background'] !== 'template_default_landscape.jpg') {
            @unlink(FCPATH . 'uploads/piagam/templates/' . $template['file_background']);
        }

        $this->templateModel->delete($id);

        return redirect()->to(base_url('piagam/templates'))->with('success', 'Template berhasil dihapus.');
    }

    /**
     * Helper: Format nomor lomba spesifik dengan Kategori Kelompok Umur untuk Piagam
     */
    protected function formatNomorLombaPiagam(array $w): string
    {
        $namaAcara = trim($w['nama_acara'] ?? '');
        $namaKu    = trim($w['nama_ku'] ?? '');
        $namaLomba = trim($w['nama_lomba'] ?? '');
        $gender    = trim($w['event_gender'] ?? $w['gender'] ?? '');
        $genderStr = ($gender === 'L') ? 'Putra' : (($gender === 'P') ? 'Putri' : '');

        if (!empty($namaAcara)) {
            if (!empty($namaKu) && stripos($namaAcara, 'KU') === false && stripos($namaAcara, 'Kelompok Umur') === false) {
                return trim("{$namaAcara} ({$namaKu})");
            }
            return $namaAcara;
        }

        return trim("{$namaLomba} {$namaKu} {$genderStr}");
    }

    /**
     * Helper: Ambil daftar nama pelari dari catatan jika nomor estafet
     */
    protected function getEstafetRunners(array $winner): array
    {
        $namaLomba = $winner['nama_acara'] ?? $winner['nama_lomba'] ?? '';
        if (stripos($namaLomba, 'estafet') === false) {
            return [];
        }

        $raw = $winner['catatan_atlet'] ?? '';
        if (empty(trim($raw))) {
            return [];
        }

        $cleaned = preg_replace('/^[Pp]elari\s*:\s*/i', '', trim($raw));
        $parts = explode(',', $cleaned);
        $runners = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') continue;
            // Hapus awalan nomor dada atau nomor urut (contoh: "976 Bimo..." atau "1011 1.ECCA...")
            $name = trim(preg_replace('/^[\d\.\s]+/', '', $p));
            if ($name !== '') {
                $runners[] = $name;
            }
        }

        return $runners;
    }

    /**
     * Helper: Format baris piagam untuk daftar pemenang.
     * Khusus nomor estafet: otomatis memecah 1 tim menjadi piagam perorangan untuk tiap pelari (Opsi 1)
     */
    protected function formatPiagamItems(array $winners, array $template, int &$seq, int $eventId = 0): array
    {
        $items = [];
        $tahun = $this->activeKejuaraan['tahun'] ?? date('Y');
        $tempat = $this->activeKejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan';
        $tglStr = $this->formatTanggalKejuaraan($this->activeKejuaraan);
        $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;

        foreach ($winners as $w) {
            $currentEventId = $eventId ?: (int)($w['event_id'] ?? $w['id_event'] ?? 0);
            $juaraText = $this->getJuaraText((int)($w['peringkat'] ?? 1));
            $baseLomba = $this->formatNomorLombaPiagam($w);
            $asalSekolah = $w['nama_sekolah'] ?: 'Independen';
            $asalKota = $w['asal_daerah'] ?: $this->detectAsalKota($w['nama_sekolah'] ?? '');

            // Cek apakah nomor estafet dengan daftar pelari di catatan
            $runners = $this->getEstafetRunners($w);

            if (!empty($runners)) {
                // Cantumkan nama tim pada nomor lomba agar jelas dan terakreditasi: misal "Estafet MIX 4 X 100 U 16 - 18 (Team SMA N 1 BOJONG)"
                $lombaWithTeam = $baseLomba;
                if (!empty($w['nama_atlet'])) {
                    $lombaWithTeam .= ' (' . $w['nama_atlet'] . ')';
                }

                foreach ($runners as $rIdx => $runnerName) {
                    $nomorPiagam = str_replace(
                        ['{nomor}', '{tahun}'],
                        [sprintf('%03d', $seq), $tahun],
                        $template['format_nomor'] ?? '{nomor}/SERT/PASIJATENG/IX/{tahun}'
                    );

                    $peId = (int)($w['peserta_event_id'] ?? $w['id'] ?? 0);
                    $verifyCode = $this->generateVerificationCode($kejuaraanId, $currentEventId, $peId * 100 + $rIdx + 1, (int)($w['peringkat'] ?? 1));

                    $items[] = [
                        'id'                  => $w['id'],
                        'nomor_piagam'        => $nomorPiagam,
                        'nama_pemenang'       => $runnerName,
                        'asal_sekolah'        => $asalSekolah,
                        'asal_kota'           => $asalKota,
                        'nomor_lomba'         => $lombaWithTeam,
                        'kategori_usia'       => $w['nama_ku'] ?? '',
                        'peringkat'           => $w['peringkat'],
                        'peringkat_teks'      => $juaraText['teks'],
                        'peringkat_romawi'    => $juaraText['romawi'],
                        'prestasi'            => $w['prestasi'] ?: '-',
                        'nama_kejuaraan'      => $this->activeKejuaraan['nama'] ?? 'KEJURWIL ATLETIK KOTA PEKALONGAN 2026',
                        'tempat'              => $tempat,
                        'tanggal'             => $tglStr,
                        'satuan'              => $w['satuan_hasil'] ?? '',
                        'verification_code'   => $verifyCode,
                        'verification_url'    => base_url("verifikasi/piagam/{$verifyCode}"),
                    ];
                    $seq++;
                }
            } else {
                $nomorPiagam = str_replace(
                    ['{nomor}', '{tahun}'],
                    [sprintf('%03d', $seq), $tahun],
                    $template['format_nomor'] ?? '{nomor}/SERT/PASIJATENG/IX/{tahun}'
                );

                $peId = (int)($w['peserta_event_id'] ?? $w['id'] ?? 0);
                $verifyCode = $this->generateVerificationCode($kejuaraanId, $currentEventId, $peId, (int)($w['peringkat'] ?? 1));

                $items[] = [
                    'id'                  => $w['id'],
                    'nomor_piagam'        => $nomorPiagam,
                    'nama_pemenang'       => $w['nama_atlet'],
                    'asal_sekolah'        => $asalSekolah,
                    'asal_kota'           => $asalKota,
                    'nomor_lomba'         => $baseLomba,
                    'kategori_usia'       => $w['nama_ku'] ?? '',
                    'peringkat'           => $w['peringkat'],
                    'peringkat_teks'      => $juaraText['teks'],
                    'peringkat_romawi'    => $juaraText['romawi'],
                    'prestasi'            => $w['prestasi'] ?: '-',
                    'nama_kejuaraan'      => $this->activeKejuaraan['nama'] ?? 'KEJURWIL ATLETIK KOTA PEKALONGAN 2026',
                    'tempat'              => $tempat,
                    'tanggal'             => $tglStr,
                    'satuan'              => $w['satuan_hasil'] ?? '',
                    'verification_code'   => $verifyCode,
                    'verification_url'    => base_url("verifikasi/piagam/{$verifyCode}"),
                ];
                $seq++;
            }
        }

        return $items;
    }

    /**
     * Helper: Ambil pemenang untuk event tertentu (Default Juara 1-3)
     */
    protected function getWinnersByEvent(int $eventId, int $limitJuara = 3): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('hasil h')
            ->select('h.*, pe.lane as no_lintasan, pe.heat as seri, a.no_dada as nomor_dada, a.nama as nama_atlet, a.catatan as catatan_atlet, a.gender, s.nama as nama_sekolah, s.alamat as asal_daerah, e.nama_acara, e.nomor_acara, e.gender as event_gender, nl.nama as nama_lomba, nl.satuan_hasil, ku.nama as nama_ku, ku.keterangan as ket_ku')
            ->join('peserta_event pe', 'pe.id = h.peserta_event_id')
            ->join('atlet a', 'a.id = pe.atlet_id')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('event e', 'e.id = pe.event_id')
            ->join('nomor_lomba nl', 'nl.id = e.nomor_lomba_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = e.kelompok_umur_id', 'left')
            ->where('pe.event_id', $eventId)
            ->where('h.peringkat IS NOT NULL')
            ->where('h.peringkat >', 0);

        if ($limitJuara > 0) {
            $builder->where('h.peringkat <=', $limitJuara);
        }

        return $builder->where('h.status_prestasi', 'Valid')
            ->orderBy('h.peringkat', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Helper: Konversi integer juara ke teks dan romawi
     */
    protected function getJuaraText(int $p): array
    {
        switch ($p) {
            case 1:
                return ['teks' => 'Satu', 'romawi' => 'I'];
            case 2:
                return ['teks' => 'Dua', 'romawi' => 'II'];
            case 3:
                return ['teks' => 'Tiga', 'romawi' => 'III'];
            case 4:
                return ['teks' => 'Empat', 'romawi' => 'IV'];
            case 5:
                return ['teks' => 'Lima', 'romawi' => 'V'];
            default:
                return ['teks' => (string)$p, 'romawi' => (string)$p];
        }
    }

    /**
     * Helper: Format rentang tanggal kejuaraan
     */
    protected function formatTanggalKejuaraan(array $kj): string
    {
        $tglMulai = $kj['tanggal_mulai'] ?? null;
        $tglSelesai = $kj['tanggal_selesai'] ?? null;

        if (!$tglMulai) {
            return '29 Agustus 2026';
        }

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $time1 = strtotime($tglMulai);
        $d1 = date('j', $time1);
        $m1 = $bulanIndo[(int)date('n', $time1)];
        $y1 = date('Y', $time1);

        if ($tglSelesai && $tglSelesai !== $tglMulai) {
            $time2 = strtotime($tglSelesai);
            $d2 = date('j', $time2);
            $m2 = $bulanIndo[(int)date('n', $time2)];
            $y2 = date('Y', $time2);

            if ($m1 === $m2 && $y1 === $y2) {
                return "{$d1} - {$d2} {$m1} {$y1}";
            }
            return "{$d1} {$m1} {$y1} - {$d2} {$m2} {$y2}";
        }

        return "{$d1} {$m1} {$y1}";
    }

    /**
     * Generate kode verifikasi unik untuk piagam
     */
    protected function generateVerificationCode(int $kejuaraanId, int $eventId, int $pesertaEventId, int $peringkat): string
    {
        $secret = env('encryption.key', 'PASI_JATENG_2026_SECRET');
        $hash = substr(hash('sha256', "{$kejuaraanId}-{$eventId}-{$pesertaEventId}-{$peringkat}-{$secret}"), 0, 8);
        return "PASI-{$kejuaraanId}-{$eventId}-{$peringkat}-{$hash}";
    }

    /**
     * Halaman Publik Verifikasi Keabsahan Piagam (Tanpa Login)
     * URL: /verifikasi/piagam/{code}
     */
    public function verifikasi(string $code)
    {
        // Parse kode: PASI-{kejuaraanId}-{eventId}-{peringkat}-{hash}
        if (!preg_match('/^PASI-(\d+)-(\d+)-(\d+)-([a-f0-9]{8})$/i', $code, $m)) {
            return view('piagam/verifikasi', [
                'valid' => false,
                'code'  => $code,
                'error' => 'Format kode verifikasi tidak valid.',
            ]);
        }

        $kejuaraanId = (int)$m[1];
        $eventId     = (int)$m[2];
        $peringkat   = (int)$m[3];
        $hash        = $m[4];

        // Cari peserta_event_id yang cocok (tanpa limit juara)
        $winners = $this->getWinnersByEvent($eventId, 0);
        $matchedWinner = null;
        $secret = env('encryption.key', 'PASI_JATENG_2026_SECRET');

        foreach ($winners as $w) {
            if ((int)$w['peringkat'] === $peringkat) {
                $peId = (int)($w['peserta_event_id'] ?? $w['id'] ?? 0);
                $expectedHash = substr(hash('sha256', "{$kejuaraanId}-{$eventId}-{$peId}-{$peringkat}-{$secret}"), 0, 8);
                if ($expectedHash === $hash) {
                    $matchedWinner = $w;
                    break;
                }
            }
        }

        if (!$matchedWinner) {
            return view('piagam/verifikasi', [
                'valid' => false,
                'code'  => $code,
                'error' => 'Piagam dengan kode ini tidak ditemukan dalam database.',
            ]);
        }

        // Ambil data kejuaraan & event
        $kejuaraan = (new \App\Models\KejuaraanModel())->find($kejuaraanId);
        $event = $this->eventModel->getEventWithDetails($eventId);

        return view('piagam/verifikasi', [
            'valid'      => true,
            'code'       => $code,
            'winner'     => $matchedWinner,
            'kejuaraan'  => $kejuaraan,
            'event'      => $event,
            'juaraText'  => $this->getJuaraText($peringkat),
            'tanggal'    => $this->formatTanggalKejuaraan($kejuaraan ?: []),
        ]);
    }

    /**
     * AJAX: Simpan penyesuaian posisi grid background, offset TTD, dan ukuran font secara instan
     */
    public function updateTemplatePosition()
    {
        $templateId = (int)$this->request->getPost('template_id');
        if (!$templateId) {
            $def = $this->templateModel->getDefaultTemplate();
            $templateId = $def ? $def['id'] : 0;
        }

        $template = $this->templateModel->find($templateId);
        if (!$template) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Template tidak ditemukan']);
        }

        $data = [];
        if ($this->request->getPost('offset_top') !== null) {
            $data['offset_top'] = (int)$this->request->getPost('offset_top');
        }
        if ($this->request->getPost('offset_ttd') !== null) {
            $data['offset_ttd'] = (int)$this->request->getPost('offset_ttd');
        }
        if ($this->request->getPost('font_size_nama') !== null) {
            $data['font_size_nama'] = (int)$this->request->getPost('font_size_nama');
        }
        if ($this->request->getPost('hide_header') !== null) {
            $data['hide_header'] = (int)$this->request->getPost('hide_header');
        }
        if ($this->request->getPost('hide_title') !== null) {
            $data['hide_title'] = (int)$this->request->getPost('hide_title');
        }
        if ($this->request->getPost('custom_offsets') !== null) {
            $data['custom_offsets'] = (string)$this->request->getPost('custom_offsets');
        }

        if (!empty($data)) {
            $this->templateModel->update($templateId, $data);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Tata letak dan posisi background berhasil disimpan secara permanen.',
            'data'    => $data,
        ]);
    }

    /**
     * Helper: Deteksi otomatis asal Kabupaten/Kota dari nama sekolah/kontingen
     */
    public function detectAsalKota(string $namaSekolah): string
    {
        $namaSekolah = trim($namaSekolah);
        if (empty($namaSekolah) || strcasecmp($namaSekolah, 'Independen') === 0) {
            return 'Kota Pekalongan';
        }

        // Cek database sekolah terlebih dahulu jika ada
        $db = \Config\Database::connect();
        $row = $db->table('sekolah')
            ->select('alamat')
            ->where('nama', $namaSekolah)
            ->where('alamat IS NOT NULL')
            ->where('alamat !=', '')
            ->get()
            ->getRowArray();
        if ($row && !empty($row['alamat'])) {
            return trim($row['alamat']);
        }

        $n = strtoupper($namaSekolah);

        // 1. Brebes
        if (preg_match('/(BREBES|BUMIAYU|TONJONG|LARANGAN|KETANGGUNGAN|BULAKAMBA|JATIBARANG|SONGGOM|PAGUYANGAN|SIRAMPOG|LOSARI|TANJUNG|BANJARHARJO|SALEM|BANTARKAWUNG|BREGAS)/i', $n)) {
            return 'Kabupaten Brebes';
        }

        // 2. Pemalang
        if (preg_match('/(PEMALANG|COMAL|PETARUKAN|ULUJAMI|AMPELGADING|BODEH|MOGA|RANDUDONGKAL|BELIK|WATUKUMPUL|BANTARBOLANG|WARUNGPRING|SIMA|KARANGTALOK|JATINGARANG)/i', $n)) {
            return 'Kabupaten Pemalang';
        }

        // 3. Batang
        if (preg_match('/(BATANG|KANDEMAN|WARUNGASEM|BANDAR|SUBAH|LIMPUNG|GRINGSING|BANYUPUTIH|PROYONANGGAN|KLIDANG|REBAN|BLADO|TERSONO|BAWANG|WONOTUNGGAL|PECALUNGAN|LSS|LSSC|SAAM UNIVERSE|KARTIKA AZAKA|BAKTI BATANG|CAHAYA QURAN)/i', $n)) {
            return 'Kabupaten Batang';
        }
        if (preg_match('/SDN?\s+KAUMAN\s+0[136]/i', $n)) {
            return 'Kabupaten Batang';
        }

        // 4. Tegal
        if (preg_match('/(IHSANIYAH TEGAL|KOTA TEGAL)/i', $n)) {
            return 'Kota Tegal';
        }
        if (preg_match('/(SLAWI|ADIWERNA|TARUB|DUKUHTURI|KRAMAT|SURADADI|WARUREJA|TALANG|PANGKAH|KEDUNGBANTENG|BUMIJAWA|MARGASARI|BALAPULANG|LEBAKSIU|JATINEGARA|PAGERBARANG|DUKUHWARU|KAB\.?\s*TEGAL|KABUPATEN TEGAL|TEMBOK KIDUL)/i', $n)) {
            if (preg_match('/(KRAMAT\s*SARI|KRAMATSARI)/i', $n)) {
                return 'Kota Pekalongan';
            }
            return 'Kabupaten Tegal';
        }
        if (stripos($n, 'TEGAL') !== false && stripos($n, 'TEGALREJO') === false) {
            return 'Kabupaten Tegal';
        }

        // 5. Kab Pekalongan
        if (preg_match('/(KAB\.?\s*PEKALONGAN|KAJEN|KEDUNGWUNI|WIRADESA|BOJONG|SRAGI|KESESI|DORO|KARANGANYAR|KARANGDADAP|WONOPRINGGO|BUARAN|WONOKERTO|PANINGGARAN|PETUNGKRIYONO|LEBAKBARANG|TALUN|PEKAJANGAN|BLIGO|SAPUGARUT|GEMBONG|BABALAN|LEGOKCLILE|JAJARWAYANG|BOJONGMINGGIR|NOTOGIWANG|BONDANSARI|WANGANDOWO|KRANJI|SIWALAN|API-API|KARANG ASAM|PAESAN|KEMPLONG|BEBEL|ALMALIKI|GUMAWANG|PURI|BOJONG RAYA)/i', $n)) {
            return 'Kabupaten Pekalongan';
        }

        // 6. Kota Pekalongan
        return 'Kota Pekalongan';
    }
}

