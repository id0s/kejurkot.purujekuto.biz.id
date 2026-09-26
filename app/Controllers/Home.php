<?php

namespace App\Controllers;

use App\Models\AtletModel;
use App\Models\EventModel;
use App\Models\KejuaraanModel;
use App\Models\KelompokUmurModel;
use App\Models\SekolahModel;

class Home extends BaseController
{
    protected AtletModel $atletModel;
    protected EventModel $eventModel;
    protected SekolahModel $sekolahModel;
    protected KelompokUmurModel $kuModel;

    public function __construct()
    {
        $this->atletModel   = new AtletModel();
        $this->eventModel   = new EventModel();
        $this->sekolahModel = new SekolahModel();
        $this->kuModel      = new KelompokUmurModel();
    }

    public function index(): string
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;

        // Ambil daftar atlet beserta lomba yang diikuti
        $atlets = [];
        $totalAtlet = 0;
        $totalSekolah = 0;
        $totalAcara = 0;
        $sekolahList = [];
        $kuList = [];

        if ($kejuaraanId) {
            $atlets       = $this->atletModel->getAtletWithEvents($kejuaraanId);
            $totalAtlet   = count($atlets);
            $totalAcara   = $this->eventModel->where('kejuaraan_id', $kejuaraanId)->countAllResults();

            // Sekolah yang memiliki atlet berpartisipasi pada kejuaraan aktif ini
            $db = \Config\Database::connect();
            $sekolahList = $db->table('sekolah s')
                ->join('atlet a', 'a.sekolah_id = s.id', 'inner')
                ->where('a.kejuaraan_id', $kejuaraanId)
                ->groupBy('s.id')
                ->orderBy('s.nama', 'ASC')
                ->select('s.*')
                ->get()
                ->getResultArray();
            $totalSekolah = count($sekolahList);

            $kuList       = $this->kuModel->orderBy('usia_min', 'ASC')->findAll();
        }

        $this->data['title']        = esc($this->activeKejuaraan['nama'] ?? 'Kejuaraan Kota Atletik');
        $this->data['atlets']       = $atlets;
        $this->data['totalAtlet']   = $totalAtlet;
        $this->data['totalSekolah'] = $totalSekolah;
        $this->data['totalAcara']   = $totalAcara;
        $this->data['sekolahList']  = $sekolahList;
        $this->data['kuList']       = $kuList;

        return view('home/index', $this->data);
    }
}

