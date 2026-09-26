<?php

namespace App\Libraries;

use App\Models\EventModel;
use App\Models\KejuaraanModel;
use App\Models\PesertaEventModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelExporter
{
    /**
     * Generate Buku Acara Excel dalam SATU SHEET UTUH persis sesuai model Buku Acara Baru (BUKU ACARA ATLETIK KARES 2026.xlsx):
     * 1. Cover (Halaman Depan resmi PASI)
     * 2. Susunan Acara (JAM PEMANGGILAN | NO ACARA | NOMOR LOMBA | JENIS KELAMIN | BABAK | SESI)
     * 3. Buku Acara (Font Courier New, border dashed di bawah header, LINT/NO | BIB | Nama Atlet | YR | Team | Time/Mark)
     */
    public function generateBukuAcara(int $kejuaraanId, string $sortBy = 'nomor_acara', array $filters = [], string $sortDir = 'ASC'): Spreadsheet
    {
        $kejuaraanModel = new KejuaraanModel();
        $eventModel     = new EventModel();
        $pesertaModel   = new PesertaEventModel();

        $kejuaraan = $kejuaraanModel->find($kejuaraanId);
        $events    = $eventModel->getEventsWithDetails($kejuaraanId, $sortBy, $filters, $sortDir);

        $namaKejuaraan = $kejuaraan['nama'] ?? 'Kejuaraan Atletik Kelompok Umur Eks-Karesidenan Pekalongan';
        $tempat        = $kejuaraan['tempat'] ?? 'Stadion Hoegeng';
        $tanggalMulai  = $kejuaraan['tanggal_mulai'] ?? '27 September 2026';
        $tahun         = !empty($kejuaraan['tanggal_mulai']) ? date('Y', strtotime($kejuaraan['tanggal_mulai'])) : '2026';

        $spreadsheet = new Spreadsheet();

        // ---------------------------------------------------------------------
        // SINGLE SHEET: BUKU ACARA LENGKAP
        // ---------------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ACARA');
        $sheet->setShowGridLines(true);

        // Page setup: A4 Portrait, Fit Width 1 page
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Standard column widths persis seperti BUKU ACARA ATLETIK KARES 2026.xlsx
        $sheet->getColumnDimension('A')->setWidth(8);   // NO / LINT / JAM PEMANGGILAN
        $sheet->getColumnDimension('B')->setWidth(9);   // BIB / NO ACARA
        $sheet->getColumnDimension('C')->setWidth(36);  // Nama Atlet / NOMOR LOMBA
        $sheet->getColumnDimension('D')->setWidth(7);   // YR
        $sheet->getColumnDimension('E')->setWidth(34);  // Team / JENIS KELAMIN
        $sheet->getColumnDimension('F')->setWidth(12);  // Time / Mark / BABAK
        $sheet->getColumnDimension('G')->setWidth(14);  // SESI

        // ---------------------------------------------------------------------
        // 1. BAGIAN COVER (HALAMAN DEPAN)
        // ---------------------------------------------------------------------
        $sheet->getStyle('A1:G28')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');

        $sheet->getRowDimension(1)->setRowHeight(18);
        $sheet->getRowDimension(2)->setRowHeight(15);
        $sheet->getRowDimension(3)->setRowHeight(38); // BUKU ACARA
        $sheet->getRowDimension(4)->setRowHeight(15);
        $sheet->getRowDimension(5)->setRowHeight(36); // Judul Kejuaraan
        $sheet->getRowDimension(6)->setRowHeight(22); // Tempat & Tanggal

        for ($r = 7; $r <= 9; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(16);
        }
        for ($r = 10; $r <= 20; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18); // Area Logo (200px)
        }
        for ($r = 21; $r <= 23; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(16);
        }

        $sheet->getRowDimension(24)->setRowHeight(22);
        $sheet->getRowDimension(25)->setRowHeight(22);
        $sheet->getRowDimension(26)->setRowHeight(22);
        $sheet->getRowDimension(27)->setRowHeight(22);
        $sheet->getRowDimension(28)->setRowHeight(20);

        // Judul Utama: BUKU ACARA
        $sheet->setCellValue('A3', 'BUKU ACARA');
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getFont()->setName('Times New Roman')->setBold(true)->setSize(24)->getColor()->setARGB('FF000000');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Subtitle Nama Kejuaraan
        $sheet->setCellValue('A5', "KEJUARAAN ATLETIK KELOMPOK UMUR\nEKS-KARESIDENAN PEKALONGAN");
        $sheet->mergeCells('A5:G5');
        $sheet->getStyle('A5')->getFont()->setName('Times New Roman')->setBold(true)->setSize(15)->getColor()->setARGB('FF000000');
        $sheet->getStyle('A5')->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Subtitle Tempat & Tanggal
        $tempatDisplay = 'Stadion Hoegeng, Minggu, 27 September ' . $tahun;
        $sheet->setCellValue('A6', $tempatDisplay);
        $sheet->mergeCells('A6:G6');
        $sheet->getStyle('A6')->getFont()->setName('Times New Roman')->setBold(true)->setSize(13.5)->getColor()->setARGB('FF000000');
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Logo PASI di tengah cover
        $logoPath = FCPATH . 'assets/images/pasi-logo.png';
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo PASI');
            $drawing->setDescription('Logo Persatuan Atletik Seluruh Indonesia');
            $drawing->setPath($logoPath);
            $drawing->setHeight(200);
            $drawing->setWidth(200);
            $drawing->setCoordinates('C10');
            $drawing->setOffsetX(160);
            $drawing->setOffsetY(4);
            $drawing->setWorksheet($sheet);
        }

        // Identitas Cover Bagian Bawah
        $sheet->setCellValue('A25', 'EKS - KARESIDENAN PEKALONGAN');
        $sheet->mergeCells('A25:G25');
        $sheet->getStyle('A25')->getFont()->setName('Times New Roman')->setBold(true)->setSize(14)->getColor()->setARGB('FF000000');
        $sheet->getStyle('A25')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('A26', 'PERSATUAN ATLETIK SELURUH INDONESIA');
        $sheet->mergeCells('A26:G26');
        $sheet->getStyle('A26')->getFont()->setName('Times New Roman')->setBold(true)->setSize(13)->getColor()->setARGB('FF000000');
        $sheet->getStyle('A26')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('A27', 'TAHUN ' . $tahun);
        $sheet->mergeCells('A27:G27');
        $sheet->getStyle('A27')->getFont()->setName('Times New Roman')->setBold(true)->setSize(14)->getColor()->setARGB('FF000000');
        $sheet->getStyle('A27')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Page break setelah Cover
        $sheet->setBreak('A28', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        // ---------------------------------------------------------------------
        // 2. BAGIAN SUSUNAN ACARA (JADWAL PAGI & SORE)
        // ---------------------------------------------------------------------
        $susunanStart = 31;
        $sheet->setCellValue('A' . $susunanStart, 'SUSUNAN ACARA KEJURWIL ATLETIK PEKALONGAN');
        $sheet->mergeCells("A{$susunanStart}:G{$susunanStart}");
        $sheet->getStyle("A{$susunanStart}")->getFont()->setName('Times New Roman')->setBold(true)->setSize(14);
        $sheet->getStyle("A{$susunanStart}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A' . ($susunanStart + 1), 'MINGGU, 27 SEPTEMBER ' . $tahun);
        $sheet->mergeCells('A' . ($susunanStart + 1) . ':G' . ($susunanStart + 1));
        $sheet->getStyle('A' . ($susunanStart + 1))->getFont()->setName('Times New Roman')->setBold(true)->setSize(11);
        $sheet->getStyle('A' . ($susunanStart + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Header Susunan Acara persis model baru (JAM PEMANGGILAN | NO ACARA | NOMOR LOMBA | JENIS KELAMIN | BABAK | SESI)
        $headerRow = $susunanStart + 3;
        $sheet->setCellValue('A' . $headerRow, 'JAM PEMANGGILAN');
        $sheet->setCellValue('B' . $headerRow, 'NO ACARA');
        $sheet->setCellValue('C' . $headerRow, 'NOMOR LOMBA');
        $sheet->mergeCells("C{$headerRow}:D{$headerRow}");
        $sheet->setCellValue('E' . $headerRow, 'JENIS KELAMIN');
        $sheet->setCellValue('F' . $headerRow, 'BABAK');
        $sheet->setCellValue('G' . $headerRow, 'SESI');

        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFont()->setName('Times New Roman')->setBold(true)->setSize(10);
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $sheet->getRowDimension($headerRow)->setRowHeight(24);

        // Kelompokkan sesi Pagi vs Sore
        $pagiEvents = [];
        $soreEvents = [];
        foreach ($events as $ev) {
            $waktu = strtolower($ev['jadwal_waktu'] ?? '');
            if (strpos($waktu, 'sore') !== false || strpos($waktu, 'siang') !== false || preg_match('/(1[2-9]|20)\./', $waktu) || (int)$ev['nomor_acara'] >= 27) {
                $soreEvents[] = $ev;
            } else {
                $pagiEvents[] = $ev;
            }
        }

        $currentRow = $headerRow + 1;
        $tableStartRow = $currentRow;

        // Render Baris Pagi
        $pagiStartRow = $currentRow;
        if (!empty($pagiEvents)) {
            foreach ($pagiEvents as $ev) {
                $jk = ($ev['gender'] === 'L') ? 'PUTRA' : (($ev['gender'] === 'P') ? 'PUTRI' : 'MIX');
                $jamPanggil = '';
                $wkt = trim($ev['jadwal_waktu'] ?? '');
                if (preg_match('/(\d{2}\.\d{2})/', $wkt, $jm)) {
                    $jamPanggil = $jm[1];
                } elseif ((int)$ev['nomor_acara'] >= 5) {
                    $jamPanggil = 'Event';
                }

                $sheet->setCellValue('A' . $currentRow, $jamPanggil ?: 'Event');
                $sheet->setCellValue('B' . $currentRow, $ev['nomor_acara']);
                $sheet->setCellValue('C' . $currentRow, strtoupper($ev['nama_acara']));
                $sheet->mergeCells("C{$currentRow}:D{$currentRow}");
                $sheet->setCellValue('E' . $currentRow, $jk);
                $sheet->setCellValue('F' . $currentRow, strtoupper($ev['babak']));

                $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $currentRow)->getFont()->setBold(true);

                $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFont()->setName('Times New Roman')->setSize(9.5);
                $sheet->getRowDimension($currentRow)->setRowHeight(20);
                $currentRow++;
            }
            $pagiEndRow = $currentRow - 1;

            // Merge Sesi Pagi di kolom G
            $sheet->setCellValue('G' . $pagiStartRow, "PAGI\n05.30");
            if ($pagiEndRow >= $pagiStartRow) {
                $sheet->mergeCells("G{$pagiStartRow}:G{$pagiEndRow}");
            }
            $sheet->getStyle("G{$pagiStartRow}")->getAlignment()
                ->setWrapText(true)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("G{$pagiStartRow}")->getFont()->setName('Times New Roman')->setBold(true)->setSize(10);
        }

        // Render Baris Sore
        $soreStartRow = $currentRow;
        if (!empty($soreEvents)) {
            $firstSore = true;
            foreach ($soreEvents as $ev) {
                $jk = ($ev['gender'] === 'L') ? 'PUTRA' : (($ev['gender'] === 'P') ? 'PUTRI' : 'MIX');
                $jamPanggil = 'Event';
                $wkt = trim($ev['jadwal_waktu'] ?? '');
                if ($firstSore && preg_match('/(\d{2}\.\d{2})/', $wkt, $jm)) {
                    $jamPanggil = $jm[1];
                }
                $firstSore = false;

                $sheet->setCellValue('A' . $currentRow, $jamPanggil);
                $sheet->setCellValue('B' . $currentRow, $ev['nomor_acara']);
                $sheet->setCellValue('C' . $currentRow, strtoupper($ev['nama_acara']));
                $sheet->mergeCells("C{$currentRow}:D{$currentRow}");
                $sheet->setCellValue('E' . $currentRow, $jk);
                $sheet->setCellValue('F' . $currentRow, strtoupper($ev['babak']));

                $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $currentRow)->getFont()->setBold(true);

                $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFont()->setName('Times New Roman')->setSize(9.5);
                $sheet->getRowDimension($currentRow)->setRowHeight(20);
                $currentRow++;
            }
            $soreEndRow = $currentRow - 1;

            // Merge Sesi Sore di kolom G
            $sheet->setCellValue('G' . $soreStartRow, "SORE\n13.30");
            if ($soreEndRow >= $soreStartRow) {
                $sheet->mergeCells("G{$soreStartRow}:G{$soreEndRow}");
            }
            $sheet->getStyle("G{$soreStartRow}")->getAlignment()
                ->setWrapText(true)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("G{$soreStartRow}")->getFont()->setName('Times New Roman')->setBold(true)->setSize(10);
        }

        $tableEndRow = $currentRow - 1;
        if ($tableEndRow >= $tableStartRow) {
            $sheet->getStyle("A{$headerRow}:G{$tableEndRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Page break setelah Susunan Acara
        $sheet->setBreak('A' . ($tableEndRow + 1), \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        // ---------------------------------------------------------------------
        // 3. BAGIAN BUKU ACARA (HEAT & PESERTA PERSIS MODEL BARU)
        // Font: Courier New, Baris Header Dashed, Kolom: LINT/NO | BIB | Nama Atlet | YR | Team | Time/Mark
        // ---------------------------------------------------------------------
        $row = $tableEndRow + 3;

        // Header Section Buku Acara
        $sheet->setCellValue('A' . $row, 'Buku Acara Kejurwil Atletik Pekalongan');
        $sheet->getStyle("A{$row}")->getFont()->setName('Times New Roman')->setBold(true)->setSize(14);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;

        $sheet->setCellValue('A' . $row, 'Minggu, 27 September ' . $tahun);
        $sheet->getStyle("A{$row}")->getFont()->setName('Times New Roman')->setBold(true)->setSize(11);
        $sheet->getRowDimension($row)->setRowHeight(18);
        $row += 2;

        $sesiPagiRendered = false;
        $sesiSoreRendered = false;

        foreach ($events as $ev) {
            $pesertaList = $pesertaModel->getPesertaByEvent((int)$ev['id']);

            // Sesi divider
            $wkt = strtolower($ev['jadwal_waktu'] ?? '');
            $isSore = (strpos($wkt, 'sore') !== false || strpos($wkt, 'siang') !== false || preg_match('/(1[2-9]|20)\./', $wkt) || (int)$ev['nomor_acara'] >= 27);

            if (!$sesiPagiRendered && !$isSore) {
                $sheet->setCellValue('A' . $row, 'Sesi Pagi');
                $sheet->getStyle("A{$row}")->getFont()->setName('Times New Roman')->setBold(true)->setSize(13);
                $sheet->getRowDimension($row)->setRowHeight(22);
                $row += 2;
                $sesiPagiRendered = true;
            } elseif (!$sesiSoreRendered && $isSore) {
                $row++;
                $sheet->setCellValue('A' . $row, 'Sesi Sore');
                $sheet->getStyle("A{$row}")->getFont()->setName('Times New Roman')->setBold(true)->setSize(13);
                $sheet->getRowDimension($row)->setRowHeight(22);
                $row += 2;
                $sesiSoreRendered = true;
            }

            // Kelompokkan peserta berdasarkan Heat
            $heats = [];
            foreach ($pesertaList as $p) {
                $hNum = (int)($p['heat'] ?: 1);
                $heats[$hNum][] = $p;
            }
            ksort($heats);

            $isBabakFinal   = (strcasecmp($ev['babak'] ?? '', 'Final') === 0);
            $namaCheck      = strtolower(($ev['nama_acara'] ?? '') . ' ' . ($ev['nama_lomba'] ?? '') . ' ' . ($ev['kategori_lomba'] ?? ''));
            $isLari         = (bool)preg_match('/(lari|estafet|sprint)/i', $namaCheck);
            $isField        = !$isLari && (bool)preg_match('/(tolak|lempar|lompat|lontar|cakram|martil|peluru|lembing)/i', $namaCheck);
            $isSprint       = (bool)preg_match('/\b(30|60|80|100|200|400|estafet)\b/i', $namaCheck);
            $isDistance     = (bool)preg_match('/(800|1\.?000|1\.?500|3\.?000|5\.?000)/i', $namaCheck);

            $firstCol   = ($isSprint && !$isDistance && !$isField) ? 'LINT' : 'NO';
            $lastCol    = $isField ? 'Mark' : 'Time';
            $genderText = ($ev['gender'] === 'L') ? 'Male' : (($ev['gender'] === 'P') ? 'Famale' : 'MIX');

            if ($isBabakFinal && ($isField || $isDistance || count($heats) <= 1)) {
                $isFinal = true;
                $heats = [];
            } elseif ($isBabakFinal) {
                $isFinal = true;
            } else {
                $isFinal = (count($heats) <= 1);
                if ($isFinal) {
                    $heats = [];
                }
            }

            // Baris Event Title: Event  : 1. 5000M U 16-18 Famale
            $eventTitle = "Event  : {$ev['nomor_acara']}. {$ev['nama_acara']} {$genderText}";
            $sheet->setCellValue('A' . $row, $eventTitle);
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setName('Courier New')->setBold(true)->setSize(12);
            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;

            if ($isFinal && empty($heats)) {
                // Babak Final tanpa multi-heat
                $row = $this->renderCourierTable($sheet, $pesertaList, $row, $firstCol, $lastCol);
            } elseif ($isFinal) {
                // Final dengan beberapa seri
                foreach ($heats as $heatNumber => $pesertaHeat) {
                    $sheet->setCellValue('A' . $row, "HIT  " . $heatNumber);
                    $sheet->getStyle("A{$row}")->getFont()->setName('Courier New')->setBold(true)->setSize(10.5);
                    $sheet->getRowDimension($row)->setRowHeight(18);
                    $row++;
                    $row = $this->renderCourierTable($sheet, $pesertaHeat, $row, $firstCol, $lastCol);
                }
            } else {
                // Babak Penyisihan (Multi-hit)
                if (empty($heats)) {
                    $sheet->setCellValue('A' . $row, 'HIT  1');
                    $sheet->getStyle("A{$row}")->getFont()->setName('Courier New')->setBold(true)->setSize(10.5);
                    $sheet->getRowDimension($row)->setRowHeight(18);
                    $row++;
                    $row = $this->renderCourierTable($sheet, [], $row, $firstCol, $lastCol);
                } else {
                    foreach ($heats as $heatNumber => $pesertaHeat) {
                        $sheet->setCellValue('A' . $row, "HIT  " . $heatNumber);
                        $sheet->getStyle("A{$row}")->getFont()->setName('Courier New')->setBold(true)->setSize(10.5);
                        $sheet->getRowDimension($row)->setRowHeight(18);
                        $row++;
                        $row = $this->renderCourierTable($sheet, $pesertaHeat, $row, $firstCol, $lastCol);
                    }
                }
            }

            $row++; // Spasi antar event
        }

        // ---------------------------------------------------------------------
        // SHEET 2: "Sheet 0" (DAFTAR SEMUA ATLET LENGKAP - PERSIS FILE ASLI BUKU ACARA ATLETIK KARES 2026.xlsx)
        // Kolom: No Dada | Asal Sekolah | Nama Atlet | L/P | YR
        // ---------------------------------------------------------------------
        $sheet0 = $spreadsheet->createSheet();
        $sheet0->setTitle('Sheet 0');
        $sheet0->setShowGridLines(true);

        $sheet0->setCellValue('A1', 'No Dada');
        $sheet0->setCellValue('B1', 'Asal Sekolah');
        $sheet0->setCellValue('C1', 'Nama Atlet');
        $sheet0->setCellValue('D1', 'L/P');
        $sheet0->setCellValue('E1', 'YR');

        $sheet0->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet0->getColumnDimension('A')->setWidth(12);
        $sheet0->getColumnDimension('B')->setWidth(38);
        $sheet0->getColumnDimension('C')->setWidth(36);
        $sheet0->getColumnDimension('D')->setWidth(8);
        $sheet0->getColumnDimension('E')->setWidth(8);

        $atletModel = new \App\Models\AtletModel();
        $allAtlets = $atletModel->getAtletWithDetails($kejuaraanId);

        $r0 = 2;
        foreach ($allAtlets as $a) {
            $bib = !empty($a['no_dada']) ? "'" . ltrim((string)$a['no_dada'], "'") : '';
            $yr = !empty($a['tanggal_lahir']) ? date('y', strtotime($a['tanggal_lahir'])) : '';
            $lp = ($a['gender'] === 'L') ? 'L' : 'P';

            $sheet0->setCellValueExplicit('A' . $r0, $bib, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet0->setCellValue('B' . $r0, $a['nama_sekolah'] ?? '-');
            $sheet0->setCellValue('C' . $r0, $a['nama'] ?? '-');
            $sheet0->setCellValue('D' . $r0, $lp);
            $sheet0->setCellValueExplicit('E' . $r0, $yr, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheet0->getStyle('A' . $r0)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet0->getStyle('D' . $r0)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet0->getStyle('E' . $r0)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r0++;
        }

        // Kembalikan active sheet ke sheet index 0 (ACARA)
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Helper render tabel peserta gaya atletik resmi (Courier New, border dashed di bawah header)
     */
    private function renderCourierTable($sheet, array $pesertaList, int $row, string $firstCol, string $lastCol): int
    {
        // Table Header
        $sheet->setCellValue('A' . $row, $firstCol);
        $sheet->setCellValue('B' . $row, 'BIB');
        $sheet->setCellValue('C' . $row, 'Nama Atlet');
        $sheet->setCellValue('D' . $row, 'YR');
        $sheet->setCellValue('E' . $row, 'Team');
        $sheet->setCellValue('F' . $row, $lastCol);

        $sheet->getStyle("A{$row}:F{$row}")->getFont()->setName('Courier New')->setBold(true)->setSize(10.5);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Dashed border di bawah baris header persis seperti model baru
        $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DASHED);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        if (empty($pesertaList)) {
            $sheet->setCellValue('A' . $row, '-');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setName('Courier New')->setSize(10.5);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;
        } else {
            $noUrut = 1;
            foreach ($pesertaList as $p) {
                $lintVal = ($firstCol === 'LINT') ? (!empty($p['lane']) ? $p['lane'] : $noUrut) : $noUrut;
                $bibVal  = !empty($p['no_dada']) ? ltrim((string)$p['no_dada'], "'") : '';
                $yrVal   = !empty($p['tanggal_lahir']) ? date('y', strtotime($p['tanggal_lahir'])) : '';

                // Kolom A: NO / LINT
                $sheet->setCellValue('A' . $row, $lintVal);

                // Kolom B: BIB (Text agar leading zero terjaga)
                if ($bibVal !== '') {
                    $sheet->setCellValueExplicit('B' . $row, $bibVal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                }

                // Kolom C: Nama Atlet
                $sheet->setCellValue('C' . $row, $p['nama_atlet'] ?? '-');

                // Kolom D: YR (2 digit)
                if ($yrVal !== '') {
                    $sheet->setCellValueExplicit('D' . $row, $yrVal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                }

                // Kolom E: Team
                $sheet->setCellValue('E' . $row, $p['nama_sekolah'] ?? '-');

                // Kolom F: Time / Mark
                $sheet->setCellValue('F' . $row, $p['prestasi'] ?? '');

                // Alignments & Font
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A{$row}:F{$row}")->getFont()->setName('Courier New')->setSize(10.5);
                $sheet->getRowDimension($row)->setRowHeight(18);

                $row++;
                $noUrut++;
            }
        }

        return $row;
    }
}
