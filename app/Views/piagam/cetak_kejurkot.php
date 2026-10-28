<?php
// Default F4 Folio (330 x 215 mm) standar blanko piagam resmi Indonesia
$paperParam = strtolower(trim((string)($_GET['paper'] ?? $_GET['kertas'] ?? 'f4')));
$defaultPaper = in_array($paperParam, ['a4']) ? 'a4' : 'f4';
$isF4 = ($defaultPaper === 'f4');
$bgTemplateImage = $isF4 ? 'template_kejurkot_pekalongan_f4.jpg' : 'template_kejurkot_pekalongan_2026.jpg';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Cetak Piagam Kejurkot Kota Pekalongan 2026') ?></title>

    <link rel="icon" type="image/png" href="<?= base_url('assets/images/pasi-logo.png') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;0,900;1,600&family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">

    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        #toolbarContainer, #debug-icon, #debug-bar, .ci-debug-toolbar {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #2e3440;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
        }

        @page {
            size: 330mm 215.9mm;
            margin: 0mm !important;
        }

        /* Floating Toolbar */
        .print-toolbar {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
        }

        .print-toolbar .btn {
            font-size: 13px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Lembar Piagam A4 Landscape */
        .piagam-sheet {
            position: relative;
            background-color: #FFFFFF;
            width: 330mm;
            height: 215.9mm;
            margin: 65px auto 25px auto;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .piagam-sheet:last-child {
            page-break-after: auto;
        }

        /* Subtle Anti-Counterfeit Background Security Motif (Guilloche Mesh) */
        .piagam-sheet::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 2;
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(2, 132, 199, 0.02) 0%, transparent 65%),
                repeating-linear-gradient(45deg, rgba(15, 23, 42, 0.03) 0px, rgba(15, 23, 42, 0.03) 1px, transparent 1px, transparent 12px),
                repeating-linear-gradient(-45deg, rgba(15, 23, 42, 0.03) 0px, rgba(15, 23, 42, 0.03) 1px, transparent 1px, transparent 12px);
            opacity: 1;
        }

        /* Watermark Logo PASI Transparan */
        .piagam-sheet::after {
            content: '';
            position: absolute;
            top: 43%;
            left: 55%;
            transform: translate(-50%, -50%);
            width: 290px;
            height: 290px;
            background: url('<?= base_url("assets/images/pasi-logo.png") ?>') no-repeat center center;
            background-size: contain;
            opacity: 0.12;
            pointer-events: none;
            z-index: 2;
            filter: grayscale(100%);
        }

        .piagam-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
            pointer-events: none;
        }

        .piagam-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 4;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 18mm 20mm 14mm 80mm;
            border: 2px solid rgba(180, 83, 9, 0.08);
            outline: 1px solid rgba(15, 23, 42, 0.05);
            outline-offset: 3mm;
        }

        /* Micro-text security border - Bawah */
        .piagam-content::before {
            content: 'PASI KOTA PEKALONGAN 2026 • KEJUARAAN ATLETIK KELOMPOK UMUR • PASI KOTA PEKALONGAN 2026 • KEJUARAAN ATLETIK KELOMPOK UMUR • PASI KOTA PEKALONGAN 2026 • KEJUARAAN ATLETIK KELOMPOK UMUR • PASI KOTA PEKALONGAN 2026 • KEJUARAAN ATLETIK KELOMPOK UMUR • ';
            position: absolute;
            bottom: 8mm;
            left: 80mm;
            right: 15mm;
            font-size: 4pt;
            letter-spacing: 0.8px;
            color: rgba(15, 23, 42, 0.12);
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            overflow: hidden;
            white-space: nowrap;
            z-index: 5;
            pointer-events: none;
        }

        /* Micro-text security border - Atas */
        .piagam-content::after {
            content: 'PASI KOTA PEKALONGAN 2026 • PIAGAM PENGHARGAAN RESMI • PASI KOTA PEKALONGAN 2026 • PIAGAM PENGHARGAAN RESMI • PASI KOTA PEKALONGAN 2026 • PIAGAM PENGHARGAAN RESMI • ';
            position: absolute;
            top: 10mm;
            left: 80mm;
            right: 15mm;
            font-size: 4pt;
            letter-spacing: 0.8px;
            color: rgba(15, 23, 42, 0.10);
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            overflow: hidden;
            white-space: nowrap;
            z-index: 5;
            pointer-events: none;
        }

        /* Header Piagam */
        .header-title-box {
            text-align: center;
        }

        .instansi-title {
            font-family: 'Cinzel', 'Times New Roman', serif;
            font-size: 12.5pt;
            font-weight: 800;
            letter-spacing: 0.6px;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.25;
        }

        .piagam-award-title {
            font-family: 'Cinzel', serif;
            font-size: 22pt;
            font-weight: 900;
            letter-spacing: 2px;
            color: #b45309;
            text-shadow: 0 1px 2px rgba(180, 83, 9, 0.15);
            margin: 4px 0 2px 0;
            text-transform: uppercase;
        }

        .nomor-sertifikat {
            font-family: 'Inter', sans-serif;
            font-size: 11pt;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: 0.5px;
            margin: 0;
        }

        /* Body Teks */
        .kalimat-pembuka {
            font-family: 'Inter', sans-serif;
            font-size: 10pt;
            color: #334155;
            text-align: center;
            line-height: 1.35;
            margin: 4px auto 0 auto;
            max-width: 100%;
        }

        /* Section Data Atlet Pemenang */
        .pemenang-block {
            margin: 6px auto;
            width: 96%;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 4px 8px;
        }

        .table-pemenang-cert {
            width: 100%;
            border-collapse: collapse;
            font-size: 12pt;
        }

        .table-pemenang-cert td {
            padding: 2.5px 4px;
            vertical-align: middle;
        }

        .table-pemenang-cert .label-col {
            width: 22%;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
        }

        .table-pemenang-cert .colon-col {
            width: 2%;
            font-weight: 700;
            color: #334155;
            text-align: center;
        }

        .table-pemenang-cert .val-col {
            width: 73%;
            color: #0f172a;
            font-weight: 600;
        }

        .nama-pemenang-val {
            font-family: 'Cinzel', serif;
            font-size: 15pt !important;
            font-weight: 900 !important;
            color: #0f172a !important;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .juara-val {
            font-weight: 800 !important;
            color: #b45309 !important;
            font-size: 12.5pt !important;
        }

        /* Penutup */
        .kalimat-penutup {
            font-family: 'Inter', sans-serif;
            font-size: 10pt;
            font-weight: 500;
            color: #1e293b;
            text-align: center;
            line-height: 1.4;
            margin: 14mm auto 0 auto;
            max-width: 100%;
        }

        /* Footer Row (QR & TTD) */
        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: auto;
            padding: 0 5mm;
        }

        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgba(255, 255, 255, 0.85);
            padding: 6px;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        .qr-box {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-label {
            font-size: 7.5pt;
            font-weight: 600;
            color: #64748b;
            margin-top: 3px;
            text-align: center;
            letter-spacing: 0.3px;
        }

        .ttd-section {
            text-align: center;
            min-width: 300px;
        }

        .ttd-date {
            font-size: 11pt;
            font-weight: 600;
            color: #334155;
            margin-bottom: 3px;
            letter-spacing: 0.2px;
        }

        .ttd-jabatan {
            font-size: 12pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
            line-height: 1.3;
            letter-spacing: 0.3px;
        }

        .ttd-image-container {
            height: 125px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -6px 0 -14px 0;
            position: relative;
            z-index: 2;
        }

        .ttd-img {
            max-height: 130px;
            max-width: 310px;
            object-fit: contain;
            mix-blend-mode: multiply;
            filter: contrast(1.05) saturate(1.1);
            user-select: none;
            -webkit-user-drag: none;
            pointer-events: none;
            transform: scale(1.10);
        }

        .ttd-nama {
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 2px solid #0f172a;
            display: inline-block;
            padding-bottom: 2px;
            margin-top: 2px;
            letter-spacing: 0.3px;
        }

        /* DRAG MODE STYLES */
        .cert-drag-item {
            position: relative;
            transition: transform 0.05s ease;
        }

        body.drag-mode-active .cert-drag-item {
            cursor: grab !important;
            user-select: none !important;
        }

        body.drag-mode-active .cert-drag-item:hover {
            outline: 2.5px dashed #F59E0B !important;
            background-color: rgba(245, 158, 11, 0.08) !important;
            border-radius: 4px;
        }

        body.drag-mode-active .cert-drag-item.is-dragging {
            cursor: grabbing !important;
            outline: 3px solid #10B981 !important;
        }

        body.drag-mode-active .cert-drag-item.is-active-target {
            outline: 2.5px solid #06B6D4 !important;
            background-color: rgba(6, 182, 212, 0.1) !important;
            border-radius: 4px;
        }

        body.drag-mode-active .cert-drag-item::before {
            content: '🎯 ' attr(data-drag-name);
            position: absolute;
            top: -18px;
            left: 0;
            font-size: 8pt;
            background: #0f172a;
            color: #fff;
            padding: 1px 6px;
            border-radius: 3px;
            z-index: 100;
            white-space: nowrap;
            pointer-events: none;
            font-weight: 600;
        }

        /* Floating Toolbar for Drag Mode */
        .drag-floating-bar {
            position: fixed;
            top: 65px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9998;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(12px);
            padding: 8px 18px;
            border-radius: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: none !important;
                color: #000 !important;
            }

            .print-toolbar,
            #dragModeToolbar,
            .mm-grid-overlay {
                display: none !important;
            }

            body.drag-mode-active .cert-drag-item::before {
                display: none !important;
            }

            body.drag-mode-active .cert-drag-item {
                outline: none !important;
                background-color: transparent !important;
            }

            .piagam-sheet {
                margin: 0 !important;
                box-shadow: none !important;
                width: 330mm !important;
                height: 215.9mm !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
            }

            .piagam-sheet:last-child {
                page-break-after: auto !important;
            }
        }
    </style>
    <?php
    $kejurkotSavedOffsets = [];
    if (!empty($template['custom_offsets'])) {
        $decoded = json_decode($template['custom_offsets'], true);
        if (is_array($decoded)) {
            $kejurkotSavedOffsets = $decoded['umum'] ?? $decoded;
        }
    }
    ?>
    <style id="server-custom-offsets-style">
    <?php if (!empty($kejurkotSavedOffsets) && is_array($kejurkotSavedOffsets)): ?>
        <?php foreach ($kejurkotSavedOffsets as $k => $coord): ?>
            <?php 
            if (str_starts_with((string)$k, '__')) continue;
            $x = (int)($coord['x'] ?? 0);
            $y = (int)($coord['y'] ?? 0);
            if ($x !== 0 || $y !== 0):
            ?>
                .cert-drag-item[data-drag-key="<?= esc($k) ?>"] {
                    transform: translate(<?= $x ?>px, <?= $y ?>px) !important;
                }
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    </style>
</head>
<body>

    <!-- Floating Print Toolbar -->
    <div class="print-toolbar no-print">
        <a href="<?= base_url('piagam-kejurkot') ?>" class="btn btn-outline-light">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <span class="opacity-50">|</span>
        <button type="button" class="btn btn-outline-warning" id="btnToggleDrag" onclick="toggleDragMode()">
            <i class="bi bi-arrows-move"></i>
            <span id="dragBtnLabel">Mode Geser Elemen</span>
        </button>
        <span class="opacity-50">|</span>
        <span class="small fw-bold">
            <i class="bi bi-award text-warning me-1"></i> Piagam Kejurkot (<?= count($pemenang) ?> Lembar)
        </span>
        <span class="opacity-50">|</span>
        <button type="button" class="btn btn-warning text-dark fw-bold px-3 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer-fill"></i> Cetak Sekarang
        </button>
    </div>

    <!-- Floating Toolbar Khusus Saat Mode Geser Elemen Aktif -->
    <div id="dragModeToolbar" class="drag-floating-bar" style="display: none; max-width: 98vw; width: auto;">
        <div class="d-flex align-items-center gap-2 gap-md-3 flex-wrap justify-content-center">
            <div class="d-flex align-items-center gap-2 text-white">
                <span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="bi bi-arrows-move"></i> GESER AKTIF</span>
            </div>
            
            <div class="vr bg-secondary opacity-50 my-1 d-none d-md-block"></div>
            
            <!-- Element Selector Dropdown -->
            <div class="d-flex align-items-center gap-1">
                <span class="small text-white-50 d-none d-sm-inline">Elemen:</span>
                <select id="dragElementSelector" class="form-select form-select-sm bg-dark text-warning border-warning fw-bold py-1" style="min-width: 175px; max-width: 220px; font-size: 0.85rem;" onchange="selectDragElement(this.value)">
                    <option value="">-- Pilih Elemen --</option>
                    <option value="header">🏛️ Header Judul</option>
                    <option value="pembuka">📝 Kalimat Pembuka</option>
                    <option value="pemenang" selected>👤 Data Pemenang</option>
                    <option value="penutup">📄 Kalimat Penutup</option>
                    <option value="qr">📱 QR Code Verifikasi</option>
                    <option value="ttd">✍️ TTD & Stempel Ketua</option>
                </select>
            </div>

            <!-- Target Info & Posisi mm -->
            <div id="dragActiveInfo" class="badge bg-dark border border-secondary text-info px-2 py-1 small fw-bold font-monospace text-nowrap d-flex align-items-center gap-1" style="font-size: 0.82rem;">
                X: <span id="val_pos_x" class="text-white">0mm</span> | Y: <span id="val_pos_y" class="text-white">0mm</span>
            </div>

            <!-- Presisi Nudge mm -->
            <div class="d-flex align-items-center gap-1">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-light px-2 py-0 fw-bold" onclick="nudgeActiveElementMm(0, -1)" title="Naik 1mm (Panah Atas)">↑ 1mm</button>
                    <button type="button" class="btn btn-outline-light px-2 py-0 fw-bold" onclick="nudgeActiveElementMm(0, 1)" title="Turun 1mm (Panah Bawah)">↓ 1mm</button>
                    <button type="button" class="btn btn-outline-light px-2 py-0 fw-bold" onclick="nudgeActiveElementMm(-1, 0)" title="Kiri 1mm (Panah Kiri)">← 1mm</button>
                    <button type="button" class="btn btn-outline-light px-2 py-0 fw-bold" onclick="nudgeActiveElementMm(1, 0)" title="Kanan 1mm (Panah Kanan)">→ 1mm</button>
                </div>
                <div class="btn-group btn-group-sm d-none d-lg-inline-flex">
                    <button type="button" class="btn btn-outline-secondary text-white-50 px-1 py-0" onclick="nudgeActiveElementMm(0, -5)" title="Naik 5mm (Shift + Panah Atas)">↑ 5mm</button>
                    <button type="button" class="btn btn-outline-secondary text-white-50 px-1 py-0" onclick="nudgeActiveElementMm(0, 5)" title="Turun 5mm (Shift + Panah Bawah)">↓ 5mm</button>
                    <button type="button" class="btn btn-outline-secondary text-white-50 px-1 py-0" onclick="nudgeActiveElementMm(-5, 0)" title="Kiri 5mm (Shift + Panah Kiri)">← 5mm</button>
                    <button type="button" class="btn btn-outline-secondary text-white-50 px-1 py-0" onclick="nudgeActiveElementMm(5, 0)" title="Kanan 5mm (Shift + Panah Kanan)">→ 5mm</button>
                </div>
            </div>

            <div class="vr bg-secondary opacity-50 my-1 d-none d-md-block"></div>

            <!-- Toggle Grid mm -->
            <button type="button" id="btnToggleGrid" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" onclick="toggleMmGrid()" title="Tampilkan Garis Milimeter">
                <i class="bi bi-grid-3x3"></i> <span class="d-none d-xl-inline">Grid</span> mm
            </button>

            <div class="vr bg-secondary opacity-50 my-1 d-none d-md-block"></div>

            <button type="button" class="btn btn-sm btn-success fw-bold px-3 rounded-pill shadow-sm" onclick="saveCustomDragOffsets(event)">
                <i class="bi bi-check2-circle me-1"></i> Simpan
            </button>

            <button type="button" class="btn btn-sm btn-outline-danger px-2 rounded-pill" onclick="resetCustomDragOffsets()" title="Kembalikan semua posisi ke default">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>

            <button type="button" class="btn btn-sm btn-secondary px-2 rounded-pill" onclick="toggleDragMode(false)">
                Selesai
            </button>
        </div>
    </div>

    <!-- Halaman Piagam Loop -->
    <?php foreach ($pemenang as $idx => $p): 
        $rank = (int)($p['peringkat'] ?? 1);
        $rankTeks = $p['peringkat_teks'] ?? '';
        if (empty($rankTeks)) {
            $map = [1 => 'Pertama', 2 => 'Kedua', 3 => 'Ketiga', 4 => 'Keempat', 5 => 'Kelima'];
            $rankTeks = $map[$rank] ?? "Ke-{$rank}";
        }

        $romawiMap = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];
        $romawi = $romawiMap[$rank] ?? (string)$rank;

       $nomorSertifikat = '001/SERT/PASI.KOTA/VIII/2026';
        $uuid = $p['uuid'] ?: ('KEJURKOT-' . $p['id']);
        $verifyUrl = base_url('verifikasi/piagam/' . $uuid);
    ?>
    <div class="piagam-sheet">
        <!-- Background Trophy PASI Biru-Emas -->
        <img src="<?= base_url('uploads/piagam/templates/' . $bgTemplateImage . '?v=' . time()) ?>" class="piagam-bg" alt="Background Piagam">

        <div class="piagam-content">
            <!-- Header Judul -->
            <div class="header-title-box cert-drag-item" data-drag-key="header" data-drag-name="Header Judul">
                <h1 class="instansi-title">
                    KEJUARAAN ATLETIK KELOMPOK UMUR<br>TINGKAT KOTA PEKALONGAN TAHUN 2026
                </h1>
                <div class="piagam-award-title">PIAGAM PENGHARGAAN</div>
                <div class="nomor-sertifikat">NO : <?= esc($nomorSertifikat) ?></div>
            </div>

            <!-- Kalimat Pembuka -->
            <div class="kalimat-pembuka cert-drag-item" data-drag-key="pembuka" data-drag-name="Kalimat Pembuka">
                Pengurus Kota Persatuan Atletik Seluruh Indonesia (PASI) Kota Pekalongan<br>
                dengan ini memberikan Piagam Penghargaan kepada :
            </div>

            <!-- Data Pemenang -->
            <div class="pemenang-block cert-drag-item" data-drag-key="pemenang" data-drag-name="Data Pemenang">
                <table class="table-pemenang-cert">
                    <tr>
                        <td class="label-col">Nama</td>
                        <td class="colon-col">:</td>
                        <td class="val-col nama-pemenang-val"><?= esc($p['nama_pemenang']) ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Nomor Lomba</td>
                        <td class="colon-col">:</td>
                        <td class="val-col"><?php 
                            $lombaClean = $p['nomor_lomba'] ?: '-';
                            // Bersihkan awalan nomor acara seperti "1. 5000M" -> "5000m" / "5000M"
                            if (preg_match('/^\d+\.\s*(.+)$/', $lombaClean, $mLomba)) {
                                $lombaClean = $mLomba[1];
                            }
                            echo esc($lombaClean);
                        ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Sebagai Juara</td>
                        <td class="colon-col">:</td>
                        <td class="val-col juara-val">Juara <?= $romawi ?> ( <?= esc($rankTeks) ?> )</td>
                    </tr>
                    <tr>
                        <td class="label-col">Prestasi</td>
                        <td class="colon-col">:</td>
                        <td class="val-col"><?= esc($p['prestasi'] ?: '-') ?></td>
                    </tr>
                </table>
            </div>

            <!-- Kalimat Penutup -->
            <div class="kalimat-penutup cert-drag-item" data-drag-key="penutup" data-drag-name="Kalimat Penutup">
                Dalam kejuaraan Atletik Kelompok Umur tingkat Kota Pekalongan yang diselenggarakan<br>
                tanggal 29 Agustus 2026 di Stadion Hoegeng Kota Pekalongan
            </div>

            <!-- Bagian Bawah: QR Code (Kiri) & TTD Pak Zaeni (Kanan) -->
            <div class="footer-row">
                <!-- QR Code Pojok Kiri Bawah -->
                <div class="qr-section cert-drag-item" data-drag-key="qr" data-drag-name="QR Code">
                    <div id="qrcode-<?= $idx ?>" class="qr-box" data-url="<?= esc($verifyUrl) ?>"></div>
                    <div class="qr-label">Verifikasi Resmi PASI</div>
                </div>

                <!-- Tanda Tangan Pak Zaeni Pojok Kanan Bawah -->
                <div class="ttd-section cert-drag-item" data-drag-key="ttd" data-drag-name="Tanda Tangan">
                    <div class="ttd-date">Pekalongan, 29 Agustus 2026</div>
                    <div class="ttd-jabatan">Ketua PASI</div>
                    <div class="ttd-image-container">
                        <img src="<?= base_url('assets/images/ttd_zaini_pasi_pekalongan.png?v=' . time()) ?>" alt="TTD Zaini, S.Pd." class="ttd-img">
                    </div>
                    <div class="ttd-nama">Zaini, S.Pd.</div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Library QR Code JS -->
    <script src="<?= base_url('assets/js/qrcode.min.js') ?>"></script>
    <script>
    const currentTemplateId = '<?= (int)($template['id'] ?? 23) ?>';
    const serverSavedOffsets = <?= !empty($template['custom_offsets']) ? $template['custom_offsets'] : '{}' ?>;
    let customOffsets = {};
    let isDragMode = false;
    let activeDragKey = 'pemenang';
    const PX_PER_MM = 3.779527559;

    function pxToMm(px) {
        return Math.round(((px || 0) / PX_PER_MM) * 10) / 10;
    }

    function mmToPx(mm) {
        return Math.round((mm || 0) * PX_PER_MM);
    }

    function updateDragCoordinateDisplay() {
        if (!activeDragKey) return;
        const cur = customOffsets[activeDragKey] || { x: 0, y: 0 };
        const curX = cur.x || 0;
        const curY = cur.y || 0;
        const mmX = pxToMm(curX);
        const mmY = pxToMm(curY);

        const elX = document.getElementById('val_pos_x');
        const elY = document.getElementById('val_pos_y');
        if (elX) elX.textContent = `${mmX > 0 ? '+' : ''}${mmX}mm (${curX > 0 ? '+' : ''}${curX}px)`;
        if (elY) elY.textContent = `${mmY > 0 ? '+' : ''}${mmY}mm (${curY > 0 ? '+' : ''}${curY}px)`;
    }

    function setActiveDragElement(key) {
        if (!key) return;
        activeDragKey = key;

        document.querySelectorAll('.cert-drag-item').forEach(el => {
            if (el.getAttribute('data-drag-key') === key) {
                el.classList.add('is-active-target');
            } else {
                el.classList.remove('is-active-target');
            }
        });

        const sel = document.getElementById('dragElementSelector');
        if (sel && sel.value !== key) {
            sel.value = key;
        }

        updateDragCoordinateDisplay();
    }

    function selectDragElement(key) {
        if (!key) return;
        setActiveDragElement(key);

        const target = document.querySelector(`.cert-drag-item[data-drag-key="${key}"]`);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function applyAllCustomOffsets() {
        for (const [key, offset] of Object.entries(customOffsets)) {
            if (!offset || key.startsWith('__')) continue;
            const x = offset.x || 0;
            const y = offset.y || 0;
            document.querySelectorAll(`.cert-drag-item[data-drag-key="${key}"]`).forEach(el => {
                el.style.setProperty('transform', `translate(${x}px, ${y}px)`, 'important');
            });
        }
        updateDragCoordinateDisplay();
    }

    function toggleDragMode(forceState) {
        if (typeof forceState === 'boolean') {
            isDragMode = forceState;
        } else {
            isDragMode = !isDragMode;
        }

        const body = document.body;
        const bar = document.getElementById('dragModeToolbar');
        const btn = document.getElementById('btnToggleDrag');

        if (isDragMode) {
            body.classList.add('drag-mode-active');
            if (bar) bar.style.display = 'block';
            if (btn) {
                btn.classList.remove('btn-outline-warning');
                btn.classList.add('btn-warning', 'text-dark', 'fw-bold');
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Selesai Geser';
            }
            if (!activeDragKey) {
                activeDragKey = 'pemenang';
            }
            setActiveDragElement(activeDragKey);
        } else {
            body.classList.remove('drag-mode-active');
            if (bar) bar.style.display = 'none';
            if (btn) {
                btn.classList.remove('btn-warning', 'text-dark');
                btn.classList.add('btn-outline-warning');
                btn.innerHTML = '<i class="bi bi-arrows-move text-warning"></i> <span id="dragBtnLabel">Mode Geser Elemen</span>';
            }
            document.querySelectorAll('.cert-drag-item').forEach(el => {
                el.classList.remove('is-active-target', 'is-dragging');
            });
        }
    }

    function initDraggableItems() {
        let draggedElement = null;
        let dragKey = null;
        let startX = 0, startY = 0;
        let initTranslateX = 0, initTranslateY = 0;

        function onPointerDown(e) {
            if (!isDragMode) return;
            const target = e.target.closest('.cert-drag-item');
            if (!target) return;

            e.stopPropagation();

            draggedElement = target;
            dragKey = target.getAttribute('data-drag-key');
            setActiveDragElement(dragKey);

            startX = e.clientX !== undefined ? e.clientX : 0;
            startY = e.clientY !== undefined ? e.clientY : 0;

            const current = customOffsets[dragKey] || { x: 0, y: 0 };
            initTranslateX = current.x || 0;
            initTranslateY = current.y || 0;

            draggedElement.classList.add('is-dragging');
            if (target.setPointerCapture && e.pointerId) {
                try { target.setPointerCapture(e.pointerId); } catch(err) {}
            }
            if (e.cancelable) e.preventDefault();

            window.addEventListener('pointermove', onPointerMove, { passive: false });
            window.addEventListener('pointerup', onPointerUp);
            window.addEventListener('pointercancel', onPointerUp);
        }

        function onPointerMove(e) {
            if (!draggedElement) return;

            const curX = e.clientX !== undefined ? e.clientX : 0;
            const curY = e.clientY !== undefined ? e.clientY : 0;

            const dx = Math.round(curX - startX);
            const dy = Math.round(curY - startY);

            const newX = initTranslateX + dx;
            const newY = initTranslateY + dy;

            customOffsets[dragKey] = { x: newX, y: newY };

            document.querySelectorAll(`.cert-drag-item[data-drag-key="${dragKey}"]`).forEach(el => {
                el.style.setProperty('transform', `translate(${newX}px, ${newY}px)`, 'important');
            });

            updateDragCoordinateDisplay();

            if (e.cancelable) e.preventDefault();
        }

        function onPointerUp(e) {
            if (!draggedElement) return;

            draggedElement.classList.remove('is-dragging');
            if (draggedElement.releasePointerCapture && e.pointerId) {
                try { draggedElement.releasePointerCapture(e.pointerId); } catch(err) {}
            }

            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('pointerup', onPointerUp);
            window.removeEventListener('pointercancel', onPointerUp);

            draggedElement = null;
            dragKey = null;

            // Auto-persist immediately on drag release!
            persistCustomOffsetsLocally();
            scheduleAutoSaveToServer();
        }

        document.querySelectorAll('.cert-drag-item').forEach(item => {
            item.addEventListener('pointerdown', onPointerDown);
        });
    }

    function nudgeActiveElementMm(dmmX, dmmY) {
        if (!activeDragKey) {
            setActiveDragElement('pemenang');
        }
        const dpxX = mmToPx(dmmX);
        const dpxY = mmToPx(dmmY);
        nudgeActiveElementPx(dpxX, dpxY);
    }

    function nudgeActiveElementPx(dx, dy) {
        if (!activeDragKey) {
            setActiveDragElement('pemenang');
        }
        const cur = customOffsets[activeDragKey] || { x: 0, y: 0 };
        const newX = (cur.x || 0) + dx;
        const newY = (cur.y || 0) + dy;
        customOffsets[activeDragKey] = { x: newX, y: newY };

        document.querySelectorAll(`.cert-drag-item[data-drag-key="${activeDragKey}"]`).forEach(el => {
            el.style.setProperty('transform', `translate(${newX}px, ${newY}px)`, 'important');
        });

        updateDragCoordinateDisplay();
        persistCustomOffsetsLocally();
        scheduleAutoSaveToServer();
    }

    function toggleMmGrid() {
        const existing = document.querySelectorAll('.mm-grid-overlay');
        const btn = document.getElementById('btnToggleGrid');
        if (existing.length > 0) {
            existing.forEach(el => el.remove());
            if (btn) {
                btn.classList.remove('btn-info', 'text-dark', 'fw-bold');
                btn.classList.add('btn-outline-info');
            }
            return;
        }

        document.querySelectorAll('.piagam-sheet').forEach(sheet => {
            const grid = document.createElement('div');
            grid.className = 'mm-grid-overlay';
            grid.style.cssText = `
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                width: 100%; height: 100%;
                pointer-events: none;
                z-index: 999;
                background-image: 
                    linear-gradient(to right, rgba(2, 132, 199, 0.25) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(2, 132, 199, 0.25) 1px, transparent 1px),
                    linear-gradient(to right, rgba(2, 132, 199, 0.6) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(2, 132, 199, 0.6) 1px, transparent 1px);
                background-size: 
                    3.78px 3.78px,
                    3.78px 3.78px,
                    37.8px 37.8px,
                    37.8px 37.8px;
            `;
            sheet.appendChild(grid);
        });

        if (btn) {
            btn.classList.remove('btn-outline-info');
            btn.classList.add('btn-info', 'text-dark', 'fw-bold');
        }
    }

    function persistCustomOffsetsLocally() {
        const storageKey = 'piagam_offsets_kejurkot_' + currentTemplateId;
        try {
            localStorage.setItem(storageKey, JSON.stringify(customOffsets));
        } catch(e) {}
    }

    let autoSaveTimer = null;
    function scheduleAutoSaveToServer() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            let fullOffsets = {};
            if (serverSavedOffsets && typeof serverSavedOffsets === 'object') {
                const rel = serverSavedOffsets.umum || serverSavedOffsets;
                fullOffsets = Object.assign({}, rel);
            }
            fullOffsets = Object.assign(fullOffsets, customOffsets);

            const payload = new FormData();
            payload.append('template_id', currentTemplateId);
            payload.append('custom_offsets', JSON.stringify(fullOffsets));
            fetch('<?= base_url('piagam/update-template-position') ?>', {
                method: 'POST',
                body: payload
            }).then(r => r.json()).then(data => {
                if (data && data.status === 'success') {
                    if (serverSavedOffsets && typeof serverSavedOffsets === 'object') {
                        Object.assign(serverSavedOffsets, fullOffsets);
                    }
                }
            }).catch(err => {
                console.warn('Auto-save background sync failed, local copy intact:', err);
            });
        }, 600);
    }

    function saveCustomDragOffsets(evt) {
        const ev = evt || window.event;
        const btn = (ev && ev.target) ? ev.target.closest('button') : document.querySelector('button[onclick*="saveCustomDragOffsets"]');
        const orig = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        }

        let fullOffsets = {};
        if (serverSavedOffsets && typeof serverSavedOffsets === 'object') {
            const rel = serverSavedOffsets.umum || serverSavedOffsets;
            fullOffsets = Object.assign({}, rel);
        }
        fullOffsets = Object.assign(fullOffsets, customOffsets);

        persistCustomOffsetsLocally();

        const payload = new FormData();
        payload.append('template_id', currentTemplateId);
        payload.append('custom_offsets', JSON.stringify(fullOffsets));

        fetch('<?= base_url('piagam/update-template-position') ?>', {
            method: 'POST',
            body: payload
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.status === 'success') {
                if (serverSavedOffsets && typeof serverSavedOffsets === 'object') {
                    Object.assign(serverSavedOffsets, fullOffsets);
                }
            }
            if (btn) {
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Posisi Tersimpan!';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-light', 'text-success');
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.classList.remove('btn-light', 'text-success');
                    btn.classList.add('btn-success');
                    btn.disabled = false;
                }, 1800);
            }
        })
        .catch(err => {
            console.error('Gagal simpan ke DB:', err);
            if (btn) {
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Tersimpan di Browser!';
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.disabled = false;
                }, 1800);
            }
        });
    }

    function resetCustomDragOffsets() {
        if (!confirm('Kembalikan seluruh posisi elemen ke posisi default awal?')) return;
        const storageKey = 'piagam_offsets_kejurkot_' + currentTemplateId;

        customOffsets = {};
        try {
            localStorage.removeItem(storageKey);
        } catch(e) {}

        document.querySelectorAll('.cert-drag-item').forEach(el => {
            el.style.removeProperty('transform');
            el.style.transform = '';
        });

        const payload = new FormData();
        payload.append('template_id', currentTemplateId);
        payload.append('custom_offsets', JSON.stringify({}));
        fetch('<?= base_url('piagam/update-template-position') ?>', {
            method: 'POST',
            body: payload
        });

        updateDragCoordinateDisplay();
    }

    window.addEventListener('keydown', (e) => {
        if (!isDragMode) return;
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
            if (document.activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                return;
            }
            e.preventDefault();
            const step = e.shiftKey ? 5 : 1;
            if (e.key === 'ArrowUp') nudgeActiveElementMm(0, -step);
            if (e.key === 'ArrowDown') nudgeActiveElementMm(0, step);
            if (e.key === 'ArrowLeft') nudgeActiveElementMm(-step, 0);
            if (e.key === 'ArrowRight') nudgeActiveElementMm(step, 0);
        }
    });

    function loadSavedCustomOffsets() {
        try {
            let loadedOffsets = {};
            if (serverSavedOffsets && typeof serverSavedOffsets === 'object') {
                const rel = serverSavedOffsets.umum || serverSavedOffsets;
                if (rel && typeof rel === 'object') {
                    loadedOffsets = Object.assign({}, rel);
                }
            }
            const storageKey = 'piagam_offsets_kejurkot_' + currentTemplateId;
            const localRaw = localStorage.getItem(storageKey);
            if (localRaw) {
                try {
                    const localParsed = JSON.parse(localRaw);
                    if (localParsed && typeof localParsed === 'object') {
                        // Merge local changes on top of server baseline
                        loadedOffsets = Object.assign({}, loadedOffsets, localParsed);
                    }
                } catch(err) {}
            }
            customOffsets = loadedOffsets;
            try {
                localStorage.setItem(storageKey, JSON.stringify(customOffsets));
            } catch(e) {}
            applyAllCustomOffsets();
        } catch(e) {
            console.warn('Gagal load offsets:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const qrBoxes = document.querySelectorAll('.qr-box');
        qrBoxes.forEach(box => {
            const url = box.dataset.url;
            if (url) {
                new QRCode(box, {
                    text: url,
                    width: 100,
                    height: 100,
                    colorDark: "#0f172a",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        });

        initDraggableItems();
        loadSavedCustomOffsets();
    });
    </script>
</body>
</html>
