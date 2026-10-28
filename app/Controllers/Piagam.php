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

        // Ambil semua event di kejuaraan aktif (Hanya Babak Final, Babak Penyisihan tidak dicetak piagam)
        $rawEvents = $this->eventModel->getEventsWithDetails($kejuaraanId);
        $events = array_values(array_filter($rawEvents, function($ev) {
            return strcasecmp($ev['babak'] ?? '', 'Penyisihan') !== 0;
        }));

        // Jika kejuaraan aktif tidak memiliki data event balap tapi memiliki pemenang custom (misal Kejurkot)
        if (empty($events) && $kejuaraanId && $this->customModel->where('kejuaraan_id', $kejuaraanId)->countAllResults() > 0) {
            return $this->kejurkotIndex();
        }

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

        $this->data['title']            = 'Piagam Penghargaan - Kejurwil Atletik';
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

        // Babak Penyisihan tidak diberikan piagam penghargaan (hanya untuk babak Final)
        if (strcasecmp($event['babak'] ?? '', 'Penyisihan') === 0) {
            return redirect()->to(base_url('piagam'))->with('error', 'Piagam Penghargaan hanya untuk Babak Final. Babak Penyisihan tidak dicetak piagam.');
        }

        $evKuCheck = strtolower(($event['kategori_usia'] ?? '') . ' ' . ($event['nama_acara'] ?? ''));
        $isEvUsia567 = (($event['kelompok_umur_id'] ?? 0) == 1 || preg_match('/\b(u[\s\-_]?[567]|ku[\s\-_]?[567]|juaraku|usia[\s\-_]?dini|[567][\s\-_]?th|[567][\s\-_]?tahun)\b|5-6|567|juaraku/i', $evKuCheck));

        $templateId = (int)($this->request->getGet('template_id') ?? 0);
        if ($isEvUsia567) {
            // Usia Dini 5, 6, 7 Tahun HANYA menggunakan Template 22 (Trophy)
            $templateId = 22;
        } else {
            // Selain Usia Dini TIDAK BISA menggunakan Template 22
            if ($templateId <= 0 || $templateId === 22) {
                $def = $this->templateModel->getDefaultTemplate();
                $templateId = ($def && (int)$def['id'] !== 22) ? (int)$def['id'] : 21;
            }
        }
        $template = $this->templateModel->find($templateId);
        if (!$template) {
                    $templateId = (int)($this->request->getGet('template_id') ?? 0);
        $template = null;
        if ($templateId > 0) {
            $template = $this->templateModel->find($templateId);
        }
        if (!$template) {
            $template = $this->templateModel->getDefaultTemplate() ?: $this->templateModel->first();
        }
        }

        $winners = $this->getWinnersByEvent($eventId);
        if (empty($winners)) {
            return redirect()->to(base_url('piagam'))->with('error', 'Belum ada data pemenang untuk nomor acara ini. Silakan input hasil lomba terlebih dahulu.');
        }

        // Susun data sertifikat per pemenang (otomatis perorangan untuk estafet)
        $seq = 1;
        $piagamList = $this->formatPiagamItems($winners, $template, $seq, $eventId);

        $allActiveTemplates = $this->templateModel->where('is_active', 1)->orderBy('is_default', 'DESC')->orderBy('id', 'ASC')->findAll();
        if ($isEvUsia567) {
            // Usia Dini 5, 6, 7 th HANYA boleh mengakses Template 22
            $filteredTemplates = array_values(array_filter($allActiveTemplates, fn($t) => (int)$t['id'] === 22));
        } else {
            // Selain Usia Dini TIDAK BISA mengakses Template 22
            $filteredTemplates = array_values(array_filter($allActiveTemplates, fn($t) => (int)$t['id'] !== 22));
        }

        $this->data['title']      = "Cetak Piagam - Acara {$event['nomor_acara']}: {$event['nama_acara']}";
        $this->data['template']   = $template;
        $this->data['templates']  = $filteredTemplates;
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

        // Hanya cetak piagam untuk event Babak Final (Penyisihan tidak dicetak piagam)
        $rawEvents = $this->eventModel->getEventsWithDetails($kejuaraanId);
        $events = array_values(array_filter($rawEvents, function($ev) {
            return strcasecmp($ev['babak'] ?? '', 'Penyisihan') !== 0;
        }));
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
     * Halaman Pilih Daerah untuk Cetak Piagam
     * URL: /piagam/cetak-daerah
     */
    public function daerahIndex()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $daerahList = $this->getDaerahList($kejuaraanId);

        $winnersByDaerah = [];
        foreach ($daerahList as $d) {
            $winnersByDaerah[$d['daerah']] = $this->getWinnersByDaerah($d['daerah'], $kejuaraanId);
        }

        $this->data['title']           = 'Cetak Piagam Berdasarkan Daerah';
        $this->data['daerahList']      = $daerahList;
        $this->data['winnersByDaerah'] = $winnersByDaerah;
        $this->data['templates']  = $this->templateModel->where('is_active', 1)->orderBy('is_default', 'DESC')->orderBy('id', 'ASC')->findAll();
        $this->data['defaultTemplate'] = $this->templateModel->getDefaultTemplate();

        return view('piagam/daerah_index', $this->data);
    }

    /**
     * Cetak Piagam Pemenang yang Difilter Berdasarkan Daerah Asal (sekolah.alamat)
     * URL: /piagam/cetak-daerah/{slug}
     * Contoh: /piagam/cetak-daerah/kabupaten-tegal
     */
    public function cetakByDaerah(string $daerahSlug = '')
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        // Konversi slug ke nama daerah: "kabupaten-tegal" -> "Kabupaten Tegal"
        $daerahName = ucwords(str_replace('-', ' ', urldecode($daerahSlug)));
        if (empty($daerahName)) {
            return redirect()->to(base_url('piagam/cetak-daerah'))->with('error', 'Daerah tidak dipilih.');
        }

        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $templateId  = (int)($this->request->getGet('template_id') ?? 0);
        $template    = $templateId ? $this->templateModel->find($templateId) : $this->templateModel->getDefaultTemplate();
        if (!$template) {
            $template = $this->templateModel->first();
        }

        // Ambil semua pemenang dari daerah tersebut lintas semua event
        $winners = $this->getWinnersByDaerah($daerahName, $kejuaraanId);

        if (empty($winners)) {
            return redirect()->to(base_url('piagam/cetak-daerah'))->with('error', "Tidak ada pemenang (Juara 1-3) dari daerah \"{$daerahName}\".");
        }

        // Kelompokkan pemenang per event agar formatPiagamItems bisa memproses estafet dengan benar
        $winnersByEvent = [];
        foreach ($winners as $w) {
            $evId = (int)($w['event_id'] ?? 0);
            if (!isset($winnersByEvent[$evId])) {
                $winnersByEvent[$evId] = [];
            }
            $winnersByEvent[$evId][] = $w;
        }

        $piagamList = [];
        $seq = 1;
        foreach ($winnersByEvent as $evId => $evWinners) {
            $items = $this->formatPiagamItems($evWinners, $template, $seq, $evId);
            $piagamList = array_merge($piagamList, $items);
        }

        if (empty($piagamList)) {
            return redirect()->to(base_url('piagam/cetak-daerah'))->with('error', "Tidak ada piagam untuk daerah \"{$daerahName}\".");
        }

        $allActiveTemplates = $this->templateModel->where('is_active', 1)->orderBy('is_default', 'DESC')->orderBy('id', 'ASC')->findAll();
        // Exclude template Usia Dini (id=22)
        $filteredTemplates = array_values(array_filter($allActiveTemplates, fn($t) => (int)$t['id'] !== 22));

        $this->data['title']      = "Cetak Piagam Daerah: {$daerahName} (" . count($piagamList) . ' Piagam)';
        $this->data['template']   = $template;
        $this->data['templates']  = $filteredTemplates;
        $this->data['piagamList'] = $piagamList;
        $this->data['event']      = null;

        return view('piagam/cetak', $this->data);
    }

    /**
     * Halaman Rekap & Link E-Piagam Publik per Sekolah / Kontingen
     * URL: /piagam/sekolah
     */
    public function sekolahIndex()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $db = \Config\Database::connect();

        $builder = $db->table('hasil h')
            ->select('s.id as sekolah_id, s.nama as nama_sekolah, s.jenjang, s.alamat as asal_daerah, s.no_telepon, s.kontak_person,
                      SUM(CASE WHEN h.peringkat = 1 THEN 1 ELSE 0 END) as emas,
                      SUM(CASE WHEN h.peringkat = 2 THEN 1 ELSE 0 END) as perak,
                      SUM(CASE WHEN h.peringkat = 3 THEN 1 ELSE 0 END) as perunggu,
                      COUNT(h.id) as total_juara')
            ->join('peserta_event pe', 'pe.id = h.peserta_event_id')
            ->join('atlet a', 'a.id = pe.atlet_id')
            ->join('sekolah s', 's.id = a.sekolah_id')
            ->join('event e', 'e.id = pe.event_id')
            ->where('h.peringkat IS NOT NULL')
            ->where('h.peringkat >', 0)
            ->where('h.peringkat <=', 3)
            ->where('h.status_prestasi', 'Valid')
            ->where('LOWER(e.babak) !=', 'penyisihan');

        if ($kejuaraanId) {
            $builder->where('e.kejuaraan_id', $kejuaraanId);
        }

        $sekolahList = $builder->groupBy('s.id, s.nama, s.jenjang, s.alamat, s.no_telepon, s.kontak_person')
            ->orderBy('total_juara', 'DESC')
            ->orderBy('emas', 'DESC')
            ->orderBy('perak', 'DESC')
            ->orderBy('perunggu', 'DESC')
            ->orderBy('s.nama', 'ASC')
            ->get()
            ->getResultArray();

        // Siapkan link publik dan ringkasan atlet pemenang untuk tiap sekolah
        foreach ($sekolahList as &$sch) {
            $sch['public_url'] = base_url('piagam/sekolah/' . $sch['sekolah_id']);
            $sch['winners'] = $this->getWinnersBySchool((int)$sch['sekolah_id'], $kejuaraanId);
        }
        unset($sch);

        $this->data['title']           = 'E-Piagam Per Sekolah (Link Publik)';
        $this->data['sekolahList']     = $sekolahList;
        $this->data['defaultTemplate'] = $this->templateModel->getDefaultTemplate();

        return view('piagam/sekolah_index', $this->data);
    }

    /**
     * Unduh File Excel (.xlsx) Rekap Medali & Link E-Piagam Online per Sekolah
     * URL: /piagam/export-sekolah-excel
     */
    public function exportSekolahExcel()
    {
        $filePath = FCPATH . 'downloads/DAFTAR_LINK_E_PIAGAM_SEKOLAH_KEJURWIL_2026.xlsx';
        
        if (!file_exists($filePath)) {
            @exec('python ' . escapeshellarg(ROOTPATH . 'generate_excel.py'));
        }

        if (file_exists($filePath)) {
            return $this->response->download($filePath, null)
                ->setFileName('DAFTAR_LINK_E_PIAGAM_SEKOLAH_KEJURWIL_2026.xlsx');
        }

        return redirect()->back()->with('error', 'File Excel rekap link piagam belum tersedia.');
    }

    /**
     * Tampilan E-Piagam Publik per Sekolah / Kontingen (Dapat diakses online tanpa login)
     * URL: /piagam/sekolah/{id_atau_slug}
     */
    public function sekolah($identifier = null)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        if (empty($identifier)) {
            return redirect()->to(base_url())->with('error', 'Sekolah tidak ditentukan.');
        }

        $db = \Config\Database::connect();
        $sekolah = null;

        if (is_numeric($identifier)) {
            $sekolah = $db->table('sekolah')->where('id', (int)$identifier)->get()->getRowArray();
        } else {
            $cleanName = trim(urldecode(str_replace('-', ' ', (string)$identifier)));
            $sekolah = $db->table('sekolah')->like('nama', $cleanName)->get()->getRowArray();
        }

        if (!$sekolah) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Data sekolah/kontingen tidak ditemukan.");
        }

        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $winners = $this->getWinnersBySchool((int)$sekolah['id'], $kejuaraanId);

        if (empty($winners)) {
            // Fallback: cari di seluruh kejuaraan agar tautan publik sekolah tidak dialihkan / dipindahkan
            $winners = $this->getWinnersBySchool((int)$sekolah['id'], null);
        }

        if (empty($winners)) {
            return redirect()->to(base_url())->with('error', "Belum ada piagam penghargaan juara final untuk kontingen \"{$sekolah['nama']}\".");
        }

                $templateId = (int)($this->request->getGet('template_id') ?? 0);
        $template = null;
        if ($templateId > 0) {
            $template = $this->templateModel->find($templateId);
        }
        if (!$template) {
            $template = $this->templateModel->getDefaultTemplate() ?: $this->templateModel->first();
        }

        // Kelompokkan pemenang per event agar estafet dan data tertata
        $winnersByEvent = [];
        foreach ($winners as $w) {
            $evId = (int)($w['event_id'] ?? 0);
            if (!isset($winnersByEvent[$evId])) {
                $winnersByEvent[$evId] = [];
            }
            $winnersByEvent[$evId][] = $w;
        }

        $piagamList = [];
        $seq = 1;
        foreach ($winnersByEvent as $evId => $evWinners) {
            $items = $this->formatPiagamItems($evWinners, $template, $seq, $evId);
            $piagamList = array_merge($piagamList, $items);
        }

        if (empty($piagamList)) {
            return redirect()->to(base_url())->with('error', "Tidak ada piagam untuk sekolah \"{$sekolah['nama']}\".");
        }

        // Siapkan QR Code Server-Side sebagai Base64 PNG data URI resolusi tinggi & rapi
        $qrOptions = new \chillerlan\QRCode\QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
            'scale'           => 6,
            'eccLevel'        => \chillerlan\QRCode\Common\EccLevel::L,
        ]);
        $qr = new \chillerlan\QRCode\QRCode($qrOptions);

        foreach ($piagamList as &$item) {
            if (!empty($item['verification_url'])) {
                try {
                    $item['qr_data_uri'] = (string)$qr->render($item['verification_url']);
                } catch (\Throwable $e) {
                    $item['qr_data_uri'] = '';
                }
            } else {
                $item['qr_data_uri'] = '';
            }
        }
        unset($item);

        $this->data['title']       = "E-Piagam Resmi - {$sekolah['nama']}";
        $this->data['template']    = $template;
        $this->data['templates']   = [$template];
        $this->data['piagamList']  = $piagamList;
        $this->data['event']       = null;
        $this->data['sekolah']     = $sekolah;
        $this->data['isPublic']    = true;

        return view('piagam/cetak', $this->data);
    }

    /**
     * Unduh Berkas Pure PDF E-Piagam Resmi per Sekolah (Mobile HP & Desktop Friendly)
     * URL: /piagam/download-sekolah-pdf/{id_atau_slug}
     */
    public function downloadSekolahPdf($identifier = null)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        if (empty($identifier)) {
            return redirect()->to(base_url('piagam/sekolah'))->with('error', 'Sekolah tidak ditentukan.');
        }

        $db = \Config\Database::connect();
        $sekolah = null;

        if (is_numeric($identifier)) {
            $sekolah = $db->table('sekolah')->where('id', (int)$identifier)->get()->getRowArray();
        } else {
            $cleanName = trim(urldecode(str_replace('-', ' ', (string)$identifier)));
            $sekolah = $db->table('sekolah')->like('nama', $cleanName)->get()->getRowArray();
        }

        if (!$sekolah) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Data sekolah/kontingen tidak ditemukan.");
        }

        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $winners = $this->getWinnersBySchool((int)$sekolah['id'], $kejuaraanId);

        if (empty($winners)) {
            // Fallback: cari di seluruh kejuaraan agar berkas PDF sekolah publik tetap dapat diunduh
            $winners = $this->getWinnersBySchool((int)$sekolah['id'], null);
        }

        if (empty($winners)) {
            return redirect()->to(base_url('piagam/sekolah'))->with('error', "Belum ada piagam penghargaan juara final untuk kontingen \"{$sekolah['nama']}\".");
        }

                $templateId = (int)($this->request->getGet('template_id') ?? 0);
        $template = null;
        if ($templateId > 0) {
            $template = $this->templateModel->find($templateId);
        }
        if (!$template) {
            $template = $this->templateModel->getDefaultTemplate() ?: $this->templateModel->first();
        }

        // Kelompokkan pemenang per event agar estafet dan data tertata
        $winnersByEvent = [];
        foreach ($winners as $w) {
            $evId = (int)($w['event_id'] ?? 0);
            if (!isset($winnersByEvent[$evId])) {
                $winnersByEvent[$evId] = [];
            }
            $winnersByEvent[$evId][] = $w;
        }

        $piagamList = [];
        $seq = 1;
        foreach ($winnersByEvent as $evId => $evWinners) {
            $items = $this->formatPiagamItems($evWinners, $template, $seq, $evId);
            $piagamList = array_merge($piagamList, $items);
        }

        if (empty($piagamList)) {
            return redirect()->to(base_url('piagam/sekolah'))->with('error', "Tidak ada piagam untuk sekolah \"{$sekolah['nama']}\".");
        }

        // Siapkan QR Code Server-Side sebagai Base64 PNG data URI resolusi tinggi & rapi
        $qrOptions = new \chillerlan\QRCode\QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
            'scale'           => 6,
            'eccLevel'        => \chillerlan\QRCode\Common\EccLevel::L,
        ]);
        $qr = new \chillerlan\QRCode\QRCode($qrOptions);

        foreach ($piagamList as &$item) {
            if (!empty($item['verification_url'])) {
                try {
                    $item['qr_data_uri'] = (string)$qr->render($item['verification_url']);
                } catch (\Throwable $e) {
                    $item['qr_data_uri'] = '';
                }
            } else {
                $item['qr_data_uri'] = '';
            }
        }
        unset($item);

        // Siapkan data URI gambar background dan tanda tangan
        $bgFile = $template['file_background'] ?: 'template_1790317760_9b669099.png';
        $bgPath = FCPATH . 'uploads/piagam/templates/' . $bgFile;
        $bgDataUri = file_exists($bgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bgPath)) : '';

        $loadSig = function($fn) {
            $p = FCPATH . 'uploads/piagam/ttd/' . $fn;
            if (!file_exists($p)) $p = FCPATH . 'assets/images/' . $fn;
            return file_exists($p) ? 'data:image/png;base64,' . base64_encode(file_get_contents($p)) : '';
        };

        $sig1DataUri = $loadSig('ttd_jumanto_pasi_pekalongan.png');
        $sig2DataUri = $loadSig('ttd_zaini_pasi_pekalongan.png');
        $sig3DataUri = $loadSig('ttd_agus_widodo_pasi_jateng.png');

        $viewData = [
            'title'       => "E-Piagam Resmi - {$sekolah['nama']}",
            'sekolah'     => $sekolah,
            'template'    => $template,
            'piagamList'  => $piagamList,
            'bgDataUri'   => $bgDataUri,
            'sig1DataUri' => $sig1DataUri,
            'sig2DataUri' => $sig2DataUri,
            'sig3DataUri' => $sig3DataUri,
        ];

        $html = view('piagam/cetak_pdf', $viewData);

        // Render PDF murni menggunakan Dompdf
        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->set('isRemoteEnabled', true);
        $dompdfOptions->set('isHtml5ParserEnabled', true);
        $dompdfOptions->set('dpi', 150);
        $dompdfOptions->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($dompdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 609.45, 935.43], 'portrait'); // F4 Folio Portrait (215mm x 330mm)
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        $cleanSchool = preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($sekolah['nama'] ?? 'Sekolah'));
        $cleanSchool = preg_replace('/_+/', '_', $cleanSchool);
        $filename = "E-Piagam_{$cleanSchool}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string)strlen($pdfOutput))
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->setHeader('Pragma', 'public')
            ->setBody($pdfOutput);
    }

    /**
     * Helper: Ambil pemenang (Juara 1-3 Babak Final) khusus sekolah tertentu
     */
    protected function getWinnersBySchool(int $sekolahId, ?int $kejuaraanId = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('hasil h')
            ->select('h.*, pe.id as peserta_event_id, pe.lane as no_lintasan, pe.heat as seri, pe.event_id, a.id as atlet_id, a.no_dada as nomor_dada, a.nama as nama_atlet, a.catatan as catatan_atlet, a.gender, a.tanggal_lahir, a.akta_file, a.akta_url, s.id as sekolah_id, s.nama as nama_sekolah, s.alamat as asal_daerah, e.nama_acara, e.nomor_acara, e.gender as event_gender, nl.nama as nama_lomba, nl.satuan_hasil, ku.nama as nama_ku, ku.keterangan as ket_ku')
            ->join('peserta_event pe', 'pe.id = h.peserta_event_id')
            ->join('atlet a', 'a.id = pe.atlet_id')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('event e', 'e.id = pe.event_id')
            ->join('nomor_lomba nl', 'nl.id = e.nomor_lomba_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = e.kelompok_umur_id', 'left')
            ->where('h.peringkat IS NOT NULL')
            ->where('h.peringkat >', 0)
            ->where('h.peringkat <=', 3)
            ->where('h.status_prestasi', 'Valid')
            ->where('LOWER(e.babak) !=', 'penyisihan')
            ->where('s.id', $sekolahId);

        if ($kejuaraanId) {
            $builder->where('e.kejuaraan_id', $kejuaraanId);
        }

        return $builder->orderBy('e.nomor_acara', 'ASC')
            ->orderBy('h.peringkat', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Helper: Ambil daftar daerah beserta jumlah pemenang (Juara 1-3)
     */
    protected function getDaerahList(?int $kejuaraanId = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('hasil h')
            ->select('s.alamat as daerah, COUNT(*) as jumlah_pemenang')
            ->join('peserta_event pe', 'pe.id = h.peserta_event_id')
            ->join('atlet a', 'a.id = pe.atlet_id')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('event e', 'e.id = pe.event_id')
            ->where('h.peringkat IS NOT NULL')
            ->where('h.peringkat >', 0)
            ->where('h.peringkat <=', 3)
            ->where('h.status_prestasi', 'Valid')
            ->where('LOWER(e.babak) !=', 'penyisihan');

        if ($kejuaraanId) {
            $builder->where('e.kejuaraan_id', $kejuaraanId);
        }

        return $builder->groupBy('s.alamat')
            ->orderBy('jumlah_pemenang', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Helper: Ambil pemenang (Juara 1-3) dari semua event, difilter berdasarkan daerah asal
     */
    protected function getWinnersByDaerah(string $daerahName, ?int $kejuaraanId = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('hasil h')
            ->select('h.*, pe.id as peserta_event_id, pe.lane as no_lintasan, pe.heat as seri, pe.event_id, a.id as atlet_id, a.no_dada as nomor_dada, a.nama as nama_atlet, a.catatan as catatan_atlet, a.gender, a.tanggal_lahir, a.akta_file, a.akta_url, s.nama as nama_sekolah, s.alamat as asal_daerah, e.nama_acara, e.nomor_acara, e.gender as event_gender, nl.nama as nama_lomba, nl.satuan_hasil, ku.nama as nama_ku, ku.keterangan as ket_ku')
            ->join('peserta_event pe', 'pe.id = h.peserta_event_id')
            ->join('atlet a', 'a.id = pe.atlet_id')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('event e', 'e.id = pe.event_id')
            ->join('nomor_lomba nl', 'nl.id = e.nomor_lomba_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = e.kelompok_umur_id', 'left')
            ->where('h.peringkat IS NOT NULL')
            ->where('h.peringkat >', 0)
            ->where('h.peringkat <=', 3)
            ->where('h.status_prestasi', 'Valid')
            ->where('LOWER(e.babak) !=', 'penyisihan')
            ->where('s.alamat', $daerahName);

        if ($kejuaraanId) {
            $builder->where('e.kejuaraan_id', $kejuaraanId);
        }

        return $builder->orderBy('e.nomor_acara', 'ASC')
            ->orderBy('h.peringkat', 'ASC')
            ->get()
            ->getResultArray();
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

        // Jika opsi simpan_ke_database dicentang, update tanggal & tempat ke tabel kejuaraan
        $simpanKeDb = $this->request->getPost('simpan_ke_database') ?? $this->request->getGet('simpan_ke_database');
        if ($simpanKeDb == '1' || $simpanKeDb === 'true') {
            $kejuaraanController = new \App\Controllers\Kejuaraan();
            $parsedDate = $kejuaraanController->parseIndonesianDate($customItem['tanggal']);
            if ($parsedDate) {
                $kId = $this->activeKejuaraan['id'] ?? 1;
                $upData = ['tanggal_mulai' => $parsedDate, 'tanggal_selesai' => $parsedDate];
                if (!empty($customItem['tempat'])) {
                    $upData['tempat'] = $customItem['tempat'];
                }
                (new \App\Models\KejuaraanModel())->update($kId, $upData);
            }
        }

        $nomorLombaInput   = $getOrPost('nomor_lomba', '');
        $kategoriUsiaInput = $getOrPost('kategori_usia', '');
        $kejuaraanInput    = $getOrPost('nama_kejuaraan', '');
        $paramHanyaPekalongan = $this->request->getPost('hanya_ketua_pekalongan') ?? $this->request->getGet('hanya_ketua_pekalongan');
        $checkUsia567 = strtolower($nomorLombaInput . ' ' . $kategoriUsiaInput . ' ' . $kejuaraanInput);
        $isCustomUsia567 = (preg_match('/\b(u[\s\-_]?[567]|ku[\s\-_]?[567]|juaraku|usia[\s\-_]?dini|[567][\s\-_]?th|[567][\s\-_]?tahun)\b|5-6|567|juaraku/i', $checkUsia567) || ($paramHanyaPekalongan == '1' || $paramHanyaPekalongan === 'true'));

        $templateId = (int)($this->request->getPost('template_id') ?? $this->request->getGet('template_id') ?? 0);
        if ($isCustomUsia567) {
            // Usia Dini 5, 6, 7 th HANYA boleh menggunakan Template 22
            $templateId = 22;
        } else {
            // Selain Usia Dini TIDAK BISA menggunakan Template 22
            if ($templateId <= 0 || $templateId === 22) {
                $def = $this->templateModel->getDefaultTemplate();
                $templateId = ($def && (int)$def['id'] !== 22) ? (int)$def['id'] : 21;
            }
        }
        $template = $this->templateModel->find($templateId);
        if (!$template) {
                    $templateId = (int)($this->request->getGet('template_id') ?? 0);
        $template = null;
        if ($templateId > 0) {
            $template = $this->templateModel->find($templateId);
        }
        if (!$template) {
            $template = $this->templateModel->getDefaultTemplate() ?: $this->templateModel->first();
        }
        }

        $allActiveTemplates = $this->templateModel->where('is_active', 1)->orderBy('is_default', 'DESC')->orderBy('id', 'ASC')->findAll();
        if ($isCustomUsia567) {
            $filteredTemplates = array_values(array_filter($allActiveTemplates, fn($t) => (int)$t['id'] === 22));
        } else {
            $filteredTemplates = array_values(array_filter($allActiveTemplates, fn($t) => (int)$t['id'] !== 22));
        }

        $this->data['title']      = "Cetak Piagam Kustom - " . esc($customItem['nama_pemenang']);
        $this->data['template']   = $template;
        $this->data['templates']  = $filteredTemplates;
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
        $query->groupStart()->where('kejuaraan_id', 1)->orWhere('kejuaraan_id', null)->groupEnd();
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
        $target = $this->templateModel->find($id);
        $kId = $target['kejuaraan_id'] ?? 1;
        $this->templateModel->where('kejuaraan_id', $kId)->where('id !=', $id)->set(['is_default' => 0])->update();
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
                        ['001', $tahun],
                        $template['format_nomor'] ?? '001/SERT/PASIJATENG/IX/{tahun}'
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
                    ['001', $tahun],
                    $template['format_nomor'] ?? '001/SERT/PASIJATENG/IX/{tahun}'
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
            ->select('h.*, pe.lane as no_lintasan, pe.heat as seri, a.id as atlet_id, a.no_dada as nomor_dada, a.nama as nama_atlet, a.catatan as catatan_atlet, a.gender, a.tanggal_lahir, a.akta_file, a.akta_url, s.nama as nama_sekolah, s.alamat as asal_daerah, e.nama_acara, e.nomor_acara, e.gender as event_gender, nl.nama as nama_lomba, nl.satuan_hasil, ku.nama as nama_ku, ku.keterangan as ket_ku')
            ->join('peserta_event pe', 'pe.id = h.peserta_event_id')
            ->join('atlet a', 'a.id = pe.atlet_id')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('event e', 'e.id = pe.event_id')
            ->join('nomor_lomba nl', 'nl.id = e.nomor_lomba_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = e.kelompok_umur_id', 'left')
            ->where('pe.event_id', $eventId)
            ->where('LOWER(e.babak) !=', 'penyisihan')
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
            if (!empty($kj['id']) && (int)$kj['id'] === 2) {
                return '29 Agustus 2026';
            }
            return '27 September 2026';
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
    public function verifikasi(...$params)
    {
        // 0. Ambil full path input dari parameter / URI
        $raw = !empty($params) ? implode('/', $params) : '';
        if (empty($raw)) {
            $uri = service('uri');
            $segments = $uri->getSegments();
            $idx = array_search('piagam', $segments);
            if ($idx !== false && isset($segments[$idx + 1])) {
                $raw = implode('/', array_slice($segments, $idx + 1));
            }
        }

        $code = trim($raw);

        // 1. Sanitasi cerdas jika scanner kamera HP menempelkan beberapa URL sekaligus
        if (stripos($code, 'http://') !== false || stripos($code, 'https://') !== false) {
            $tokens = preg_split('/https?:\/\/[^\/]+\/verifikasi\/piagam\/|https?:\/\//i', $code);
            $cleanTokens = array_values(array_filter(array_map('trim', $tokens)));
            if (!empty($cleanTokens)) {
                $code = end($cleanTokens);
            }
        }

        // Cari pola PASI spesifik (ambil yang terakhir jika ada beberapa)
        if (preg_match_all('/PASI-\d+-\d+-\d+-[a-f0-9]{8}/i', $code, $pasiMatches)) {
            $code = end($pasiMatches[0]);
        }
        // Cari pola UUID spesifik jika ada
        elseif (preg_match_all('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/i', $code, $uuidMatches)) {
            $code = end($uuidMatches[0]);
        }

        $code = trim($code, " /");

        // Helper fungsi konversi waktu resmi bahasa Indonesia
        $formatWaktuId = function($timestamp) {
            $ts = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
            $waktu = date('l, d F Y', $ts) . ' pukul ' . date('H:i', $ts);
            $hariEn = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            $hariId = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            $bulanEn = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            $bulanId = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            $waktu = str_replace($hariEn, $hariId, $waktu);
            return str_replace($bulanEn, $bulanId, $waktu);
        };

        // 1. Cek dari piagam_custom (Kejurkot Wali Kota & sertifikat custom standalone)
        $customWinner = $this->customModel
            ->where('uuid', $code)
            ->orWhere('nomor_piagam', $code)
            ->first();

        if ($customWinner) {
            $kejuaraanId = $customWinner['kejuaraan_id'] ?? 2;
            $kejuaraan = (new \App\Models\KejuaraanModel())->find($kejuaraanId) ?: [
                'nama'   => $customWinner['nama_kejuaraan'] ?? 'KEJUARAAN ATLETIK KELOMPOK UMUR TINGKAT KOTA PEKALONGAN TAHUN 2026',
                'tempat' => $customWinner['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan',
            ];

            $winnerData = [
                'nama_atlet'     => $customWinner['nama_pemenang'],
                'sekolah'        => $customWinner['asal_sekolah'] ?: ($customWinner['asal_kota'] ?: 'Kota Pekalongan'),
                'peringkat'      => $customWinner['peringkat'] ?? 1,
                'peringkat_teks' => $customWinner['peringkat_teks'] ?? 'Pertama',
                'prestasi'       => $customWinner['prestasi'] ?? '-',
                'nomor_piagam'   => $customWinner['nomor_piagam'] ?? '-',
            ];

            $eventData = [
                'nama_acara'   => $customWinner['nomor_lomba'] ?? 'Atletik',
                'satuan_hasil' => '',
            ];

            $waktuTtd = !empty($customWinner['created_at'])
                ? $formatWaktuId($customWinner['created_at'])
                : 'Sabtu, 29 Agustus 2026 pukul 10:00';

            $hashCode = hash('sha256', $code . ($customWinner['nomor_piagam'] ?? ''));

            // KEJUARAAN WALI KOTA: HANYA 1 PENANDATANGAN (ZAINI, S.Pd.)
            $signers = [
                [
                    'nama'     => 'Zaini, S.Pd.',
                    'jabatan'  => 'Ketua Pengkot PASI Kota Pekalongan',
                    'unit'     => 'PENGURUS KOTA PASI KOTA PEKALONGAN',
                    'instansi' => 'Persatuan Atletik Seluruh Indonesia (PASI)',
                    'waktu'    => $waktuTtd,
                ]
            ];

            return view('piagam/verifikasi', [
                'valid'        => true,
                'code'         => $code,
                'hash'         => $hashCode,
                'winner'       => $winnerData,
                'kejuaraan'    => $kejuaraan,
                'event'        => $eventData,
                'juaraText'    => $this->getJuaraText((int)($customWinner['peringkat'] ?? 1)),
                'tanggal'      => $customWinner['tanggal'] ?? '29 Agustus 2026',
                'nomor_piagam' => $customWinner['nomor_piagam'] ?? '-',
                'signers'      => $signers,
                'cetak_url'    => base_url('piagam-kejurkot/cetak/' . $customWinner['id']),
            ]);
        }

        // 2. Parse kode untuk kejuaraan existing (Eks-Karesidenan): PASI-{kejuaraanId}-{eventId}-{peringkat}-{hash}
        if (!preg_match('/^PASI-(\d+)-(\d+)-(\d+)-([a-f0-9]{8})$/i', $code, $m)) {
            return view('piagam/verifikasi', [
                'valid' => false,
                'code'  => $code,
                'hash'  => hash('sha256', $code),
                'error' => 'Format kode verifikasi tidak valid atau tidak terdaftar.',
            ]);
        }

        $kejuaraanId = (int)$m[1];
        $eventId     = (int)$m[2];
        $peringkat   = (int)$m[3];
        $hash        = $m[4];

        // Cari peserta_event_id yang cocok
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
                'hash'  => hash('sha256', $code),
                'error' => 'Piagam dengan kode ini tidak ditemukan dalam database resmi.',
            ]);
        }

        // Ambil data kejuaraan & event
        $kejuaraan = (new \App\Models\KejuaraanModel())->find($kejuaraanId);
        $event = $this->eventModel->getEventWithDetails($eventId);

        // DETEKSI USIA DINI (5, 6, 7 TAHUN) VS KARESIDENAN UMUM
        $kuId = (int)($event['kelompok_umur_id'] ?? 0);
        $kuCheckStr = strtolower(($event['kategori_usia'] ?? '') . ' ' . ($event['nama_acara'] ?? '') . ' ' . ($event['nama_ku'] ?? '') . ' ' . ($event['keterangan'] ?? ''));
        $isUsia567 = ($kuId === 1) || preg_match('/\b(u[\s\-_]?[567]|ku[\s\-_]?[567]|juaraku|usia[\s\-_]?dini|[567][\s\-_]?th|[567][\s\-_]?tahun)\b|5-6|567|juaraku/i', $kuCheckStr);

        $waktuKares = 'Minggu, 27 September 2026 pukul 14:00';

        if ($isUsia567) {
            // USIA DINI 5, 6, 7 TH: HANYA 1 PENANDATANGAN (ZAINI, S.Pd.)
            $signers = [
                [
                    'nama'     => 'Zaini, S.Pd.',
                    'jabatan'  => 'Ketua Panitia Pelaksana',
                    'unit'     => 'PENGURUS KOTA PASI KOTA PEKALONGAN',
                    'instansi' => 'Persatuan Atletik Seluruh Indonesia (PASI)',
                    'waktu'    => $waktuKares,
                ]
            ];
        } else {
            // EKS-KARESIDENAN: 3 PENANDATANGAN LENGKAP
            $signers = [
                [
                    'nama'     => 'Jumanto, S.Pd., M.Si.',
                    'jabatan'  => 'Koordinator Wilayah Eks-Karesidenan Pekalongan',
                    'unit'     => 'KOORDINATOR WILAYAH EKS-KARESIDENAN PEKALONGAN',
                    'instansi' => 'Persatuan Atletik Seluruh Indonesia (PASI)',
                    'waktu'    => $waktuKares,
                ],
                [
                    'nama'     => 'Zaini, S.Pd.',
                    'jabatan'  => 'Ketua Panitia Pelaksana',
                    'unit'     => 'PANITIA PELAKSANA KEJURWIL ATLETIK 2026',
                    'instansi' => 'Pengkot PASI Kota Pekalongan',
                    'waktu'    => $waktuKares,
                ],
                [
                    'nama'     => 'Dr. Agus Widodo Suripto, M. Pd.',
                    'jabatan'  => 'Plt. Ketua Umum Pengurus Provinsi PASI Jawa Tengah',
                    'unit'     => 'PENGURUS PROVINSI PASI JAWA TENGAH',
                    'instansi' => 'Persatuan Atletik Seluruh Indonesia (PASI)',
                    'waktu'    => $waktuKares,
                ],
            ];
        }

        return view('piagam/verifikasi', [
            'valid'        => true,
            'code'         => $code,
            'hash'         => hash('sha256', "{$kejuaraanId}-{$eventId}-" . ($matchedWinner['id'] ?? 0) . "-{$secret}"),
            'winner'       => $matchedWinner,
            'kejuaraan'    => $kejuaraan,
            'event'        => $event,
            'juaraText'    => $this->getJuaraText($peringkat),
            'tanggal'      => $this->formatTanggalKejuaraan($kejuaraan ?: []),
            'nomor_piagam' => $matchedWinner['nomor_piagam'] ?? "PASI-{$kejuaraanId}-{$eventId}-{$peringkat}",
            'signers'      => $signers,
            'cetak_url'    => base_url('piagam/cetak-event/' . $eventId),
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
            $rawInput = trim((string)$this->request->getPost('custom_offsets'));
            $newOffsets = json_decode($rawInput, true);
            if (is_array($newOffsets)) {
                // Unnest segala nested wrapper jika ada
                while (isset($newOffsets['kejurwil']) && is_array($newOffsets['kejurwil'])) {
                    $newOffsets = $newOffsets['kejurwil'];
                }
                while (isset($newOffsets['kejurkot']) && is_array($newOffsets['kejurkot'])) {
                    $newOffsets = $newOffsets['kejurkot'];
                }

                if (isset($newOffsets['usia_567']) || isset($newOffsets['umum'])) {
                    $data['custom_offsets'] = json_encode([
                        'umum'     => is_array($newOffsets['umum'] ?? null) ? $newOffsets['umum'] : [],
                        'usia_567' => is_array($newOffsets['usia_567'] ?? null) ? $newOffsets['usia_567'] : [],
                    ]);
                } else {
                    $data['custom_offsets'] = json_encode($newOffsets);
                }
            } else {
                $data['custom_offsets'] = $rawInput;
            }
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

    /**
     * API: Ambil Data Akta Kelahiran Atlet untuk Preview Modal
     * Pastikan aman (Auth check, sanitize input, return metadata)
     */
    public function previewAkta(int $atletId)
    {
        // 1. Verifikasi Autentikasi Pengguna
        $session = session();
        if (!$session->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Akses tidak diizinkan. Silakan login terlebih dahulu.',
            ]);
        }

        $db = \Config\Database::connect();
        $atlet = $db->table('atlet a')
            ->select('a.id, a.nama, a.no_dada, a.gender, a.tanggal_lahir, a.catatan, a.akta_file, a.akta_url, 
                      s.nama as nama_sekolah, s.alamat as asal_daerah, ku.nama as nama_ku')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = a.kelompok_umur_id', 'left')
            ->where('a.id', (int)$atletId)
            ->get()
            ->getRowArray();

        if (!$atlet) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Data atlet tidak ditemukan.',
            ]);
        }

        // Cek file lokal
        $hasLocalFile = false;
        $fileType = null;
        if (!empty($atlet['akta_file'])) {
            $filename = basename($atlet['akta_file']);
            $filePath = WRITEPATH . 'uploads/akta/' . $filename;
            if (file_exists($filePath) && is_file($filePath)) {
                $hasLocalFile = true;
                $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            }
        }

        // Info audit untuk watermark
        $userSession = $session->get('user') ?? [];
        $adminName = $userSession['nama'] ?? ($session->get('username') ?? 'Admin');
        $watermarkText = 'DOKUMEN RAHASIA â€¢ HANYA UNTUK VERIFIKASI KEJURWIL â€¢ ' . strtoupper($adminName) . ' â€¢ ' . date('d/m/Y H:i');

        return $this->response->setJSON([
            'status'         => 'success',
            'atlet'          => [
                'id'            => (int)$atlet['id'],
                'nama'          => $atlet['nama'],
                'no_dada'       => $atlet['no_dada'] ?: '-',
                'gender'        => $atlet['gender'] === 'L' ? 'Laki-laki' : 'Perempuan',
                'tanggal_lahir' => $atlet['tanggal_lahir'] ? date('d-m-Y', strtotime($atlet['tanggal_lahir'])) : '-',
                'tgl_raw'       => $atlet['tanggal_lahir'] ?: '',
                'nama_sekolah'  => $atlet['nama_sekolah'] ?: 'Independen',
                'asal_daerah'   => $atlet['asal_daerah'] ?: '-',
                'nama_ku'       => $atlet['nama_ku'] ?: '-',
                'has_local'     => $hasLocalFile,
                'file_type'     => $fileType,
                'view_url'      => $hasLocalFile ? base_url('piagam/view-akta-file/' . $atlet['id']) : null,
                'akta_url'      => !empty($atlet['akta_url']) ? $atlet['akta_url'] : null,
            ],
            'watermark'      => $watermarkText,
        ]);
    }

    /**
     * Streaming File Akta dengan Proteksi Keamanan Tinggi
     * - Hanya user login
     * - File di luar public directory (writable/uploads/akta/)
     * - Anti-directory traversal
     * - Anti-cache headers & no-sniff
     */
    public function viewAktaFile(int $atletId)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $atlet = $db->table('atlet')->select('id, akta_file')->where('id', (int)$atletId)->get()->getRowArray();
        if (!$atlet || empty($atlet['akta_file'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Dokumen tidak ditemukan.');
        }

        // Sanitasi nama file ketat (hanya alfanumerik, titik, minus, underscore)
        $filename = basename($atlet['akta_file']);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak valid.');
        }

        $baseDir = realpath(WRITEPATH . 'uploads/akta');
        $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . $filename);

        // Pastikan tidak ada path traversal di luar base directory
        if (!$filePath || strpos($filePath, $baseDir) !== 0 || !is_file($filePath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Dokumen tidak ditemukan.');
        }

        // Tentukan MIME type aman
        $mime = 'application/octet-stream';
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg'])) {
            $mime = 'image/jpeg';
        } elseif ($ext === 'png') {
            $mime = 'image/png';
        } elseif ($ext === 'webp') {
            $mime = 'image/webp';
        } elseif ($ext === 'pdf') {
            $mime = 'application/pdf';
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Format file tidak diizinkan.');
        }

        // Kirim dengan HTTP security headers lengkap
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($filePath));
    }

    /**
     * API: Perbarui Nama Atlet Sesuai Akte Langsung dari Modal Preview
     */
    public function updateNamaAtlet()
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Akses ditolak. Silakan login kembali.',
            ]);
        }

        $atletId = (int)$this->request->getPost('atlet_id');
        $namaBaru = trim((string)$this->request->getPost('nama'));

        if (!$atletId || empty($namaBaru)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'ID atlet dan nama baru wajib diisi.',
            ]);
        }

        // Sanitasi teks nama
        $namaBaru = strtoupper(strip_tags($namaBaru));
        if (strlen($namaBaru) > 150) {
            $namaBaru = substr($namaBaru, 0, 150);
        }

        $db = \Config\Database::connect();
        $db->table('atlet')->where('id', $atletId)->update([
            'nama'       => $namaBaru,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Nama atlet berhasil diperbarui menjadi: ' . $namaBaru,
            'atlet_id' => $atletId,
            'nama'     => $namaBaru,
        ]);
    }

    /**
     * API: Upload Berkas Akta Baru atau Update Link Google Drive untuk Atlet
     */
    public function uploadAkta()
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Sesi login telah berakhir. Silakan login kembali.',
            ]);
        }

        $atletId = (int)$this->request->getPost('atlet_id');
        $aktaUrl = trim((string)$this->request->getPost('akta_url'));
        $file    = $this->request->getFile('file_akta');

        $db = \Config\Database::connect();
        $atlet = $db->table('atlet')->where('id', $atletId)->get()->getRowArray();
        if (!$atlet) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Data atlet tidak ditemukan.',
            ]);
        }

        $updateData = [];

        // 1. Jika ada upload file fisik
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            $mime = $file->getMimeType();
            if (!in_array($mime, $allowedMimes)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Format berkas tidak diizinkan. Hanya menerima JPG, PNG, atau PDF.',
                ]);
            }

            if ($file->getSizeByUnit('mb') > 15) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Ukuran berkas terlalu besar. Maksimum 15 MB.',
                ]);
            }

            $storageDir = WRITEPATH . 'uploads/akta';
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            $ext = strtolower($file->getClientExtension());
            $safeName = 'akta_' . $atletId . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($atlet['nama'])) . '.' . $ext;
            if (strlen($safeName) > 100) {
                $safeName = 'akta_' . $atletId . '_' . time() . '.' . $ext;
            }

            $file->move($storageDir, $safeName, true);
            $updateData['akta_file'] = $safeName;
        }

        // 2. Jika ada update URL Drive
        if (!empty($aktaUrl)) {
            $updateData['akta_url'] = $aktaUrl;
        }

        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $db->table('atlet')->where('id', $atletId)->update($updateData);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Dokumen Akta Kelahiran berhasil disimpan!',
                'atlet_id'=> $atletId,
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status'  => 'error',
            'message' => 'Silakan pilih berkas dokumen atau masukkan tautan Google Drive.',
        ]);
    }

    // =========================================================================
    // MODUL PIAGAM KEJURKOT KOTA PEKALONGAN 2026 (STANDALONE - ISOLASI TOTAL)
    // =========================================================================
    public const KEJURKOT_ID = 2;

    /**
     * Halaman Utama Piagam Kejurkot 2026 (Standalone & Terpisah dari Eks-Karesidenan)
     */
    public function kejurkotIndex()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? self::KEJURKOT_ID;
        $kejuaraan   = (new \App\Models\KejuaraanModel())->find($kejuaraanId);
        if (!$kejuaraan) {
            $kejuaraan = (new \App\Models\KejuaraanModel())
                ->like('nama', 'KOTA PEKALONGAN')
                ->first();
        }
        $kejuaraanId = $kejuaraan['id'] ?? self::KEJURKOT_ID;

        // Ambil data pemenang khusus Kejurkot
        $pemenang = $this->customModel
            ->where('kejuaraan_id', $kejuaraanId)
            ->orderBy('id', 'ASC')
            ->findAll();

        // Ambil template khusus Kejurkot
        $template = $this->templateModel
            ->where('kejuaraan_id', $kejuaraanId)
            ->first();

        if (!$template) {
            $template = $this->templateModel->find(23);
        }

        // Hitung nomor sertifikat berikutnya
        $nextNomorInt = count($pemenang) + 1;
        $nextNomorFormatted = sprintf('%03d/SERT/PASI.KOTA/VIII/2026', $nextNomorInt);

        // Kelompokkan pemenang berdasarkan nomor lomba (1., 2., 3., dst.)
        $groups = [];
        foreach ($pemenang as $p) {
            $nomorLomba = trim($p['nomor_lomba'] ?? '');
            if (preg_match('/^(\d+)\.\s*(.*)/', $nomorLomba, $m)) {
                $groupNum = (int)$m[1];
                $titleRaw = trim($m[2]);
            } else {
                $groupNum = 999;
                $titleRaw = $nomorLomba ?: 'Lainnya';
            }

            if (!isset($groups[$groupNum])) {
                $groups[$groupNum] = [
                    'number' => $groupNum,
                    'title'  => $titleRaw,
                    'titles' => [],
                    'count'  => 0,
                    'items'  => [],
                ];
            }

            if (!in_array($titleRaw, $groups[$groupNum]['titles'])) {
                $groups[$groupNum]['titles'][] = $titleRaw;
            }
            $groups[$groupNum]['count']++;
            $groups[$groupNum]['items'][] = $p;
        }

        // Susun judul ringkas untuk setiap nomor
        foreach ($groups as $gNum => &$gData) {
            if (count($gData['titles']) === 1) {
                $gData['title'] = $gData['titles'][0];
            } else {
                $cleaned = [];
                foreach ($gData['titles'] as $t) {
                    $c = trim(preg_replace('/\b(Pa|Pi|PA|PI)\b/i', '', $t));
                    $c = preg_replace('/\s+/', ' ', $c);
                    if (!in_array($c, $cleaned)) {
                        $cleaned[] = $c;
                    }
                }
                $gData['title'] = implode(' & ', $cleaned);
            }
        }
        unset($gData);
        ksort($groups);

        return view('piagam/kejurkot_index', [
            'title'              => 'Piagam Kejurkot Kota Pekalongan 2026',
            'kejuaraan'          => $kejuaraan,
            'pemenang'           => $pemenang,
            'groups'             => $groups,
            'template'           => $template,
            'totalPemenang'      => count($pemenang),
            'nextNomorFormatted' => $nextNomorFormatted,
            'nextNomorInt'       => $nextNomorInt,
        ]);
    }

    /**
     * Simpan / Tambah / Edit Pemenang Piagam Kejurkot
     */
    public function saveKejurkotPemenang()
    {
        $id = $this->request->getPost('id');
        $namaPemenang = trim($this->request->getPost('nama_pemenang') ?? '');
        $asalSekolah  = trim($this->request->getPost('asal_sekolah') ?? '');
        $asalKota     = trim($this->request->getPost('asal_kota') ?? 'Kota Pekalongan');
        $nomorLomba   = trim($this->request->getPost('nomor_lomba') ?? '');
        $peringkat    = (int)($this->request->getPost('peringkat') ?? 1);
        $prestasi     = trim($this->request->getPost('prestasi') ?? '-');
        $nomorPiagam  = trim($this->request->getPost('nomor_piagam') ?? '');

        if (empty($namaPemenang) || empty($nomorLomba)) {
            return redirect()->back()->with('error', 'Nama Pemenang dan Nomor Lomba wajib diisi.');
        }

        $kejuaraan = (new \App\Models\KejuaraanModel())->find(self::KEJURKOT_ID);
        $namaKejuaraan = $kejuaraan['nama'] ?? 'KEJUARAAN ATLETIK KELOMPOK UMUR TINGKAT KOTA PEKALONGAN TAHUN 2026';
        $tempat = $kejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan';
        $tanggal = '29 Agustus 2026';

        // Peringkat teks
        $peringkatTeksMap = [
            1 => 'Pertama',
            2 => 'Kedua',
            3 => 'Ketiga',
            4 => 'Keempat',
            5 => 'Kelima'
        ];
        $peringkatTeks = $this->request->getPost('peringkat_teks') ?: ($peringkatTeksMap[$peringkat] ?? "Ke-{$peringkat}");

        // Auto nomor sertifikat jika kosong
        if (empty($nomorPiagam)) {
            $count = $this->customModel->where('kejuaraan_id', self::KEJURKOT_ID)->countAllResults();
            $nomorPiagam = sprintf('%03d/SERT/PASI.KOTA/VIII/2026', $count + 1);
        }

        // UUID
        $uuid = $this->request->getPost('uuid');
        if (empty($uuid)) {
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }

        $data = [
            'kejuaraan_id'    => self::KEJURKOT_ID,
            'template_id'     => 23,
            'uuid'            => $uuid,
            'nomor_piagam'    => $nomorPiagam,
            'nama_pemenang'   => $namaPemenang,
            'asal_sekolah'    => $asalSekolah,
            'asal_kota'       => $asalKota,
            'peringkat'       => $peringkat,
            'peringkat_teks'  => $peringkatTeks,
            'nomor_lomba'     => $nomorLomba,
            'prestasi'        => $prestasi ?: '-',
            'nama_kejuaraan'  => $namaKejuaraan,
            'tempat'          => $tempat,
            'tanggal'         => $tanggal,
            'ttd_1_jabatan'   => 'Ketua',
            'ttd_1_nama'      => 'Zaini, S.Pd.',
        ];

        if (!empty($id)) {
            // Update
            $existing = $this->customModel->where('id', $id)->where('kejuaraan_id', self::KEJURKOT_ID)->first();
            if ($existing) {
                unset($data['uuid']); // pertahankan uuid awal
                $this->customModel->update($id, $data);
                return redirect()->to(base_url('piagam-kejurkot'))->with('success', 'Data pemenang berhasil diperbarui.');
            }
        }

        $this->customModel->insert($data);
        return redirect()->to(base_url('piagam-kejurkot'))->with('success', 'Data pemenang baru berhasil ditambahkan.');
    }

    /**
     * Hapus Pemenang Piagam Kejurkot
     */
    public function deleteKejurkotPemenang($id)
    {
        $existing = $this->customModel->where('id', $id)->where('kejuaraan_id', self::KEJURKOT_ID)->first();
        if ($existing) {
            $this->customModel->delete($id);
            return redirect()->to(base_url('piagam-kejurkot'))->with('success', 'Data pemenang berhasil dihapus.');
        }
        return redirect()->to(base_url('piagam-kejurkot'))->with('error', 'Data tidak ditemukan.');
    }

    /**
     * Cetak Semua Piagam Kejurkot (Landscape A4 + Background Trophy + QR Code + TTD Zaeni)
     */
    public function cetakKejurkot()
    {
        $nomor = $this->request->getGet('nomor');
        $builder = $this->customModel->where('kejuaraan_id', self::KEJURKOT_ID);

        if (!empty($nomor)) {
            // Filter nomor awalan, contoh: "1." atau "2."
            $builder->like('nomor_lomba', $nomor . '.', 'after');
        }

        $pemenang = $builder->orderBy('id', 'ASC')->findAll();

        if (empty($pemenang)) {
            return redirect()->to(base_url('piagam-kejurkot'))->with('error', 'Belum ada data pemenang untuk dicetak.');
        }

        $template = $this->templateModel->find(23);
        if (!$template) {
            $template = $this->templateModel->where('kejuaraan_id', self::KEJURKOT_ID)->first();
        }

        $title = 'Cetak Piagam Kejurkot Kota Pekalongan 2026';
        if (!empty($nomor)) {
            $title .= ' - Nomor ' . $nomor;
        }

        return view('piagam/cetak_kejurkot', [
            'title'     => $title,
            'pemenang'  => $pemenang,
            'template'  => $template,
            'isSingle'  => false,
            'nomor'     => $nomor,
        ]);
    }

    /**
     * Cetak Single Piagam Kejurkot
     */
    public function cetakKejurkotSingle($id)
    {
        $winner = $this->customModel
            ->where('id', $id)
            ->where('kejuaraan_id', self::KEJURKOT_ID)
            ->first();

        if (!$winner) {
            return redirect()->to(base_url('piagam-kejurkot'))->with('error', 'Data pemenang tidak ditemukan.');
        }

        $template = $this->templateModel->find(23);
        if (!$template) {
            $template = $this->templateModel->where('kejuaraan_id', self::KEJURKOT_ID)->first();
        }

        return view('piagam/cetak_kejurkot', [
            'title'     => 'Cetak Piagam - ' . $winner['nama_pemenang'],
            'pemenang'  => [$winner],
            'template'  => $template,
            'isSingle'  => true,
        ]);
    }

    /**
     * Import Data Pemenang Kejurkot dari Excel / CSV / Baris Teks
     */
    public function importKejurkotExcel()
    {
        $file = $this->request->getFile('file_excel');
        $textData = $this->request->getPost('data_teks');

        $rows = [];

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->getClientExtension());
            if ($ext === 'csv' || $ext === 'txt') {
                $content = file_get_contents($file->getTempName());
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    $cols = str_getcsv(trim($line), ";");
                    if (count($cols) < 2) $cols = str_getcsv(trim($line), ",");
                    if (count($cols) < 2) $cols = explode("\t", trim($line));
                    if (count($cols) >= 2) {
                        $rows[] = array_map('trim', $cols);
                    }
                }
            } elseif (in_array($ext, ['xlsx', 'xls'])) {
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
                    $sheet = $spreadsheet->getActiveSheet();
                    $rows = $sheet->toArray();
                } catch (\Throwable $e) {
                    log_message('error', 'Excel load error: ' . $e->getMessage());
                }
            }
        } elseif (!empty($textData)) {
            $lines = explode("\n", trim($textData));
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $cols = explode("\t", trim($line));
                if (count($cols) < 2) $cols = explode(",", trim($line));
                if (count($cols) < 2) $cols = explode(";", trim($line));
                if (count($cols) >= 2) {
                    $rows[] = array_map('trim', $cols);
                }
            }
        }

        if (empty($rows)) {
            return redirect()->to(base_url('piagam-kejurkot'))->with('error', 'Tidak ada data valid yang dapat diimpor.');
        }

        $kejuaraan = (new \App\Models\KejuaraanModel())->find(self::KEJURKOT_ID);
        $namaKejuaraan = $kejuaraan['nama'] ?? 'KEJUARAAN ATLETIK KELOMPOK UMUR TINGKAT KOTA PEKALONGAN TAHUN 2026';
        $tempat = $kejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan';

        $imported = 0;
        $currentCount = $this->customModel->where('kejuaraan_id', self::KEJURKOT_ID)->countAllResults();

        // Cek baris header
        $firstRow = $rows[0];
        $isHeader = false;
        foreach ($firstRow as $c) {
            if (in_array(strtolower(trim($c)), ['nama', 'nama pemenang', 'nama atlet', 'nomor lomba', 'juara', 'peringkat'])) {
                $isHeader = true;
                break;
            }
        }
        $startIndex = $isHeader ? 1 : 0;

        $peringkatTeksMap = [1 => 'Pertama', 2 => 'Kedua', 3 => 'Ketiga', 4 => 'Keempat', 5 => 'Kelima'];

        for ($i = $startIndex; $i < count($rows); $i++) {
            $r = $rows[$i];
            if (empty($r[0])) continue;

            $nama = $r[0] ?? '';
            $asal = $r[1] ?? 'Kota Pekalongan';
            $nomorLomba = $r[2] ?? 'Lari 100m';
            $peringkat = isset($r[3]) ? (int)preg_replace('/[^0-9]/', '', $r[3]) : 1;
            if ($peringkat <= 0) $peringkat = 1;
            $prestasi = $r[4] ?? '-';
            $nomorPiagam = $r[5] ?? '';

            if (empty($nomorPiagam)) {
                $nomorPiagam = sprintf('%03d/SERT/PASI.KOTA/VIII/2026', $currentCount + $imported + 1);
            }

            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            $this->customModel->insert([
                'kejuaraan_id'    => self::KEJURKOT_ID,
                'template_id'     => 23,
                'uuid'            => $uuid,
                'nomor_piagam'    => $nomorPiagam,
                'nama_pemenang'   => $nama,
                'asal_sekolah'    => $asal,
                'asal_kota'       => $asal,
                'peringkat'       => $peringkat,
                'peringkat_teks'  => $peringkatTeksMap[$peringkat] ?? "Ke-{$peringkat}",
                'nomor_lomba'     => $nomorLomba,
                'prestasi'        => $prestasi ?: '-',
                'nama_kejuaraan'  => $namaKejuaraan,
                'tempat'          => $tempat,
                'tanggal'         => '29 Agustus 2026',
                'ttd_1_jabatan'   => 'Ketua',
                'ttd_1_nama'      => 'Zaini, S.Pd.',
            ]);

            $imported++;
        }

        return redirect()->to(base_url('piagam-kejurkot'))->with('success', "Berhasil mengimpor {$imported} data pemenang.");
    }

    /**
     * Endpoint API / Form untuk update cepat tanggal kejuaraan langsung dari modul Piagam
     */
    public function updateTanggal()
    {
        $kejuaraanController = new \App\Controllers\Kejuaraan();
        $kejuaraanController->initController($this->request, $this->response, $this->logger);
        return $kejuaraanController->updateTanggal();
    }
}
