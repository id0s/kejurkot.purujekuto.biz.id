<?php

namespace App\Controllers;

use App\Models\AtletModel;
use App\Models\EventModel;
use App\Models\KelompokUmurModel;
use App\Models\NomorLombaModel;
use App\Models\PesertaEventModel;
use App\Models\SekolahModel;

class Event extends BaseController
{
    protected EventModel $eventModel;
    protected NomorLombaModel $lombaModel;
    protected KelompokUmurModel $kuModel;
    protected PesertaEventModel $pesertaModel;
    protected AtletModel $atletModel;
    protected SekolahModel $sekolahModel;

    public function __construct()
    {
        $this->eventModel   = new EventModel();
        $this->lombaModel   = new NomorLombaModel();
        $this->kuModel      = new KelompokUmurModel();
        $this->pesertaModel = new PesertaEventModel();
        $this->atletModel   = new AtletModel();
        $this->sekolahModel = new SekolahModel();
    }

    public function index()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;

        $this->data['title']  = 'Jadwal & Acara Perlombaan';
        $this->data['events'] = $this->eventModel->getEventsWithDetails($kejuaraanId);

        return view('event/index', $this->data);
    }

    public function create()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;

        // Auto next event number
        $lastEvent = $this->eventModel->where('kejuaraan_id', $kejuaraanId)->orderBy('nomor_acara', 'DESC')->first();
        $nextNoAcara = $lastEvent ? ((int)$lastEvent['nomor_acara'] + 1) : 1;

        $this->data['title']       = 'Tambah Acara Perlombaan';
        $this->data['event']       = null;
        $this->data['nextNoAcara'] = $nextNoAcara;
        $this->data['lombas']      = $this->lombaModel->orderBy('nama', 'ASC')->findAll();
        $this->data['kus']         = $this->kuModel->orderBy('usia_min', 'ASC')->findAll();

        return view('event/form', $this->data);
    }

    public function edit(int $id)
    {
        $event = $this->eventModel->find($id);
        if (!$event) {
            return redirect()->to(base_url('event'))->with('error', 'Acara perlombaan tidak ditemukan.');
        }

        $this->data['title']       = 'Edit Acara Perlombaan';
        $this->data['event']       = $event;
        $this->data['nextNoAcara'] = $event['nomor_acara'];
        $this->data['lombas']      = $this->lombaModel->orderBy('nama', 'ASC')->findAll();
        $this->data['kus']         = $this->kuModel->orderBy('usia_min', 'ASC')->findAll();

        return view('event/form', $this->data);
    }

    public function save(?int $id = null)
    {
        $rules = [
            'nomor_lomba_id' => 'required|is_natural_no_zero',
            'nomor_acara'    => 'required|numeric',
            'nama_acara'     => 'required|min_length[3]|max_length[255]',
            'babak'          => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;

        $postData = [
            'kejuaraan_id'     => $kejuaraanId,
            'nomor_lomba_id'   => (int)$this->request->getPost('nomor_lomba_id'),
            'kelompok_umur_id' => $this->request->getPost('kelompok_umur_id') ?: null,
            'gender'           => $this->request->getPost('gender') ?? 'L',
            'nomor_acara'      => (int)$this->request->getPost('nomor_acara'),
            'nama_acara'       => trim($this->request->getPost('nama_acara')),
            'babak'            => $this->request->getPost('babak'),
            'jadwal_hari'      => $this->request->getPost('jadwal_hari'),
            'jadwal_waktu'     => $this->request->getPost('jadwal_waktu'),
            'status'           => $this->request->getPost('status') ?? 'Belum Dimulai',
            'catatan'          => $this->request->getPost('catatan'),
        ];

        if ($id) {
            $this->eventModel->update($id, $postData);
            $msg = 'Acara perlombaan berhasil diperbarui.';
        } else {
            $this->eventModel->insert($postData);
            $msg = 'Acara perlombaan baru berhasil dibuat.';
        }

        return redirect()->to(base_url('event'))->with('success', $msg);
    }

    public function delete(int $id)
    {
        $this->pesertaModel->where('event_id', $id)->delete();
        $this->eventModel->delete($id);

        return redirect()->to(base_url('event'))->with('success', 'Acara perlombaan dan relasi peserta berhasil dihapus.');
    }

    /**
     * Kelola peserta di dalam suatu acara perlombaan
     */
    public function peserta(int $id)
    {
        $event = $this->eventModel->getEventWithDetails($id);
        if (!$event) {
            return redirect()->to(base_url('event'))->with('error', 'Acara perlombaan tidak ditemukan.');
        }

        $pesertaList = $this->pesertaModel->getPesertaByEvent($id);
        $registeredIds = array_column($pesertaList, 'atlet_id');

        // Ambil atlet yang belum terdaftar di event ini untuk pilihan tambah peserta
        $kejuaraanId = $event['kejuaraan_id'];
        $availableAtlets = $this->atletModel->db->table('atlet a')
            ->select('a.*, s.nama as nama_sekolah, ku.nama as nama_ku')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->join('kelompok_umur ku', 'ku.id = a.kelompok_umur_id', 'left')
            ->where('a.kejuaraan_id', $kejuaraanId);

        // Filter atlet berdasarkan gender acara jika bukan campuran
        if ($event['gender'] !== 'Campuran') {
            $availableAtlets->where('a.gender', $event['gender']);
        }

        // Filter atlet berdasarkan KU jika diset (tetap tampilkan atlet tanpa KU agar fleksibel tanpa batasan usia kaku)
        if (!empty($event['kelompok_umur_id'])) {
            $availableAtlets->groupStart()
                ->where('a.kelompok_umur_id', $event['kelompok_umur_id'])
                ->orWhere('a.kelompok_umur_id IS NULL')
            ->groupEnd();
        }

        if (!empty($registeredIds)) {
            $availableAtlets->whereNotIn('a.id', $registeredIds);
        }

        $availableAtletsList = $availableAtlets->orderBy('a.nama', 'ASC')->get()->getResultArray();
        $sekolahList = $this->sekolahModel->orderBy('nama', 'ASC')->findAll();

        $this->data['title']           = "Peserta Acara {$event['nomor_acara']}: {$event['nama_acara']}";
        $this->data['event']           = $event;
        $this->data['pesertaList']     = $pesertaList;
        $this->data['availableAtlets'] = $availableAtletsList;
        $this->data['sekolahList']     = $sekolahList;

        return view('event/peserta', $this->data);
    }

    /**
     * Tambahkan atlet ke acara perlombaan dengan pilihan Hit (Heat) tujuan
     */
    public function addPeserta(int $eventId)
    {
        $atletIds = $this->request->getPost('atlet_ids') ?? [];
        if (!is_array($atletIds)) {
            $singleId = $this->request->getPost('atlet_id');
            $atletIds = $singleId ? [$singleId] : [];
        }

        $targetHeat = max(1, (int)($this->request->getPost('target_heat') ?: 1));

        $evObj = $this->eventModel->find($eventId);
        $namaAcara = strtolower($evObj['nama_acara'] ?? '');
        $isField = in_array(strtolower($evObj['kategori_lomba'] ?? ''), ['lompat', 'lempar']) || preg_match('/(tolak|lempar|lompat|lontar|cakram|martil|peluru|lembing)/i', $namaAcara);
        $isLongDist = (bool)preg_match('/(1\.?000|1\.?500|3\.?000|5\.?000|jalan\s*cepat)/i', $namaAcara);
        $isLaneTrack = (!$isField && !$isLongDist);

        // Tentukan lane berikutnya pada hit ini
        $lastPeserta = $this->pesertaModel->where('event_id', $eventId)->where('heat', $targetHeat)->orderBy('no_urut', 'DESC')->first();
        $nextLane = ($lastPeserta && !empty($lastPeserta['lane']) && is_numeric($lastPeserta['lane'])) ? ((int)$lastPeserta['lane'] + 1) : 1;
        $nextOrder = ($lastPeserta && !empty($lastPeserta['no_urut'])) ? ((int)$lastPeserta['no_urut'] + 1) : 1;

        $added = 0;
        foreach ($atletIds as $aId) {
            $aId = (int)$aId;
            $exists = $this->pesertaModel->where('event_id', $eventId)->where('atlet_id', $aId)->first();
            if (!$exists && $aId > 0) {
                // Untuk nomor lari lintasan, stadion hanya 8 lintasan. Jika > 8, buka hit baru
                if ($isLaneTrack && $nextLane > 8) {
                    $targetHeat++;
                    $nextLane = 1;
                }

                $this->pesertaModel->insert([
                    'event_id' => $eventId,
                    'atlet_id' => $aId,
                    'heat'     => $targetHeat,
                    'lane'     => (string)$nextLane,
                    'no_urut'  => $nextOrder++,
                ]);
                $nextLane++;
                $added++;
            }
        }

        // Sinkronkan babak: 5000, 1000, 1500, 800, 400, 200m & lapangan & estafet = Final (Langsung Rangking)
        $allPeserta = $this->pesertaModel->where('event_id', $eventId)->findAll();
        $distinctHeats = count(array_unique(array_filter(array_column($allPeserta, 'heat'))));
        $newBabak = $this->determineBabak($evObj['nama_acara'] ?? '', $distinctHeats);
        $this->eventModel->update($eventId, ['babak' => $newBabak]);

        // Hapus cache file statis agar buku acara selalu diperbarui
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

        return redirect()->to(base_url('event/peserta/' . $eventId))
            ->with('success', "{$added} atlet berhasil ditambahkan ke Hit {$targetHeat}. Babak acara: {$newBabak}.");
    }

    /**
     * Update susunan Hit (Heat) dan Lintasan (Lane) peserta secara manual/fleksibel
     */
    public function updatePeserta(int $eventId)
    {
        $pesertaData = $this->request->getPost('peserta') ?? [];
        $updated = 0;

        if (!empty($pesertaData) && is_array($pesertaData)) {
            $heatsUsed = [];
            foreach ($pesertaData as $pId => $data) {
                $pId = (int)$pId;
                if ($pId > 0) {
                    $heat = max(1, (int)($data['heat'] ?? 1));
                    $lane = trim($data['lane'] ?? '');
                    $noUrut = !empty($data['no_urut']) ? (int)$data['no_urut'] : (is_numeric($lane) ? (int)$lane : 1);

                    $this->pesertaModel->update($pId, [
                        'heat'    => $heat,
                        'lane'    => $lane,
                        'no_urut' => $noUrut,
                    ]);
                    $heatsUsed[$heat] = true;
                    $updated++;
                }
            }

            // Aturan atletik: tentukan babak sesuai jenis nomor lomba (Timed Final vs Penyisihan)
            $evObj = $this->eventModel->find($eventId);
            $newBabak = $this->determineBabak($evObj['nama_acara'] ?? '', count($heatsUsed));
            $this->eventModel->update($eventId, ['babak' => $newBabak]);

            // Hapus cache file statis agar buku acara selalu diperbarui
            @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
            @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

            $statusText = ($newBabak === 'Final') ? '1 Hit (Langsung FINAL)' : count($heatsUsed) . ' Hit (Penyisihan)';

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success'    => true,
                    'message'    => "Susunan Hit & Lintasan ({$updated} peserta) berhasil disimpan.",
                    'statusText' => $statusText,
                    'babak'      => $newBabak,
                    'heatsCount' => count($heatsUsed),
                ]);
            }

            return redirect()->to(base_url('event/peserta/' . $eventId))
                ->with('success', "Susunan Hit & Lintasan ({$updated} peserta) berhasil disimpan. Status acara: {$statusText}.");
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada data peserta yang diperbarui.']);
        }

        return redirect()->to(base_url('event/peserta/' . $eventId))->with('error', 'Tidak ada data peserta yang diperbarui.');
    }

    /**
     * Hapus peserta dari acara
     */
    public function removePeserta(int $eventId, int $pesertaEventId)
    {
        $this->pesertaModel->delete($pesertaEventId);

        // Sinkronkan babak setelah peserta dihapus
        $remaining = $this->pesertaModel->where('event_id', $eventId)->findAll();
        $distinctHeats = count(array_unique(array_filter(array_column($remaining, 'heat'))));
        $evObj = $this->eventModel->find($eventId);
        $newBabak = $this->determineBabak($evObj['nama_acara'] ?? '', $distinctHeats);
        $this->eventModel->update($eventId, ['babak' => $newBabak]);

        // Hapus cache file statis
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

        return redirect()->to(base_url('event/peserta/' . $eventId))
            ->with('success', 'Peserta berhasil dikeluarkan dari acara ini.');
    }

    /**
     * Tambah / Edit Tim Estafet dan susunan nama-nama pelari
     */
    public function saveTeamEstafet(int $eventId)
    {
        $evObj = $this->eventModel->find($eventId);
        if (!$evObj) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Acara perlombaan tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Acara perlombaan tidak ditemukan.');
        }

        $kejuaraanId     = $evObj['kejuaraan_id'];
        $pesertaEventId  = (int)($this->request->getPost('peserta_event_id') ?: 0);
        $atletId         = (int)($this->request->getPost('atlet_id') ?: 0);
        $namaTim         = trim($this->request->getPost('nama_tim') ?? '');
        $noDada          = trim($this->request->getPost('no_dada') ?? '');
        $sekolahId       = (int)($this->request->getPost('sekolah_id') ?: 0);
        $catatanPelari   = trim($this->request->getPost('catatan_pelari') ?? '');
        $heat            = max(1, (int)($this->request->getPost('heat') ?: 1));
        $lane            = trim($this->request->getPost('lane') ?? '');

        if (empty($namaTim)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Nama Tim / Regu Estafet wajib diisi.']);
            }
            return redirect()->back()->with('error', 'Nama Tim / Regu Estafet wajib diisi.');
        }

        // Pastikan catatanPelari diawali kata "Pelari: " jika diisi dan belum ada
        if (!empty($catatanPelari) && stripos($catatanPelari, 'pelari') === false) {
            $catatanPelari = 'Pelari: ' . $catatanPelari;
        }

        // Tentukan gender tim
        $teamGender = ($evObj['gender'] === 'Campuran') ? 'L' : ($evObj['gender'] ?: 'L');

        if ($atletId > 0) {
            // EDIT atlet tim yang sudah ada
            $updateData = [
                'nama'       => $namaTim,
                'no_dada'    => $noDada,
                'catatan'    => $catatanPelari,
            ];
            if ($sekolahId > 0) {
                $updateData['sekolah_id'] = $sekolahId;
            }
            $this->atletModel->update($atletId, $updateData);
        } else {
            // TAMBAH atlet tim baru
            $atletId = $this->atletModel->insert([
                'kejuaraan_id'     => $kejuaraanId,
                'sekolah_id'       => $sekolahId > 0 ? $sekolahId : 0,
                'no_dada'          => $noDada,
                'nama'             => $namaTim,
                'gender'           => $teamGender,
                'kelompok_umur_id' => $evObj['kelompok_umur_id'] ?? null,
                'catatan'          => $catatanPelari,
            ]);
        }

        if ($pesertaEventId > 0) {
            // EDIT peserta_event
            $this->pesertaModel->update($pesertaEventId, [
                'heat'    => $heat,
                'lane'    => $lane,
                'no_urut' => is_numeric($lane) ? (int)$lane : 1,
            ]);
        } else {
            // TAMBAH peserta_event baru
            if (empty($lane)) {
                $lastP = $this->pesertaModel->where('event_id', $eventId)->where('heat', $heat)->orderBy('no_urut', 'DESC')->first();
                $lane = ($lastP && is_numeric($lastP['lane'])) ? (string)((int)$lastP['lane'] + 1) : '1';
            }

            $pesertaEventId = $this->pesertaModel->insert([
                'event_id' => $eventId,
                'atlet_id' => $atletId,
                'heat'     => $heat,
                'lane'     => $lane,
                'no_urut'  => is_numeric($lane) ? (int)$lane : 1,
            ]);
        }

        // Sinkronkan babak acara
        $allPeserta = $this->pesertaModel->where('event_id', $eventId)->findAll();
        $distinctHeats = count(array_unique(array_filter(array_column($allPeserta, 'heat'))));
        $newBabak = $this->determineBabak($evObj['nama_acara'] ?? '', $distinctHeats);
        $this->eventModel->update($eventId, ['babak' => $newBabak]);

        // Hapus cache file statis buku acara
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => "Data Tim Estafet '{$namaTim}' berhasil disimpan.",
                'data' => [
                    'peserta_event_id' => $pesertaEventId,
                    'atlet_id'         => $atletId,
                    'nama_tim'         => $namaTim,
                    'no_dada'          => $noDada,
                    'catatan'          => $catatanPelari,
                    'heat'             => $heat,
                    'lane'             => $lane,
                ]
            ]);
        }

        return redirect()->to(base_url('event/peserta/' . $eventId))
            ->with('success', "Data Tim Estafet '{$namaTim}' berhasil disimpan.");
    }

    /**
     * Algoritma Seeding & Pengacakan Hit Atletik:
     * - Mengacak peserta ke dalam Hit sesuai kapasitas masing-masing Hit
     * - Memisahkan peserta dari Kota/Kabupaten yang sama agar tidak berada dalam 1 Hit (regional separation)
     * - Memisahkan peserta dari Sekolah/Klub yang sama agar tidak saling berhadapan di babak penyisihan
     * - Mengacak nomor lintasan (lane) di dalam masing-masing Hit
     *
     * @param array $pesertaList Daftar peserta lengkap dengan data sekolah & alamat
     * @param array $heatCapacities Array asosiatif nomor_hit => kapasitas (misal: [1 => 7, 2 => 6])
     * @return array Array hasil alokasi: [heat_number => [ [id, heat, lane, no_urut, ...], ... ]]
     */
    protected function distributePesertaByRegion(array $pesertaList, array $heatCapacities): array
    {
        if (empty($pesertaList)) {
            return [];
        }

        // 1. Tentukan kota/kabupaten untuk setiap peserta dan acak urutan awal
        foreach ($pesertaList as &$p) {
            $p['kota_kab'] = detect_kota_kabupaten($p['nama_sekolah'] ?? '', $p['alamat_sekolah'] ?? '');
            $p['sch_id']   = (int)($p['sekolah_id'] ?? 0);
        }
        unset($p);

        // Acak awal peserta
        shuffle($pesertaList);

        // Jika hanya ada 1 Hit (Langsung Final), cukup acak lintasannya
        if (count($heatCapacities) <= 1) {
            $hNum = !empty($heatCapacities) ? array_key_first($heatCapacities) : 1;
            shuffle($pesertaList);
            $lane = 1;
            $order = 1;
            $result = [$hNum => []];
            foreach ($pesertaList as $p) {
                $p['heat']    = $hNum;
                $p['lane']    = (string)$lane++;
                $p['no_urut'] = $order++;
                $result[$hNum][] = $p;
            }
            return $result;
        }

        // 2. Inisialisasi wadah/bin setiap Heat
        $bins = [];
        $totalCapacity = 0;
        foreach ($heatCapacities as $hNum => $cap) {
            $hNum = (int)$hNum;
            $cap  = max(1, (int)$cap);
            $totalCapacity += $cap;
            $bins[$hNum] = [
                'capacity' => $cap,
                'peserta'  => [],
                'regions'  => [], // ['BATANG' => count, ...]
                'schools'  => [], // [sekolah_id => count, ...]
            ];
        }

        // 3. Kelompokkan peserta berdasarkan Kota/Kabupaten, lalu Sekolah
        $byRegion = [];
        foreach ($pesertaList as $p) {
            $byRegion[$p['kota_kab']][] = $p;
        }

        // Sub-kelompokkan per sekolah di dalam masing-masing daerah
        $sortedRegionList = [];
        foreach ($byRegion as $reg => $atlets) {
            $bySchool = [];
            foreach ($atlets as $a) {
                $bySchool[$a['sch_id']][] = $a;
            }
            // Urutkan sekolah dengan atlet terbanyak terlebih dahulu
            uasort($bySchool, fn($x, $y) => count($y) <=> count($x));

            $flatRegAtlets = [];
            foreach ($bySchool as $schAtlets) {
                shuffle($schAtlets);
                foreach ($schAtlets as $sa) {
                    $flatRegAtlets[] = $sa;
                }
            }
            $sortedRegionList[$reg] = $flatRegAtlets;
        }

        // Urutkan grup daerah: daerah dengan atlet terbanyak diproses paling awal (heuristic graph coloring)
        uasort($sortedRegionList, fn($x, $y) => count($y) <=> count($x));

        // 4. Masukkan peserta satu per satu ke dalam Hit terbaik (Greedy dengan penalti & faktor acak)
        foreach ($sortedRegionList as $reg => $atlets) {
            foreach ($atlets as $a) {
                $schId = $a['sch_id'];

                // Cari hit yang masih punya kapasitas
                $availableHeats = [];
                foreach ($bins as $hNum => $bin) {
                    if (count($bin['peserta']) < $bin['capacity']) {
                        $availableHeats[] = $hNum;
                    }
                }

                // Jika seluruh kapasitas penuh karena total peserta > total kapasitas, ambil hit mana saja
                if (empty($availableHeats)) {
                    $availableHeats = array_keys($bins);
                }

                $bestHeat = null;
                $lowestPenalty = PHP_INT_MAX;

                // Kocok urutan hit kandidat agar jika penalti seri, hit yang dipilih acak
                shuffle($availableHeats);

                foreach ($availableHeats as $hNum) {
                    $bin = $bins[$hNum];
                    $regCount  = $bin['regions'][$reg] ?? 0;
                    $schCount  = $bin['schools'][$schId] ?? 0;
                    $fillCount = count($bin['peserta']);
                    $fillRatio = $bin['capacity'] > 0 ? ($fillCount / $bin['capacity']) : 0;

                    // Penalti:
                    // - Daerah sama: bobot 10,000 per orang
                    // - Sekolah sama: bobot 5,000 per orang
                    // - Kepadatan hit: bobot 100 * rasio keterisian
                    // - Noise acak: 0..20 untuk variasi alami
                    $penalty = ($regCount * 10000) 
                             + ($schCount * 5000) 
                             + (int)($fillRatio * 100) 
                             + mt_rand(0, 20);

                    if ($penalty < $lowestPenalty) {
                        $lowestPenalty = $penalty;
                        $bestHeat = $hNum;
                    }
                }

                // Alokasikan ke $bestHeat
                $bins[$bestHeat]['peserta'][] = $a;
                $bins[$bestHeat]['regions'][$reg] = ($bins[$bestHeat]['regions'][$reg] ?? 0) + 1;
                $bins[$bestHeat]['schools'][$schId] = ($bins[$bestHeat]['schools'][$schId] ?? 0) + 1;
            }
        }

        // 5. Acak nomor lintasan (lane) dan urutan di dalam tiap-tiap Hit
        $result = [];
        $globalOrder = 1;
        foreach ($bins as $hNum => $bin) {
            $assigned = $bin['peserta'];
            shuffle($assigned); // Acak urutan pelari dalam Hit ini untuk menentukan nomor lintasan

            $lane = 1;
            foreach ($assigned as &$p) {
                $p['heat']    = $hNum;
                $p['lane']    = (string)$lane++;
                $p['no_urut'] = $globalOrder++;
            }
            unset($p);

            $result[$hNum] = $assigned;
        }

        return $result;
    }

    /**
     * Atur heat dan lintasan otomatis sesuai aturan atletik:
     * - Random / acak, tetapi peserta dari Kota/Kabupaten yang sama TIDAK disatukan dalam satu Hit
     * - Peserta dari sekolah/klub yang sama juga dipisahkan
     * - Mendukung mode Bagi Rata (misal 8 per Hit), Kustom per Hit, atau 1 Hit (Langsung Final)
     */
    public function autoArrange(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->to(base_url('event'))->with('error', 'Acara lomba tidak ditemukan.');
        }

        $pesertaList = $this->pesertaModel->getPesertaByEvent($eventId);
        $totalPeserta = count($pesertaList);

        if ($totalPeserta === 0) {
            return redirect()->to(base_url('event/peserta/' . $eventId))->with('error', 'Belum ada peserta di acara ini untuk diatur.');
        }

        $arrangeType = $this->request->getPost('arrange_type') ?? 'random_region';
        $customHeats = $this->request->getPost('custom_heats') ?? [];

        $evObj = $this->eventModel->find($eventId);
        $namaAcara = strtolower($evObj['nama_acara'] ?? '');
        $kat = strtolower($evObj['kategori_lomba'] ?? '');
        $isField = in_array($kat, ['lompat', 'lempar']) || preg_match('/(tolak|lempar|lompat|lontar|cakram|martil|peluru|lembing)/i', $namaAcara);
        $is5000 = (bool)preg_match('/5\.?000\s*m/i', $namaAcara);
        $isDistance = (bool)preg_match('/(1\.?000|1\.?500|3\.?000|5\.?000|jalan\s*cepat)/i', $namaAcara);
        $isBabakFinal = (strcasecmp($evObj['babak'] ?? '', 'Final') === 0);

        if ($arrangeType === 'single') {
            // Mode 1 Hit (Langsung FINAL)
            // Nomor lapangan, 5000m, jarak menengah/jauh, atau jika babak memang Final:
            // Seluruh peserta masuk ke 1 Hit tanpa dipaksa pecah menjadi hit 8 orang
            $distributed = $this->distributePesertaByRegion($pesertaList, [1 => $totalPeserta]);
            foreach ($distributed[1] as $p) {
                $this->pesertaModel->update($p['id'], [
                    'heat'    => 1,
                    'lane'    => $p['lane'],
                    'no_urut' => $p['no_urut'],
                ]);
            }
            $this->eventModel->update($eventId, ['babak' => 'Final']);
            $msg = "Seluruh {$totalPeserta} peserta berhasil diatur menjadi 1 Hit bersama (Langsung FINAL) dengan nomor lintasan/giliran diacak.";
        } elseif ($arrangeType === 'custom' && !empty($customHeats) && is_array($customHeats)) {
            // Mode Kustom per Hit (Bisa tentukan misal Hit 1: 8 orang, Hit 2: 7 orang, dst)
            ksort($customHeats);
            $cleanCapacities = [];
            $sumCap = 0;
            foreach ($customHeats as $hNum => $cap) {
                $hNum = (int)$hNum;
                $cap  = (int)$cap;
                if ($cap > 0 && $hNum > 0) {
                    $cleanCapacities[$hNum] = $cap;
                    $sumCap += $cap;
                }
            }

            // Jika kapasitas kustom kurang dari total peserta, buat Hit tambahan otomatis
            if ($sumCap < $totalPeserta) {
                $nextH = !empty($cleanCapacities) ? max(array_keys($cleanCapacities)) + 1 : 1;
                $cleanCapacities[$nextH] = $totalPeserta - $sumCap;
            }

            $distributed = $this->distributePesertaByRegion($pesertaList, $cleanCapacities);

            $summary = [];
            $totalHeatsUsed = 0;
            foreach ($distributed as $hNum => $atlets) {
                foreach ($atlets as $p) {
                    $this->pesertaModel->update($p['id'], [
                        'heat'    => $p['heat'],
                        'lane'    => $p['lane'],
                        'no_urut' => $p['no_urut'],
                    ]);
                }
                $count = count($atlets);
                if ($count > 0) {
                    $summary[] = "Hit {$hNum}: {$count} orang";
                    $totalHeatsUsed++;
                }
            }

            $newBabak = $this->determineBabak($evObj['nama_acara'] ?? '', $totalHeatsUsed);
            $this->eventModel->update($eventId, ['babak' => $newBabak]);

            $msg = ($totalHeatsUsed > 1)
                ? "Susunan Hit berhasil diacak dengan pemisahan kota/kabupaten ({$totalHeatsUsed} Hit - Babak {$newBabak}): " . implode(', ', $summary) . '.'
                : "Acara diatur menjadi 1 Hit bersama (Babak {$newBabak}).";
        } else {
            // Mode Bagi Rata Otomatis / Acak Regional
            $lanesPerHeat = (int)($this->request->getPost('lanes_per_heat') ?? 8);
            if ($lanesPerHeat < 2) $lanesPerHeat = 2;
            if ($lanesPerHeat > 8) $lanesPerHeat = 8; // Lintasan stadion maksimal 8!

            // 1. Nomor Jarak Jauh (1.000m, 1.500m, 3.000m, 5.000m, Jalan Cepat), Nomor Lapangan, atau Babak Final:
            // Seluruh peserta berada dalam 1 kelompok bersama (Langsung FINAL).
            // Batasannya adalah waktu tempuh/limit waktu lomba, bukan pembagian heat!
            if ($isDistance || $is5000 || $isField || ($isBabakFinal && $totalPeserta <= $lanesPerHeat)) {
                $distributed = $this->distributePesertaByRegion($pesertaList, [1 => $totalPeserta]);
                $order = 1;
                foreach ($distributed[1] as $p) {
                    $this->pesertaModel->update($p['id'], [
                        'heat'    => 1,
                        'lane'    => $isDistance ? '' : $p['lane'],
                        'no_urut' => $order++,
                    ]);
                }
                $this->eventModel->update($eventId, ['babak' => 'Final']);
                $msg = $isDistance
                    ? "Seluruh {$totalPeserta} peserta diatur dalam 1 kelompok bersama (Langsung FINAL). Batasan perlombaan jarak jauh adalah waktu tempuh/limit waktu, bukan pembagian heat."
                    : "Seluruh {$totalPeserta} peserta berada dalam 1 Hit (Langsung FINAL) tanpa dibagi hit.";
            } elseif ($totalPeserta <= $lanesPerHeat) {
                // Semua masuk ke 1 Hit (Langsung Final)
                $distributed = $this->distributePesertaByRegion($pesertaList, [1 => $totalPeserta]);
                foreach ($distributed[1] as $p) {
                    $this->pesertaModel->update($p['id'], [
                        'heat'    => 1,
                        'lane'    => $p['lane'],
                        'no_urut' => $p['no_urut'],
                    ]);
                }
                $this->eventModel->update($eventId, ['babak' => 'Final']);
                $msg = "Seluruh {$totalPeserta} peserta berada dalam kapasitas 1 Hit (Langsung FINAL) dengan lintasan diacak.";
            } else {
                // Hitung jumlah hit yang dibutuhkan: ceil(total / lanesPerHeat)
                $numHeats = (int)ceil($totalPeserta / $lanesPerHeat);
                
                // Distribusikan kapasitas secara seimbang (misal 9 peserta -> 5 dan 4)
                $basePerHeat = (int)floor($totalPeserta / $numHeats);
                $remainder   = $totalPeserta % $numHeats;

                $capacities = [];
                for ($h = 1; $h <= $numHeats; $h++) {
                    $capacities[$h] = $basePerHeat + ($h <= $remainder ? 1 : 0);
                }

                $distributed = $this->distributePesertaByRegion($pesertaList, $capacities);

                $summary = [];
                foreach ($distributed as $hNum => $atlets) {
                    foreach ($atlets as $p) {
                        $this->pesertaModel->update($p['id'], [
                            'heat'    => $p['heat'],
                            'lane'    => $p['lane'],
                            'no_urut' => $p['no_urut'],
                        ]);
                    }
                    $summary[] = "Hit {$hNum}: " . count($atlets) . " orang";
                }

                $newBabak = $this->determineBabak($evObj['nama_acara'] ?? '', $numHeats);
                $this->eventModel->update($eventId, ['babak' => $newBabak]);

                $msg = "Peserta berhasil diacak dan dipisahkan kota/kabupaten menjadi {$numHeats} Hit (Babak {$newBabak}): " . implode(', ', $summary) . ".";
            }
        }


        // Hapus cache file statis agar buku acara selalu diperbarui
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

        return redirect()->to(base_url('event/peserta/' . $eventId))->with('success', $msg);
    }

    /**
     * Atur Hit, Lintasan, dan Babak otomatis untuk SEMUA acara perlombaan sekaligus:
     * - Nomor Lari sprint (<= 400m dan 800m) dibagi maks 8 orang per Hit, diacak dan dipisahkan kota/kabupaten & sekolah
     * - Nomor Lari jarak menengah/jauh (1000m, 1500m, 5000m) tidak dipecah jika final (atau dibagi 2 seri jika > 25 orang)
     * - Nomor Lapangan (lompat, lempar, tolak) 1 Hit (Langsung Final) dengan nomor Giliran 1..N
     * - Nomor Estafet dikelompokkan per sekolah/tim
     */
    public function autoArrangeAll()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;
        $events = $this->eventModel->getEventsWithDetails($kejuaraanId);

        $arrangedCount = 0;
        foreach ($events as $ev) {
            $eventId = (int)$ev['id'];
            $pesertaList = $this->pesertaModel->getPesertaByEvent($eventId);
            $totalPeserta = count($pesertaList);
            if ($totalPeserta === 0) {
                continue;
            }

            $kat = strtolower($ev['kategori_lomba'] ?? $ev['kategori'] ?? '');
            $namaAcara = strtolower($ev['nama_acara'] ?? '');
            $isField = in_array($kat, ['lompat', 'lempar']) || preg_match('/(tolak|lempar|lompat|lontar|cakram|martil|peluru|lembing)/i', $namaAcara);
            $isRelay = ($kat === 'estafet') || preg_match('/estafet/i', $namaAcara);
            $is5000 = (bool)preg_match('/5\.?000\s*m/i', $namaAcara);
            $isDistance = (bool)preg_match('/(1\.?000|1\.?500|3\.?000|5\.?000|jalan\s*cepat)/i', $namaAcara);
            $isBabakFinal = (strcasecmp($ev['babak'] ?? '', 'Final') === 0);

            if ($isField) {
                // Nomor Lapangan: 1 Hit (Final), Giliran 1..N
                shuffle($pesertaList);
                $lane = 1;
                $order = 1;
                foreach ($pesertaList as $p) {
                    $this->pesertaModel->update($p['id'], [
                        'heat'    => 1,
                        'lane'    => (string)$lane++,
                        'no_urut' => $order++,
                    ]);
                }
                $this->eventModel->update($eventId, ['babak' => 'Final']);
                $arrangedCount++;
            } elseif ($isRelay) {
                // Nomor Estafet: kelompokkan berdasarkan tim/sekolah
                $teams = [];
                foreach ($pesertaList as $p) {
                    $teams[$p['sekolah_id']][] = $p;
                }
                $numTeams = count($teams);
                $heatsCount = ($numTeams <= 8) ? 1 : (int)ceil($numTeams / 8);
                $babak = ($heatsCount === 1) ? 'Final' : 'Penyisihan';

                $teamKeys = array_keys($teams);
                shuffle($teamKeys);

                $teamHeatLane = [];
                if ($heatsCount === 1) {
                    $lane = 1;
                    foreach ($teamKeys as $schId) {
                        $teamHeatLane[$schId] = [1, (string)$lane++];
                    }
                } else {
                    $base = (int)floor($numTeams / $heatsCount);
                    $rem = $numTeams % $heatsCount;
                    $curIdx = 0;
                    for ($h = 1; $h <= $heatsCount; $h++) {
                        $cap = $base + ($h <= $rem ? 1 : 0);
                        $lane = 1;
                        for ($c = 0; $c < $cap; $c++) {
                            if ($curIdx < count($teamKeys)) {
                                $schId = $teamKeys[$curIdx++];
                                $teamHeatLane[$schId] = [$h, (string)$lane++];
                            }
                        }
                    }
                }

                $globalOrder = 1;
                foreach ($pesertaList as $p) {
                    [$hNum, $laneStr] = $teamHeatLane[$p['sekolah_id']] ?? [1, '1'];
                    $this->pesertaModel->update($p['id'], [
                        'heat'    => $hNum,
                        'lane'    => $laneStr,
                        'no_urut' => $globalOrder++,
                    ]);
                }

                $this->eventModel->update($eventId, ['babak' => $babak]);
                $arrangedCount++;
            } elseif ($isDistance || $is5000 || ($isBabakFinal && $totalPeserta <= 8)) {
                // Nomor Jarak Jauh (1000m, 1500m, 3000m, 5000m, Jalan Cepat) selalu 1 Kelompok (Langsung FINAL):
                // Batasan lomba adalah waktu tempuh/limit waktu, bukan pembagian heat
                shuffle($pesertaList);
                $order = 1;
                foreach ($pesertaList as $p) {
                    $this->pesertaModel->update($p['id'], [
                        'heat'    => 1,
                        'lane'    => $isDistance ? '' : (string)min(8, $order),
                        'no_urut' => $order++,
                    ]);
                }
                $this->eventModel->update($eventId, ['babak' => 'Final']);
                $arrangedCount++;
            } else {
                // Nomor Lari Lintasan Sprint (maks 8 lintasan per hit)
                $lanesPerHeat = 8;

                if ($totalPeserta <= $lanesPerHeat) {
                    $distributed = $this->distributePesertaByRegion($pesertaList, [1 => $totalPeserta]);
                    foreach ($distributed[1] as $p) {
                        $this->pesertaModel->update($p['id'], [
                            'heat'    => 1,
                            'lane'    => $p['lane'],
                            'no_urut' => $p['no_urut'],
                        ]);
                    }
                    $this->eventModel->update($eventId, ['babak' => 'Final']);
                } else {
                    // Ketika peserta > 8, bagi rata (diratakan) agar kapasitas seimbang dan tidak ada hit tersisa < 4
                    $numHeats = (int)ceil($totalPeserta / $lanesPerHeat);
                    $basePerHeat = (int)floor($totalPeserta / $numHeats);
                    $remainder   = $totalPeserta % $numHeats;

                    $capacities = [];
                    for ($h = 1; $h <= $numHeats; $h++) {
                        $capacities[$h] = $basePerHeat + ($h <= $remainder ? 1 : 0);
                    }

                    $distributed = $this->distributePesertaByRegion($pesertaList, $capacities);
                    foreach ($distributed as $hNum => $atlets) {
                        foreach ($atlets as $p) {
                            $this->pesertaModel->update($p['id'], [
                                'heat'    => $p['heat'],
                                'lane'    => $p['lane'],
                                'no_urut' => $p['no_urut'],
                            ]);
                        }
                    }

                    $newBabak = $this->determineBabak($ev['nama_acara'] ?? '', $numHeats);
                    $this->eventModel->update($eventId, ['babak' => $newBabak]);
                }
                $arrangedCount++;
            }
        }

        // Hapus cache file statis
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

        return redirect()->to(base_url('event'))->with('success', "Berhasil menyusun Hit, Lintasan, dan Babak untuk {$arrangedCount} acara perlombaan sesuai regulasi atletik.");
    }

    /**
     * Tentukan Babak acara berdasarkan regulasi atletik:
     * - Nomor 5000m, 1000m, 1500m, 800m, 400m, 200m: Langsung Rangking / Timed Final (Final)
     * - Nomor Lapangan (lompat, tolak, lempar, cakram) dan Estafet: Langsung Final (Final)
     * - Nomor Sprint pendek (30m, 60m, 80m, 100m): Penyisihan (jika > 1 Hit), Final (jika 1 Hit)
     */
    protected function determineBabak(string $namaAcara, int $distinctHeats): string
    {
        $namaLower = strtolower($namaAcara);
        
        // JuarAKU (30m anak-anak) langsung final / ranking
        if (preg_match('/(juaraku|30\s*m)/i', $namaLower)) {
            return 'Final';
        }

        // 5000, 1000, 1500, 800, 400, 200m langsung rangking (Final)
        if (preg_match('/(5\.?000|1\.?500|1\.?000|800|400|200)/i', $namaLower)) {
            return 'Final';
        }
        
        // Nomor Lapangan & Estafet: Langsung Final
        if (preg_match('/(tolak|lempar|lompat|lontar|cakram|peluru|lembing|estafet)/i', $namaLower)) {
            return 'Final';
        }

        // Nomor Sprint dengan babak penyisihan (60m, 80m, 100m)
        if (preg_match('/\b(60|80|100)\s*m\b/i', $namaLower)) {
            return ($distinctHeats > 1) ? 'Penyisihan' : 'Final';
        }

        return ($distinctHeats > 1) ? 'Penyisihan' : 'Final';
    }
}

