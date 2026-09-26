<?php

namespace App\Models;

use CodeIgniter\Model;

class KelompokUmurModel extends Model
{
    protected $table            = 'kelompok_umur';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama',
        'usia_min',
        'usia_max',
        'keterangan',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Hitung umur atlet berdasarkan tanggal lahir relatif terhadap tanggal acuan (misal tgl kejuaraan)
     * dan kembalikan id kelompok umur yang cocok
     */
    public function hitungKUOtomatis(?string $tanggalLahir, ?string $tanggalAcuan = null): ?int
    {
        if (empty($tanggalLahir)) {
            return null;
        }

        $birthDate = new \DateTime($tanggalLahir);
        $refDate   = $tanggalAcuan ? new \DateTime($tanggalAcuan) : new \DateTime();

        $interval = $birthDate->diff($refDate);
        $usiaTahun = $interval->y;

        // Cari KU yang cocok
        $kuList = $this->orderBy('usia_min', 'ASC')->findAll();
        foreach ($kuList as $ku) {
            if ($usiaTahun >= (int)$ku['usia_min'] && $usiaTahun <= (int)$ku['usia_max']) {
                return (int)$ku['id'];
            }
        }

        // Jika umur lebih kecil dari batas min pertama (misal balita < 5 tahun), masukkan ke kategori terkecil
        if (!empty($kuList) && $usiaTahun < (int)$kuList[0]['usia_min']) {
            return (int)$kuList[0]['id'];
        }

        // Jika umur lebih besar dari kategori terbesar (misal > 18 tahun)
        if (!empty($kuList)) {
            $last = end($kuList);
            if ($usiaTahun > (int)$last['usia_max']) {
                return (int)$last['id'];
            }
        }

        return null;
    }
}
