<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\HasilModel;
use App\Models\PesertaEventModel;

class Hasil extends BaseController
{
    protected EventModel $eventModel;
    protected PesertaEventModel $pesertaModel;
    protected HasilModel $hasilModel;

    public function __construct()
    {
        $this->eventModel   = new EventModel();
        $this->pesertaModel = new PesertaEventModel();
        $this->hasilModel   = new HasilModel();
    }

    public function index()
    {
        $kejuaraanId = $this->activeKejuaraan['id'] ?? null;

        $this->data['title']  = 'Input & Rekap Hasil Perlombaan';
        $this->data['events'] = $this->eventModel->getEventsWithDetails($kejuaraanId);

        return view('hasil/index', $this->data);
    }

    public function input(int $eventId)
    {
        $event = $this->eventModel->getEventWithDetails($eventId);
        if (!$event) {
            $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;
            $evByNo = $this->eventModel->where('kejuaraan_id', $kejuaraanId)->where('nomor_acara', $eventId)->first();
            if ($evByNo) {
                return redirect()->to(base_url('hasil/input/' . $evByNo['id']));
            }
            return redirect()->to(base_url('hasil'))->with('error', 'Acara perlombaan tidak ditemukan.');
        }

        $pesertaList = $this->pesertaModel->getPesertaByEvent($eventId);
        $isField = $this->isFieldEvent($event);

        // Ekstrak data 3x percobaan jika sudah pernah disimpan di keterangan
        foreach ($pesertaList as &$p) {
            $p['p1'] = '';
            $p['p2'] = '';
            $p['p3'] = '';
            if (!empty($p['ket_hasil']) && preg_match('/P1:\s*([^|]+)\|\s*P2:\s*([^|]+)\|\s*P3:\s*(.+)/i', $p['ket_hasil'], $m)) {
                $p['p1'] = trim($m[1]) === '-' ? '' : trim($m[1]);
                $p['p2'] = trim($m[2]) === '-' ? '' : trim($m[2]);
                $p['p3'] = trim($m[3]) === '-' ? '' : trim($m[3]);
            }
        }
        unset($p);

        $penyisihanEvent   = null;
        $finalEvent        = null;
        $finalPesertaCount = 0;

        if (strcasecmp($event['babak'], 'Final') === 0) {
            $penyisihanEvent = $this->eventModel->where([
                'kejuaraan_id'     => $event['kejuaraan_id'],
                'nomor_lomba_id'   => $event['nomor_lomba_id'],
                'kelompok_umur_id' => $event['kelompok_umur_id'],
                'gender'           => $event['gender'],
                'babak'            => 'Penyisihan',
            ])->first();
        } elseif (strcasecmp($event['babak'], 'Penyisihan') === 0) {
            $finalEvent = $this->eventModel->where([
                'kejuaraan_id'     => $event['kejuaraan_id'],
                'nomor_lomba_id'   => $event['nomor_lomba_id'],
                'kelompok_umur_id' => $event['kelompok_umur_id'],
                'gender'           => $event['gender'],
                'babak'            => 'Final',
            ])->first();
            if ($finalEvent) {
                $finalPesertaCount = $this->pesertaModel->where('event_id', $finalEvent['id'])->countAllResults();
            }
        }

        $this->data['title']             = "Input Hasil Acara {$event['nomor_acara']}: {$event['nama_acara']}";
        $this->data['event']             = $event;
        $this->data['pesertaList']       = $pesertaList;
        $this->data['isFieldEvent']      = $isField;
        $this->data['penyisihanEvent']   = $penyisihanEvent;
        $this->data['finalEvent']        = $finalEvent;
        $this->data['finalPesertaCount'] = $finalPesertaCount;

        return view('hasil/input', $this->data);
    }


