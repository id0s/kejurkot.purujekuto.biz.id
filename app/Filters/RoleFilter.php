<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Membatasi akses rute berdasarkan role user
     * Argumen: role yang diizinkan (misal: 'admin')
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        if (!$session->get('logged_in')) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Silakan login terlebih dahulu.');
        }

        $userRole = $session->get('role');

        if (!empty($arguments)) {
            if (!in_array($userRole, $arguments, true)) {
                return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak: Anda tidak memiliki izin untuk halaman tersebut.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
