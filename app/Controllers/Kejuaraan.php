<?php

namespace App\Controllers;

use App\Models\KejuaraanModel;

class Kejuaraan extends BaseController
{
    protected KejuaraanModel $kejuaraanModel;

    public function __construct()
    {
        $this->kejuaraanModel = new KejuaraanModel();
    }

    public function index()
    {
        $this->data['title'] = 'Manajemen Kejuaraan';
        $this->data['kejuaraans'] = $this->kejuaraanModel->orderBy('id', 'DESC')->findAll();
        return view('kejuaraan/index', $this->data);
    }

    public function create()
    {
        $this->data['title'] = 'Tambah Kejuaraan Baru';
        $this->data['kejuaraan'] = null;
        return view('kejuaraan/form', $this->data);
    }

    public function edit(int $id)
    {
        $kejuaraan = $this->kejuaraanModel->find($id);
        if (!$kejuaraan) {
            return redirect()->to(base_url('kejuaraan'))->with('error', 'Kejuaraan tidak ditemukan.');
        }

        $this->data['title'] = 'Edit Kejuaraan';
        $this->data['kejuaraan'] = $kejuaraan;
        return view('kejuaraan/form', $this->data);
    }

    public function save(?int $id = null)
    {
        $rules = [
            'nama'          => 'required|min_length[3]|max_length[200]',
            'tahun'         => 'required|numeric',
            'tempat'        => 'required',
            'tanggal_mulai' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = [
            'nama'            => $this->request->getPost('nama'),
            'tahun'           => $this->request->getPost('tahun'),
            'tempat'          => $this->request->getPost('tempat'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'penyelenggara'   => $this->request->getPost('penyelenggara'),
            'status'          => $this->request->getPost('status') ?? 'Draft',
            'deskripsi'       => $this->request->getPost('deskripsi'),
        ];

        // Jika status Aktif, non-aktifkan yang lain
        if ($postData['status'] === 'Aktif') {
            $this->kejuaraanModel->where('status', 'Aktif')->set(['status' => 'Draft'])->update();
        }

        if ($id) {
            $this->kejuaraanModel->update($id, $postData);
            $msg = 'Data kejuaraan berhasil diperbarui.';
        } else {
            $this->kejuaraanModel->insert($postData);
            $msg = 'Kejuaraan baru berhasil ditambahkan.';
        }

        return redirect()->to(base_url('kejuaraan'))->with('success', $msg);
    }

    public function activate(int $id)
    {
        $this->kejuaraanModel->where('status', 'Aktif')->set(['status' => 'Draft'])->update();
        $this->kejuaraanModel->update($id, ['status' => 'Aktif']);
        return redirect()->to(base_url('kejuaraan'))->with('success', 'Kejuaraan berhasil diaktifkan sebagai event utama.');
    }

    public function delete(int $id)
    {
        $this->kejuaraanModel->delete($id);
        return redirect()->to(base_url('kejuaraan'))->with('success', 'Data kejuaraan berhasil dihapus.');
    }

    /**
     * Ganti kejuaraan aktif untuk sesi kerja saat ini (Multi-Event Selector)
     */
    public function switch(int $id)
    {
        $kejuaraan = $this->kejuaraanModel->find($id);
        if (!$kejuaraan) {
            return redirect()->back()->with('error', 'Kejuaraan tidak ditemukan.');
        }

        // Simpan kejuaraan aktif di sesi pengguna
        session()->set('active_kejuaraan_id', $id);

        $referrer = $this->request->getServer('HTTP_REFERER');
        if ($referrer && !str_contains($referrer, 'switch')) {
            return redirect()->to($referrer)->with('success', "Kejuaraan aktif dialihkan ke: {$kejuaraan['nama']}");
        }

        return redirect()->to(base_url('dashboard'))->with('success', "Kejuaraan aktif dialihkan ke: {$kejuaraan['nama']}");
    }

    /**
     * Update cepat tanggal kejuaraan langsung (dari halaman Piagam / Topbar)
     */
    public function updateTanggal()
    {
        $kejuaraanId = (int)($this->request->getPost('kejuaraan_id') ?: session()->get('active_kejuaraan_id') ?: 1);
        $tanggalInput = trim((string)$this->request->getPost('tanggal'));
        $tanggalSelesaiInput = trim((string)$this->request->getPost('tanggal_selesai'));
        $tempatInput = trim((string)$this->request->getPost('tempat'));
        $namaInput = trim((string)$this->request->getPost('nama'));

        if (empty($tanggalInput)) {
            $msg = 'Tanggal kejuaraan tidak boleh kosong.';
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => $msg]);
            }
            return redirect()->back()->with('error', $msg);
        }