    public function save(int $eventId)
    {
        $hasilData = $this->request->getPost('hasil') ?? [];
        $event     = $this->eventModel->getEventWithDetails($eventId);
        $isField   = $event ? $this->isFieldEvent($event) : false;

        if (!is_array($hasilData)) {
            return redirect()->back()->with('error', 'Format data tidak valid.');
        }

        $saved = 0;
        foreach ($hasilData as $pesertaEventId => $row) {
            $pesertaEventId = (int)$pesertaEventId;
            $status   = $row['status_prestasi'] ?? 'Valid';
            $prestasi = trim($row['prestasi'] ?? '');

            // Normalisasi input status khusus pada kolom prestasi
            $upperPres = strtoupper($prestasi);
            if (in_array($upperPres, ['DISK', 'DISQ', 'DQ'])) {
                $status   = 'DQ';
                $prestasi = 'DQ';
            } elseif (in_array($upperPres, ['NM', 'X'])) {
                $status   = 'NM';
                $prestasi = 'NM';
            } elseif ($upperPres === 'DNS') {
                $status   = 'DNS';
                $prestasi = 'DNS';
            } elseif ($upperPres === 'DNF') {
                $status   = 'DNF';
                $prestasi = 'DNF';
            } elseif ($prestasi === '0' || $prestasi === '0.0' || $prestasi === '0.00') {
                $prestasi = '0.00';
            } elseif ($prestasi === '-') {
                $prestasi = '-';
            }

            // Jika status terpilih DQ/DNS/DNF/NM tapi prestasi kosong, sinkronkan
            if ($status !== 'Valid' && empty($prestasi)) {
                $prestasi = $status;
            }

            // Normalisasi format waktu jika user memasukkan koma atau format desimal (misal 3,54 atau 3.54 pada nomor lari jarak menengah)
            if (!$isField && !empty($prestasi) && !in_array($status, ['DQ', 'NM', 'DNS', 'DNF', '-'])) {
                $prestasiClean = str_replace(',', '.', $prestasi);
                if (strpos($prestasiClean, ':') === false && is_numeric($prestasiClean)) {
                    $parts = explode('.', $prestasiClean);
                    if (count($parts) === 2) {
                        $isMiddleLong = in_array((int)($event['nomor_lomba_id'] ?? 0), [8, 9, 10, 11]) || preg_match('/(800|1000|1500|3000|5000)\s*m/i', $event['nama_acara'] ?? '');
                        if ($isMiddleLong && (int)$parts[0] < 60 && (int)$parts[1] < 60) {
                            $prestasi = sprintf('%d:%02d.00', (int)$parts[0], (int)$parts[1]);
                        }
                    }
                }
            }

            // Jika nomor lapangan (Tolak Peluru / Lompat Jauh / Lempar Lembing / Cakram), periksa 3 percobaan
            $p1 = trim($row['p1'] ?? '');
            $p2 = trim($row['p2'] ?? '');
            $p3 = trim($row['p3'] ?? '');

            if ($isField && ($p1 !== '' || $p2 !== '' || $p3 !== '')) {
                $trials = [strtoupper($p1), strtoupper($p2), strtoupper($p3)];

                // Jika ada percobaan yang bertuliskan DISK/DISQ/DQ
                if (in_array('DISK', $trials) || in_array('DISQ', $trials) || in_array('DQ', $trials)) {
                    $status   = 'DQ';
                    $prestasi = 'DQ';
                } else {
                    // Cari lemparan / lompatan yang paling jauh (nilai maksimum) dari 3 percobaan
                    $bestVal = null;
                    $bestStr = '';

                    foreach ([$p1, $p2, $p3] as $tVal) {
                        $cleaned = str_replace(',', '.', trim($tVal));
                        if (is_numeric($cleaned)) {
                            $num = (float)$cleaned;
                            if ($bestVal === null || $num > $bestVal) {
                                $bestVal = $num;
                                $bestStr = number_format($num, 2, '.', '');
                            }
                        }
                    }

                    if ($bestVal !== null) {
                        $prestasi = $bestStr;
                        if ($bestVal <= 0 && $status === 'Valid') {
                            $prestasi = '0.00';
                        }
                    } elseif (in_array('X', $trials) || in_array('NM', $trials)) {
                        $status   = 'NM'; // No Mark (semua foul/gagal)
                        $prestasi = 'NM';
                    } elseif ($p1 === '-' && $p2 === '-' && $p3 === '-') {
                        $prestasi = '-';
                    }
                }

                $ket = "P1: " . ($p1 ?: '-') . " | P2: " . ($p2 ?: '-') . " | P3: " . ($p3 ?: '-');
            } else {
                $ket = trim($row['keterangan'] ?? '');
            }

            // Jika prestasi kosong DAN status Valid DAN peringkat kosong:
            if (($prestasi === '' || $prestasi === null) && $status === 'Valid' && empty($row['peringkat'])) {
                // Jika sebelumnya ada data di DB, hapus agar status benar-benar ter-reset
                $existing = $this->hasilModel->where('peserta_event_id', $pesertaEventId)->first();
                if ($existing) {
                    $this->hasilModel->delete($existing['id']);
                    $saved++;
                }
                continue;
            }

            $peringkat = !empty($row['peringkat']) ? (int)$row['peringkat'] : null;
            $medali    = $row['medali'] ?? 'None';

            // Jika status tidak valid atau nilai prestasi 0/DQ/DNS/DNF/NM, pastikan tidak dapat peringkat juara/medali
            if ($status !== 'Valid' || in_array(strtoupper($prestasi), ['DQ', 'NM', 'DNS', 'DNF', '0', '0.00', '-'])) {
                $peringkat = null;
                $medali    = 'None';
            }

            $this->hasilModel->simpanHasil($pesertaEventId, [
                'prestasi'        => $prestasi,
                'status_prestasi' => $status,
                'peringkat'       => $peringkat,
                'medali'          => $medali,
                'keterangan'      => $ket ?: null,
            ]);
            $saved++;
        }

        // Update status event menjadi Selesai dan otomatis hitung ranking & medali jika belum diset manual
        if ($saved > 0) {
            $hasManualRank = false;
            foreach ($hasilData as $r) {
                if (!empty($r['peringkat'])) {
                    $hasManualRank = true;
                    break;
                }
            }
            if (!$hasManualRank) {
                $this->hasilModel->autoRankingEvent($eventId);
            }
            $this->eventModel->update($eventId, ['status' => 'Selesai']);
        }

        return redirect()->to(base_url('hasil/input/' . $eventId))
            ->with('success', "Hasil berhasil disimpan untuk {$saved} peserta. Ranking & medali juara otomatis diperbarui.");
    }

