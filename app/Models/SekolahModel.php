<?php

namespace App\Models;

use CodeIgniter\Model;

class SekolahModel extends Model
{
    protected $table            = 'sekolah';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama',
        'jenjang',
        'kontak_person',
        'no_telepon',
        'alamat',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dapatkan id sekolah berdasarkan nama, atau buat baru jika belum ada
     */
    public function getOrCreate(string $nama, ?string $jenjang = null): int
    {
        $namaClean = trim($nama);
        $found = $this->where('nama', $namaClean)->first();
        if ($found) {
            return (int)$found['id'];
        }

        // Tentukan jenjang otomatis jika kosong
        if (!$jenjang) {
            $upper = strtoupper($namaClean);
            if (str_contains($upper, 'TK') || str_contains($upper, 'KB') || str_contains($upper, 'PAUD') || str_contains($upper, 'RA')) {
                $jenjang = 'PAUD/TK';
            } elseif (str_contains($upper, 'SD') || str_contains($upper, 'MI')) {
                $jenjang = 'SD/MI';
            } elseif (str_contains($upper, 'SMP') || str_contains($upper, 'MTS')) {
                $jenjang = 'SMP/MTs';
            } elseif (str_contains($upper, 'SMA') || str_contains($upper, 'SMK') || str_contains($upper, 'MAN') || str_contains($upper, 'MA')) {
                $jenjang = 'SMA/SMK/MA';
            } else {
                $jenjang = 'Umum/Club';
            }
        }

        $id = $this->insert([
            'nama'    => $namaClean,
            'jenjang' => $jenjang,
        ]);

        return (int)$id;
    }
}
