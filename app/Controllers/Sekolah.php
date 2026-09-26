<?php

namespace App\Controllers;

use App\Models\SekolahModel;

class Sekolah extends BaseController
{
    protected SekolahModel $sekolahModel;

    public function __construct()
    {
        $this->sekolahModel = new SekolahModel();
    }

    public function index()
    {
        $this->data['title'] = 'Data Sekolah & Kontingen';
        // Ambil sekolah beserta jumlah atlet yang terdaftar
        $this->data['sekolahs'] = $this->sekolahModel->db->table('sekolah s')
            ->select('s.*, COUNT(a.id) as total_atlet')
            ->join('atlet a', 'a.sekolah_id = s.id', 'left')
            ->groupBy('s.id')
            ->orderBy('s.nama', 'ASC')
            ->get()
            ->getResultArray();

        return view('sekolah/index', $this->data);
    }

    public function create()
    {
        $this->data['title'] = 'Tambah Sekolah / Kontingen';
        $this->data['sekolah'] = null;
        return view('sekolah/form', $this->data);
    }

    public function edit(int $id)
    {
        $sekolah = $this->sekolahModel->find($id);
        if (!$sekolah) {
            return redirect()->to(base_url('sekolah'))->with('error', 'Sekolah tidak ditemukan.');
        }

        $this->data['title'] = 'Edit Sekolah';
        $this->data['sekolah'] = $sekolah;
        return view('sekolah/form', $this->data);
    }

    public function save(?int $id = null)
    {
        $rules = [
            'nama'    => 'required|min_length[2]|max_length[150]',
            'jenjang' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = [
            'nama'          => trim($this->request->getPost('nama')),
            'jenjang'       => $this->request->getPost('jenjang'),
            'kontak_person' => $this->request->getPost('kontak_person'),
            'no_telepon'    => $this->request->getPost('no_telepon'),
            'alamat'        => $this->request->getPost('alamat'),
        ];

        if ($id) {
            $this->sekolahModel->update($id, $postData);
            $msg = 'Data sekolah berhasil diperbarui.';
        } else {
            $this->sekolahModel->insert($postData);
            $msg = 'Sekolah baru berhasil ditambahkan.';
        }

        return redirect()->to(base_url('sekolah'))->with('success', $msg);
    }

    public function delete(int $id)
    {
        $this->sekolahModel->delete($id);
        return redirect()->to(base_url('sekolah'))->with('success', 'Data sekolah berhasil dihapus.');
    }
}
