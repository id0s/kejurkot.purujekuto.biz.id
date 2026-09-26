<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Hasil Resmi Perlombaan (Meet Manager)') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 12mm 15mm 12mm;
        }
        body {
            background-color: #f1f5f9;
            color: #000;
            font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
            font-size: 13px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Toolbar */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #0f172a;
            color: #fff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            border-bottom: 1px solid #334155;
            font-family: system-ui, -apple-system, sans-serif;
        }

        /* Sheet Page */
        .sheet-page {
            width: 210mm;
            min-height: 297mm;
            margin: 25px auto 40px auto;
            background: #ffffff;
            padding: 16mm 14mm;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            box-sizing: border-box;
        }

        /* Monospace Results Table */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
            font-size: 13px;
        }
        .results-table thead th {
            font-weight: normal;
            padding: 3px 0;
            border-bottom: 1px dashed #000;
        }
        .results-table tbody td {
            padding: 2.5px 0;
            vertical-align: top;
        }
        .results-table .col-rank {
            width: 50px;
            text-align: right;
            padding-right: 12px;
        }
        .results-table .col-bib {
            width: 60px;
            text-align: right;
            padding-right: 18px;
        }
        .results-table .col-name {
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 260px;
        }
        .results-table .col-yr {
            width: 40px;
            text-align: center;
        }
        .results-table .col-team {
            text-align: left;
            padding-left: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
        .results-table .col-mark {
            width: 100px;
            text-align: right;
            padding-left: 10px;
        }

        @media print {
            body {
                background: #fff;
            }
            .print-toolbar {
                display: none !important;
            }
            .sheet-page {
                width: 100%;
                margin: 0;
                padding: 5mm 0;
                box-shadow: none;
                border: none;
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
        <a href="<?= base_url('hasil/form-lapangan/' . $event['id']) ?>" target="_blank" class="btn btn-sm btn-outline-warning text-warning d-inline-flex align-items-center gap-1" title="Buka Formulir Blangko Lapangan untuk Juri">
            <i class="bi bi-file-earmark-spreadsheet"></i> Buka Blangko Juri Lapangan
        </a>
        <div class="fw-bold text-white small ms-2">
            <i class="bi bi-trophy-fill text-warning me-1"></i> Format Meet Manager Resmi (Lembar Hasil Acara <?= esc($event['nomor_acara']) ?>)
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button onclick="window.print()" class="btn btn-sm btn-warning fw-bold px-3 text-dark d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-printer-fill"></i> Cetak / Print Lembar Hasil
        </button>
    </div>
</div>

<!-- Main Sheet (Official Meet Manager Layout) -->
<div class="sheet-page">

    <!-- Header Acara Resmi Atletik (Persis Format Gambar 3) -->
    <div class="text-center mb-4">
        <div style="font-size: 14.5px; font-weight: bold; letter-spacing: 0.5px;">
            <?= strtoupper(esc($activeKejuaraan['nama'] ?? 'KEJUARAAN ATLETIK POPDA JATENG 2026')) ?>
        </div>
        <?php if (!empty($activeKejuaraan['sub_nama'])): ?>
            <div style="font-size: 13px; font-weight: bold;">
                <?= strtoupper(esc($activeKejuaraan['sub_nama'])) ?>
            </div>
        <?php else: ?>
            <div style="font-size: 13px; font-weight: bold;">
                PERSATUAN ATLETIK SELURUH INDONESIA (PASI)
            </div>
        <?php endif; ?>
        <div style="font-size: 12px; font-weight: bold;">
            <?= strtoupper(esc($activeKejuaraan['tempat'] ?? 'STADION HOEGENG PEKALONGAN')) ?>
            <?php if (!empty($activeKejuaraan['tanggal_mulai'])): ?>
                &nbsp;-&nbsp;
                <?= date('d/m/y', strtotime($activeKejuaraan['tanggal_mulai'])) ?>
                <?php if (!empty($activeKejuaraan['tanggal_selesai']) && $activeKejuaraan['tanggal_selesai'] !== $activeKejuaraan['tanggal_mulai']): ?>
                    to <?= date('d/m/y', strtotime($activeKejuaraan['tanggal_selesai'])) ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="mt-3">
            <?php 
            $gShort = ($event['gender'] === 'L') ? 'Male' : (($event['gender'] === 'P') ? 'Female' : 'MIX');
            $fullNamaAcara = esc($event['nama_acara']);
            if (stripos($fullNamaAcara, 'male') === false && stripos($fullNamaAcara, 'female') === false && stripos($fullNamaAcara, 'mix') === false) {
                $fullNamaAcara .= ' ' . $gShort;
            }
            ?>
            <div style="font-size: 13.5px; font-weight: bold;">
                Event <?= esc($event['nomor_acara']) ?> <?= $fullNamaAcara ?>
            </div>
            <div style="font-size: 12.5px; font-weight: bold;">
                Hasil - <?= esc($event['babak'] ?: 'Finals') ?> - <?= esc($event['jadwal_hari'] ?: date('l d/m/y')) ?>
            </div>
            <div style="font-size: 12px; font-weight: bold;">
                Compiled By <?= ($isFieldEvent ?? false) ? 'Mark' : 'Time' ?>
            </div>
        </div>
    </div>

    <?php 
    $isRelayEvent = (bool)preg_match('/(estafet|4\s*x|5\s*x|relay)/i', ($event['nama_acara'] ?? '') . ' ' . ($event['kategori_lomba'] ?? ''));
    ?>

    <!-- Official Results Table (Gambar 3) -->
    <table class="results-table">
        <thead>
            <tr>
                <th class="col-rank">RANK</th>
                <th class="col-bib">BIB</th>
                <th class="col-name">NAMA <?= $isRelayEvent ? 'TIM / REGU' : 'ATLET' ?></th>
                <?php if (!$isRelayEvent): ?>
                    <th class="col-yr">YR</th>
                <?php endif; ?>
                <th class="col-team">TEAM</th>
                <th class="col-mark"><?= ($isFieldEvent ?? false) ? 'MARK' : 'TIME' ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($validList) && empty($unrankedList)): ?>
                <tr>
                    <td colspan="<?= $isRelayEvent ? 5 : 6 ?>" class="text-center py-4" style="font-style: italic;">
                        Belum ada catatan hasil yang diinputkan untuk acara ini.
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                $autoRank = 1;
                foreach ($validList as $p): 
                    $bib = ltrim($p['no_dada'] ?? '', "'") ?: '-';
                    $yr = '--';
                    if (!empty($p['tanggal_lahir']) && $p['tanggal_lahir'] !== '0000-00-00') {
                        $yr = date('y', strtotime($p['tanggal_lahir']));
                    }
                    $team = strtoupper($p['nama_sekolah'] ?: '-');
                    $prestasi = !empty($p['prestasi']) ? esc($p['prestasi']) : '-';
                    $rank = !empty($p['peringkat']) ? (int)$p['peringkat'] : $autoRank;
                ?>
                    <tr>
                        <td class="col-rank"><?= $rank ?></td>
                        <td class="col-bib"><?= esc($bib) ?></td>
                        <td class="col-name">
                            <?= esc($p['nama_atlet']) ?>
                            <?php if ($isRelayEvent && !empty($p['catatan_atlet'])): ?>
                                <div class="relay-members" style="font-size: 11px; font-weight: normal; color: #111; line-height: 1.3; margin-top: 2px;">
                                    <?= esc($p['catatan_atlet']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <?php if (!$isRelayEvent): ?>
                            <td class="col-yr"><?= $yr ?></td>
                        <?php endif; ?>
                        <td class="col-team"><?= esc($team) ?></td>
                        <td class="col-mark"><?= $prestasi ?></td>
                    </tr>
                <?php 
                    $autoRank++;
                endforeach; 
                ?>

                <?php foreach ($unrankedList as $p): 
                    $bib = ltrim($p['no_dada'] ?? '', "'") ?: '-';
                    $yr = '--';
                    if (!empty($p['tanggal_lahir']) && $p['tanggal_lahir'] !== '0000-00-00') {
                        $yr = date('y', strtotime($p['tanggal_lahir']));
                    }
                    $team = strtoupper($p['nama_sekolah'] ?: '-');
                    $status = !empty($p['status_prestasi']) && $p['status_prestasi'] !== 'Valid' 
                        ? $p['status_prestasi'] 
                        : (!empty($p['prestasi']) ? $p['prestasi'] : 'DNS');
                ?>
                    <tr>
                        <td class="col-rank">---</td>
                        <td class="col-bib"><?= esc($bib) ?></td>
                        <td class="col-name">
                            <?= esc($p['nama_atlet']) ?>
                            <?php if ($isRelayEvent && !empty($p['catatan_atlet'])): ?>
                                <div class="relay-members" style="font-size: 11px; font-weight: normal; color: #111; line-height: 1.3; margin-top: 2px;">
                                    <?= esc($p['catatan_atlet']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <?php if (!$isRelayEvent): ?>
                            <td class="col-yr"><?= $yr ?></td>
                        <?php endif; ?>
                        <td class="col-team"><?= esc($team) ?></td>
                        <td class="col-mark"><?= esc($status) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>
