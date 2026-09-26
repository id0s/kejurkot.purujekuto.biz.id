<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table            = 'event';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kejuaraan_id',
        'nomor_lomba_id',
        'kelompok_umur_id',
        'gender',
        'nomor_acara',
        'nama_acara',
        'babak',
        'jadwal_hari',
        'jadwal_waktu',
        'status',
        'catatan',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dapatkan daftar event dengan detail nomor lomba, kelompok umur, dan jumlah peserta
     * Mendukung pengurutan per jenis (kategori lomba, gender, kelompok umur, babak, waktu, atau nomor acara)
     */
    public function getEventsWithDetails(?int $kejuaraanId = null, string $sortBy = 'nomor_acara', array $filters = [], string $sortDir = 'ASC'): array
    {
        $builder = $this->db->table($this->table . ' e')
            ->select('e.*, nl.nama as nama_lomba, nl.kategori as kategori_lomba, nl.satuan_hasil, ku.nama as nama_ku, ku.usia_min, ku.usia_max, COUNT(DISTINCT pe.id) as total_peserta, COUNT(DISTINCT h.id) as total_hasil')
            ->join('nomor_lomba nl', 'nl.id = e.nomor_lomba_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = e.kelompok_umur_id', 'left')
            ->join('peserta_event pe', 'pe.event_id = e.id', 'left')
            ->join('hasil h', 'h.peserta_event_id = pe.id AND h.prestasi IS NOT NULL AND h.prestasi != ""', 'left')
            ->groupBy('e.id');

        if ($kejuaraanId) {
            $builder->where('e.kejuaraan_id', $kejuaraanId);
        }

        // Terapkan filter per jenis jika ada
        if (!empty($filters['kategori'])) {
            $builder->where('nl.kategori', $filters['kategori']);
        }
        if (!empty($filters['gender'])) {
            $builder->where('e.gender', $filters['gender']);
        }
        if (!empty($filters['babak'])) {
            $builder->where('e.babak', $filters['babak']);
        }
        if (!empty($filters['kelompok_umur_id'])) {
            $builder->where('e.kelompok_umur_id', (int)$filters['kelompok_umur_id']);
        }

        $sortDir = (strtoupper($sortDir) === 'DESC') ? 'DESC' : 'ASC';

        // Terapkan pengurutan (Sorting)
        switch ($sortBy) {
            case 'kategori':
                $builder->orderBy('nl.kategori', $sortDir)->orderBy('e.nomor_acara', 'ASC');
                break;
            case 'gender':
                $builder->orderBy('e.gender', $sortDir)->orderBy('e.nomor_acara', 'ASC');
                break;
            case 'ku':
                $builder->orderBy('ku.usia_min', $sortDir)->orderBy('e.nomor_acara', 'ASC');
                break;
            case 'babak':
                $builder->orderBy('e.babak', $sortDir)->orderBy('e.nomor_acara', 'ASC');
                break;
            case 'sesi':
                $builder->orderBy('e.jadwal_waktu', $sortDir)->orderBy('e.nomor_acara', 'ASC');
                break;
            case 'nama':
                $builder->orderBy('e.nama_acara', $sortDir);
                break;
            case 'nomor_acara':
            default:
                $builder->orderBy('e.nomor_acara', $sortDir)->orderBy('e.id', 'ASC');
                break;
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Dapatkan satu event lengkap beserta nomor lomba dan KU
     */
    public function getEventWithDetails(int $id): ?array
    {
        return $this->db->table($this->table . ' e')
            ->select('e.*, nl.nama as nama_lomba, nl.kategori as kategori_lomba, nl.satuan_hasil, ku.nama as nama_ku')
            ->join('nomor_lomba nl', 'nl.id = e.nomor_lomba_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = e.kelompok_umur_id', 'left')
            ->where('e.id', $id)
            ->get()
            ->getRowArray();
    }
}