        $parsedMulai = $this->parseIndonesianDate($tanggalInput);
        if (!$parsedMulai) {
            $msg = "Format tanggal '{$tanggalInput}' tidak dikenali. Gunakan format YYYY-MM-DD atau '27 September 2026'.";
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => $msg]);
            }
            return redirect()->back()->with('error', $msg);
        }

        $parsedSelesai = !empty($tanggalSelesaiInput) ? ($this->parseIndonesianDate($tanggalSelesaiInput) ?: $parsedMulai) : $parsedMulai;

        $updateData = [
            'tanggal_mulai'   => $parsedMulai,
            'tanggal_selesai' => $parsedSelesai,
        ];
        if (!empty($tempatInput)) {
            $updateData['tempat'] = $tempatInput;
        }
        if (!empty($namaInput)) {
            $updateData['nama'] = $namaInput;
        }

        $this->kejuaraanModel->update($kejuaraanId, $updateData);

        // Update tanggal di tabel piagam_custom dan event jika ada data terkait
        $db = \Config\Database::connect();
        $tglFormatted = $this->formatTanggalIndo($parsedMulai);

        // Update jadwal_hari di event agar sinkron dengan jadwal lomba
        $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $namaHari = $hariIndo[(int)date('w', strtotime($parsedMulai))];
        $jadwalHariStr = "{$namaHari}, {$tglFormatted}";
        $db->table('event')->where('kejuaraan_id', $kejuaraanId)->update(['jadwal_hari' => $jadwalHariStr]);

        // Update di tabel piagam_custom jika ada
        $piagamCustomUp = ['tanggal' => $tglFormatted];
        if (!empty($updateData['tempat'])) {
            $piagamCustomUp['tempat'] = $updateData['tempat'];
        }
        $db->table('piagam_custom')->where('kejuaraan_id', $kejuaraanId)->update($piagamCustomUp);

        $successMsg = "Tanggal kejuaraan berhasil disimpan ke database: {$tglFormatted}";

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'            => 'success',
                'message'           => $successMsg,
                'tanggal_mulai'     => $parsedMulai,
                'tanggal_formatted' => $tglFormatted,
                'tempat'            => $updateData['tempat'] ?? null,
                'kejuaraan_id'      => $kejuaraanId
            ]);
        }

        return redirect()->back()->with('success', $successMsg);
    }

    /**
     * Helper: Parse string tanggal Indonesia atau standar ke format YYYY-MM-DD
     */
    public function parseIndonesianDate(string $s): ?string
    {
        $s = trim($s);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return $s;
        }

        $months = [
            'januari' => '01', 'jan' => '01',
            'februari' => '02', 'feb' => '02',
            'maret' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'mei' => '05', 'may' => '05',
            'juni' => '06', 'jun' => '06',
            'juli' => '07', 'jul' => '07',
            'agustus' => '08', 'agu' => '08', 'ags' => '08',
            'september' => '09', 'sep' => '09', 'sept' => '09',
            'oktober' => '10', 'okt' => '10',
            'november' => '11', 'nov' => '11',
            'desember' => '12', 'des' => '12',
        ];

        if (preg_match('/(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})/', $s, $m)) {
            $day = (int)$m[1];
            $monthStr = strtolower($m[2]);
            $year = $m[3];
            if (isset($months[$monthStr])) {
                return sprintf('%s-%s-%02d', $year, $months[$monthStr], $day);
            }
        }

        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $s, $m)) {
            $day = (int)$m[1];
            $month = (int)$m[2];
            $year = $m[3];
            return sprintf('%s-%02d-%02d', $year, $month, $day);
        }

        $time = strtotime($s);
        if ($time !== false && $time > 0) {
            return date('Y-m-d', $time);
        }

        return null;
    }

    /**
     * Helper: Format tanggal standar ke bahasa Indonesia
     */
    public function formatTanggalIndo(string $dateStr): string
    {
        $time = strtotime($dateStr);
        if (!$time) return $dateStr;
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return date('j', $time) . ' ' . ($bulanIndo[(int)date('n', $time)] ?? '') . ' ' . date('Y', $time);
    }
}
