<?php

namespace App\Models;

use CodeIgniter\Model;

class KejuaraanModel extends Model
{
    protected $table            = 'kejuaraan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama',
        'tahun',
        'tempat',
        'tanggal_mulai',
        'tanggal_selesai',
        'penyelenggara',
        'status',
        'deskripsi',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nama'          => 'required|min_length[3]|max_length[200]',
        'tahun'         => 'required|numeric',
        'tanggal_mulai' => 'required|valid_date',
    ];

    /**
     * Dapatkan kejuaraan yang sedang aktif saat ini
     */
    public function getActive(): ?array
    {
        $active = $this->where('status', 'Aktif')->orderBy('id', 'DESC')->first();
        if (!$active) {
            $active = $this->orderBy('id', 'DESC')->first();
        }
        return $active;
    }
}
