<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/pasi-logo.png') ?>">

    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            font-size: 13px;
        }
        .print-toolbar {
            position: sticky;
            top: 0;
            background: #172B4D;
            color: #fff;
            padding: 0.75rem 1.5rem;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            font-family: 'Inter', system-ui, sans-serif;
        }
        .page-sheet {
            max-width: 860px;
            margin: 2rem auto;
            background: #fff;
            padding: 2.8rem 2.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 4px;
            min-height: 1050px;
            page-break-after: always;
            position: relative;
        }
        .page-sheet:last-child {
            page-break-after: auto;
        }

        /* Tabel Susunan Acara */
        .table-susunan {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
        }
        .table-susunan th, .table-susunan td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .table-susunan th {
            background: #fff;
            font-weight: bold;
            text-align: center;
        }

        /* Tabel Buku Acara Perlombaan (Khas IAAF / Meet Manager / Excel baru) */
        .buku-acara-font {
            font-family: 'Courier New', Courier, monospace;
            color: #000;
        }
        .event-header-title {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            font-size: 13.5px;
            margin-top: 14px;
            margin-bottom: 3px;
        }
        .hit-header-title {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            font-size: 12.5px;
            margin-top: 3px;
            margin-bottom: 2px;
        }
        .table-buku {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11.5px;
            margin-bottom: 12px;
        }
        .table-buku th {
            border-top: none;
            border-left: none;
            border-right: none;
            border-bottom: 1.5px dashed #000;
            padding: 5px 5px;
            font-weight: bold;
        }
        .table-buku td {
            border: none;
            padding: 6px 5px;
            vertical-align: middle;
        }
        .table-buku tbody tr:first-child td {
            padding-top: 9px;
        }

        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            margin: auto;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            white-space: nowrap;
        }

        .relay-members {
            font-size: 11.5px;
            color: #000;
            font-weight: normal;
            line-height: 1.35;
            margin-top: 3px;
        }

        @media print {
            .print-toolbar { display: none !important; }
            body { background: #fff !important; margin: 0; padding: 0; }
            .page-sheet {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 1.5cm 1.2cm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                min-height: auto;
            }
            .relay-members {
                font-size: 11px !important;
                color: #000 !important;
                line-height: 1.3 !important;
                margin-top: 2px !important;
            }
            #toolbarContainer,
            #debug-icon,
            #debug-bar,
            .ci-debug-toolbar {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
            }
        }

        /* Sembunyikan CodeIgniter Debug Toolbar & Icon CI */
        #toolbarContainer,
        #debug-icon,
        #debug-bar,
        .ci-debug-toolbar {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }
    </style>
</head>
<body>

<div class="print-toolbar no-print">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-book-half fs-5 text-warning"></i>
        <span class="fw-bold">Preview Cetak Dokumen Buku Acara Resmi</span>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-light" onclick="window.close()">
            <i class="bi bi-x-lg me-1"></i> Tutup
        </button>
        <button class="btn btn-sm btn-primary fw-bold px-3 shadow" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </button>
    </div>
</div>

<!-- ========================================== -->
<!-- HALAMAN 1: COVER RESMI                    -->
<!-- ========================================== -->
<div class="page-sheet d-flex flex-column justify-content-between text-center" style="min-height: 1000px;">
    <!-- Bagian Atas -->
    <div class="pt-4">
        <h1 class="fw-bold mb-3" style="font-size: 32px; letter-spacing: 3px;">BUKU ACARA</h1>
        <h4 class="fw-bold mb-2" style="font-size: 20px;">
            KEJUARAAN ATLETIK KELOMPOK UMUR<br>EKS-KARESIDENAN PEKALONGAN
        </h4>
        <h5 class="fw-bold mb-0" style="font-size: 18px;">
            <?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng') ?>, Minggu, 27 September 2026
        </h5>
    </div>

    <!-- Bagian Tengah: Logo PASI Resmi -->
    <div class="my-5 py-4">
        <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="max-height: 270px; width: auto;" class="img-fluid">
    </div>

    <!-- Bagian Bawah: Footer Instansi Resmi -->
    <div class="pb-4" style="line-height: 1.8; font-size: 16px;">
        <div class="fw-bold fs-5">EKS - KARESIDENAN PEKALONGAN</div>
        <div class="fw-bold">PERSATUAN ATLETIK SELURUH INDONESIA</div>
        <div class="fw-bold">TAHUN 2026</div>
    </div>
</div>

<!-- ========================================== -->
<!-- HALAMAN 2: SUSUNAN ACARA RESMI (PAGI & SORE) -->
<!-- ========================================== -->
<div class="page-sheet">
    <div class="text-center mb-3">
        <h3 class="fw-bold mb-1" style="font-size: 17px; font-family: Calibri, 'Times New Roman', sans-serif; letter-spacing: 0.5px;">
            SUSUNAN ACARA KEJURWIL  ATLETIK  PEKALONGAN
        </h3>
        <h5 class="fw-bold mb-0" style="font-size: 14px; font-family: Calibri, 'Times New Roman', sans-serif;">
            MINGGU, 27 SEPTEMBER  2026
        </h5>
    </div>

    <table class="table-susunan align-middle">
        <thead>
            <tr>
                <th style="width: 105px;">JAM PEMANGGILAN</th>
                <th style="width: 50px;">NO ACARA</th>
                <th style="text-align: left; padding-left: 8px;">NOMOR LOMBA</th>
                <th style="width: 85px;">JENIS KELAMIN</th>
                <th style="width: 85px;">BABAK</th>
                <th style="width: 95px;">SESI</th>
            </tr>
        </thead>
        <tbody>
            <!-- Sesi Pagi -->
            <?php if (!empty($pagiEvents)): ?>
                <?php $firstPagi = true; ?>
                <?php foreach ($pagiEvents as $ev): ?>
                    <?php 
                    $jk = ($ev['gender'] === 'L') ? 'PUTRA' : (($ev['gender'] === 'P') ? 'PUTRI' : 'MIX');
                    // Format Jam Pemanggilan
                    $jamPanggil = '';
                    $wkt = trim($ev['jadwal_waktu'] ?? '');
                    if (preg_match('/(\d{2}\.\d{2})/', $wkt, $jm)) {
                        $jamPanggil = $jm[1];
                    } elseif ($ev['nomor_acara'] >= 5) {
                        $jamPanggil = 'Event';
                    }
                    ?>
                    <tr>
                        <td class="text-center fw-bold"><?= esc($jamPanggil ?: 'Event') ?></td>
                        <td class="text-center fw-bold"><?= esc($ev['nomor_acara']) ?></td>
                        <td class="text-uppercase fw-semibold" style="text-align: left; padding-left: 8px;">
                            <?= esc($ev['nama_acara']) ?>
                        </td>
                        <td class="text-center"><?= esc($jk) ?></td>
                        <td class="text-center fw-bold"><?= strtoupper(esc($ev['babak'])) ?></td>
                        <?php if ($firstPagi): ?>
                            <td rowspan="<?= count($pagiEvents) ?>" class="text-center align-middle fw-bold" style="line-height: 1.4;">
                                PAGI 05.30
                            </td>
                            <?php $firstPagi = false; ?>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Sesi Sore -->
            <?php if (!empty($soreEvents)): ?>
                <?php $firstSore = true; ?>
                <?php foreach ($soreEvents as $ev): ?>
                    <?php 
                    $jk = ($ev['gender'] === 'L') ? 'PUTRA' : (($ev['gender'] === 'P') ? 'PUTRI' : 'MIX');
                    $jamPanggil = 'Event';
                    $wkt = trim($ev['jadwal_waktu'] ?? '');
                    if ($firstSore && preg_match('/(\d{2}\.\d{2})/', $wkt, $jm)) {
                        $jamPanggil = $jm[1];
                    }
                    ?>
                    <tr style="<?= $firstSore && !empty($pagiEvents) ? 'border-top: 2px solid #000;' : '' ?>">
                        <td class="text-center fw-bold"><?= esc($jamPanggil) ?></td>
                        <td class="text-center fw-bold"><?= esc($ev['nomor_acara']) ?></td>
                        <td class="text-uppercase fw-semibold" style="text-align: left; padding-left: 8px;">
                            <?= esc($ev['nama_acara']) ?>
                        </td>
                        <td class="text-center"><?= esc($jk) ?></td>
                        <td class="text-center fw-bold"><?= strtoupper(esc($ev['babak'])) ?></td>
                        <?php if ($firstSore): ?>
                            <td rowspan="<?= count($soreEvents) ?>" class="text-center align-middle fw-bold" style="line-height: 1.4;">
                                SORE 13.30
                            </td>
                            <?php $firstSore = false; ?>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ========================================== -->
<!-- HALAMAN 3+: BUKU ACARA (HEAT & PESERTA)    -->
<!-- ========================================== -->
<div class="page-sheet buku-acara-font">
    <div class="mb-3" style="font-family: 'Times New Roman', serif;">
        <h4 class="fw-bold mb-0" style="font-size: 16px;">Buku Acara Kejurwil Atletik Pekalongan</h4>
        <h5 class="fw-bold mb-2" style="font-size: 14px;">Minggu, 27 September 2026</h5>
    </div>

    <?php 
    $sesiPagiRendered = false;
    $sesiSoreRendered = false;
    ?>

    <!-- Loop Events -->
    <?php foreach ($bukuAcaraData as $item): ?>
        <?php 
        $ev        = $item['event']; 
        $peserta   = $item['peserta']; 
        $isFinal   = $item['is_final']; 
        $heats     = $item['heats']; 
        $namaCheck = strtolower(($ev['nama_acara'] ?? '') . ' ' . ($ev['nama_lomba'] ?? '') . ' ' . ($ev['kategori_lomba'] ?? ''));
        $isLari    = (bool)preg_match('/(lari|estafet|sprint)/i', $namaCheck);
        $isField   = !$isLari && (bool)preg_match('/(tolak|lempar|lompat|lontar|cakram|martil|peluru|lembing)/i', $namaCheck);
        $isSprint  = (bool)preg_match('/\b(30|60|80|100|200|400|estafet)\b/i', $namaCheck);
        $isDistance = (bool)preg_match('/(800|1\.?000|1\.?500|3\.?000|5\.?000)/i', $namaCheck);
        $isRelay   = (bool)preg_match('/(estafet|4\s*x|5\s*x|relay)/i', $namaCheck);

        // Header Kolom Pertama (LINT untuk sprint berlintasan, NO untuk jarak jauh & lapangan)
        $firstCol = ($isSprint && !$isDistance && !$isField) ? 'LINT' : 'NO';
        // Header Kolom Terakhir (Mark untuk lapangan, Time untuk lari)
        $lastCol  = $isField ? 'Mark' : 'Time';

        // Gender Label khas buku acara baru
        $genderText = ($ev['gender'] === 'L') ? 'Male' : (($ev['gender'] === 'P') ? 'Famale' : 'MIX');

        // Pemisah Sesi Pagi vs Sesi Sore
        $wkt = strtolower($ev['jadwal_waktu'] ?? '');
        $isSore = (strpos($wkt, 'sore') !== false || strpos($wkt, 'siang') !== false || preg_match('/(1[2-9]|20)\./', $wkt) || (int)$ev['nomor_acara'] >= 27);
        ?>

        <?php if (!$sesiPagiRendered && !$isSore): ?>
            <div class="fw-bold my-2" style="font-size: 15px; font-family: 'Times New Roman', serif;">
                Sesi Pagi
            </div>
            <?php $sesiPagiRendered = true; ?>
        <?php elseif (!$sesiSoreRendered && $isSore): ?>
            <div class="fw-bold my-3 pt-3" style="font-size: 15px; font-family: 'Times New Roman', serif; border-top: 1px dashed #666;">
                Sesi Sore
            </div>
            <?php $sesiSoreRendered = true; ?>
        <?php endif; ?>

        <div style="page-break-inside: avoid; margin-bottom: 16px;">
            <?php 
            $evNamaTitle = esc($ev['nama_acara']);
            if (stripos($evNamaTitle, 'male') === false && stripos($evNamaTitle, 'female') === false && stripos($evNamaTitle, 'mix') === false) {
                $evNamaTitle .= ' ' . esc($genderText);
            }
            ?>
            <div class="event-header-title">
                Event &nbsp;: <?= esc($ev['nomor_acara']) ?>. <?= $evNamaTitle ?>
            </div>

            <?php if (empty($peserta)): ?>
                <table class="table-buku">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;"><?= $firstCol ?></th>
                            <th style="width: 55px; text-align: center;">BIB</th>
                            <th style="text-align: left;">Nama <?= $isRelay ? 'Tim / Regu' : 'Atlet' ?></th>
                            <?php if (!$isRelay): ?>
                                <th style="width: 45px; text-align: center;">YR</th>
                            <?php endif; ?>
                            <th style="width: 250px; text-align: left;">Team</th>
                            <th style="width: 80px; text-align: center;"><?= $lastCol ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="<?= $isRelay ? 5 : 6 ?>" style="text-align: center; padding: 6px; font-style: italic; color: #666;">
                                Belum ada atlet terdaftar pada nomor ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php elseif (!empty($heats)): ?>
                <!-- MULTI-HEAT (HIT 1, HIT 2, dst) -->
                <?php foreach ($heats as $heatNum => $pesertaHeat): ?>
                    <div class="hit-header-title">
                        HIT &nbsp;<?= esc($heatNum) ?>
                    </div>
                    <table class="table-buku">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;"><?= $firstCol ?></th>
                                <th style="width: 55px; text-align: center;">BIB</th>
                                <th style="text-align: left;">Nama <?= $isRelay ? 'Tim / Regu' : 'Atlet' ?></th>
                                <?php if (!$isRelay): ?>
                                    <th style="width: 45px; text-align: center;">YR</th>
                                <?php endif; ?>
                                <th style="width: 250px; text-align: left;">Team</th>
                                <th style="width: 80px; text-align: center;"><?= $lastCol ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rowNo = 1; foreach ($pesertaHeat as $p): ?>
                                <?php
                                $lintVal = !empty($p['lane']) ? $p['lane'] : $rowNo;
                                $col1Val = ($firstCol === 'LINT') ? $lintVal : $rowNo++;
                                $bib     = ltrim((string)($p['no_dada'] ?? ''), "'");
                                $yr      = !empty($p['tanggal_lahir']) ? date('y', strtotime($p['tanggal_lahir'])) : '';
                                $team    = $p['nama_sekolah'] ?: ($p['alamat_sekolah'] ?: '-');
                                $timeVal = $p['prestasi'] ?: '';
                                ?>
                                <tr>
                                    <td style="text-align: center;"><?= esc($col1Val) ?></td>
                                    <td style="text-align: center;"><?= esc($bib) ?></td>
                                    <td style="text-align: left;">
                                        <?= esc($p['nama_atlet']) ?>
                                        <?php if (!empty($p['catatan_atlet'])): ?>
                                            <div class="relay-members">
                                                <?= esc($p['catatan_atlet']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (!$isRelay): ?>
                                        <td style="text-align: center;"><?= esc($yr) ?></td>
                                    <?php endif; ?>
                                    <td style="text-align: left;"><?= esc($team) ?></td>
                                    <td style="text-align: center;"><?= esc($timeVal) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- SATU HIT BERSAMA (LANGSUNG FINAL) -->
                <table class="table-buku">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;"><?= $firstCol ?></th>
                            <th style="width: 55px; text-align: center;">BIB</th>
                            <th style="text-align: left;">Nama <?= $isRelay ? 'Tim / Regu' : 'Atlet' ?></th>
                            <?php if (!$isRelay): ?>
                                <th style="width: 45px; text-align: center;">YR</th>
                            <?php endif; ?>
                            <th style="width: 250px; text-align: left;">Team</th>
                            <th style="width: 80px; text-align: center;"><?= $lastCol ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rowNo = 1; foreach ($peserta as $p): ?>
                            <?php
                            $lintVal = !empty($p['lane']) ? $p['lane'] : $rowNo;
                            $col1Val = ($firstCol === 'LINT') ? $lintVal : $rowNo++;
                            $bib     = ltrim((string)($p['no_dada'] ?? ''), "'");
                            $yr      = !empty($p['tanggal_lahir']) ? date('y', strtotime($p['tanggal_lahir'])) : '';
                            $team    = $p['nama_sekolah'] ?: ($p['alamat_sekolah'] ?: '-');
                            $timeVal = $p['prestasi'] ?: '';
                            ?>
                            <tr>
                                <td style="text-align: center;"><?= esc($col1Val) ?></td>
                                <td style="text-align: center;"><?= esc($bib) ?></td>
                                <td style="text-align: left;">
                                    <?= esc($p['nama_atlet']) ?>
                                    <?php if (!empty($p['catatan_atlet'])): ?>
                                        <div class="relay-members">
                                            <?= esc($p['catatan_atlet']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php if (!$isRelay): ?>
                                    <td style="text-align: center;"><?= esc($yr) ?></td>
                                <?php endif; ?>
                                <td style="text-align: left;"><?= esc($team) ?></td>
                                <td style="text-align: center;"><?= esc($timeVal) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>

