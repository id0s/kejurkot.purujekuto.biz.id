<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if ($this->session->get('logged_in')) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('auth/login', [
            'title' => 'Login Admin — Kejurwil Atletik',
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
            return redirect()->back()->withInput()->with('error', 'Username atau password yang dimasukkan salah.');
        }

        $this->session->set([
            'user_id'      => $user['id'],
            'username'     => $user['username'],
            'nama_lengkap' => $user['nama_lengkap'],
            'role'         => $user['role'],
            'logged_in'    => true,
        ]);

        return redirect()->to(base_url('dashboard'))->with('success', 'Selamat datang kembali, ' . esc($user['nama_lengkap']) . '!');
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to(base_url('auth/login'))->with('success', 'Anda telah berhasil logout.');
    }
}
