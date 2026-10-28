<?php

namespace App\Models;

use CodeIgniter\Model;

class CaborModel extends Model
{
    protected $table            = 'cabor';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode',
        'nama',
        'penanggung_jawab',
        'kontak',
        'icon',
        'is_active',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua cabor yang aktif
     */
    public function getActiveCabor(): array
    {
        return $this->where('is_active', 1)->orderBy('nama', 'ASC')->findAll();
    }
}
