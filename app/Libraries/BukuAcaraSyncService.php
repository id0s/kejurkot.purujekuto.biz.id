<?php

namespace App\Libraries;

use App\Models\AtletModel;
use App\Models\EventModel;
use App\Models\KejuaraanModel;
use App\Models\PesertaEventModel;
use App\Models\SekolahModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BukuAcaraSyncService
{
    protected EventModel $eventModel;
    protected PesertaEventModel $pesertaModel;
    protected AtletModel $atletModel;
    protected SekolahModel $sekolahModel;
    protected KejuaraanModel $kejuaraanModel;
    protected $db;

    public function __construct()
    {
        $this->eventModel     = new EventModel();
        $this->pesertaModel   = new PesertaEventModel();
        $this->atletModel     = new AtletModel();
        $this->sekolahModel   = new SekolahModel();
        $this->kejuaraanModel = new KejuaraanModel();
        $this->db             = \Config\Database::connect();
    }

    /**
     * Membersihkan nilai cell Excel (menangani float .0, leading quote, spasi)
     */
    protected function cleanCellVal($val): string
    {
        if ($val === null) {
            return '';
        }
        if (is_int($val)) {
            return (string)$val;
        }
        if (is_float($val) && $val == (int)$val) {
            return (string)((int)$val);
        }
        $s = trim((string)$val);
        if (str_starts_with($s, "'")) {
            $s = substr($s, 1);
        }
        if (preg_match('/^(\d+)\.0+$/', $s, $m)) {
            $s = $m[1];
        }
        return trim($s);
    }

    /**
     * Resolusi path file buku acara resmi default
     */
    public function getDefaultFilePath(): string
    {
        // Prioritaskan file revisi terbaru
        $candidates = [
            ROOTPATH . '0FIX BUKU ACARA ATLETIK KARES 2026-1.xlsx',
            'z:/htdocs/kejurkot.diaplikasi.com/0FIX BUKU ACARA ATLETIK KARES 2026-1.xlsx',
            FCPATH . '0FIX BUKU ACARA ATLETIK KARES 2026-1.xlsx',
            ROOTPATH . '0FIX BUKU ACARA ATLETIK KARES 2026.xlsx',
            'z:/htdocs/kejurkot.diaplikasi.com/0FIX BUKU ACARA ATLETIK KARES 2026.xlsx',
            FCPATH . '0FIX BUKU ACARA ATLETIK KARES 2026.xlsx',
            dirname(ROOTPATH) . DIRECTORY_SEPARATOR . '0FIX BUKU ACARA ATLETIK KARES 2026-1.xlsx',
            dirname(ROOTPATH) . DIRECTORY_SEPARATOR . '0FIX BUKU ACARA ATLETIK KARES 2026.xlsx',
        ];

        // Cari file terbaru berdasarkan timestamp jika ada beberapa file buku acara
        $searchDirs = [ROOTPATH, 'z:/htdocs/kejurkot.diaplikasi.com/', FCPATH];
        $latestFile = '';
        $latestMtime = 0;
        foreach ($searchDirs as $dir) {
            if (!is_dir($dir)) continue;
            $files = glob($dir . '*BUKU ACARA*.xlsx');
            if (!$files) continue;
            foreach ($files as $f) {
                if (stripos($f, '.bak') !== false) continue;
                $mtime = filemtime($f);
                if ($mtime > $latestMtime) {
                    $latestMtime = $mtime;
                    $latestFile = $f;
                }
            }
        }

        if (!empty($latestFile) && file_exists($latestFile)) {
            return $latestFile;
        }

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return ROOTPATH . '0FIX BUKU ACARA ATLETIK KARES 2026-1.xlsx';
    }

    /**
     * Parse and sync the entire official workbook to the database
     */
    public function sync(string $filePath, int $kejuaraanId = 1): array
    {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'message' => "File tidak ditemukan di path: {$filePath}",
            ];
        }

        // Muat spreadsheet dengan PhpSpreadsheet (data only untuk performa cepat)
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $wsAcara = $spreadsheet->getSheetByName('ACARA') ?? $spreadsheet->getActiveSheet();
        $wsSheet0 = $spreadsheet->getSheetByName('Sheet 0');

        $stats = [
            'total_events_synced'   => 0,
            'total_athletes_indexed'=> 0,
            'total_peserta_synced'  => 0,
            'relay_teams_synced'    => 0,
            'final_events_blank'    => 0,
            'errors'                => [],
        ];

        // Mulai Transaksi Database
        $this->db->transBegin();

        try {
            // ==========================================
            // PASS 1: Master Dictionary dari Sheet 0
            // ==========================================
            $sheet0Atlets = [];
            if ($wsSheet0) {
                $maxRowS0 = $wsSheet0->getHighestRow();
                for ($r = 2; $r <= $maxRowS0; $r++) {
                    $bib     = $this->cleanCellVal($wsSheet0->getCell('A' . $r)->getValue());
                    $sekolah = trim((string)$wsSheet0->getCell('B' . $r)->getValue());
                    $nama    = trim((string)$wsSheet0->getCell('C' . $r)->getValue());
                    $gender  = strtoupper(trim((string)$wsSheet0->getCell('D' . $r)->getValue()));
                    $yr      = $this->cleanCellVal($wsSheet0->getCell('E' . $r)->getValue());

                    if ($gender !== 'P') $gender = 'L';

                    if (!empty($bib)) {
                        $sheet0Atlets[$bib] = [
                            'sekolah' => $sekolah,
                            'nama'    => $nama,
                            'gender'  => $gender,
                            'yr'      => $yr,
                        ];
                    }
                }
            }
            $stats['total_athletes_indexed'] = count($sheet0Atlets);

            // Cache Sekolah
            $sekolahRows = $this->sekolahModel->findAll();
            $sekolahMap = [];
            foreach ($sekolahRows as $s) {
                $sekolahMap[strtolower(trim($s['nama']))] = (int)$s['id'];
            }

            $getOrCreateSekolah = function (?string $namaSek) use (&$sekolahMap) {
                if (empty($namaSek)) return null;
                $clean = trim($namaSek);
                $key = strtolower($clean);
                if (isset($sekolahMap[$key])) {
                    return $sekolahMap[$key];
                }
                $newId = (int)$this->sekolahModel->insert([
                    'nama' => $clean,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $sekolahMap[$key] = $newId;
                return $newId;
            };

            // Cache Atlet di Kejuaraan ini
            $atletRows = $this->atletModel->where('kejuaraan_id', $kejuaraanId)->findAll();
            $atletByBib = [];
            $atletByName = [];
            foreach ($atletRows as $a) {
                $cleanBib = $this->cleanCellVal($a['no_dada'] ?? '');
                if (!empty($cleanBib)) {
                    $atletByBib[$cleanBib] = $a;
                }
                $cleanName = strtolower(trim($a['nama']));
                $atletByName[$cleanName] = $a;
            }

            $getOrCreateAtlet = function ($bib, $nama, $sekolahName, $defaultGender = 'L') use (
                &$atletByBib, &$atletByName, &$sheet0Atlets, $kejuaraanId, $getOrCreateSekolah
            ) {
                $cleanBib  = $this->cleanCellVal($bib);
                $cleanName = trim((string)$nama);

                if (!empty($cleanBib) && isset($atletByBib[$cleanBib])) {
                    $existing = $atletByBib[$cleanBib];
                    if (!empty($cleanName) && strcasecmp($existing['nama'], $cleanName) !== 0) {
                        $this->atletModel->update((int)$existing['id'], ['nama' => $cleanName]);
                        $atletByBib[$cleanBib]['nama'] = $cleanName;
                    }
                    if (!empty($sekolahName) && empty($existing['sekolah_id'])) {
                        $sekId = $getOrCreateSekolah($sekolahName);
                        if (!empty($sekId)) {
                            $this->atletModel->update((int)$existing['id'], ['sekolah_id' => $sekId]);
                            $atletByBib[$cleanBib]['sekolah_id'] = $sekId;
                        }
                    }
                    return (int)$existing['id'];
                }
                if (!empty($cleanName) && isset($atletByName[strtolower($cleanName)])) {
                    $existing = $atletByName[strtolower($cleanName)];
                    if (!empty($cleanBib) && empty($existing['no_dada'])) {
                        $this->atletModel->update((int)$existing['id'], ['no_dada' => $cleanBib]);
                        $atletByName[strtolower($cleanName)]['no_dada'] = $cleanBib;
                        $atletByBib[$cleanBib] = $atletByName[strtolower($cleanName)];
                    }
                    return (int)$existing['id'];
                }

                $s0 = $sheet0Atlets[$cleanBib] ?? [];
                $gender = $s0['gender'] ?? $defaultGender;
                if ($gender !== 'P') $gender = 'L';

                $sekName = !empty($sekolahName) ? $sekolahName : ($s0['sekolah'] ?? '');
                $sekId   = $getOrCreateSekolah($sekName);

                $newId = (int)$this->atletModel->insert([
                    'kejuaraan_id' => $kejuaraanId,
                    'sekolah_id'   => $sekId,
                    'no_dada'      => $cleanBib,
                    'nama'         => $cleanName,
                    'gender'       => $gender,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);

                $record = [
                    'id'         => $newId,
                    'no_dada'    => $cleanBib,
                    'nama'       => $cleanName,
                    'gender'     => $gender,
                    'sekolah_id' => $sekId,
                ];

                if (!empty($cleanBib)) $atletByBib[$cleanBib] = $record;
                if (!empty($cleanName)) $atletByName[strtolower($cleanName)] = $record;

                return $newId;
            };

            // ==========================================
            // PASS 2: Update Jadwal / Timetable Acara
            // ==========================================
            $eventRows = $this->eventModel->where('kejuaraan_id', $kejuaraanId)->findAll();
            $eventMapByNo = [];
            foreach ($eventRows as $ev) {
                $eventMapByNo[(int)$ev['nomor_acara']] = (int)$ev['id'];
            }

            $hariResmi = 'Minggu, 27 September 2026';
            for ($evNum = 1; $evNum <= 52; $evNum++) {
                if (!isset($eventMapByNo[$evNum])) continue;
                $evId = $eventMapByNo[$evNum];

                // Waktu standar buku acara
                if ($evNum === 1)       $waktu = 'Pagi 05.30';
                elseif ($evNum === 2)  $waktu = 'Pagi 05.45';
                elseif ($evNum === 3)  $waktu = 'Pagi 06.30';
                elseif ($evNum === 4)  $waktu = 'Pagi 06.45';
                elseif ($evNum <= 26)  $waktu = 'Pagi 05.30';
                else                   $waktu = 'Sore 13.30';

                $updateData = [
                    'jadwal_hari'  => $hariResmi,
                    'jadwal_waktu' => $waktu,
                ];

                if ($evNum === 51) {
                    $updateData['nama_acara'] = 'Estafet MIX 5 X 80 U 10 - 12';
                    $updateData['gender']     = 'Campuran';
                    $updateData['babak']      = 'Final';
                } elseif ($evNum === 52) {
                    $updateData['nama_acara'] = 'Estafet MIX 4 X 100 U 16 - 18';
                    $updateData['gender']     = 'Campuran';
                    $updateData['babak']      = 'Final';
                } else {
                    $existingEv = $this->eventModel->find($evId);
                    $curNama = $existingEv['nama_acara'] ?? '';
                    $curGender = $existingEv['gender'] ?? 'L';
                    $genderSuffix = ($curGender === 'L') ? 'Male' : (($curGender === 'P') ? 'Female' : 'MIX');
                    if (!preg_match('/\b(male|female|famale|putra|putri)\b/i', $curNama) && stripos($curNama, 'mix') === false) {
                        $updateData['nama_acara'] = trim($curNama) . ' ' . $genderSuffix;
                    }
                }

                $this->eventModel->update($evId, $updateData);
                $stats['total_events_synced']++;
            }

            // ==========================================
            // PASS 3: Parsing Blok Start List Per Acara
            // ==========================================
            $highestRow = $wsAcara->getHighestRow();
            $eventSections = [];

            for ($r = 110; $r <= $highestRow; $r++) {
                $c1 = trim((string)$wsAcara->getCell('A' . $r)->getValue());
                $c2 = trim((string)$wsAcara->getCell('B' . $r)->getValue());
                $c3 = trim((string)$wsAcara->getCell('C' . $r)->getValue());
                $line = "{$c1} {$c2} {$c3}";

                if (stripos($line, 'event') !== false && strpos($line, ':') !== false) {
                    if (preg_match('/event\s*:\s*(\d+)/i', $line, $m)) {
                        $eventSections[] = [
                            'event_num' => (int)$m[1],
                            'start_row' => $r,
                            'title'     => $line,
                        ];
                    }
                }
            }

            $countSections = count($eventSections);
            for ($i = 0; $i < $countSections; $i++) {
                $evNum  = $eventSections[$i]['event_num'];
                $startR = $eventSections[$i]['start_row'];
                $endR   = ($i + 1 < $countSections) ? ($eventSections[$i + 1]['start_row'] - 1) : $highestRow;

                if (!isset($eventMapByNo[$evNum])) continue;
                $evId = $eventMapByNo[$evNum];

                // ------------------------------------------
                // A. Penanganan Acara Estafet (51 & 52)
                // ------------------------------------------
                if ($evNum === 51 || $evNum === 52) {
                    $currentHeat = 1;
                    $teams = [];
                    $currentTeam = null;

                    for ($r = $startR; $r <= $endR; $r++) {
                        $c1 = $this->cleanCellVal($wsAcara->getCell('A' . $r)->getValue());
                        $c2 = $this->cleanCellVal($wsAcara->getCell('B' . $r)->getValue());
                        $c3 = trim((string)$wsAcara->getCell('C' . $r)->getValue());
                        $c4 = $this->cleanCellVal($wsAcara->getCell('D' . $r)->getValue());
                        $c5 = trim((string)$wsAcara->getCell('E' . $r)->getValue());

                        if (strcasecmp($c1, 'HIT') === 0) {
                            $currentHeat = is_numeric($c2) ? (int)$c2 : $currentHeat;
                            continue;
                        }
                        if (in_array(strtoupper($c1), ['LINT', 'NO']) || stripos($c1, 'event') !== false) {
                            continue;
                        }
                        if (empty($c1) && empty($c2) && empty($c3) && empty($c4) && empty($c5)) {
                            continue;
                        }

                        if (is_numeric($c1)) {
                            $lane = (int)$c1;
                            $teamName = !empty($c5) ? $c5 : $c3;
                            $bib = $c2;
                            $currentTeam = [
                                'heat'      => $currentHeat,
                                'lane'      => (string)$lane,
                                'team_name' => $teamName,
                                'bib'       => $bib,
                                'runners'   => [[$bib, $c3, $c4]],
                            ];
                            $teams[] = &$currentTeam;
                            unset($currentTeam);
                        } elseif (!empty($teams) && (!empty($c2) || !empty($c3))) {
                            $bib = $c2;
                            $lastIdx = count($teams) - 1;
                            $teams[$lastIdx]['runners'][] = [$bib, $c3, $c4];
                        }
                    }

                    // Hapus relasi lama lalu insert 1 team per lintasan
                    $this->pesertaModel->where('event_id', $evId)->delete();

                    foreach ($teams as $idx => $t) {
                        $sekName = str_ireplace('Team ', '', $t['team_name']);
                        $sekId = $getOrCreateSekolah($sekName);
                        $runnersStr = implode(', ', array_map(function ($r) {
                            return "{$r[0]} {$r[1]}";
                        }, $t['runners']));

                        // Ambil atau buat atlet yang mewakili Tim Regu
                        $teamAtlet = $this->atletModel->where('kejuaraan_id', $kejuaraanId)
                            ->where('nama', $t['team_name'])
                            ->first();

                        if ($teamAtlet) {
                            $teamAtletId = (int)$teamAtlet['id'];
                            $this->atletModel->update($teamAtletId, [
                                'no_dada' => $t['bib'],
                                'catatan' => "Pelari: {$runnersStr}",
                            ]);
                        } else {
                            $teamAtletId = (int)$this->atletModel->insert([
                                'kejuaraan_id' => $kejuaraanId,
                                'sekolah_id'   => $sekId,
                                'no_dada'      => $t['bib'],
                                'nama'         => $t['team_name'],
                                'gender'       => 'L',
                                'catatan'      => "Pelari: {$runnersStr}",
                                'created_at'   => date('Y-m-d H:i:s'),
                                'updated_at'   => date('Y-m-d H:i:s'),
                            ]);
                        }

                        $this->pesertaModel->insert([
                            'event_id'   => $evId,
                            'atlet_id'   => $teamAtletId,
                            'heat'       => $t['heat'],
                            'lane'       => $t['lane'],
                            'no_urut'    => $idx + 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                        $stats['total_peserta_synced']++;
                        $stats['relay_teams_synced']++;
                    }

                // ------------------------------------------
                // B. Final Sprint Kosong (Acara 39 s/d 46)
                // ------------------------------------------
                } elseif ($evNum >= 39 && $evNum <= 46) {
                    // Cek apakah ada nama atlet di dalamnya
                    $hasAthletes = false;
                    for ($r = $startR; $r <= $endR; $r++) {
                        $c3 = trim((string)$wsAcara->getCell('C' . $r)->getValue());
                        if (!empty($c3) && !in_array(strtoupper($c3), ['NAMA ATLET', 'NAMA REGU', 'TEAM'])) {
                            $hasAthletes = true;
                            break;
                        }
                    }

                    if (!$hasAthletes) {
                        // Jangan timpa jika sudah ada peserta kualifikasi yang diloloskan di sistem
                        $existingCount = $this->pesertaModel->where('event_id', $evId)->countAllResults();
                        if ($existingCount === 0) {
                            $stats['final_events_blank']++;
                        }
                        continue;
                    }

                // ------------------------------------------
                // C. Nomor Individu (Acara 1 s/d 50)
                // ------------------------------------------
                } else {
                    $currentHeat = 1;
                    $runners = [];
                    $seenAtletIds = [];

                    for ($r = $startR; $r <= $endR; $r++) {
                        $c1 = $this->cleanCellVal($wsAcara->getCell('A' . $r)->getValue());
                        $c2 = $this->cleanCellVal($wsAcara->getCell('B' . $r)->getValue());
                        $c3 = trim((string)$wsAcara->getCell('C' . $r)->getValue());
                        $c4 = $this->cleanCellVal($wsAcara->getCell('D' . $r)->getValue());
                        $c5 = trim((string)$wsAcara->getCell('E' . $r)->getValue());

                        if (strcasecmp($c1, 'HIT') === 0) {
                            $currentHeat = is_numeric($c2) ? (int)$c2 : $currentHeat;
                            continue;
                        }
                        if (in_array(strtoupper($c1), ['LINT', 'NO']) || stripos($c1, 'event') !== false) {
                            continue;
                        }
                        if (empty($c1) && empty($c2) && empty($c3) && empty($c4) && empty($c5)) {
                            continue;
                        }

                        if (!empty($c2) || !empty($c3)) {
                            $lane = is_numeric($c1) ? (string)((int)$c1) : '';
                            $bib = $c2;
                            $nama = $c3;
                            $sekolah = $c5;

                            $atletId = $getOrCreateAtlet($bib, $nama, $sekolah);
                            if (isset($seenAtletIds[$atletId])) {
                                continue;
                            }
                            $seenAtletIds[$atletId] = true;

                            $runners[] = [
                                'atlet_id' => $atletId,
                                'heat'     => $currentHeat,
                                'lane'     => $lane,
                            ];
                        }
                    }

                    // Sinkronkan peserta ke event
                    $this->pesertaModel->where('event_id', $evId)->delete();
                    foreach ($runners as $idx => $rn) {
                        $this->pesertaModel->insert([
                            'event_id'   => $evId,
                            'atlet_id'   => $rn['atlet_id'],
                            'heat'       => $rn['heat'],
                            'lane'       => $rn['lane'],
                            'no_urut'    => $idx + 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $stats['total_peserta_synced']++;
                    }
                }
            }

            // Invalidate cache buku acara excel
            @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
            @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');

            $this->db->transCommit();
            $stats['success'] = true;
            $stats['message'] = "Berhasil menyinkronkan {$stats['total_events_synced']} acara dan {$stats['total_peserta_synced']} peserta dari buku acara resmi.";
            return $stats;

        } catch (\Throwable $e) {
            $this->db->transRollback();
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat parsing/sinkronisasi: ' . $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ];
        }
    }
}
