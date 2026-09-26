<?php

namespace App\Controllers;

use App\Models\HasilModel;
use App\Models\SekolahModel;

class Laporan extends BaseController
{
    protected HasilModel $hasilModel;
    protected SekolahModel $sekolahModel;

    public function __construct()
    {
        $this->hasilModel   = new HasilModel();
        $this->sekolahModel = new SekolahModel();
    }

    public function klasemen()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;

        $builder = $this->hasilModel->db->table('sekolah s')
            ->select('s.id, s.nama as nama_sekolah, s.jenjang,
                SUM(CASE WHEN h.medali = "Emas" THEN 1 ELSE 0 END) as emas,
                SUM(CASE WHEN h.medali = "Perak" THEN 1 ELSE 0 END) as perak,
                SUM(CASE WHEN h.medali = "Perunggu" THEN 1 ELSE 0 END) as perunggu,
                COUNT(CASE WHEN h.medali IN ("Emas", "Perak", "Perunggu") THEN 1 END) as total_medali')
            ->join('atlet a', 'a.sekolah_id = s.id', 'inner')
            ->join('peserta_event pe', 'pe.atlet_id = a.id', 'inner')
            ->join('hasil h', 'h.peserta_event_id = pe.id', 'inner');

        if ($kejuaraanId) {
            $builder->where('a.kejuaraan_id', $kejuaraanId);
        }

        $klasemen = $builder->groupBy('s.id')
            ->orderBy('emas', 'DESC')
            ->orderBy('perak', 'DESC')
            ->orderBy('perunggu', 'DESC')
            ->orderBy('total_medali', 'DESC')
            ->get()
            ->getResultArray();

        $this->data['title']    = 'Klasemen Perolehan Medali';
        $this->data['klasemen'] = $klasemen;

        return view('laporan/klasemen', $this->data);
    }
}