    public function autoRank(int $eventId)
    {
        $this->hasilModel->autoRankingEvent($eventId);
        $this->eventModel->update($eventId, ['status' => 'Selesai']);

        return redirect()->to(base_url('hasil/input/' . $eventId))
            ->with('success', 'Auto-ranking dan penentuan medali (Juara 1, 2, 3) berhasil dihitung berdasarkan catatan prestasi terbaik (terjauh).');
    }

    /**
     * Loloskan atlet terbaik dari Babak Penyisihan ke Babak Final secara otomatis
     */
    public function qualifyToFinal(int $eventId)
    {
        $event = $this->eventModel->getEventWithDetails($eventId);
        if (!$event) {
            return redirect()->to(base_url('hasil'))->with('error', 'Acara tidak ditemukan.');
        }

        // Auto-rank terlebih dahulu agar nilai_angka dan peringkat valid
        $this->hasilModel->autoRankingEvent($eventId);

        $quota = (int)($this->request->getPost('quota') ?? $this->request->getGet('quota') ?? 8);
        if ($quota < 2) $quota = 8;

        $satuan = $event['satuan_hasil'] ?? 'Waktu';
        $db = \Config\Database::connect();

        // Ambil semua atlet dengan catatan waktu/prestasi valid pada babak penyisihan
        $builder = $db->table('hasil h')
            ->select('h.*, pe.atlet_id, pe.heat, a.nama as nama_atlet, a.no_dada, s.nama as nama_sekolah')
            ->join('peserta_event pe', 'pe.id = h.peserta_event_id')
            ->join('atlet a', 'a.id = pe.atlet_id')
            ->join('sekolah s', 's.id = a.sekolah_id', 'left')
            ->where('pe.event_id', $eventId)
            ->where('h.status_prestasi', 'Valid')
            ->where('h.nilai_angka IS NOT NULL');

        if ($satuan === 'Waktu') {
            $builder->orderBy('h.nilai_angka', 'ASC');
        } else {
            $builder->orderBy('h.nilai_angka', 'DESC');
        }

        $allValid = $builder->get()->getResultArray();

        if (empty($allValid)) {
            return redirect()->back()->with('error', 'Belum ada catatan waktu/prestasi valid pada babak penyisihan ini. Silakan input hasil peserta terlebih dahulu.');
        }

        // Ambil atlet dengan catatan prestasi/waktu terbaik secara murni (tercepat dahulu)
        // dari seluruh macam hit/penyisihan. Siapapun yang lebih cepat (contoh: pelari hit 5
        // lebih cepat dari hit 1), dia langsung mendapatkan urutan unggulan pertama (Lintasan 4 dst).
        $qualifiers = array_slice($allValid, 0, min($quota, count($allValid)));

        // Cek apakah sudah ada event babak Final untuk nomor lomba, KU, dan gender yang sama
        $finalEvent = $this->eventModel->where([
            'kejuaraan_id'     => $event['kejuaraan_id'],
            'nomor_lomba_id'   => $event['nomor_lomba_id'],
            'kelompok_umur_id' => $event['kelompok_umur_id'],
            'gender'           => $event['gender'],
            'babak'            => 'Final',
        ])->first();

        if (!$finalEvent) {
            // Cari nomor acara tertinggi
            $maxRow = $db->table('event')
                ->where('kejuaraan_id', $event['kejuaraan_id'])
                ->selectMax('nomor_acara')
                ->get()->getRowArray();
            $newNomorAcara = ((int)($maxRow['nomor_acara'] ?? 0)) + 1;

            $namaFinal = trim(preg_replace('/(\s*-\s*penyisihan|\s*\(penyisihan\))/i', '', $event['nama_acara'])) . ' - FINAL';

            $finalEventId = $this->eventModel->insert([
                'kejuaraan_id'     => $event['kejuaraan_id'],
                'nomor_lomba_id'   => $event['nomor_lomba_id'],
                'kelompok_umur_id' => $event['kelompok_umur_id'],
                'gender'           => $event['gender'],
                'nomor_acara'      => $newNomorAcara,
                'nama_acara'       => $namaFinal,
                'babak'            => 'Final',
                'jadwal_hari'      => $event['jadwal_hari'] ?: 'Sabtu',
                'jadwal_waktu'     => 'Sore',
                'status'           => 'Belum Mulai',
                'catatan'          => "Babak Final (8 Lintasan) hasil kualifikasi dari Acara #{$event['nomor_acara']}",
            ]);
        } else {
            $finalEventId = $finalEvent['id'];
            // Kosongkan peserta final lama untuk regenerasi
            $this->pesertaModel->where('event_id', $finalEventId)->delete();
        }

        // 4. Seeding lintasan atletik resmi untuk 8 pelari babak final:
        // Urutan lintasan terbaik dari juara hit: 4, 3, 5, 2, 6, 7, 1, 8
        $laneOrder = [4, 3, 5, 2, 6, 7, 1, 8];

        foreach ($qualifiers as $idx => $q) {
            $assignedLane = $laneOrder[$idx] ?? ($idx + 1);
            $this->pesertaModel->insert([
                'event_id' => $finalEventId,
                'atlet_id' => $q['atlet_id'],
                'heat'     => 1,
                'lane'     => (string)$assignedLane,
                'no_urut'  => $idx + 1,
            ]);
        }

        // Tandai event penyisihan sebagai Selesai
        $this->eventModel->update($eventId, ['status' => 'Selesai']);

        // Invalidate cache excel
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

        return redirect()->to(base_url('hasil/input/' . $finalEventId))
            ->with('success', "Sukses! " . count($qualifiers) . " atlet terbaik dari Babak Penyisihan berhasil diloloskan ke Babak Final (Acara #{$finalEventId}). Lintasan telah diatur otomatis.");
    }

