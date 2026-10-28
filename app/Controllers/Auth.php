<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CaborModel;

class Auth extends BaseController
{
    public function login()
    {
        if ($this->session->get('logged_in')) {
            return redirect()->to(base_url('dashboard'));
        }

        $caborModel = new CaborModel();
        $caborList = $caborModel->getActiveCabor();

        return view('auth/login', [
            'title'     => 'Login Panitia & Admin Cabor — Kejurwil Kejurkot',
            'caborList' => $caborList,
        ]);
    }

    public function processLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $user = $userModel->verifyLogin($this->request->getPost('username'), $this->request->getPost('password'));

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Username atau password yang dimasukkan salah, atau akun nonaktif.');
        }

        // Simpan sesi login lengkap beserta konteks Cabor jika ada
        $sessionData = [
            'user_id'      => $user['id'],
            'username'     => $user['username'],
            'nama_lengkap' => $user['nama_lengkap'],
            'role'         => $user['role'],
            'cabor_id'     => $user['cabor_id'] ?? null,
            'cabor_nama'   => $user['cabor_nama'] ?? null,
            'cabor_kode'   => $user['cabor_kode'] ?? null,
            'logged_in'    => true,
        ];

        $this->session->set($sessionData);

        $welcomeMsg = 'Selamat datang kembali, ' . esc($user['nama_lengkap']);
        if ($user['role'] === 'admin_cabor' && !empty($user['cabor_nama'])) {
            $welcomeMsg .= ' (Admin Cabang Olahraga ' . esc($user['cabor_nama']) . ')';
        }

        return redirect()->to(base_url('dashboard'))->with('success', $welcomeMsg . '!');
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to(base_url('auth/login'))->with('success', 'Anda telah berhasil logout.');
    }
}
