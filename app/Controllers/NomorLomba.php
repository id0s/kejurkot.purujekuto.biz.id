<?php

namespace App\Controllers;

use App\Models\NomorLombaModel;

class NomorLomba extends BaseController
{
    protected NomorLombaModel $lombaModel;

    public function __construct()
    {
        $this->lombaModel = new NomorLombaModel();
    }

    public function index()
    {
        // Standardisasi otomatis nama nomor lomba & acara agar konsisten (selalu diawali Lari, Lompat, Lempar, Estafet)
        $lombas = $this->lombaModel->findAll();
        $changed = 0;
        foreach ($lombas as $l) {
            $nama = trim($l['nama']);
            $kategori = $l['kategori'];
            $newNama = $nama;

            if ($kategori === 'Lari' && !preg_match('/^lari/i', $nama)) {
                $newNama = 'Lari ' . $nama;
            } elseif ($kategori === 'Lari' && preg_match('/^lari\s*(\d+)m$/i', $nama, $m)) {
                $newNama = 'Lari ' . $m[1] . ' m';
            }

            if ($newNama !== $nama) {
                $this->lombaModel->update($l['id'], ['nama' => $newNama]);
                $changed++;
            }
        }

        // Sinkronkan nama di tabel event jika masih ada yang belum diawali kata 'Lari'
        $db = \Config\Database::connect();
        $events = $db->table('event e')
            ->select('e.id, e.nama_acara, nl.nama as nama_lomba, nl.kategori as kategori_lomba')
            ->join('nomor_lomba nl', 'nl.id = e.nomor_lomba_id')
            ->where('nl.kategori', 'Lari')
            ->get()->getResultArray();

        foreach ($events as $ev) {
            $evNama = trim($ev['nama_acara']);
            if (!preg_match('/^lari/i', $evNama)) {
                $updatedEvNama = 'Lari ' . $evNama;
                $db->table('event')->where('id', $ev['id'])->update(['nama_acara' => $updatedEvNama]);
                $changed++;
            }
        }

        if ($changed > 0) {
            @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
            @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');
        }

        $this->data['title'] = 'Nomor Lomba Atletik';
        $this->data['lombas'] = $this->lombaModel->orderBy('kategori', 'ASC')->orderBy('nama', 'ASC')->findAll();
        return view('master/nomor_lomba', $this->data);
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $rules = [
            'nama'         => 'required|min_length[2]|max_length[100]',
            'kategori'     => 'required',
            'satuan_hasil' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = [
            'nama'         => trim($this->request->getPost('nama')),
            'kategori'     => $this->request->getPost('kategori'),
            'satuan_hasil' => $this->request->getPost('satuan_hasil'),
            'keterangan'   => $this->request->getPost('keterangan'),
        ];

        if ($id) {
            $this->lombaModel->update($id, $postData);
            $msg = 'Nomor lomba berhasil diperbarui.';
        } else {
            $this->lombaModel->insert($postData);
            $msg = 'Nomor lomba baru berhasil ditambahkan.';
        }

        return redirect()->to(base_url('nomor-lomba'))->with('success', $msg);
    }

    public function delete(int $id)
    {
        $this->lombaModel->delete($id);
        return redirect()->to(base_url('nomor-lomba'))->with('success', 'Nomor lomba berhasil dihapus.');
    }
}
