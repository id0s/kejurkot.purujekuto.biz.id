<?php

namespace App\Models;

use CodeIgniter\Model;

class NomorLombaModel extends Model
{
    protected $table            = 'nomor_lomba';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama',
        'kategori',
        'satuan_hasil',
        'keterangan',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nama' => 'required|min_length[2]|max_length[100]',
    ];
}
