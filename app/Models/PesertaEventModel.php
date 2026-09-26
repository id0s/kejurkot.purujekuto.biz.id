<?php

namespace App\Models;

use CodeIgniter\Model;

class PesertaEventModel extends Model
{
    protected $table            = 'peserta_event';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'event_id',
        'atlet_id',
        'heat',
        'lane',
        'no_urut',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dapatkan daftar peserta di suatu event, terurut berdasarkan heat dan no_urut/lane
     * lengkap dengan data atlet, asal sekolah, dan hasil perlombaan jika ada
     */
    public function getPesertaByEvent(int $eventId): array
    {
        return $this->db->table($this->table . ' pe')
            ->select('pe.*, a.nama as nama_atlet, a.no_dada, a.gender, a.tanggal_lahir, a.catatan as catatan_atlet, s.id as sekolah_id, s.nama as nama_sekolah, s.alamat as alamat_sekolah, h.id as hasil_id, h.prestasi, h.nilai_angka, h.peringkat, h.medali, h.keterangan as ket_hasil, h.status_prestasi')
            ->join('atlet a', 'a.id = pe.atlet_id', 'inner')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('hasil h', 'h.peserta_event_id = pe.id', 'left')
            ->where('pe.event_id', $eventId)
            ->orderBy('pe.heat', 'ASC')
            ->orderBy('pe.no_urut', 'ASC')
            ->orderBy('pe.lane', 'ASC')
            ->orderBy('a.nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Dapatkan daftar ID atlet yang sudah terdaftar di event tertentu
     */
    public function getRegisteredAthleteIds(int $eventId): array
    {
        $rows = $this->where('event_id', $eventId)->findAll();
        return array_column($rows, 'atlet_id');
    }
}
