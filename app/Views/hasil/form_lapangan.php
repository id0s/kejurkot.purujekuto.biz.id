<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>

    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
            color: #000;
            font-size: 13px;
            line-height: 1.35;
        }
        .print-toolbar {
            position: sticky;
            top: 0;
            background: #091E42;
            color: #fff;
            padding: 0.75rem 1.5rem;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            font-family: system-ui, -apple-system, sans-serif;
        }
        .sheet-page {
            max-width: 920px;
            margin: 1.5rem auto;
            background: #fff;
            padding: 2.5rem 3rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 2px;
            min-height: 1000px;
        }
        
        /* Meet Manager Official Table Formatting */
        .meet-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
            font-size: 12px;
            margin-bottom: 0.75rem;
            page-break-inside: auto;
        }
        .meet-table thead {
            display: table-header-group;
        }
        .meet-table thead th {
            border-top: 1px dashed #000;
            border-bottom: 1.5px dashed #000;
            padding: 5px 4px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: transparent;
        }
        .meet-table tbody {
            page-break-inside: auto;
        }
        .meet-table tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .meet-table tbody td {
            padding: 5px 4px;
            border: none;
            vertical-align: middle;
            line-height: 1.3;
        }
        .meet-table tbody tr:first-child td {
            padding-top: 6px;
        }
        .meet-table tbody tr.empty-row td {
            padding: 7px 5px;
        }
        .separator-dashed {
            border-bottom: 1px dashed #000;
            margin: 1rem 0;
        }
        .write-line {
            display: inline-block;
            border-bottom: 1.5px dotted #555;
            height: 16px;
            vertical-align: middle;
            margin-bottom: 2px;
        }
        .hit-container {
            page-break-inside: auto;
            margin-bottom: 0.75rem;
        }
        .hit-sub-header {
            font-size: 12px;
            margin-bottom: 2px;
        }
        .hit-sub-header .wind-info {
            font-size: 11px;
            font-weight: normal;
        }
        .signatures-area {
            margin-top: 1rem;
            padding-top: 0.5rem;
            font-size: 11px;
            page-break-inside: avoid;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 4mm 6mm 4mm 6mm;
            }
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .print-toolbar {
                display: none !important;
            }
            .sheet-page {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                min-height: auto !important;
            }
            .sheet-header {
                margin-bottom: 2px !important;
            }
            .sheet-header .kop-title {
                font-size: 13px !important;
                line-height: 1.15 !important;
            }
            .sheet-header .kop-sub {
                font-size: 11px !important;
                line-height: 1.15 !important;
            }
            .sheet-header .kop-venue {
                font-size: 10px !important;
                line-height: 1.15 !important;
            }
            .sheet-event-info {
                margin-top: 2px !important;
            }
            .sheet-event-info .ev-title {
                font-size: 12px !important;
                line-height: 1.15 !important;
            }
            .sheet-event-info .ev-sub {
                font-size: 10.5px !important;
                line-height: 1.15 !important;
            }
            .sheet-event-info .ev-compiled {
                font-size: 9.5px !important;
                line-height: 1.15 !important;
            }
            .hit-container {
                page-break-inside: auto !important;
                margin-bottom: 3px !important;
            }
            .hit-sub-header {
                font-size: 10px !important;
                margin-bottom: 1px !important;
            }
            .hit-sub-header .wind-info {
                font-size: 9px !important;
            }
            .meet-table {
                font-size: 9.5px !important;
                margin-bottom: 2px !important;
                page-break-inside: auto !important;
            }
            .meet-table thead {
                display: table-header-group !important;
            }
            .meet-table thead th {
                border-top: 1px dashed #000 !important;
                border-bottom: 1.5px dashed #000 !important;
                padding: 2px 2px !important;
                font-size: 9px !important;
                line-height: 1.1 !important;
            }
            .meet-table tbody {
                page-break-inside: auto !important;
            }
            .meet-table tbody tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            .meet-table tbody td {
                padding: 2.2px 2px !important;
                line-height: 1.2 !important;
            }
            .meet-table tbody tr:first-child td {
                padding-top: 2px !important;
            }
            .write-line {
                border-bottom: 1.5px dotted #333 !important;
                height: 12px !important;
                margin-bottom: 1px !important;
            }
            .signatures-area {
                margin-top: 4px !important;
                padding-top: 2px !important;
                page-break-inside: avoid !important;
                font-size: 9.5px !important;
            }
            .signatures-area .sig-space {
                height: 22px !important;
            }
            .relay-members {
                font-size: 11px !important;
                color: #000 !important;
                font-weight: normal !important;
                line-height: 1.3 !important;
                margin-top: 2px !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

<!-- Floating Screen Toolbar -->
<div class="print-toolbar">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('hasil/input/' . $event['id']) ?>" class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Kembali ke Input Hasil
        </a>
        <div class="fw-bold" style="font-size: 14px;">
            <i class="bi bi-file-earmark-text me-1 text-warning"></i> Format Meet Manager Atletik Resmi (Hit & Penulisan Rank)
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if ($isBlank): ?>
            <a href="<?= base_url('hasil/form-lapangan/' . $event['id']) ?>" class="btn btn-sm btn-outline-info text-white">
                <i class="bi bi-people-fill me-1"></i> Muat Nama Atlet Terdaftar
            </a>
        <?php else: ?>
            <a href="<?= base_url('hasil/form-lapangan/' . $event['id'] . '?blank=1') ?>" class="btn btn-sm btn-outline-warning text-white">
                <i class="bi bi-file-earmark me-1"></i> Mode Blangko Kosong
            </a>
        <?php endif; ?>

        <button onclick="window.print()" class="btn btn-sm btn-warning fw-bold px-3 text-dark d-inline-flex align-items-center gap-1">
            <i class="bi bi-printer-fill"></i> Cetak / Print Dokumen
        </button>
    </div>
</div>

<!-- Main Sheet (Official Meet Manager Layout) -->
<div class="sheet-page">

    <!-- Header Acara Resmi Atletik -->
    <div class="text-center sheet-header">
        <div class="fw-bold text-uppercase kop-title" style="font-size: 14.5px; letter-spacing: 0.5px;">
            <?= esc($activeKejuaraan['nama'] ?? 'KEJUARAAN ATLETIK POPDA JATENG 2026') ?>
        </div>
        <div class="fw-bold text-uppercase kop-sub" style="font-size: 12.5px;">
            PERSATUAN ATLETIK SELURUH INDONESIA (PASI)
        </div>
        <div class="fw-bold text-uppercase kop-venue" style="font-size: 11.5px;">
            <?= strtoupper(esc($activeKejuaraan['tempat'] ?? 'STADION HOEGENG PEKALONGAN')) ?> &bull; <?= esc($event['jadwal_hari'] ?: (!empty($activeKejuaraan['id']) && (int)$activeKejuaraan['id'] === 2 ? 'Sabtu, 29 Agustus 2026' : 'Minggu, 27 September 2026')) ?> <?= !empty($event['jadwal_waktu']) ? ' &bull; ' . esc($event['jadwal_waktu']) : '' ?>
        </div>

        <div class="sheet-event-info mt-2">
            <?php 
            $genderLabel = '';
            if (!empty($event['gender'])) {
                $gUpper = strtoupper($event['gender']);
                if ($gUpper === 'P' || $gUpper === 'PUTRI' || $gUpper === 'FEMALE') {
                    $genderLabel = 'Putri (Female)';
                } elseif ($gUpper === 'L' || $gUpper === 'PUTRA' || $gUpper === 'MALE') {
                    $genderLabel = 'Putra (Male)';
                } elseif ($gUpper === 'CAMPURAN' || $gUpper === 'MIX') {
                    $genderLabel = 'Campuran (MIX)';
                }
            }
            $fullEventName = esc($event['nama_acara']);
            if (!empty($genderLabel) && stripos($fullEventName, 'putra') === false && stripos($fullEventName, 'putri') === false && stripos($fullEventName, 'male') === false && stripos($fullEventName, 'female') === false) {
                $fullEventName .= ' ' . $genderLabel;
            }
            ?>
            <div class="fw-bold ev-title" style="font-size: 13.5px;">
                Event <?= esc($event['nomor_acara']) ?> <?= $fullEventName ?>
            </div>
            <div class="fw-bold ev-sub" style="font-size: 12px;">
                Hasil - <?= esc($event['babak'] ?: 'Final') ?> - <?= esc($event['jadwal_hari'] ?: 'Sabtu 29/08/26') ?>
            </div>
            <div class="fw-semibold ev-compiled" style="font-size: 11px;">
                Compiled By <?= ($isFieldEvent ?? false) ? 'Mark' : 'Time' ?>
            </div>
        </div>
    </div>

    <!-- Loop Heats / Hits -->
    <?php 
    $totalHeats = count($heats);
    $allRankedList = [];
    ?>

    <?php foreach ($heats as $heatNum => $pesertaHeat): ?>
        <div class="hit-container">
            
            <!-- Sub Judul Hit / Heat -->
            <?php 
            $isLongDistEvent = (bool)preg_match('/(1\.?000|1\.?500|3\.?000|5\.?000|jalan\s*cepat)/i', $event['nama_acara'] ?? '');
            $isRelayEvent    = (bool)preg_match('/(estafet|4\s*x|5\s*x|relay)/i', ($event['nama_acara'] ?? '') . ' ' . ($event['kategori_lomba'] ?? ''));
            ?>
            <div class="d-flex justify-content-between align-items-center hit-sub-header fw-bold">
                <span>
                    <?php if ($totalHeats > 1): ?>
                        HIT <?= $heatNum ?>
                    <?php else: ?>
                        <?= strtoupper(esc($event['babak'] ?: 'FINAL')) ?>
                    <?php endif; ?>
                </span>
                <span class="wind-info">Wind Reading: NWI</span>
            </div>

            <!-- Tabel Peserta Hit -->
            <table class="meet-table">
                <thead>
                    <?php if ($isFieldEvent ?? false): ?>
                        <!-- Header Nomor Lapangan: Penulisan RANK ditaruh pada judul paling akhir -->
                        <tr>
                            <th class="text-center" width="40">NO</th>
                            <th class="text-center" width="60">BIB</th>
                            <th class="text-start" style="width: 250px;">NAMA ATLET</th>
                            <?php if (!$isRelayEvent): ?>
                                <th class="text-center" width="40">YR</th>
                            <?php endif; ?>
                            <th class="text-start" style="width: 220px;">TEAM</th>
                            <th class="text-center" width="60">KE-1</th>
                            <th class="text-center" width="60">KE-2</th>
                            <th class="text-center" width="60">KE-3</th>
                            <th class="text-end" width="75">MARK</th>
                            <th class="text-center" width="55">RANK</th>
                        </tr>
                    <?php else: ?>
                        <!-- Header Nomor Lari: Penulisan RANK ditaruh pada judul paling akhir -->
                        <?php 
                        $isLongDist = $isLongDistEvent;
                        $colTrackHeader = $isLongDist ? 'NO' : 'LN';
                        ?>
                        <tr>
                            <th class="text-center" width="40"><?= $colTrackHeader ?></th>
                            <th class="text-center" width="60">BIB</th>
                            <th class="text-start" style="<?= $isRelayEvent ? 'width: 325px;' : 'width: 280px;' ?>">NAMA <?= $isRelayEvent ? 'TIM / REGU' : 'ATLET' ?></th>
                            <?php if (!$isRelayEvent): ?>
                                <th class="text-center" width="45">YR</th>
                            <?php endif; ?>
                            <th class="text-start" style="<?= $isRelayEvent ? 'width: 280px;' : 'width: 260px;' ?>">TEAM</th>
                            <th class="text-end" width="105">TIME</th>
                            <th class="text-center" width="60">RANK</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if (empty($pesertaHeat) || $isBlank): ?>
                        <?php 
                        $blankRowsCount = $isBlank ? 8 : 4;
                        for ($b = 1; $b <= $blankRowsCount; $b++): 
                        ?>
                            <tr class="empty-row">
                                <td class="text-center"><?= $b ?></td>
                                <td class="text-center"><span class="write-line" style="width: 45px;"></span></td>
                                <td><span class="write-line" style="width: 95%;"></span></td>
                                <?php if (!$isRelayEvent): ?>
                                    <td class="text-center"><span class="write-line" style="width: 25px;"></span></td>
                                <?php endif; ?>
                                <td><span class="write-line" style="width: 95%;"></span></td>
                                <?php if ($isFieldEvent ?? false): ?>
                                    <td class="text-center"><span class="write-line" style="width: 45px;"></span></td>
                                    <td class="text-center"><span class="write-line" style="width: 45px;"></span></td>
                                    <td class="text-center"><span class="write-line" style="width: 45px;"></span></td>
                                    <td class="text-end"><span class="write-line" style="width: 55px;"></span></td>
                                <?php else: ?>
                                    <td class="text-end"><span class="write-line" style="width: 75px;"></span></td>
                                <?php endif; ?>
                                <td class="text-center"><span class="write-line" style="width: 40px;"></span></td>
                            </tr>
                        <?php endfor; ?>
                    <?php else: ?>
                        <?php 
                        $noGiliran = 1;
                        foreach ($pesertaHeat as $p): 
                            $allRankedList[] = $p;
                            // Format 2-digit birth year (YR)
                            $yr = '--';
                            if (!empty($p['tanggal_lahir']) && $p['tanggal_lahir'] !== '0000-00-00') {
                                $yr = date('y', strtotime($p['tanggal_lahir']));
                            }

                            // Ekstrak percobaan 1, 2, 3 jika nomor lapangan
                            $p1 = ''; $p2 = ''; $p3 = '';
                            if (($isFieldEvent ?? false) && !empty($p['ket_hasil']) && preg_match('/P1:\s*([^|]+)\s*\|\s*P2:\s*([^|]+)\s*\|\s*P3:\s*(.*)/i', $p['ket_hasil'], $km)) {
                                $p1 = trim($km[1]) === '-' ? '' : trim($km[1]);
                                $p2 = trim($km[2]) === '-' ? '' : trim($km[2]);
                                $p3 = trim($km[3]) === '-' ? '' : trim($km[3]);
                            }

                            $prestasi = !empty($p['prestasi']) ? esc($p['prestasi']) : '';
                            $rank     = !empty($p['peringkat']) ? esc((string)$p['peringkat']) : '';
                            $laneNo   = ($isLongDistEvent || ($isFieldEvent ?? false)) ? $noGiliran : (!empty($p['lane']) ? esc($p['lane']) : $noGiliran);
                        ?>
                            <tr>
                                <td class="text-center font-monospace"><?= $laneNo ?></td>
                                <td class="text-center font-monospace fw-bold"><?= esc(ltrim($p['no_dada'] ?? '', "'") ?: '-') ?></td>
                                <td class="text-uppercase">
                                    <?= esc($p['nama_atlet']) ?>
                                    <?php if (!empty($p['catatan_atlet'])): ?>
                                        <div class="relay-members" style="font-size: 12px; font-weight: normal; text-transform: none; color: #000; line-height: 1.35; margin-top: 3px;">
                                            <?= esc($p['catatan_atlet']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php if (!$isRelayEvent): ?>
                                    <td class="text-center font-monospace"><?= $yr ?></td>
                                <?php endif; ?>
                                <td class="text-uppercase"><?= esc($p['nama_sekolah'] ?: '-') ?></td>

                                <?php if ($isFieldEvent ?? false): ?>
                                    <td class="text-center font-monospace"><span class="write-line" style="width:48px;"></span></td>
                                    <td class="text-center font-monospace"><span class="write-line" style="width:48px;"></span></td>
                                    <td class="text-center font-monospace"><span class="write-line" style="width:48px;"></span></td>
                                    <td class="text-end font-monospace fw-bold"><span class="write-line" style="width:60px;"></span></td>
                                <?php else: ?>
                                    <td class="text-end font-monospace fw-bold"><span class="write-line" style="width:85px;"></span></td>
                                <?php endif; ?>

                                <!-- Kolom RANK ditaruh paling akhir (selalu bergaris kosong untuk diisi juri lapangan) -->
                                <td class="text-center font-monospace fw-bold"><span class="write-line" style="width:48px;"></span></td>
                            </tr>
                        <?php 
                            $noGiliran++;
                        endforeach; 
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>

    <!-- Tanda Tangan Wasit & Juri Resmi (Format Atletik) -->
    <div class="row signatures-area" style="page-break-inside: avoid;">
        <div class="col-4 text-center">
            <div>Juri 1 / Pencatat Waktu</div>
            <div class="sig-space" style="height: 38px;"></div>
            <div class="fw-bold border-top border-dark d-inline-block px-2 pt-1 text-nowrap" style="white-space: nowrap;">( .................... )</div>
        </div>
        <div class="col-4 text-center">
            <div>Juri 2 / Kedatangan</div>
            <div class="sig-space" style="height: 38px;"></div>
            <div class="fw-bold border-top border-dark d-inline-block px-2 pt-1 text-nowrap" style="white-space: nowrap;">( .................... )</div>
        </div>
        <div class="col-4 text-center">
            <div>Wasit / Referee Lapangan</div>
            <div class="sig-space" style="height: 38px;"></div>
            <div class="fw-bold border-top border-dark d-inline-block px-2 pt-1 text-nowrap" style="white-space: nowrap;">( .................... )</div>
        </div>
    </div>

</div>

</body>
</html>