    /**
     * Reset Babak Final: Kosongkan seluruh peserta dan hasil di Babak Final.
     * Dapat dipanggil dari Acara Final langsung, atau dari Acara Penyisihan terkait.
     * Jaga-jaga jika panitia salah klik loloskan atau ingin merevisi input nilai penyisihan.
     */
    public function resetFinal(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->to(base_url('hasil'))->with('error', 'Acara tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $targetFinalEvent = null;
        $penyisihanEvent  = null;

        if (strcasecmp($event['babak'], 'Final') === 0) {
            $targetFinalEvent = $event;
            // Cari babak penyisihan terkait jika ada
            $penyisihanEvent = $this->eventModel->where([
                'kejuaraan_id'     => $event['kejuaraan_id'],
                'nomor_lomba_id'   => $event['nomor_lomba_id'],
                'kelompok_umur_id' => $event['kelompok_umur_id'],
                'gender'           => $event['gender'],
                'babak'            => 'Penyisihan',
            ])->first();
        } else {
            // Dipanggil dari babak Penyisihan
            $penyisihanEvent = $event;
            $targetFinalEvent = $this->eventModel->where([
                'kejuaraan_id'     => $event['kejuaraan_id'],
                'nomor_lomba_id'   => $event['nomor_lomba_id'],
                'kelompok_umur_id' => $event['kelompok_umur_id'],
                'gender'           => $event['gender'],
                'babak'            => 'Final',
            ])->first();
        }

        if (!$targetFinalEvent) {
            return redirect()->back()->with('error', 'Acara Babak Final tidak ditemukan untuk nomor lomba ini.');
        }

        $finalId = $targetFinalEvent['id'];

        // 1. Hapus catatan hasil dari peserta di babak final
        $pesertaIds = $db->table('peserta_event')
            ->select('id')
            ->where('event_id', $finalId)
            ->get()->getResultArray();

        if (!empty($pesertaIds)) {
            $ids = array_column($pesertaIds, 'id');
            $db->table('hasil')->whereIn('peserta_event_id', $ids)->delete();
        }

        // 2. Kosongkan peserta di babak final
        $this->pesertaModel->where('event_id', $finalId)->delete();

        // 3. Reset status acara final kembali ke Belum Dimulai
        $this->eventModel->update($finalId, ['status' => 'Belum Dimulai']);

        // 4. Jika ada babak penyisihan dan statusnya 'Selesai', kembalikan ke 'Berlangsung' agar bisa diedit
        if ($penyisihanEvent && strcasecmp($penyisihanEvent['status'], 'Selesai') === 0) {
            $this->eventModel->update($penyisihanEvent['id'], ['status' => 'Berlangsung']);
        }

        // 5. Invalidate cache Excel
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
        @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

        $msg = "Sukses! Babak Final Acara #{$targetFinalEvent['nomor_acara']} ({$targetFinalEvent['nama_acara']}) berhasil di-reset menjadi 0 atlet.";
        if ($penyisihanEvent && $eventId == $penyisihanEvent['id']) {
            $msg .= " Anda kini dapat memperbaiki input hasil penyisihan lalu meloloskan ulang atlet terbaik ke Final.";
        }

        return redirect()->to(base_url('hasil/input/' . $eventId))->with('success', $msg);
    }

    /**
     * Reset seluruh catatan hasil peserta pada acara tertentu
     */
    public function reset(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->to(base_url('hasil'))->with('error', 'Acara tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $pesertaIds = $db->table('peserta_event')
            ->select('id')
            ->where('event_id', $eventId)
            ->get()->getResultArray();

        if (!empty($pesertaIds)) {
            $ids = array_column($pesertaIds, 'id');
            $db->table('hasil')->whereIn('peserta_event_id', $ids)->delete();
        }

        $this->eventModel->update($eventId, ['status' => 'Belum Dimulai']);

        return redirect()->back()->with('success', "Seluruh catatan hasil untuk Acara #{$event['nomor_acara']} ({$event['nama_acara']}) berhasil di-reset.");
    }

    /**
     * Cetak Blangko / Form Kertas Penjurian & Hasil Acara (Format Meet Manager Atletik)
     * Mendukung pembagian Hit (Heats), format nomor lari & lapangan, serta rekapitulasi ranking
     */
    public function formLapangan(int $eventId)
    {
        $event = $this->eventModel->getEventWithDetails($eventId);
        if (!$event) {
            $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;
            $evByNo = $this->eventModel->where('kejuaraan_id', $kejuaraanId)->where('nomor_acara', $eventId)->first();
            if ($evByNo) {
                return redirect()->to(base_url('hasil/form-lapangan/' . $evByNo['id']));
            }
            return redirect()->to(base_url('hasil'))->with('error', 'Acara perlombaan tidak ditemukan.');
        }

        $isBlank = (bool)($this->request->getGet('blank') ?? false);
        $pesertaList = $this->pesertaModel->getPesertaByEvent((int)$event['id']);
        $isFieldEvent = $this->isFieldEvent($event);

        // Kelompokkan peserta berdasarkan Heat
        $heats = [];
        if (!$isBlank && !empty($pesertaList)) {
            foreach ($pesertaList as $p) {
                $h = (int)($p['heat'] ?: 1);
                $heats[$h][] = $p;
            }
            ksort($heats);
        } else {
            $heats = [1 => []];
        }

        $this->data['title']        = "Hasil & Form Acara {$event['nomor_acara']}: {$event['nama_acara']} (Meet Manager)";
        $this->data['event']        = $event;
        $this->data['pesertaList']  = $pesertaList;
        $this->data['heats']        = $heats;
        $this->data['isBlank']      = $isBlank;
        $this->data['isFieldEvent'] = $isFieldEvent;

        return view('hasil/form_lapangan', $this->data);
    }

    /**
     * Cetak Lembar Hasil Perlombaan Resmi (Format Meet Manager Atletik - Gambar 3)
     */
    public function cetakHasil(int $eventId)
    {
        $event = $this->eventModel->getEventWithDetails($eventId);
        if (!$event) {
            $kejuaraanId = $this->activeKejuaraan['id'] ?? 1;
            $evByNo = $this->eventModel->where('kejuaraan_id', $kejuaraanId)->where('nomor_acara', $eventId)->first();
            if ($evByNo) {
                return redirect()->to(base_url('hasil/cetak-hasil/' . $evByNo['id']));
            }
            return redirect()->to(base_url('hasil'))->with('error', 'Acara perlombaan tidak ditemukan.');
        }

        $pesertaList = $this->pesertaModel->getPesertaByEvent($eventId);
        $isFieldEvent = $this->isFieldEvent($event);

        // Kelompokkan peserta berdasarkan Heat
        $heats = [];
        foreach ($pesertaList as $p) {
            $h = (int)($p['heat'] ?: 1);
            $heats[$h][] = $p;
        }
        ksort($heats);

        // Urutkan peserta di masing-masing Heat berdasarkan performa terbaik
        foreach ($heats as $hNum => &$hList) {
            usort($hList, function ($a, $b) use ($isFieldEvent) {
                $stA = in_array(strtoupper($a['status_prestasi'] ?? ''), ['DNS', 'DNF', 'DQ', 'NM']);
                $stB = in_array(strtoupper($b['status_prestasi'] ?? ''), ['DNS', 'DNF', 'DQ', 'NM']);
                if ($stA !== $stB) return $stA ? 1 : -1;

                $valA = $a['nilai_angka'] !== null ? (float)$a['nilai_angka'] : null;
                $valB = $b['nilai_angka'] !== null ? (float)$b['nilai_angka'] : null;
                if ($valA !== null && $valB !== null) {
                    return $isFieldEvent ? ($valB <=> $valA) : ($valA <=> $valB);
                }
                return 0;
            });
        }
        unset($hList);

        // Buat daftar atlet yang valid dan unranked (DNS/DNF/DQ/NM)
        $validList = [];
        $unrankedList = [];

        foreach ($pesertaList as $p) {
            $status = strtoupper($p['status_prestasi'] ?? 'VALID');
            $pres   = strtoupper(trim($p['prestasi'] ?? ''));

            if (in_array($status, ['DNS', 'DNF', 'DQ', 'NM']) || in_array($pres, ['DNS', 'DNF', 'DQ', 'NM'])) {
                $unrankedList[] = $p;
            } else {
                $validList[] = $p;
            }
        }

        // Urutkan validList: prioritas utama peringkat (1, 2, 3...), jika belum ada gunakan nilai_angka
        usort($validList, function ($a, $b) use ($isFieldEvent) {
            $rankA = !empty($a['peringkat']) ? (int)$a['peringkat'] : 999999;
            $rankB = !empty($b['peringkat']) ? (int)$b['peringkat'] : 999999;

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            $valA = $a['nilai_angka'] !== null ? (float)$a['nilai_angka'] : null;
            $valB = $b['nilai_angka'] !== null ? (float)$b['nilai_angka'] : null;

            if ($valA !== null && $valB !== null) {
                return $isFieldEvent ? ($valB <=> $valA) : ($valA <=> $valB);
            }

            return 0;
        });

        $this->data['title']        = "Hasil Resmi Acara {$event['nomor_acara']}: {$event['nama_acara']} (Meet Manager)";
        $this->data['event']        = $event;
        $this->data['pesertaList']  = $pesertaList;
        $this->data['validList']    = $validList;
        $this->data['unrankedList'] = $unrankedList;
        $this->data['heats']        = $heats;
        $this->data['isFieldEvent'] = $isFieldEvent;

        return view('hasil/cetak_hasil', $this->data);
    }

    /**
     * Helper cek apakah acara merupakan nomor lapangan (Tolak Peluru, Lompat Jauh, Lempar Lembing, dll)
     */
    protected function isFieldEvent(array $event): bool
    {
        $kat    = strtolower($event['kategori_lomba'] ?? '');
        $nama   = strtolower(($event['nama_lomba'] ?? '') . ' ' . ($event['nama_acara'] ?? ''));
        $satuan = strtolower($event['satuan_hasil'] ?? '');

        // Nomor Lari / Estafet / Sprint PASTI nomor lintasan (bukan nomor lapangan, tidak ada 3 percobaan)
        if ($kat === 'lari' || $kat === 'estafet' || strpos($nama, 'lari') !== false || strpos($nama, 'estafet') !== false || strpos($nama, 'sprint') !== false) {
            return false;
        }

        if ($kat === 'lompat' || $kat === 'lempar') {
            return true;
        }
        if (strpos($nama, 'tolak') !== false || strpos($nama, 'peluru') !== false || 
            strpos($nama, 'lempar') !== false || strpos($nama, 'lembing') !== false || 
            strpos($nama, 'cakram') !== false || strpos($nama, 'lompat') !== false) {
            return true;
        }
        if ($satuan === 'jarak' || $satuan === 'meter' || $satuan === 'm' || $satuan === 'tinggi') {
            return true;
        }
        return false;
    }

    /**
     * Beralih status acara antara Selesai dan Berlangsung
     */
    public function toggleStatus(int $eventId)
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->back()->with('error', 'Acara tidak ditemukan.');
        }

        $newStatus = ($event['status'] === 'Selesai') ? 'Berlangsung' : 'Selesai';
        $this->eventModel->update($eventId, ['status' => $newStatus]);

        return redirect()->to(base_url('hasil/input/' . $eventId))
            ->with('success', "Status Acara #{$event['nomor_acara']} ({$event['nama_acara']}) berhasil diubah menjadi: {$newStatus}");
    }
}
