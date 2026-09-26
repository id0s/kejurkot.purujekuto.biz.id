<?php

namespace App\Models;

use CodeIgniter\Model;

class AtletModel extends Model
{
    protected $table            = 'atlet';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kejuaraan_id',
        'sekolah_id',
        'no_dada',
        'nama',
        'gender',
        'tanggal_lahir',
        'kelompok_umur_id',
        'catatan',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Callbacks: hitung KU dinamis otomatis dari tanggal lahir
    protected $beforeInsert = ['autoCalculateKU'];
    protected $beforeUpdate = ['autoCalculateKU'];

    protected function autoCalculateKU(array $data)
    {
        if (isset($data['data']['tanggal_lahir']) && !empty($data['data']['tanggal_lahir'])) {
            $kuModel = new KelompokUmurModel();
            $kuId = $kuModel->hitungKUOtomatis($data['data']['tanggal_lahir']);
            if ($kuId) {
                $data['data']['kelompok_umur_id'] = $kuId;
            }
        }
        return $data;
    }

    /**
     * Ambil data atlet lengkap dengan relasi nama sekolah, kelompok umur, dan jumlah event
     */
    public function getAtletWithDetails(?int $kejuaraanId = null, ?int $sekolahId = null, ?string $search = null): array
    {
        $builder = $this->db->table($this->table . ' a')
            ->select('a.*, s.nama as nama_sekolah, s.jenjang, ku.nama as nama_ku, COUNT(pe.id) as total_event')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = a.kelompok_umur_id', 'left')
            ->join('peserta_event pe', 'pe.atlet_id = a.id', 'left')
            ->groupBy('a.id');

        if ($kejuaraanId) {
            $builder->where('a.kejuaraan_id', $kejuaraanId);
        }

        if ($sekolahId) {
            $builder->where('a.sekolah_id', $sekolahId);
        }

        if ($search) {
            $builder->groupStart()
                ->like('a.nama', $search)
                ->orLike('a.no_dada', $search)
                ->orLike('s.nama', $search)
                ->groupEnd();
        }

        return $builder->orderBy('a.no_dada', 'ASC')->orderBy('a.nama', 'ASC')->get()->getResultArray();
    }

    /**
     * Ambil data atlet lengkap beserta daftar lomba/acara yang diikuti (untuk Landing Page Publik)
     */
    public function getAtletWithEvents(int $kejuaraanId): array
    {
        $builder = $this->db->table($this->table . ' a')
            ->select('a.*, s.nama as nama_sekolah, s.jenjang, ku.nama as nama_ku')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = a.kelompok_umur_id', 'left')
            ->where('a.kejuaraan_id', $kejuaraanId)
            ->orderBy('a.no_dada', 'ASC')
            ->orderBy('a.nama', 'ASC');

        $atletList = $builder->get()->getResultArray();
        if (empty($atletList)) {
            return [];
        }

        $peRows = $this->db->table('peserta_event pe')
            ->select('pe.atlet_id, pe.event_id, pe.heat, pe.lane, pe.no_urut, 
                      e.nomor_acara, e.nama_acara, e.babak, e.jadwal_hari, e.jadwal_waktu,
                      nl.nama as nama_lomba, nl.kategori as kategori_lomba,
                      h.prestasi, h.peringkat, h.medali, h.status_prestasi')
            ->join('event e', 'e.id = pe.event_id', 'inner')
            ->join('nomor_lomba nl', 'nl.id = e.nomor_lomba_id', 'left')
            ->join('hasil h', 'h.peserta_event_id = pe.id', 'left')
            ->where('e.kejuaraan_id', $kejuaraanId)
            ->orderBy('e.nomor_acara', 'ASC')
            ->get()
            ->getResultArray();

        $eventsByAtlet = [];
        foreach ($peRows as $r) {
            $eventsByAtlet[$r['atlet_id']][] = $r;
        }

        foreach ($atletList as &$a) {
            $a['events'] = $eventsByAtlet[$a['id']] ?? [];
        }
        unset($a);

        return $atletList;
    }
}
