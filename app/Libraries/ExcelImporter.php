<?php

namespace App\Libraries;

use App\Models\AtletModel;
use App\Models\EventModel;
use App\Models\KejuaraanModel;
use App\Models\KelompokUmurModel;
use App\Models\NomorLombaModel;
use App\Models\PesertaEventModel;
use App\Models\SekolahModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ExcelImporter
{
    protected AtletModel $atletModel;
    protected SekolahModel $sekolahModel;
    protected KejuaraanModel $kejuaraanModel;
    protected KelompokUmurModel $kuModel;
    protected NomorLombaModel $lombaModel;
    protected EventModel $eventModel;
    protected PesertaEventModel $pesertaModel;

    public function __construct()
    {
        $this->atletModel     = new AtletModel();
        $this->sekolahModel   = new SekolahModel();
        $this->kejuaraanModel = new KejuaraanModel();
        $this->kuModel        = new KelompokUmurModel();
        $this->lombaModel     = new NomorLombaModel();
        $this->eventModel     = new EventModel();
        $this->pesertaModel   = new PesertaEventModel();
    }

    /**
     * Import atlet dan pendaftaran lomba dari file Excel mentahan
     * Format kolom:
     * Col B: NO
     * Col C: NAMA
     * Col D: ASAL SEKOLAH
     * Col E: TGL LAHIR
     * Col F: NOMOR LOMBA
     * Col H: L/P
     */
    public function importMentahan(string $filePath, int $kejuaraanId): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('ACARA') ?? $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $stats = [
            'total_rows'       => 0,
            'atlet_baru'       => 0,
            'atlet_terdaftar'  => 0,
            'sekolah_baru'     => 0,
            'pendaftaran_event'=> 0,
            'errors'           => [],
        ];

        // Cache nomor lomba
        $allLomba = $this->lombaModel->findAll();
        $lombaMap = [];
        foreach ($allLomba as $l) {
            $key = strtolower(trim(str_replace(' ', '', $l['nama'])));
            $lombaMap[$key] = $l;
        }

        // Cache kelompok umur
        $allKU = $this->kuModel->findAll();

        for ($row = 2; $row <= $highestRow; $row++) {
            $nama = trim((string)$sheet->getCell('C' . $row)->getValue());
            if (empty($nama)) {
                continue; // baris kosong
            }

            $stats['total_rows']++;
            $asalSekolah = trim((string)$sheet->getCell('D' . $row)->getValue());
            $tglLahirRaw = $sheet->getCell('E' . $row)->getValue();
            $nomorLomba  = trim((string)$sheet->getCell('F' . $row)->getValue());
            $genderRaw   = strtoupper(trim((string)$sheet->getCell('H' . $row)->getValue()));
            $gender      = ($genderRaw === 'P' || str_contains($genderRaw, 'PUTRI')) ? 'P' : 'L';

            // Format tanggal lahir
            $tanggalLahir = null;
            if (!empty($tglLahirRaw)) {
                if (is_numeric($tglLahirRaw)) {
                    try {
                        $tanggalLahir = ExcelDate::excelToDateTimeObject($tglLahirRaw)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $tanggalLahir = null;
                    }
                } elseif (strtotime($tglLahirRaw)) {
                    $tanggalLahir = date('Y-m-d', strtotime($tglLahirRaw));
                }
            }

            // Dapatkan atau buat sekolah
            $sekolahId = 1;
            if (!empty($asalSekolah)) {
                $sekolahId = $this->sekolahModel->getOrCreate($asalSekolah);
            }

            // Hitung KU dinamis otomatis dari tanggal lahir
            $kuId = null;
            if (!empty($tanggalLahir)) {
                $kuId = $this->kuModel->hitungKUOtomatis($tanggalLahir);
            }

            // Cari atau buat atlet di kejuaraan ini
            // Satu atlet bisa ikut multi event, dicari berdasarkan nama + asal sekolah + kejuaraan
            $existingAtlet = $this->atletModel->where('kejuaraan_id', $kejuaraanId)
                ->where('nama', $nama)
                ->where('sekolah_id', $sekolahId)
                ->first();

            if ($existingAtlet) {
                $atletId = (int)$existingAtlet['id'];
                $stats['atlet_terdaftar']++;
            } else {
                $atletId = (int)$this->atletModel->insert([
                    'kejuaraan_id'     => $kejuaraanId,
                    'sekolah_id'       => $sekolahId,
                    'nama'             => $nama,
                    'gender'           => $gender,
                    'tanggal_lahir'    => $tanggalLahir,
                    'kelompok_umur_id' => $kuId,
                ]);
                $stats['atlet_baru']++;
            }

            // Daftarkan ke event perlombaan jika ada
            if (!empty($nomorLomba)) {
                $cleanLombaKey = strtolower(trim(str_replace(' ', '', $nomorLomba)));
                $matchedLomba = $lombaMap[$cleanLombaKey] ?? null;

                if (!$matchedLomba) {
                    // Coba cari fuzzy
                    foreach ($lombaMap as $k => $l) {
                        if (str_contains($cleanLombaKey, $k) || str_contains($k, $cleanLombaKey)) {
                            $matchedLomba = $l;
                            break;
                        }
                    }
                }

                if ($matchedLomba) {
                    // Cari atau buat event yang cocok untuk nomor lomba, KU, dan gender
                    $eventQuery = $this->eventModel->where('kejuaraan_id', $kejuaraanId)
                        ->where('nomor_lomba_id', $matchedLomba['id'])
                        ->where('gender', $gender);

                    if ($kuId) {
                        $eventQuery->where('kelompok_umur_id', $kuId);
                    }

                    $event = $eventQuery->first();

                    if (!$event) {
                        // Tentukan nama acara otomatis
                        $kuName = '';
                        if ($kuId) {
                            foreach ($allKU as $k) {
                                if ($k['id'] == $kuId) {
                                    $kuName = ' ' . $k['nama'];
                                    break;
                                }
                            }
                        }
                        $genderText = $gender === 'L' ? 'Putra' : 'Putri';
                        $namaAcara  = "{$matchedLomba['nama']}{$kuName} {$genderText}";

                        $lastEvent = $this->eventModel->where('kejuaraan_id', $kejuaraanId)
                            ->orderBy('nomor_acara', 'DESC')
                            ->first();
                        $nextNoAcara = $lastEvent ? ((int)$lastEvent['nomor_acara'] + 1) : 1;

                        $eventId = (int)$this->eventModel->insert([
                            'kejuaraan_id'     => $kejuaraanId,
                            'nomor_lomba_id'   => $matchedLomba['id'],
                            'kelompok_umur_id' => $kuId,
                            'gender'           => $gender,
                            'nomor_acara'      => $nextNoAcara,
                            'nama_acara'       => $namaAcara,
                            'babak'            => 'Final',
                            'status'           => 'Belum Dimulai',
                        ]);
                    } else {
                        $eventId = (int)$event['id'];
                    }

                    // Daftarkan atlet ke event jika belum terdaftar
                    $isRegistered = $this->pesertaModel->where('event_id', $eventId)
                        ->where('atlet_id', $atletId)
                        ->first();

                    if (!$isRegistered) {
                        $this->pesertaModel->insert([
                            'event_id' => $eventId,
                            'atlet_id' => $atletId,
                            'heat'     => 1,
                        ]);
                        $stats['pendaftaran_event']++;
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Import mapping nomor dada dari sheet nomor dada (misal Sheet 0: No Dada | Asal Sekolah | Nama Atlet | L/P)
     */
    public function importNomorDada(string $filePath, int $kejuaraanId): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Sheet 0') ?? $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $updated = 0;
        for ($row = 2; $row <= $highestRow; $row++) {
            $noDada = trim((string)$sheet->getCell('A' . $row)->getValue());
            $sekolah = trim((string)$sheet->getCell('B' . $row)->getValue());
            $nama = trim((string)$sheet->getCell('C' . $row)->getValue());

            if (empty($nama) || empty($noDada)) {
                continue;
            }

            // Bersihkan tanda petik
            $noDada = ltrim($noDada, "'");

            $atlet = $this->atletModel->where('kejuaraan_id', $kejuaraanId)
                ->where('nama', $nama)
                ->first();

            if ($atlet) {
                $this->atletModel->update($atlet['id'], ['no_dada' => $noDada]);
                $updated++;
            }
        }

        return ['total_updated' => $updated];
    }
}
