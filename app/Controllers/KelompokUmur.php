<?php

namespace App\Controllers;

use App\Models\KelompokUmurModel;

class KelompokUmur extends BaseController
{
    protected KelompokUmurModel $kuModel;

    public function __construct()
    {
        $this->kuModel = new KelompokUmurModel();
    }

    public function index()
    {
        $this->data['title'] = 'Kelompok Umur (KU)';
        $this->data['kus'] = $this->kuModel->orderBy('usia_min', 'ASC')->findAll();
        return view('master/kelompok_umur', $this->data);
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $rules = [
            'nama'     => 'required|min_length[2]|max_length[50]',
            'usia_min' => 'required|numeric',
            'usia_max' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = [
            'nama'       => trim($this->request->getPost('nama')),
            'usia_min'   => (int)$this->request->getPost('usia_min'),
            'usia_max'   => (int)$this->request->getPost('usia_max'),
            'keterangan' => $this->request->getPost('keterangan'),
        ];

        if ($id) {
            $this->kuModel->update($id, $postData);
            $msg = 'Kelompok umur berhasil diperbarui.';
        } else {
            $this->kuModel->insert($postData);
            $msg = 'Kelompok umur baru berhasil ditambahkan.';
        }

        return redirect()->to(base_url('kelompok-umur'))->with('success', $msg);
    }

    public function delete(int $id)
    {
        $this->kuModel->delete($id);
        return redirect()->to(base_url('kelompok-umur'))->with('success', 'Kelompok umur berhasil dihapus.');
    }
}
