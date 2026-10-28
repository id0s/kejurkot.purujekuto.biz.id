<?php

namespace App\Controllers;

use App\Models\CaborModel;

class Cabor extends BaseController
{
    protected CaborModel $caborModel;

    public function __construct()
    {
        $this->caborModel = new CaborModel();
    }

    /**
     * Tampilan daftar Cabang Olahraga
     */
    public function index()
    {
        $caborList = $this->caborModel->orderBy('nama', 'ASC')->findAll();

        return view('cabor/index', [
            'title'     => 'Data Master Cabang Olahraga (Cabor)',
            'caborList' => $caborList,
        ]);
    }

    /**
     * Simpan / Update Cabor
     */
    public function save()
    {
        $id = $this->request->getPost('id');

        $rules = [
            'kode' => 'required|min_length[2]|max_length[50]',
            'nama' => 'required|min_length[2]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode'             => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $this->request->getPost('kode')))),
            'nama'             => trim($this->request->getPost('nama')),
            'penanggung_jawab' => trim($this->request->getPost('penanggung_jawab') ?? ''),
            'kontak'           => trim($this->request->getPost('kontak') ?? ''),
            'icon'             => trim($this->request->getPost('icon') ?? 'bi-trophy'),
            'is_active'        => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            $this->caborModel->update($id, $data);
            $msg = 'Cabang Olahraga berhasil diperbarui.';
        } else {
            $this->caborModel->insert($data);
            $msg = 'Cabang Olahraga berhasil ditambahkan.';
        }

        return redirect()->to(base_url('cabor'))->with('success', $msg);
    }

    /**
     * Hapus cabor
     */
    public function delete(int $id)
    {
        $cabor = $this->caborModel->find($id);
        if (!$cabor) {
            return redirect()->to(base_url('cabor'))->with('error', 'Cabor tidak ditemukan.');
        }

        $this->caborModel->delete($id);
        return redirect()->to(base_url('cabor'))->with('success', 'Cabang Olahraga berhasil dihapus.');
    }
}
