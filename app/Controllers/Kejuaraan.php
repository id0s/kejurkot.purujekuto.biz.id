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
}
