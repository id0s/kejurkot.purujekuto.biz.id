<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CaborModel;

class User extends BaseController
{
    protected UserModel $userModel;
    protected CaborModel $caborModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->caborModel = new CaborModel();
    }

    /**
     * Tampilan daftar user & admin cabor
     */
    public function index()
    {
        $users = $this->userModel->getUsersWithCabor();
        $caborList = $this->caborModel->getActiveCabor();

        return view('user/index', [
            'title'     => 'Kelola User & Admin Cabor',
            'users'     => $users,
            'caborList' => $caborList,
        ]);
    }

    /**
     * Form tambah user baru
     */
    public function create()
    {
        $caborList = $this->caborModel->getActiveCabor();

        return view('user/form', [
            'title'     => 'Tambah Pengguna / Admin Cabor Baru',
            'user'      => null,
            'caborList' => $caborList,
        ]);
    }

    /**
     * Simpan user baru
     */
    public function store()
    {
        $rules = [
            'username'     => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'password'     => 'required|min_length[6]',
            'role'         => 'required|in_list[admin,admin_cabor,panitia,juri]',
        ];

        if ($this->request->getPost('role') === 'admin_cabor') {
            $rules['cabor_id'] = 'required|is_not_unique[cabor.id]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $caborId = $this->request->getPost('role') === 'admin_cabor' ? (int) $this->request->getPost('cabor_id') : null;

        $data = [
            'username'      => strtolower(trim($this->request->getPost('username'))),
            'nama_lengkap'  => trim($this->request->getPost('nama_lengkap')),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'          => $this->request->getPost('role'),
            'cabor_id'      => $caborId,
            'is_active'     => $this->request->getPost('is_active') ? 1 : 0,
        ];

        $this->userModel->insert($data);

        return redirect()->to(base_url('users'))->with('success', 'User ' . esc($data['username']) . ' berhasil ditambahkan.');
    }

    /**
     * Form edit user
     */
    public function edit(int $id)
    {
        $user = $this->userModel->getUserWithCabor($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        $caborList = $this->caborModel->getActiveCabor();

        return view('user/form', [
            'title'     => 'Edit Pengguna: ' . esc($user['username']),
            'user'      => $user,
            'caborList' => $caborList,
        ]);
    }

    /**
     * Update user
     */
    public function update(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'username'     => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'role'         => 'required|in_list[admin,admin_cabor,panitia,juri]',
        ];

        if ($this->request->getPost('role') === 'admin_cabor') {
            $rules['cabor_id'] = 'required|is_not_unique[cabor.id]';
        }

        if (!empty($this->request->getPost('password'))) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $caborId = $this->request->getPost('role') === 'admin_cabor' ? (int) $this->request->getPost('cabor_id') : null;

        $data = [
            'username'     => strtolower(trim($this->request->getPost('username'))),
            'nama_lengkap' => trim($this->request->getPost('nama_lengkap')),
            'role'         => $this->request->getPost('role'),
            'cabor_id'     => $caborId,
            'is_active'    => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (!empty($this->request->getPost('password'))) {
            $data['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);

        return redirect()->to(base_url('users'))->with('success', 'User ' . esc($data['username']) . ' berhasil diperbarui.');
    }

    /**
     * Toggle status aktif/nonaktif
     */
    public function toggleStatus(int $id)
    {
        $currentUserId = (int) session()->get('user_id');
        if ($id === $currentUserId) {
            return redirect()->to(base_url('users'))->with('error', 'Anda tidak dapat menonaktifkan akun sendiri yang sedang aktif.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->userModel->update($id, ['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->to(base_url('users'))->with('success', "Akun {$user['username']} berhasil {$statusText}.");
    }

    /**
     * Hapus user
     */
    public function delete(int $id)
    {
        $currentUserId = (int) session()->get('user_id');
        if ($id === $currentUserId) {
            return redirect()->to(base_url('users'))->with('error', 'Anda tidak dapat menghapus akun sendiri yang sedang login.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        $this->userModel->delete($id);

        return redirect()->to(base_url('users'))->with('success', "User {$user['username']} berhasil dihapus.");
    }
}
