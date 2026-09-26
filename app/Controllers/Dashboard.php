<?php

namespace App\Controllers;

use App\Models\AtletModel;
use App\Models\EventModel;
use App\Models\HasilModel;
use App\Models\KelompokUmurModel;
use App\Models\NomorLombaModel;
use App\Models\SekolahModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $atletModel = new AtletModel();
        $sekolahModel = new SekolahModel();
        $eventModel = new EventModel();
        $hasilModel = new HasilModel();
        $kuModel = new KelompokUmurModel();
        $lombaModel = new NomorLombaModel();

        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;

        // Hitung statistik utama
        $totalAtlet = $kejuaraanId ? $atletModel->where('kejuaraan_id', $kejuaraanId)->countAllResults() : $atletModel->countAllResults();
        $totalSekolah = $sekolahModel->countAllResults();
        $totalEvent = $kejuaraanId ? $eventModel->where('kejuaraan_id', $kejuaraanId)->countAllResults() : $eventModel->countAllResults();
        
        $totalHasil = $hasilModel->db->table('hasil h')
            ->join('peserta_event pe', 'pe.id = h.peserta_event_id')
            ->join('event e', 'e.id = pe.event_id')
            ->where('h.prestasi IS NOT NULL')
            ->where('h.prestasi != ""');
        if ($kejuaraanId) {
            $totalHasil->where('e.kejuaraan_id', $kejuaraanId);
        }
        $totalHasilCount = $totalHasil->countAllResults();

        // Atlet per gender
        $atletPutra = $kejuaraanId ? $atletModel->where('kejuaraan_id', $kejuaraanId)->where('gender', 'L')->countAllResults() : 0;
        $atletPutri = $kejuaraanId ? $atletModel->where('kejuaraan_id', $kejuaraanId)->where('gender', 'P')->countAllResults() : 0;

        // Distribusi KU
        $kuStats = [];
        $allKU = $kuModel->findAll();
        foreach ($allKU as $ku) {
            $c = $kejuaraanId ? $atletModel->where('kejuaraan_id', $kejuaraanId)->where('kelompok_umur_id', $ku['id'])->countAllResults() : 0;
            $kuStats[] = [
                'nama' => $ku['nama'],
                'count' => $c,
            ];
        }

        // Acara terbaru
        $recentEvents = $eventModel->getEventsWithDetails($kejuaraanId);
        $recentEvents = array_slice($recentEvents, 0, 6);

        // Klasemen medali top 5
        $klasemenBuilder = $hasilModel->db->table('sekolah s')
            ->select('s.id, s.nama as nama_sekolah,
                SUM(CASE WHEN h.medali = "Emas" THEN 1 ELSE 0 END) as emas,
                SUM(CASE WHEN h.medali = "Perak" THEN 1 ELSE 0 END) as perak,
                SUM(CASE WHEN h.medali = "Perunggu" THEN 1 ELSE 0 END) as perunggu,
                COUNT(CASE WHEN h.medali IN ("Emas", "Perak", "Perunggu") THEN 1 END) as total_medali')
            ->join('atlet a', 'a.sekolah_id = s.id', 'inner')
            ->join('peserta_event pe', 'pe.atlet_id = a.id', 'inner')
            ->join('hasil h', 'h.peserta_event_id = pe.id', 'inner');

        if ($kejuaraanId) {
            $klasemenBuilder->where('a.kejuaraan_id', $kejuaraanId);
        }

        $topKlasemen = $klasemenBuilder->groupBy('s.id')
            ->having('total_medali >', 0)
            ->orderBy('emas', 'DESC')
            ->orderBy('perak', 'DESC')
            ->orderBy('perunggu', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $this->data['title'] = 'Dashboard — Kejurwil Atletik';
        $this->data['totalAtlet'] = $totalAtlet;
        $this->data['totalSekolah'] = $totalSekolah;
        $this->data['totalEvent'] = $totalEvent;
        $this->data['totalHasilCount'] = $totalHasilCount;
        $this->data['atletPutra'] = $atletPutra;
        $this->data['atletPutri'] = $atletPutri;
        $this->data['kuStats'] = $kuStats;
        $this->data['recentEvents'] = $recentEvents;
        $this->data['topKlasemen'] = $topKlasemen;

        return view('dashboard/index', $this->data);
    }
}
