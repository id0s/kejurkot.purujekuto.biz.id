<?php

namespace App\Models;

use CodeIgniter\Model;

class PiagamCustomModel extends Model
{
    protected $table            = 'piagam_custom';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'template_id',
        'event_id',
        'peserta_event_id',
        'nomor_piagam',
        'nama_pemenang',
        'asal_sekolah',
        'asal_kota',
        'peringkat',
        'peringkat_teks',
        'nomor_lomba',
        'prestasi',
        'nama_kejuaraan',
        'tempat',
        'tanggal',
        'ttd_1_jabatan',
        'ttd_1_nama',
        'ttd_2_jabatan',
        'ttd_2_nama',
        'ttd_3_jabatan',
        'ttd_3_nama',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function initializeTable(): void
    {
        $db = \Config\Database::connect();

        $sql = "CREATE TABLE IF NOT EXISTS `piagam_custom` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `template_id` INT NULL,
            `event_id` INT NULL,
            `peserta_event_id` INT NULL,
            `nomor_piagam` VARCHAR(100) NULL,
            `nama_pemenang` VARCHAR(150) NOT NULL,
            `asal_sekolah` VARCHAR(150) NULL,
            `asal_kota` VARCHAR(100) NULL,
            `peringkat` INT NULL,
            `peringkat_teks` VARCHAR(50) NULL,
            `nomor_lomba` VARCHAR(150) NULL,
            `prestasi` VARCHAR(50) NULL,
            `nama_kejuaraan` VARCHAR(255) NULL,
            `tempat` VARCHAR(150) NULL,
            `tanggal` VARCHAR(100) NULL,
            `ttd_1_jabatan` VARCHAR(150) NULL,
            `ttd_1_nama` VARCHAR(150) NULL,
            `ttd_2_jabatan` VARCHAR(150) NULL,
            `ttd_2_nama` VARCHAR(150) NULL,
            `ttd_3_jabatan` VARCHAR(150) NULL,
            `ttd_3_nama` VARCHAR(150) NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $db->query($sql);
    }
}
