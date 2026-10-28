<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Cetak Piagam Penghargaan') ?></title>

    <link rel="icon" type="image/png" href="<?= base_url('assets/images/pasi-logo.png') ?>">

    <!-- Google Fonts untuk Tipografi Sertifikat Bergengsi & Canva Style -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cinzel:wght@700;800;900&family=Great+Vibes&family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700&family=Montserrat:wght@600;700;800;900&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* ========================================================= */
        /* RESET & PRINT SETUP (A4 EXACT mm)                        */
        /* ========================================================= */
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
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

        body {
            margin: 0;
            padding: 0;
            background-color: #3b4252;
            font-family: 'Inter', sans-serif;
            color: #111827;
        }

        <?php 
        $isLoggedIn       = (session()->get('logged_in') === true) || (session()->get('isLoggedIn') === true);
        $isPublic         = !empty($isPublic) || !$isLoggedIn;
        $canEdit          = !$isPublic;
        $editableAttr     = $canEdit ? 'contenteditable="true"' : 'contenteditable="false"';
        $currentOrientasi = $template['orientasi'] ?? 'landscape';
        $currentTipe      = $template['tipe'] ?? 'overlay';
        $isLandscape      = ($currentOrientasi === 'landscape');
        ?>

        /* Mode Publik & Keamanan Anti-Edit / Tamper Protection */
        body.is-public-view .cert-editable-field,
        .cert-editable-field[contenteditable="false"] {
            cursor: default !important;
            outline: none !important;
            background: transparent !important;
            box-shadow: none !important;
            pointer-events: none !important;
            user-select: text;
        }
        body.is-public-view .cert-drag-item {
            cursor: default !important;
            user-select: text !important;
            pointer-events: auto !important;
        }
        body.is-public-view .cert-drag-item:hover,
        body.is-public-view .cert-drag-item.is-dragging,
        body.is-public-view .cert-drag-item.is-active-target {
            outline: none !important;
            background-color: transparent !important;
            box-shadow: none !important;
        }
        body.is-public-view .cert-drag-item::before {
            display: none !important;
        }
        body.is-public-view #modalDirectEditData,
        body.is-public-view #editDataPanel,
        body.is-public-view #btnToggleEditData,
        body.is-public-view #btnToggleDrag,
        body.is-public-view #btnToggleAdjuster,
        body.is-public-view #dragModeToolbar,
        body.is-public-view #adjusterPanel,
        body.is-public-view .drag-floating-bar {
            display: none !important;
        }

        body.drag-mode-active .cert-drag-item {
            cursor: grab !important;
            user-select: none !important;
            pointer-events: auto !important;
        }
        body.drag-mode-active .cert-drag-item.is-dragging {
            cursor: grabbing !important;
        }
        body.drag-mode-active .cert-drag-item img {
            pointer-events: none !important;
            -webkit-user-drag: none !important;
            user-select: none !important;
        }

        /* Subtle Anti-Counterfeit Background Security Motif (Guilloche Mesh) */
        .piagam-sheet::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 4;
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(2, 132, 199, 0.015) 0%, transparent 65%),
                repeating-linear-gradient(45deg, rgba(15, 23, 42, 0.007) 0px, rgba(15, 23, 42, 0.007) 1px, transparent 1px, transparent 14px),
                repeating-linear-gradient(-45deg, rgba(15, 23, 42, 0.007) 0px, rgba(15, 23, 42, 0.007) 1px, transparent 1px, transparent 14px);
            opacity: 0.9;
        }

        @page {
            size: <?= $isLandscape ? '330mm 215mm' : '215mm 330mm' ?>;
            margin: 0mm !important;
        }

        /* Container lembar halaman piagam (Default F4: 215 x 330 mm) */
        .piagam-sheet {
            position: relative;
            background-color: #FFFFFF;
            margin: 70px auto 25px auto;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            page-break-after: always;
            page-break-inside: avoid;
            <?= $isLandscape ? 'width: 330mm; height: 215mm;' : 'width: 215mm; height: 330mm;' ?>
        }

        .piagam-sheet:last-child {
            page-break-after: auto;
        }

        /* Floating Toolbar */
        .print-toolbar {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(12px);
            padding: 8px 18px;
            border-radius: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
        }

        @media print {
            @page {
                size: <?= $isLandscape ? '330mm 215mm' : '215mm 330mm' ?>;
                margin: 0mm !important;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: none !important;
            }
            .print-toolbar,
            .adjuster-floating-panel,
            .drag-floating-bar,
            .mm-grid-overlay,
            #toolbarContainer,
            #debug-icon,
            #debug-bar,
            .ci-debug-toolbar {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
            }
            .piagam-sheet {
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
        }

        .adjuster-floating-panel {
            position: fixed;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            max-width: 95vw;
        }

        /* ========================================================= */
        /* MODE 1: OVERLAY RESMI PASI (CLEAN BACKGROUND IMAGE)       */
        /* Format & Tanda Tangan disesuaikan dengan Portrait Resmi   */
        /* ========================================================= */
        .overlay-bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: fill;
            z-index: 1;
        }

        .overlay-content-wrapper {
            position: absolute;
            top: <?= ($isLandscape ? 25 : 30) + (int)($template['offset_top'] ?? 0) ?>mm;
            left: 12mm;
            right: 12mm;
            bottom: <?= 50 + (int)($template['offset_ttd'] ?? 0) ?>mm;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            text-align: center;
            box-sizing: border-box;
            transition: top 0.15s ease, bottom 0.15s ease;
        }

        .overlay-signatures-triple {
            position: absolute;
            left: 12mm;
            right: 12mm;
            bottom: <?= 12 + (int)($template['offset_ttd'] ?? 0) ?>mm;
            z-index: 15;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            box-sizing: border-box;
            padding: 0 !important;
            transition: bottom 0.15s ease;
        }

        .overlay-sig-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0 4px;
        }

        .overlay-sig-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            line-height: 1.25;
            min-height: 28px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .overlay-sig-img-box {
            height: <?= $isLandscape ? '105px' : '120px' ?>;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: <?= $isLandscape ? '-8px 0 -8px 0' : '-14px 0 -12px 0' ?>;
            position: relative;
            z-index: 3;
            pointer-events: none;
        }

        .overlay-sig-img {
            height: <?= $isLandscape ? '105px' : '120px' ?>;
            width: auto;
            max-height: <?= $isLandscape ? '105px' : '120px' ?>;
            max-width: 250px;
            object-fit: contain;
            pointer-events: none;
            mix-blend-mode: multiply;
            filter: contrast(1.04) saturate(1.1);
        }

        .overlay-sig-img-thick {
            height: <?= $isLandscape ? '105px' : '120px' ?>;
            width: auto;
            max-height: <?= $isLandscape ? '105px' : '120px' ?>;
            max-width: 250px;
            filter: contrast(1.08) saturate(1.15);
        }

        .overlay-sig-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
            margin-top: 0;
            border-bottom: 1px solid #111827;
            padding: 0 4px 1px 4px;
            display: inline-block;
            white-space: nowrap;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        /* Penyesuaian Responsif Portrait vs Landscape untuk Custom Background */
        <?php if (!$isLandscape): ?>
        .overlay-title-text {
            font-size: 32px !important;
            letter-spacing: 2px !important;
        }
        .overlay-intro-p {
            font-size: 12.5px !important;
            margin-bottom: 2mm !important;
            max-width: 100% !important;
        }
        <?php endif; ?>

        .overlay-top-header {
            text-align: center;
            margin-bottom: 2mm;
        }

        .overlay-header-line {
            font-family: 'Montserrat', sans-serif;
            font-size: 14.5px;
            font-weight: 800;
            letter-spacing: 1.2px;
            color: #111827;
            line-height: 1.3;
            text-transform: uppercase;
        }

        .overlay-title-box {
            text-align: center;
            margin-bottom: 2mm;
        }

        .overlay-title-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 3.5px;
            color: #0A2540;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .overlay-nomor-line {
            font-family: 'Montserrat', sans-serif;
            font-size: 15.5px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #004B87;
            margin-top: 2mm;
            text-transform: uppercase;
        }

        /* --- THEME VARIANT: PORTRAIT PRESTIGE (ROYAL NAVY & GOLD) --- */
        .theme-portrait_prestige .portrait-banner-title {
            color: #F8E7B9 !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.6), 0 0 14px rgba(245,158,11,0.35) !important;
        }
        .theme-portrait_prestige .portrait-banner-nomor {
            color: #FDE68A !important;
        }
        .theme-portrait_prestige .portrait-header-line {
            color: #071E3D !important;
        }
        .theme-portrait_prestige .portrait-banner-logo {
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.35));
        }
        .theme-portrait_prestige .val-nama {
            color: #071E3D !important;
        }
        .theme-portrait_prestige .portrait-prestige-juara-badge {
            display: inline-block;
            background: linear-gradient(135deg, #0A2540 0%, #16213E 100%);
            color: #FDE68A;
            font-weight: 900;
            padding: 3px 12px;
            border-radius: 4px;
            border: 1.5px solid #D4AF37;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.18);
        }
        .theme-portrait_prestige .portrait-six-dots-col .dot {
            background-color: #D4AF37 !important;
        }

        /* --- CANVA ADAPTIVE PRECISION ZONES (RELATIVE TO FULL 297mm SHEET) --- */
        .canva-zone-nomor {
            position: absolute;
            top: calc(28.5% + <?= (int)($template['offset_top'] ?? 0) ?>mm);
            left: 0;
            right: 0;
            text-align: center;
            font-family: 'Space Grotesk', 'Inter', monospace;
            font-size: 11.5px;
            font-weight: 700;
            color: #1E293B;
            letter-spacing: 1.5px;
            z-index: 15;
            transition: top 0.15s ease;
        }

        .canva-zone-athlete {
            position: absolute;
            top: calc(40.5% + <?= (int)($template['offset_top'] ?? 0) ?>mm);
            left: 5%;
            right: 5%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 15;
            transition: top 0.15s ease;
        }

        .canva-athlete-name {
            font-family: 'Cinzel', 'Montserrat', serif;
            font-weight: 800;
            color: #0A2540;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 5px;
            text-shadow: 0 1px 2px rgba(255,255,255,0.85);
        }

        .canva-athlete-origin {
            font-family: 'Inter', sans-serif;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            letter-spacing: 0.3px;
        }

        .canva-zone-ribbon {
            position: absolute;
            top: calc(60.2% + <?= (int)($template['offset_top'] ?? 0) ?>mm);
            left: 14%;
            right: 14%;
            height: 3.8%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-family: 'Cinzel', 'Montserrat', serif;
            font-size: 18px;
            font-weight: 900;
            color: #0A2540;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-shadow: 0 1px 1px rgba(255,255,255,0.9);
            z-index: 15;
            pointer-events: none;
            transition: top 0.15s ease;
        }

        .canva-zone-event {
            position: absolute;
            top: calc(65.8% + <?= (int)($template['offset_top'] ?? 0) ?>mm);
            left: 5%;
            right: 5%;
            text-align: center;
            font-family: 'Montserrat', 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: 0.5px;
            z-index: 15;
            transition: top 0.15s ease;
        }

        .canva-zone-statement {
            position: absolute;
            top: calc(70.0% + <?= (int)($template['offset_top'] ?? 0) ?>mm);
            left: 8%;
            right: 8%;
            text-align: center;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            line-height: 1.45;
            color: #334155;
            z-index: 15;
            transition: top 0.15s ease;
        }

        /* --- SIGNATURES BOTTOM LIFT FOR OVERLAY BACKGROUNDS --- */
        .theme-overlay .overlay-signatures-triple {
            position: absolute !important;
            left: 10mm !important;
            right: 10mm !important;
            bottom: <?= ($isLandscape ? 12 : 20) + (int)($template['offset_ttd'] ?? 0) ?>mm !important;
            width: auto !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 4mm !important;
            z-index: 25;
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-end !important;
        }

        /* --- INTERACTIVE DRAG MODE (MODE GESER ELEMEN) --- */
        body.drag-mode-active .cert-drag-item {
            cursor: grab !important;
            user-select: none !important;
            outline: 2px dashed #0284C7 !important;
            outline-offset: 4px;
            background-color: rgba(2, 132, 199, 0.08) !important;
            border-radius: 6px;
            transition: outline 0.15s ease, background-color 0.15s ease;
            position: relative;
        }
        body.drag-mode-active .cert-drag-item:hover {
            outline: 2.5px solid #F59E0B !important;
            background-color: rgba(245, 158, 11, 0.14) !important;
            cursor: grab !important;
        }
        body.drag-mode-active .cert-drag-item.is-dragging {
            cursor: grabbing !important;
            outline: 3px solid #10B981 !important;
            background-color: rgba(16, 185, 129, 0.2) !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25) !important;
            z-index: 99999 !important;
            transition: none !important;
        }
        body.drag-mode-active .cert-drag-item::before {
            content: "🖐️ " attr(data-drag-name);
            position: absolute;
            top: -22px;
            left: 2px;
            background: #0F172A;
            color: #F8FAFC;
            font-size: 10px;
            font-family: system-ui, -apple-system, sans-serif;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 4px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 100000;
            opacity: 0.95;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        body.drag-mode-active .cert-drag-item.is-active-target {
            outline: 2.5px solid #06B6D4 !important;
            background-color: rgba(6, 182, 212, 0.18) !important;
            box-shadow: 0 0 14px rgba(6, 182, 212, 0.4) !important;
        }
        body.drag-mode-active .cert-drag-item.is-active-target::before {
            background: #0284C7;
            color: #FFFFFF;
            border-color: #38BDF8;
        }
        .mm-grid-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 9999;
        }
        .drag-floating-bar {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 50rem;
            padding: 8px 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            z-index: 100000;
            animation: fadeInSlideUp 0.25s ease-out;
        }
        @keyframes fadeInSlideUp {
            from { opacity: 0; transform: translate(-50%, 15px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
        /* Inline Direct Editing Teks Piagam Langsung */
        .cert-editable-field {
            cursor: text;
            transition: background-color 0.15s ease, outline 0.15s ease, box-shadow 0.15s ease;
            border-radius: 4px;
            padding: 1px 4px;
            display: inline-block;
        }
        .cert-editable-field:hover {
            outline: 1.5px dashed #0284C7;
            background-color: rgba(2, 132, 199, 0.08);
        }
        .cert-editable-field:focus {
            outline: 2px solid #0284C7 !important;
            background-color: rgba(2, 132, 199, 0.16) !important;
            box-shadow: 0 0 8px rgba(2, 132, 199, 0.35) !important;
        }

        @media print {
            .cert-editable-field {
                outline: none !important;
                background: transparent !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
            body.drag-mode-active .cert-drag-item {
                outline: none !important;
                background-color: transparent !important;
                box-shadow: none !important;
            }
            body.drag-mode-active .cert-drag-item::before {
                display: none !important;
            }
            #dragModeToolbar, #adjusterPanel, #editDataPanel, .drag-floating-bar, .mm-grid-overlay {
                display: none !important;
            }
        }

        .overlay-intro-p {
            font-family: 'Inter', sans-serif;
            font-size: 14.5px;
            line-height: 1.55;
            font-weight: 600;
            color: #1E293B;
            margin: 0 0 3.5mm 0;
            max-width: 960px;
            text-align: center;
        }

        .overlay-data-table {
            width: 100%;
            max-width: 820px;
            margin: 0 auto 4mm auto;
            border-collapse: collapse;
            text-align: left;
        }

        .overlay-data-table td {
            padding: 3.5px 10px;
            vertical-align: middle;
        }

        .overlay-data-table .td-label {
            font-family: 'Inter', sans-serif;
            font-size: 15.5px;
            font-weight: 700;
            color: #1E293B;
            width: 145px;
            white-space: nowrap;
        }

        .overlay-data-table .td-sep {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #1E293B;
            width: 20px;
            text-align: center;
        }

        .overlay-data-table .val-nama {
            font-family: 'Montserrat', sans-serif;
            font-size: <?= (int)($template['font_size_nama'] ?: 25) ?>px;
            font-weight: 900;
            color: #000000;
            letter-spacing: 0.5px;
        }

        /* --- FLEX DATA LIST UNTUK DRAG ELEMEN PER BARIS --- */
        .overlay-data-list {
            width: 100%;
            max-width: 820px;
            margin: 0 auto 4mm auto;
        }

        .overlay-data-row {
            display: flex;
            align-items: center;
            padding: 3.5px 10px;
            box-sizing: border-box;
            border-radius: 4px;
        }

        .overlay-data-row .lbl {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #1E293B;
            width: 145px;
            white-space: nowrap;
            flex-shrink: 0;
            text-align: left;
        }

        .overlay-data-row .sep {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #1E293B;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        /* --- KHUSUS USIA DINI 5, 6, 7 TH (TEMPLATE 22 & USIA DINI) --- */
        .piagam-sheet.is-usia-567.sheet-portrait .overlay-content-wrapper {
            top: 30mm;
            left: 48mm;
            right: 12mm;
            bottom: 25mm;
            align-items: center;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-top-header {
            margin-bottom: 4mm;
            margin-left: 0;
            text-align: center;
            width: 100%;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-header-line {
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1.5px;
            line-height: 1.35;
            color: #0b1a30;
            text-align: center;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-title-box {
            margin-bottom: 3.5mm;
            margin-left: 0;
            text-align: center;
            width: 100%;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-title-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 34px !important;
            font-weight: 900 !important;
            letter-spacing: 3px !important;
            color: #0b1a30 !important;
            margin-bottom: 1.5mm;
            text-align: center;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-nomor-line {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: #004085;
            text-align: center;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-intro-p {
            font-size: 14px !important;
            line-height: 1.45;
            width: 100%;
            max-width: 100%;
            margin-bottom: 4mm !important;
            margin-left: 0;
            text-align: center;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-data-list {
            display: inline-flex;
            flex-direction: column;
            width: auto;
            max-width: 100%;
            margin: 0 auto 4mm auto !important;
            padding: 0;
            box-sizing: border-box;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-data-row {
            padding: 4px 6px;
            display: flex;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-data-row .lbl {
            width: 125px;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            text-align: left;
            flex-shrink: 0;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-data-row .sep {
            width: 16px;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            text-align: center;
            flex-shrink: 0;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-data-row .val {
            font-size: 16.5px;
            white-space: nowrap;
            color: #000000;
            text-align: left;
            flex: 1;
            padding-left: 4px;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-data-row .val-nama {
            font-size: 26px;
            font-weight: 900;
            white-space: nowrap;
            letter-spacing: 0.5px;
            color: #000000;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .overlay-closing-p {
            font-size: 13px !important;
            line-height: 1.45;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            text-align: center;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .portrait-official-signature-center,
        .piagam-sheet.is-usia-567.sheet-portrait .sig-container-single-pekalongan {
            bottom: 5.5% !important;
            left: 58.5% !important;
            transform: translateX(-50%) !important;
            width: 400px !important;
            text-align: center;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .sig-unit-title,
        .piagam-sheet.is-usia-567.sheet-portrait .overlay-sig-title {
            font-size: 15.5px !important;
            line-height: 1.35 !important;
            text-align: center !important;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .sig-unit-name,
        .piagam-sheet.is-usia-567.sheet-portrait .overlay-sig-name {
            font-size: 16.5px !important;
            margin-top: 0 !important;
            text-align: center !important;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .sig-unit-img,
        .piagam-sheet.is-usia-567.sheet-portrait .overlay-sig-img {
            height: 170px !important;
            max-height: 175px !important;
            width: auto !important;
            max-width: 380px !important;
            object-fit: contain !important;
            margin: 0 auto !important;
        }

        .piagam-sheet.is-usia-567.sheet-portrait .sig-unit-img-box,
        .piagam-sheet.is-usia-567.sheet-portrait .overlay-sig-img-box {
            height: 170px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: -14px auto -10px auto !important;
        }

        .overlay-data-row .val {
            flex: 1;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: #000000;
            text-transform: uppercase;
            text-align: left;
        }

        .overlay-data-row .val-nama {
            font-family: 'Montserrat', sans-serif;
            font-size: <?= (int)($template['font_size_nama'] ?: 25) ?>px;
            font-weight: 900;
            color: #000000;
            letter-spacing: 0.5px;
        }

        body.hide-data-labels .overlay-data-row .lbl,
        body.hide-data-labels .overlay-data-row .sep {
            display: none !important;
        }

        body.hide-data-labels .overlay-data-row .val {
            text-align: center !important;
        }

        body.hide-ttd-center .overlay-sig-col-center {
            display: none !important;
        }

        .overlay-data-table .td-val {
            font-family: 'Montserrat', sans-serif;
            font-size: 15.5px;
            font-weight: 800;
            color: #000000;
            text-transform: uppercase;
        }

        .overlay-closing-p {
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            line-height: 1.5;
            font-weight: 600;
            color: #1E293B;
            margin: 0 0 3mm 0;
            max-width: 940px;
            text-align: center;
        }

        .overlay-signatures-triple {
            position: absolute !important;
            left: 12mm;
            right: 12mm;
            bottom: <?= ($isLandscape ? 12 : 20) + (int)($template['offset_ttd'] ?? 0) ?>mm !important;
            z-index: 25;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            box-sizing: border-box;
            padding: 0 6mm !important;
        }

        .overlay-sig-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0 4px;
        }

        .overlay-sig-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            line-height: 1.25;
            min-height: 28px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .overlay-sig-img-box {
            height: <?= $isLandscape ? '105px' : '120px' ?>;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: <?= $isLandscape ? '-8px 0 -8px 0' : '-14px 0 -12px 0' ?>;
            position: relative;
            z-index: 3;
            pointer-events: none;
        }

        .overlay-sig-img {
            height: <?= $isLandscape ? '105px' : '120px' ?>;
            width: auto;
            max-height: <?= $isLandscape ? '105px' : '120px' ?>;
            max-width: 250px;
            object-fit: contain;
            pointer-events: none;
            mix-blend-mode: multiply;
            filter: contrast(1.04) saturate(1.1);
        }

        .overlay-sig-img-thick {
            height: <?= $isLandscape ? '105px' : '120px' ?>;
            width: auto;
            max-height: <?= $isLandscape ? '105px' : '120px' ?>;
            max-width: 250px;
            filter: contrast(1.08) saturate(1.15);
        }

        .overlay-sig-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
            margin-top: 0;
            border-bottom: 1px solid #111827;
            padding: 0 4px 1px 4px;
            display: inline-block;
            white-space: nowrap;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        /* ========================================================= */
        /* ========================================================= */
        /* MODE 2: PORTRAIT RESMI EKS-KARESIDENAN (EXACT VECTOR)     */
        /* Sesuai 100% Persis Scan Resmi PASI Jateng Eks-Kari        */
        /* ========================================================= */
        .portrait-frame {
            position: relative;
            width: 100%;
            height: 100%;
            background: #FFFFFF;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* SVG Background Layer: Crisp Vector Shapes & Frame Line */
        .portrait-bg-svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        /* Top Header: Instansi / Kejuaraan - Aligned with PIAGAM PENGHARGAAN */
        .portrait-top-header {
            position: absolute;
            top: 3.8%;
            left: calc(4.6% + 68px);
            right: 28%;
            z-index: 5;
            text-align: left;
        }

        .portrait-header-line {
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.6px;
            color: #111827;
            line-height: 1.25;
            text-transform: uppercase;
        }

        /* Gold Banner Text Overlay - Rata Horizontal */
        .portrait-gold-banner-overlay {
            position: absolute;
            top: 14.6%;
            left: 4.6%;
            width: 72.1%;
            height: 16.0%;
            z-index: 5;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-left: 55px;
            box-sizing: border-box;
            pointer-events: none;
        }

        .portrait-banner-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 38px;
            line-height: 1.04;
            letter-spacing: 2.2px;
            color: #0A2540;
            text-transform: uppercase;
        }

        .portrait-banner-nomor {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 15.5px;
            letter-spacing: 1.2px;
            color: #0A2540;
            margin-top: 8px;
            text-transform: uppercase;
        }

        /* Cyan Block: PASI Logo */
        .portrait-banner-pasi-box {
            position: absolute;
            top: 14.6%;
            left: 76.7%;
            width: 18.3%;
            height: 16.0%;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .portrait-banner-logo {
            width: 78%;
            max-width: 82px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
            pointer-events: none;
        }

        /* Body Content Text Area - Aligned with PIAGAM PENGHARGAAN */
        .portrait-content-body {
            position: absolute;
            top: 33.8%;
            left: calc(4.6% + 55px);
            width: 75.5%;
            z-index: 5;
        }

        .portrait-intro-p {
            font-family: 'Inter', sans-serif;
            font-size: 14.5px;
            line-height: 1.55;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 14px;
            max-width: 95%;
        }

        .portrait-data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 16px 0;
        }

        .portrait-data-table td {
            padding: 7px 2px;
            vertical-align: middle;
        }

        .portrait-data-table .td-label {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #1E293B;
            width: 215px;
            white-space: nowrap;
        }

        .portrait-data-table .td-sep {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: #1E293B;
            width: 20px;
            text-align: center;
        }

        .portrait-data-table .td-val {
            font-family: 'Montserrat', 'Inter', sans-serif;
            font-size: 15.5px;
            font-weight: 900;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .portrait-data-table .val-nama {
            font-size: 16.5px;
            color: #000000;
            letter-spacing: 0.5px;
        }

        .portrait-closing-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            margin-bottom: 6px;
        }

        .portrait-closing-p {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #1E293B;
            font-weight: 500;
            max-width: 82%;
            margin: 0;
        }

        .portrait-six-dots-col {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: center;
            margin-right: 14px;
        }

        .portrait-six-dots-col .dot {
            width: 6.5px;
            height: 6.5px;
            background-color: #111827;
            border-radius: 50%;
            display: block;
        }

        /* Official Centered Signature: Dr. Rumini, M.Pd. / Dr. Agus Widodo Suripto, MPd + Stempel PASI Jateng */
        .portrait-official-signature-center {
            position: absolute;
            bottom: 12.5%;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            text-align: center;
            z-index: 6;
        }

        .portrait-official-signature-center .sig-unit-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            line-height: 1.3;
            margin-bottom: -4px;
        }

        .portrait-official-signature-center .sig-unit-img-box {
            height: 135px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -18px 0 -16px 0;
        }

        .portrait-official-signature-center .sig-unit-img {
            max-height: 135px;
            max-width: 290px;
            object-fit: contain;
            pointer-events: none;
            mix-blend-mode: multiply;
            filter: contrast(1.18) saturate(1.25) brightness(0.96);
        }

        .portrait-official-signature-center .sig-unit-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 14.5px;
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
            margin-top: -2px;
        }

        /* Triple signature layout (Kiri: Korwil, Kanan: Panitia, Tengah Bawah: PASI Jateng - PERSIS GAMBAR 2) */
        .portrait-signatures-triple {
            position: absolute;
            bottom: 10%;
            left: 50%;
            transform: translateX(-50%);
            width: 88%;
            z-index: 6;
        }

        .portrait-signatures-triple .sig-top-pair {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0 4px;
            position: relative;
            z-index: 2;
            margin-bottom: 2px;
        }

        .portrait-signatures-triple .sig-unit {
            text-align: center;
            width: 245px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .portrait-signatures-triple .sig-unit-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 11.5px;
            font-weight: 700;
            color: #111827;
            line-height: 1.25;
            margin-bottom: 2px;
            min-height: 30px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .portrait-signatures-triple .sig-unit-img-box {
            height: 125px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -10px 0 -12px 0;
            position: relative;
            z-index: 3;
            pointer-events: none;
        }

        .portrait-signatures-triple .sig-unit-img {
            height: 125px;
            width: auto;
            max-height: 125px;
            max-width: 260px;
            object-fit: contain;
            pointer-events: none;
            mix-blend-mode: multiply;
            filter: contrast(1.05) saturate(1.15);
        }

        .portrait-signatures-triple .sig-unit-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 12.5px;
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
            margin-top: 2px;
            position: relative;
            z-index: 1;
            border-bottom: 1.5px solid #000;
            display: inline-block;
            padding-bottom: 1px;
        }

        .portrait-signatures-triple .sig-unit-center {
            width: 340px;
            margin: 2px auto 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .portrait-signatures-triple .sig-unit-center .sig-unit-title {
            min-height: 36px;
        }

        .portrait-signatures-triple .sig-unit-center .sig-unit-img-box {
            height: 135px;
            margin: -12px 0 -14px 0;
        }

        .portrait-signatures-triple .sig-unit-center .sig-unit-img {
            height: 135px;
            width: auto;
            max-height: 135px;
            max-width: 295px;
            mix-blend-mode: multiply;
            filter: contrast(1.08) saturate(1.2);
        }

        /* ========================================================= */
        /* MODE 3: MODERN CYBER GRID (A4 LANDSCAPE)                 */
        /* Tech, Athletic Grid, Cyan & Electric Gold                */
        /* ========================================================= */
        .cyber-grid-frame {
            position: relative;
            width: 100%;
            height: 100%;
            padding: 14mm 16mm;
            background: #FFFFFF;
        }

        .cyber-grid-canvas {
            position: relative;
            width: 100%;
            height: 100%;
            border: 3px solid #004B87;
            padding: 8mm 10mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: #FAFCFF;
            background-image: 
                linear-gradient(to right, rgba(0, 75, 135, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 75, 135, 0.05) 1px, transparent 1px);
            background-size: 20px 20px;
            box-shadow: inset 0 0 0 4px #FFFFFF, inset 0 0 0 6px #00D2FF;
        }

        /* Corner HUD Accents */
        .cyber-corner {
            position: absolute;
            width: 22px;
            height: 22px;
            border-color: #004B87;
            border-style: solid;
            pointer-events: none;
        }
        .cc-tl { top: -2px; left: -2px; border-width: 4px 0 0 4px; }
        .cc-tr { top: -2px; right: -2px; border-width: 4px 4px 0 0; }
        .cc-bl { bottom: -2px; left: -2px; border-width: 0 0 4px 4px; }
        .cc-br { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; }

        .cyber-crosshair {
            position: absolute;
            font-family: 'Space Grotesk', monospace;
            font-size: 10px;
            font-weight: 700;
            color: #00ADB5;
            letter-spacing: 1px;
        }
        .ch-tl { top: 6px; left: 8px; }
        .ch-tr { top: 6px; right: 8px; }
        .ch-bl { bottom: 6px; left: 8px; }
        .ch-br { bottom: 6px; right: 8px; }

        /* ========================================================= */
        /* MODE 4: ROYAL GUILLOCHE GOLD (A4 LANDSCAPE)              */
        /* Baroque Prestige Gold Double Border & Rosettes           */
        /* ========================================================= */
        .royal-guilloche-frame {
            position: relative;
            width: 100%;
            height: 100%;
            padding: 12mm 14mm;
            background: radial-gradient(ellipse at center, #FFFFFF 50%, #FAF6EE 100%);
        }

        .royal-guilloche-outer {
            position: relative;
            width: 100%;
            height: 100%;
            border: 4px solid #C5A059;
            padding: 4px;
            background: transparent;
        }

        .royal-guilloche-inner {
            position: relative;
            width: 100%;
            height: 100%;
            border: 2px solid #8C6D37;
            outline: 1px dashed #C5A059;
            outline-offset: 4px;
            padding: 7mm 10mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #FFFEFC;
        }

        .royal-rosette {
            position: absolute;
            font-size: 24px;
            color: #C5A059;
            line-height: 1;
        }
        .rr-tl { top: -2px; left: -2px; }
        .rr-tr { top: -2px; right: -2px; }
        .rr-bl { bottom: -2px; left: -2px; }
        .rr-br { bottom: -2px; right: -2px; }

        /* ========================================================= */
        /* MODE 5: ATHLETIC TRACK 8-LANE STRIPES (A4 LANDSCAPE)     */
        /* Running Track Tartan Border & Stadium Aesthetic          */
        /* ========================================================= */
        .athletic-track-frame {
            position: relative;
            width: 100%;
            height: 100%;
            padding: 12mm 14mm;
            background: #FFFFFF;
        }

        .athletic-track-border {
            position: relative;
            width: 100%;
            height: 100%;
            border: 8px solid #A8201A;
            box-shadow: 
                0 0 0 3px #FFFFFF,
                0 0 0 6px #EC9A29,
                0 0 0 9px #FFFFFF,
                0 0 0 12px #0F172A;
            border-radius: 12px;
            padding: 7mm 10mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: radial-gradient(circle at center, #FFFFFF 65%, #F8FAFC 100%);
        }

        .track-lane-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0F172A;
            color: #FFD166;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* ========================================================= */
        /* MODE 6: GEOMETRIC GOLD HEX GRID (A4 LANDSCAPE)           */
        /* Honeycomb / Polygon Geometric Luxury Gold                */
        /* ========================================================= */
        .gold-hex-frame {
            position: relative;
            width: 100%;
            height: 100%;
            padding: 14mm 16mm;
            background: #FFFDF9;
        }

        .gold-hex-canvas {
            position: relative;
            width: 100%;
            height: 100%;
            border: 4px double #D4AF37;
            padding: 8mm 10mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: #FFFFFF;
            background-image: radial-gradient(#D4AF37 0.75px, transparent 0.75px);
            background-size: 16px 16px;
            box-shadow: inset 0 0 0 4px #FFFFFF, inset 0 0 0 6px #004B87;
        }

        .hex-corner-diamond {
            position: absolute;
            font-size: 18px;
            color: #D4AF37;
        }
        .hcd-tl { top: 2px; left: 4px; }
        .hcd-tr { top: 2px; right: 4px; }
        .hcd-bl { bottom: 2px; left: 4px; }
        .hcd-br { bottom: 2px; right: 4px; }

        /* ========================================================= */
        /* MODE 7: MINIMALIST SWISS GRID (A4 LANDSCAPE)             */
        /* Clean Architectural Typography & Strict Alignment        */
        /* ========================================================= */
        .swiss-grid-frame {
            position: relative;
            width: 100%;
            height: 100%;
            padding: 12mm 14mm;
            background: #FFFFFF;
        }

        .swiss-grid-canvas {
            position: relative;
            width: 100%;
            height: 100%;
            border: 6px solid #0F172A;
            padding: 8mm 10mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: #FFFFFF;
            background-image: 
                linear-gradient(to right, rgba(15, 23, 42, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* ========================================================= */
        /* MODE 8: MODERN FULL VECTOR NAVY & GOLD                   */
        /* ========================================================= */
        .vector-landscape-frame {
            position: relative;
            width: 100%;
            height: 100%;
            padding: 14mm 16mm;
            background: #FFF;
        }

        .vector-border-outer {
            width: 100%;
            height: 100%;
            border: 5px double #004B87;
            padding: 6mm;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: radial-gradient(circle at center, #FFFFFF 60%, #F8FAFC 100%);
        }

        .vector-corner {
            position: absolute;
            width: 28px;
            height: 28px;
            border: 3px solid #FFAB00;
        }
        .vc-tl { top: -2px; left: -2px; border-right: none; border-bottom: none; }
        .vc-tr { top: -2px; right: -2px; border-left: none; border-bottom: none; }
        .vc-bl { bottom: -2px; left: -2px; border-right: none; border-top: none; }
        .vc-br { bottom: -2px; right: -2px; border-left: none; border-top: none; }

        /* ========================================================= */
        /* MODE 9 & 10: CANVA STYLE CORNER THEMES (WAVES & DOTS)    */
        /* ========================================================= */
        .canva-frame {
            position: relative;
            width: 100%;
            height: 100%;
            background-color: #FFFFFF;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .canva-svg-corners {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .canva-content-wrapper {
            position: relative;
            z-index: 5;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 12mm 22mm 18mm 24mm;
            text-align: center;
        }

        .canva-gold-pill-nomor {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #111827;
            color: #F7DF94;
            border: 1px solid #D4AF37;
            border-radius: 999px;
            padding: 3px 20px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            font-family: 'Space Grotesk', sans-serif;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }

        .canva-name-underline-box {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            margin: 2px 0 4px 0;
        }

        .canva-name-script {
            font-family: 'Great Vibes', cursive;
            font-size: 48px;
            color: #111827;
            line-height: 1.15;
            letter-spacing: 1px;
            margin: 0;
        }

        .canva-name-serif {
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            font-weight: 900;
            color: #2E1C14;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.15;
            margin: 0;
        }

        .canva-gold-divider-flair {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 380px;
            margin: 3px auto 0 auto;
        }
        .canva-gold-divider-flair::before,
        .canva-gold-divider-flair::after {
            content: '';
            flex: 1;
            height: 1.5px;
            background: linear-gradient(90deg, transparent, #D4AF37, transparent);
        }
        .canva-gold-divider-flair span {
            color: #D4AF37;
            font-size: 14px;
            padding: 0 10px;
            line-height: 1;
        }

        .canva-juara-pill {
            display: inline-block;
            background: #1E293B;
            color: #FFFFFF;
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 26px;
            border-radius: 999px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            margin: 4px 0 6px 0;
        }

        .btn-gradient-canva {
            background: linear-gradient(135deg, #FFAB00 0%, #FF6D00 100%);
            color: #FFFFFF !important;
            border: none;
            box-shadow: 0 2px 6px rgba(255, 171, 0, 0.4);
            transition: all 0.2s ease;
        }
        .btn-gradient-canva:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(255, 171, 0, 0.6);
        }

        .canva-theme-card {
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            padding: 12px;
            background: #FFFFFF;
            transition: all 0.2s ease;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .canva-theme-card:hover {
            border-color: #FFAB00;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 75, 135, 0.12);
        }
        .canva-theme-card.active {
            border-color: #0052CC;
            background: #F0F7FF;
            box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.25);
        }

        /* ========================================================= */
        /* TEMA: RESMI FEDERASI PASI JAWA TENGAH (ANTI-CANVA)        */
        /* Banknote Security Guilloche Frame & Classical Typography  */
        /* ========================================================= */
        .theme-official_federation {
            background: #FFFFFF;
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }

        .fed-bg-svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .fed-watermark {
            position: absolute;
            top: 48%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 360px;
            opacity: 0.055;
            pointer-events: none;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fed-watermark img {
            width: 100%;
            height: auto;
            filter: grayscale(100%);
        }

        .fed-content-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 44px 50px 32px 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            z-index: 2;
            box-sizing: border-box;
        }

        .fed-header-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .fed-logo-box {
            margin-bottom: 5px;
        }

        .fed-logo-img {
            height: 64px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.12));
        }

        .fed-instansi-title {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 1.4px;
            color: #0B2545;
            text-transform: uppercase;
            line-height: 1.25;
            margin: 0;
        }

        .fed-instansi-prov {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 2.8px;
            color: #0B2545;
            text-transform: uppercase;
            line-height: 1.25;
            margin-top: 2px;
        }

        .fed-event-sub {
            font-family: 'Inter', sans-serif;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: #9A7B38;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .fed-divider-ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 78%;
            margin: 6px auto;
            gap: 10px;
        }

        .fed-divider-ornament .line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #C5A059, transparent);
        }

        .fed-divider-ornament .diamonds {
            color: #C5A059;
            font-size: 11px;
            letter-spacing: 3px;
        }

        .fed-title-box {
            margin-top: 2px;
        }

        .fed-main-title {
            font-family: 'Cinzel', serif;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 5px;
            color: #0A2540;
            text-transform: uppercase;
            line-height: 1.1;
            margin: 0;
            text-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }

        .fed-nomor-doc {
            font-family: 'Inter', sans-serif;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 1.8px;
            color: #7A5B18;
            margin-top: 3px;
            text-transform: uppercase;
        }

        .fed-body-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 2px 0;
        }

        .fed-intro-text {
            font-family: 'Cinzel', serif;
            font-style: italic;
            font-size: 12.5px;
            color: #4B5563;
            margin: 4px 0 2px 0;
        }

        .fed-athlete-name {
            font-family: 'Cinzel', 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1.2px;
            color: #0B2545;
            text-transform: uppercase;
            padding: 2px 28px 4px 28px;
            border-bottom: 2px solid #C5A059;
            display: inline-block;
            margin: 4px 0;
        }

        .fed-origin-text {
            font-family: 'Inter', sans-serif;
            font-size: 12.5px;
            color: #1F2937;
            margin: 3px 0;
        }

        .fed-origin-text strong {
            color: #0B2545;
            font-weight: 700;
        }

        .fed-merit-text {
            font-family: 'Inter', sans-serif;
            font-size: 11.5px;
            color: #4B5563;
            margin: 4px 0 6px 0;
        }

        .fed-plaque-badge {
            background: linear-gradient(135deg, rgba(254, 252, 246, 0.95) 0%, rgba(248, 244, 230, 0.95) 100%);
            border: 1.5px solid #C5A059;
            border-radius: 6px;
            padding: 8px 24px;
            width: 82%;
            box-shadow: 0 2px 8px rgba(197, 160, 89, 0.15);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            position: relative;
        }

        .fed-badge-rank {
            font-family: 'Cinzel', serif;
            font-size: 18px;
            font-weight: 900;
            color: #0A2540;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .fed-badge-rank .star {
            color: #D4AF37;
        }

        .fed-badge-details {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 18px;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            color: #1F2937;
        }

        .fed-badge-details .item {
            display: flex;
            gap: 5px;
        }

        .fed-badge-details .label {
            color: #6B7280;
            font-weight: 500;
        }

        .fed-badge-details .val {
            font-weight: 700;
            color: #0B2545;
            text-transform: uppercase;
        }

        .fed-closing-text {
            font-family: 'Inter', sans-serif;
            font-size: 11.5px;
            line-height: 1.45;
            color: #374151;
            max-width: 84%;
            margin: 8px auto 0 auto;
        }

        .fed-signatures-section {
            width: 100%;
            margin-bottom: 8px;
        }

        .fed-date-city {
            font-family: 'Inter', sans-serif;
            font-size: 11.5px;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 4px;
            text-align: center;
        }

        .fed-sig-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            width: 100%;
            margin-bottom: 2px;
        }

        .fed-sig-cell {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .fed-sig-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #0A2540;
            line-height: 1.25;
            min-height: 28px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .fed-sig-img-box {
            height: 120px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -14px 0 -12px 0;
            position: relative;
            z-index: 3;
            pointer-events: none;
        }

        .fed-sig-img-box img {
            height: 120px;
            width: auto;
            max-height: 120px;
            max-width: 250px;
            object-fit: contain;
            pointer-events: none;
            mix-blend-mode: multiply;
            filter: contrast(1.04) saturate(1.1);
        }

        .fed-sig-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 800;
            color: #0A2540;
            border-bottom: 1px solid #0A2540;
            padding: 0 6px 1px 6px;
            display: inline-block;
            white-space: nowrap;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .fed-sig-center-cell {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-top: -8px;
        }

        .fed-sig-center-cell .fed-sig-img-box {
            height: 120px;
            margin: -14px 0 -12px 0;
        }

        .fed-sig-center-cell .fed-sig-img-box img {
            height: 120px;
            width: auto;
            max-height: 120px;
            max-width: 250px;
        }
    
        /* Transisi halus saat kalibrasi live offset */
        .overlay-signatures-triple,
        .portrait-signatures-triple,
        .sign-row-official,
        .fed-signatures-section,
        .portrait-official-signature-center,
        .overlay-content-wrapper,
        .portrait-content-body,
        .fed-body,
        .canva-content-wrapper > .my-auto,
        .swiss-grid-canvas > .my-auto,
        .gold-hex-canvas > .my-auto,
        .vector-border-outer > .my-auto,
        .cyber-content-wrapper > .my-auto {
            transition: transform 0.12s ease-out, bottom 0.12s ease-out, top 0.12s ease-out;
        }
        body.drag-mode-active .cert-drag-item {
            transition: none !important;
        }

    </style>

    <!-- INJEKSI POSISI CUSTOM TERAKHIR DARI DATABASE (TERISOLASI: USIA DINI 567 vs SELAIN ITU) -->
    <?php 
    $savedOffsetsRaw = $template['custom_offsets'] ?? '';
    $allSavedOffsets = !empty($savedOffsetsRaw) ? json_decode($savedOffsetsRaw, true) : [];
    $offsets567 = [];
    $offsetsUmum = [];
    if (is_array($allSavedOffsets)) {
        if (isset($allSavedOffsets['usia_567']) || isset($allSavedOffsets['umum'])) {
            $offsets567 = is_array($allSavedOffsets['usia_567'] ?? null) ? $allSavedOffsets['usia_567'] : [];
            $offsetsUmum = is_array($allSavedOffsets['umum'] ?? null) ? $allSavedOffsets['umum'] : [];
        } else {
            // Jika data flat lama, simpan sebagai umum agar Usia Dini 5,6,7th tidak terdistorsi
            $offsetsUmum = $allSavedOffsets;
        }
    }
    ?>
    <style id="server-custom-offsets-style">
    /* 1. OFFSETS KHUSUS KELOMPOK UMUR LAINNYA (SELAIN 5, 6, 7 TAHUN) */
    <?php if (!empty($offsetsUmum) && is_array($offsetsUmum)): ?>
        <?php foreach ($offsetsUmum as $k => $coord): ?>
            <?php 
            if (str_starts_with((string)$k, '__')) continue;
            $x = (int)($coord['x'] ?? 0);
            $y = (int)($coord['y'] ?? 0);
            if ($x !== 0 || $y !== 0):
            ?>
                .piagam-sheet:not(.is-usia-567) .cert-drag-item[data-drag-key="<?= esc($k) ?>"] {
                    transform: translate(<?= $x ?>px, <?= $y ?>px) !important;
                }
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!empty($offsetsUmum['__hide_title'])): ?>
            .piagam-sheet:not(.is-usia-567) .overlay-title-box, .piagam-sheet:not(.is-usia-567) .overlay-title-text { display: none !important; }
        <?php endif; ?>
        <?php if (!empty($offsetsUmum['__hide_labels'])): ?>
            .piagam-sheet:not(.is-usia-567) .overlay-data-row .lbl,
            .piagam-sheet:not(.is-usia-567) .overlay-data-row .sep { display: none !important; }
            .piagam-sheet:not(.is-usia-567) .overlay-data-row .val { text-align: center !important; }
        <?php endif; ?>
        <?php if (!empty($offsetsUmum['__hide_ttd_center'])): ?>
            .piagam-sheet:not(.is-usia-567) .overlay-sig-col-center { display: none !important; }
        <?php endif; ?>
    <?php endif; ?>

    /* 2. OFFSETS KHUSUS USIA DINI (5, 6, 7 TAHUN) - INDEPENDEN & TERISOLASI TOTAL */
    <?php if (!empty($offsets567) && is_array($offsets567)): ?>
        <?php foreach ($offsets567 as $k => $coord): ?>
            <?php 
            if (str_starts_with((string)$k, '__')) continue;
            $x = (int)($coord['x'] ?? 0);
            $y = (int)($coord['y'] ?? 0);
            if ($x !== 0 || $y !== 0):
            ?>
                .piagam-sheet.is-usia-567 .cert-drag-item[data-drag-key="<?= esc($k) ?>"] {
                    transform: translate(<?= $x ?>px, <?= $y ?>px) !important;
                }
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!empty($offsets567['__hide_title'])): ?>
            .piagam-sheet.is-usia-567 .overlay-title-box, .piagam-sheet.is-usia-567 .overlay-title-text { display: none !important; }
        <?php endif; ?>
        <?php if (!empty($offsets567['__hide_labels'])): ?>
            .piagam-sheet.is-usia-567 .overlay-data-row .lbl,
            .piagam-sheet.is-usia-567 .overlay-data-row .sep { display: none !important; }
            .piagam-sheet.is-usia-567 .overlay-data-row .val { text-align: center !important; }
        <?php endif; ?>
    <?php endif; ?>
    </style>
</head>
<body class="<?= $isPublic ? 'is-public-view' : 'is-admin-view' ?>">

<!-- Floating Print & Theme Switcher Toolbar -->
<div class="print-toolbar <?= $isPublic ? 'is-public-toolbar' : '' ?>">
<?php if ($isPublic): ?>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary px-3 py-1.5 rounded-pill d-inline-flex align-items-center gap-1.5 fw-bold shadow-sm" style="font-size: 0.85rem;">
            <i class="bi bi-patch-check-fill text-warning"></i> E-PIAGAM RESMI TERVERIFIKASI
        </span>
        <?php if (!empty($sekolah['nama'])): ?>
            <span class="text-white fw-bold d-none d-md-inline" style="font-size: 0.95rem;">
                <i class="bi bi-building me-1 text-warning"></i> <?= esc($sekolah['nama']) ?>
            </span>
        <?php endif; ?>
    </div>

    <span class="border-start border-secondary h-50 mx-1"></span>

    <!-- Selector Ukuran Kertas: F4 (Folio 215x330mm) vs A4 (210x297mm) -->
    <div class="d-flex align-items-center gap-1">
        <span class="small text-white-50 fw-semibold d-none d-md-inline text-nowrap">
            <i class="bi bi-aspect-ratio text-info me-1"></i> Kertas:
        </span>
        <select id="paperSizeSwitcher" class="form-select form-select-sm fw-bold border-info bg-dark text-info rounded-pill px-2 shadow-sm" style="width: auto; min-width: 140px;" onchange="switchPaperSize(this.value)" title="Pilih ukuran kertas cetak: F4 / Folio (215x330mm) atau A4 (210x297mm)">
            <option value="F4" selected>📄 F4 (215×330 mm)</option>
            <option value="A4">📄 A4 (210×297 mm)</option>
        </select>
    </div>

    <span class="border-start border-secondary h-50 mx-1"></span>

    <span class="small fw-semibold text-warning">
        <i class="bi bi-award-fill me-1"></i>
        <?= count($piagamList) ?> Sertifikat
    </span>

    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow" title="Cetak langsung ke mesin printer">
        <i class="bi bi-printer-fill text-warning"></i>
        <span>Print</span>
    </button>

    <?php if (!empty($sekolah['id'])): ?>
        <a href="<?= base_url('piagam/download-sekolah-pdf/' . $sekolah['id']) ?>" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow" id="btnDownloadPdf" title="Unduh berkas PDF piagam resmi langsung (Pure Download)">
            <i class="bi bi-file-earmark-pdf-fill"></i>
            <span id="btnDownloadPdfText">Download PDF</span>
        </a>
    <?php else: ?>
        <button type="button" onclick="downloadCertificatePdf()" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow" id="btnDownloadPdf" title="Unduh berkas PDF piagam resmi ke perangkat Anda">
            <i class="bi bi-file-earmark-pdf-fill"></i>
            <span id="btnDownloadPdfText">Download PDF</span>
        </button>
    <?php endif; ?>

<!-- Mode Geser Elemen / Pemindahan dinonaktifkan pada mode publik / sekolah -->
<?php else: ?>
    <a href="<?= base_url('piagam') ?>" class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-1 rounded-pill px-3">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>

    <span class="border-start border-secondary h-50 mx-1"></span>

    <!-- Format Template Aktif Selector -->
    <?php 
    $firstItem = $piagamList[0] ?? [];
    $isCurrentUsia567 = false;
    $firstKuCheck = strtolower(($firstItem['kategori_usia'] ?? '') . ' ' . ($firstItem['nomor_lomba'] ?? ''));
    if (preg_match('/\b(u[\s\-_]?[567]|ku[\s\-_]?[567]|juaraku|usia[\s\-_]?dini|[567][\s\-_]?th|[567][\s\-_]?tahun)\b|5-6|567|juaraku/i', $firstKuCheck)) {
        $isCurrentUsia567 = true;
    }
    if (isset($firstItem['hanya_ketua_pekalongan'])) {
        $isCurrentUsia567 = (bool)$firstItem['hanya_ketua_pekalongan'];
    }

    $activeTpls = array_filter($templates ?? [], function($t) use ($isCurrentUsia567) {
        if (empty($t['is_active'])) return false;
        if ($isCurrentUsia567) {
            // Khusus event Usia Dini: HANYA tampilkan Template 22
            return (int)$t['id'] === 22;
        } else {
            // Selain Usia Dini: JANGAN tampilkan Template 22
            return (int)$t['id'] !== 22;
        }
    });
    if (empty($activeTpls)) {
        $activeTpls = $templates ?? [];
    }
    ?>
    <div class="d-flex align-items-center gap-2">
        <span class="small text-white-50 fw-semibold d-none d-xl-inline text-nowrap">
            <i class="bi bi-file-earmark-check text-warning me-1"></i> Format Template:
        </span>
        <select id="themeSwitcher" class="form-select form-select-sm fw-bold border-warning bg-dark text-white rounded-pill px-3 shadow-sm" style="min-width: 280px;" onchange="switchCertificateTheme(this.value)">
            <?php foreach ($activeTpls as $t): ?>
                <?php 
                $val = $t['tipe'];
                if ($val === 'portrait_ekskari') {
                    $val = 'portrait';
                }
                $isSelected = ((int)($template['id'] ?? 0) === (int)$t['id']);
                ?>
                <option value="<?= esc($val) ?>" 
                    data-id="<?= (int)$t['id'] ?>"
                    data-orientasi="<?= esc($t['orientasi']) ?>" 
                    data-offset-top="<?= (int)($t['offset_top'] ?? 0) ?>"
                    data-offset-ttd="<?= (int)($t['offset_ttd'] ?? 0) ?>"
                    data-font-nama="<?= (int)($t['font_size_nama'] ?: 26) ?>"
                    data-hide-header="<?= !empty($t['hide_header']) ? 1 : 0 ?>"
                    data-hide-title="<?= !empty($t['hide_title']) ? 1 : 0 ?>"
                    <?= $isSelected ? 'selected' : '' ?>>
                    <?= esc($t['nama']) ?>
                </option>
            <?php endforeach; ?>
            <?php if (empty($activeTpls)): ?>
                <option value="portrait" selected>📜 Portrait Eks-Karesidenan (Format Resmi)</option>
            <?php endif; ?>
        </select>
    </div>

    <span class="border-start border-secondary h-50 mx-1"></span>

    <!-- Selector Ukuran Kertas: F4 (Folio 215x330mm) vs A4 (210x297mm) -->
    <div class="d-flex align-items-center gap-1">
        <span class="small text-white-50 fw-semibold d-none d-md-inline text-nowrap">
            <i class="bi bi-aspect-ratio text-info me-1"></i> Kertas:
        </span>
        <select id="paperSizeSwitcher" class="form-select form-select-sm fw-bold border-info bg-dark text-info rounded-pill px-2 shadow-sm" style="width: auto; min-width: 140px;" onchange="switchPaperSize(this.value)" title="Pilih ukuran kertas cetak: F4 / Folio (215x330mm) atau A4 (210x297mm)">
            <option value="F4" selected>📄 F4 (215×330 mm)</option>
            <option value="A4">📄 A4 (210×297 mm)</option>
        </select>
    </div>

    <span class="border-start border-secondary h-50 mx-1"></span>

    <!-- Tombol Mode Geser Elemen (Drag & Drop Interaktif Langsung) -->
    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 d-inline-flex align-items-center gap-1 shadow-sm" id="btnToggleDrag" onclick="toggleDragMode()">
        <i class="bi bi-arrows-move text-warning"></i>
        <span id="dragBtnLabel">Mode Geser Elemen</span>
    </button>

    <!-- Tombol Edit Data Peserta Live -->
    <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 d-inline-flex align-items-center gap-1 shadow-sm" id="btnToggleEditData" onclick="toggleEditDataPanel()" title="Ubah Nama Atlet, Sekolah, Juara, dan data lainnya secara realtime">
        <i class="bi bi-pencil-square text-dark"></i>
        <span>Edit Nama & Data</span>
    </button>

    <!-- Tombol Atur Grid & Letak TTD Live -->
    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 d-inline-flex align-items-center gap-1 shadow-sm" id="btnToggleAdjuster" onclick="toggleAdjusterPanel()">
        <i class="bi bi-sliders text-info"></i>
        <span>Atur Posisi TTD & Teks</span>
    </button>

    <span class="small fw-semibold d-none d-md-inline text-warning">
        <i class="bi bi-award-fill me-1"></i>
        <?= count($piagamList) ?> Lembar
    </span>

    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow" title="Cetak langsung ke mesin printer">
        <i class="bi bi-printer-fill text-warning"></i>
        <span>Print</span>
    </button>

    <button type="button" onclick="downloadCertificatePdf()" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1.5 shadow" id="btnDownloadPdfAdmin" title="Unduh berkas PDF piagam resmi ke perangkat Anda">
        <i class="bi bi-download"></i>
        <span>Download PDF</span>
    </button>
<?php endif; ?>
</div>

<?php if (!$isPublic): ?>
<!-- Floating Toolbar Khusus Saat Mode Geser Elemen Aktif (Hanya untuk Admin) -->
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
                <optgroup label="Teks Data Atlet">
                    <option value="row_nama">👤 Nama Atlet</option>
                    <option value="row_asal">🏫 Asal Kota / Sekolah</option>
                    <option value="row_lomba">🏃 Nomor Lomba</option>
                    <option value="row_juara">🥇 Sebagai Juara</option>
                    <option value="row_prestasi">⏱️ Prestasi / Catatan</option>
                </optgroup>
                <optgroup label="Header & Kalimat">
                    <option value="kop">🏛️ Kop Instansi</option>
                    <option value="judul">📜 Judul Piagam</option>
                    <option value="nomor">🔢 Nomor Piagam</option>
                    <option value="pembuka">📝 Kalimat Pembuka</option>
                    <option value="penutup">📄 Kalimat Penutup</option>
                    <option value="logo">🔰 Logo PASI</option>
                </optgroup>
                <optgroup label="Tanda Tangan & Stempel">
                    <option value="ttd_kiri">✍️ TTD Korwil (Kiri)</option>
                    <option value="ttd_kanan">✍️ TTD Panitia (Kanan)</option>
                    <option value="ttd_tengah">✍️ TTD PASI Jateng (Tengah)</option>
                    <option value="ttd">✍️ TTD Tunggal (Resmi)</option>
                </optgroup>
            </select>
        </div>

        <!-- Target Info & Posisi mm -->
        <div id="dragActiveInfo" class="badge bg-dark border border-secondary text-info px-2 py-1 small fw-bold font-monospace text-nowrap d-flex align-items-center gap-1" style="font-size: 0.82rem;">
            X: <span id="val_pos_x" class="text-white">0mm</span> | Y: <span id="val_pos_y" class="text-white">0mm</span>
        </div>

        <!-- Presisi Nudge mm -->
        <div class="d-flex align-items-center gap-1">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-light px-2 py-0 fw-bold" onclick="nudgeActiveElementMm(0, -1)" title="Naik 1mm (Tombol Panah Atas)">↑ 1mm</button>
                <button type="button" class="btn btn-outline-light px-2 py-0 fw-bold" onclick="nudgeActiveElementMm(0, 1)" title="Turun 1mm (Tombol Panah Bawah)">↓ 1mm</button>
                <button type="button" class="btn btn-outline-light px-2 py-0 fw-bold" onclick="nudgeActiveElementMm(-1, 0)" title="Kiri 1mm (Tombol Panah Kiri)">← 1mm</button>
                <button type="button" class="btn btn-outline-light px-2 py-0 fw-bold" onclick="nudgeActiveElementMm(1, 0)" title="Kanan 1mm (Tombol Panah Kanan)">→ 1mm</button>
            </div>
            <div class="btn-group btn-group-sm d-none d-lg-inline-flex">
                <button type="button" class="btn btn-outline-secondary text-white-50 px-1 py-0" onclick="nudgeActiveElementMm(0, -5)" title="Naik 5mm (Shift + Panah Atas)">↑ 5mm</button>
                <button type="button" class="btn btn-outline-secondary text-white-50 px-1 py-0" onclick="nudgeActiveElementMm(0, 5)" title="Turun 5mm (Shift + Panah Bawah)">↓ 5mm</button>
            </div>
        </div>

        <div class="vr bg-secondary opacity-50 my-1 d-none d-md-block"></div>

        <!-- Toggle Grid Penggaris mm -->
        <button type="button" id="btnToggleGrid" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" onclick="toggleMmGrid()" title="Tampilkan Garis Kotak Milimeter untuk kalibrasi ke kertas fisik">
            <i class="bi bi-grid-3x3"></i> <span class="d-none d-xl-inline">Grid</span> mm
        </button>

        <!-- Quick Toggles Sembunyikan Elemen -->
        <div class="d-flex align-items-center gap-2 text-white small">
            <div class="form-check form-check-inline m-0">
                <input class="form-check-input" type="checkbox" id="drag_hide_title" onchange="toggleHideTitle(this.checked)">
                <label class="form-check-label text-white small" for="drag_hide_title" style="cursor: pointer;">Hide Judul</label>
            </div>
            <div class="form-check form-check-inline m-0">
                <input class="form-check-input" type="checkbox" id="drag_hide_labels" onchange="toggleHideLabels(this.checked)">
                <label class="form-check-label text-white small" for="drag_hide_labels" style="cursor: pointer;">Hide Label ("Nama:")</label>
            </div>
            <div class="form-check form-check-inline m-0">
                <input class="form-check-input" type="checkbox" id="drag_hide_ttd_center" onchange="toggleHideTtdCenter(this.checked)">
                <label class="form-check-label text-white small" for="drag_hide_ttd_center" style="cursor: pointer;">Hide TTD Tengah</label>
            </div>
        </div>

        <div class="vr bg-secondary opacity-50 my-1 d-none d-md-block"></div>

        <button type="button" class="btn btn-sm btn-success fw-bold px-3 rounded-pill shadow-sm" onclick="saveCustomDragOffsets(event)">
            <i class="bi bi-check2-circle me-1"></i> Simpan
        </button>

        <button type="button" class="btn btn-sm btn-outline-danger px-2 rounded-pill" onclick="resetCustomDragOffsets()" title="Kembalikan semua posisi ke default">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
        </button>

        <button type="button" class="btn btn-sm btn-secondary px-3 rounded-pill" onclick="toggleDragMode(false)">
            Selesai
        </button>
    </div>
</div>

<!-- Floating Adjuster Panel untuk Kalibrasi Posisi Background -->
<div id="adjusterPanel" class="adjuster-floating-panel" style="display: none;">
    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary">
        <div class="fw-bold text-warning small d-flex align-items-center gap-2">
            <i class="bi bi-crosshair fs-6"></i> Kalibrasi Presisi Grid Background & Letak TTD (Live Preview)
        </div>
        <button type="button" class="btn-close btn-close-white small" onclick="toggleAdjusterPanel()"></button>
    </div>
    
    <div class="row g-2 align-items-center">
        <!-- Offset Teks Atas -->
        <div class="col-auto">
            <label class="small text-white-50 d-block mb-1">↕️ Naik/Turun Teks:</label>
            <div class="input-group input-group-sm" style="width: 140px;">
                <button type="button" class="btn btn-secondary px-2" onclick="changeOffset('top', -2)">-</button>
                <input type="number" id="live_offset_top" class="form-control text-center bg-dark text-white fw-bold" value="<?= (int)($template['offset_top'] ?? 0) ?>" oninput="applyLiveOffset()" onchange="applyLiveOffset()" onkeyup="applyLiveOffset()">
                <button type="button" class="btn btn-secondary px-2" onclick="changeOffset('top', 2)">+</button>
                <span class="input-group-text bg-secondary text-white small">mm</span>
            </div>
        </div>

        <!-- Offset TTD Bawah -->
        <div class="col-auto">
            <label class="small text-white-50 d-block mb-1"><i class="bi bi-pen-fill text-warning me-1"></i> Naik/Turun TTD & Stempel:</label>
            <div class="input-group input-group-sm" style="width: 140px;">
                <button type="button" class="btn btn-secondary px-2" onclick="changeOffset('ttd', -2)">-</button>
                <input type="number" id="live_offset_ttd" class="form-control text-center bg-dark text-white fw-bold" value="<?= (int)($template['offset_ttd'] ?? 0) ?>" oninput="applyLiveOffset()" onchange="applyLiveOffset()" onkeyup="applyLiveOffset()">
                <button type="button" class="btn btn-secondary px-2" onclick="changeOffset('ttd', 2)">+</button>
                <span class="input-group-text bg-secondary text-white small">mm</span>
            </div>
        </div>

        <!-- Ukuran Font Nama -->
        <div class="col-auto">
            <label class="small text-white-50 d-block mb-1"><i class="bi bi-fonts text-info me-1"></i> Ukuran Nama Atlet:</label>
            <div class="input-group input-group-sm" style="width: 130px;">
                <button type="button" class="btn btn-secondary px-2" onclick="changeOffset('nama', -1)">-</button>
                <input type="number" id="live_font_nama" class="form-control text-center bg-dark text-white fw-bold" value="<?= (int)($template['font_size_nama'] ?: 26) ?>" oninput="applyLiveOffset()" onchange="applyLiveOffset()" onkeyup="applyLiveOffset()">
                <button type="button" class="btn btn-secondary px-2" onclick="changeOffset('nama', 1)">+</button>
                <span class="input-group-text bg-secondary text-white small">px</span>
            </div>
        </div>

        <!-- Ukuran TTD & Stempel -->
        <div class="col-auto">
            <label class="small text-white-50 d-block mb-1"><i class="bi bi-aspect-ratio text-warning me-1"></i> Ukuran Stempel & TTD:</label>
            <div class="input-group input-group-sm" style="width: 140px;">
                <button type="button" class="btn btn-secondary px-2" onclick="changeOffset('stempel', -5)">-</button>
                <input type="number" id="live_stempel_size" class="form-control text-center bg-dark text-white fw-bold" value="130" oninput="applyLiveOffset()" onchange="applyLiveOffset()" onkeyup="applyLiveOffset()">
                <button type="button" class="btn btn-secondary px-2" onclick="changeOffset('stempel', 5)">+</button>
                <span class="input-group-text bg-secondary text-white small">px</span>
            </div>
        </div>

        <!-- Checkbox Sembunyikan Judul / Kop -->
        <div class="col-auto pt-3">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="live_hide_header" <?= !empty($template['hide_header']) ? 'checked' : '' ?> onchange="applyLiveOffset()">
                <label class="form-check-label small text-white" for="live_hide_header">Sembunyikan Kop</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="live_hide_title" <?= !empty($template['hide_title']) ? 'checked' : '' ?> onchange="applyLiveOffset()">
                <label class="form-check-label small text-white" for="live_hide_title">Sembunyikan Judul</label>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="col-auto pt-3 ms-auto">
            <button type="button" id="btnSavePosition" class="btn btn-sm btn-success fw-bold d-inline-flex align-items-center gap-1 px-3 shadow" onclick="saveTemplatePosition()">
                <i class="bi bi-save2-fill"></i> Simpan Posisi Ini
            </button>
        </div>
    </div>
</div>
<?php endif; /* end if !$isPublic for drag & adjuster toolbars */ ?>

<?php
// Helper functions khusus pemformatan Usia Dini 5, 6, 7 Tahun sesuai regulasi panitia
if (!function_exists('formatNomorPiagam567')) {
    function formatNomorPiagam567($cleanNomor, $idx = 0) {
        $nomor = trim((string)$cleanNomor);
        $nomor = preg_replace('/PASI\.?JATENG/i', 'PASI.KOTA', $nomor);
        $nomor = preg_replace('/\/SERT\//i', '/sert/', $nomor);
        if (!preg_match('/PASI\.KOTA/i', $nomor)) {
            return "001/sert/PASI.KOTA/IX/2026";
        }
        // Pastikan nomor induk sertifikat selalu stabil 001 (tidak urut 002/003)
        return preg_replace('/^\d+\//', '001/', $nomor);
    }
}

if (!function_exists('formatNomorLomba567')) {
    function formatNomorLomba567($rawLomba, $usiaSpecific = '') {
        if (empty($rawLomba)) return '';
        $clean = preg_replace('/juaraku\s*/i', '', $rawLomba);
        $clean = preg_replace('/\bu[\s\-_]?5\b|\bku[\s\-_]?5\b/i', 'Usia 5 Tahun', $clean);
        $clean = preg_replace('/\bu[\s\-_]?6\b|\bku[\s\-_]?6\b/i', 'Usia 6 Tahun', $clean);
        $clean = preg_replace('/\bu[\s\-_]?7\b|\bku[\s\-_]?7\b/i', 'Usia 7 Tahun', $clean);
        $clean = preg_replace('/\bfemale\b/i', 'Putri', $clean);
        $clean = preg_replace('/\bmale\b/i', 'Putra', $clean);
        $clean = preg_replace('/\bPutri\b/i', 'Putri', $clean);
        $clean = preg_replace('/\bPutra\b/i', 'Putra', $clean);
        $res = trim(preg_replace('/\s+/', ' ', $clean));

        // Format keterangan usia spesifik pada nomor lomba di bawah nama anak
        if (in_array($usiaSpecific, ['5', '6', '7'])) {
            if (!preg_match('/usia\s*[567]\s*tahun/i', $res)) {
                $res = preg_replace('/\b(ku|u)?\s*[\(\[]?\s*5\s*[-/&]\s*[67]\s*[\)\]]?\b/i', '', $res);
                $res = preg_replace('/\b567\b/i', '', $res);
                $res = trim(preg_replace('/\s+/', ' ', $res));

                if (preg_match('/^(.*?)\s*(putra|putri)(.*)$/i', $res, $m)) {
                    $res = trim($m[1]) . " Usia {$usiaSpecific} Tahun " . ucfirst(strtolower($m[2])) . (trim($m[3]) ? ' ' . trim($m[3]) : '');
                } else {
                    $res .= " Usia {$usiaSpecific} Tahun";
                }
            }
        } elseif ($usiaSpecific === '567') {
            if (!preg_match('/usia\s*[567]/i', $res)) {
                if (preg_match('/^(.*?)\s*(putra|putri)(.*)$/i', $res, $m)) {
                    $res = trim($m[1]) . " Usia 5, 6, 7 Tahun " . ucfirst(strtolower($m[2])) . (trim($m[3]) ? ' ' . trim($m[3]) : '');
                } else {
                    $res .= " Usia 5, 6, 7 Tahun";
                }
            }
        }

        return trim(preg_replace('/\s+/', ' ', $res));
    }
}

if (!function_exists('formatPrestasiPiagam')) {
    function formatPrestasiPiagam($rawPrestasi, $nomorLomba = '', $satuan = '') {
        $p = trim((string)$rawPrestasi);

        $upper = strtoupper($p);
        if (in_array($upper, ['DQ', 'DNS', 'DNF', 'NM', 'DISK', 'DISQ'])) {
            return $upper;
        }

        // Cek apakah nomor lapangan (Jarak / Meter: Tolak Peluru, Lempar Lembing, Lempar Cakram, Lompat Jauh, dsb)
        $isField = false;
        $satuanLower = strtolower(trim((string)$satuan));
        $lombaLower = strtolower(trim((string)$nomorLomba));

        if (in_array($satuanLower, ['jarak', 'meter', 'm'])) {
            $isField = true;
        } elseif (preg_match('/(lompat|peluru|lembing|cakram|martil|jump|shot|javelin|discus|hammer)/i', $lombaLower)) {
            $isField = true;
        }

        if ($isField) {
            // Untuk nomor lapangan (meter): tampilkan angka desimal 0.00 langsung saja (tanpa ' m' dan jangan 0:00:00)
            if ($p === '' || in_array($p, ['-', '0', '0.0', '0.00', '00.00', '0:00', '00:00', '0:00.00', '00:00.00'])) {
                return '0.00';
            }
            $cleaned = trim(preg_replace('/\s*(m|meter|jarak)$/i', '', $p));
            $cleaned = preg_replace('/^0+:/', '', $cleaned);
            if (is_numeric($cleaned)) {
                return number_format((float)$cleaned, 2, '.', '');
            }
            return $cleaned;
        }

        if ($p === '' || in_array($p, ['-', '0', '0.0', '0.00', '00.00', '0:00', '00:00', '0:00.00', '00:00.00'])) {
            return '-';
        }

        // Untuk nomor lintasan / lari (waktu)
        $cleaned = trim(preg_replace('/\s*(dtk|detik|s)$/i', '', $p));

        // Format waktu yang sudah memiliki menit:detik misal 23:28.00 atau 3:44.00 atau 0:06.48
        if (preg_match('/^(\d+):(\d{2})(\.\d{1,2})?$/', $cleaned, $m)) {
            $dec = isset($m[3]) ? $m[3] : '.00';
            if (strlen($dec) === 2) {
                $dec .= '0';
            }
            return $m[1] . ':' . $m[2] . $dec;
        }

        // Format angka desimal biasa (misal 10.61, 8.93, 6.48, 44.64, 3.54) -> ubah ke 0:00.00
        if (preg_match('/^(\d+)\.(\d{1,2})$/', $cleaned, $m)) {
            $sec = (int)$m[1];
            $dec = $m[2];
            if (strlen($dec) === 1) {
                $dec .= '0';
            }
            return sprintf('0:%02d.%s', $sec, $dec);
        }

        // Format angka bulat (misal 10 atau 8) -> ubah ke 0:10.00
        if (ctype_digit($cleaned)) {
            return sprintf('0:%02d.00', (int)$cleaned);
        }

        return $p;
    }
}
?>

<!-- Floating Panel Edit Data Peserta / Atlet (Live Sync) -->
<?php 
$firstItem = $piagamList[0] ?? []; 
$firstKuCheck = strtolower(($firstItem['kategori_usia'] ?? '') . ' ' . ($firstItem['nomor_lomba'] ?? ''));
$firstIs567 = false;
if (preg_match('/\b(u[\s\-_]?[567]|ku[\s\-_]?[567]|juaraku|usia[\s\-_]?dini|[567][\s\-_]?th|[567][\s\-_]?tahun)\b|5-6|567|juaraku/i', $firstKuCheck)) {
    $firstIs567 = true;
}
if (isset($firstItem['hanya_ketua_pekalongan'])) {
    $firstIs567 = (bool)$firstItem['hanya_ketua_pekalongan'];
}

$firstCleanNomor = trim(preg_replace('/^(NO|NOMOR)\s*:\s*/i', '', $firstItem['nomor_piagam'] ?? ''));
if ($firstIs567) {
    $firstCleanNomor = formatNomorPiagam567($firstCleanNomor, 0);
    $firstLomba = formatNomorLomba567($firstItem['nomor_lomba'] ?? '');
} else {
    $firstLomba = $firstItem['nomor_lomba'] ?? 'Lari 100 M KU 16-18 Putra';
}
// Format prestasi berlaku untuk SELURUH nomor lomba
$firstPrestasi = formatPrestasiPiagam($firstItem['prestasi'] ?? '', $firstLomba, $firstItem['satuan'] ?? '');
?>
<div id="editDataPanel" class="adjuster-floating-panel" style="display: none; width: 720px; max-width: 96vw; background: rgba(15, 23, 42, 0.98); border: 2px solid #F59E0B; box-shadow: 0 20px 45px rgba(0,0,0,0.7); border-radius: 14px;">
    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary">
        <div class="fw-bold text-warning d-flex align-items-center gap-2 fs-6">
            <i class="bi bi-pencil-square fs-5"></i>
            <span>Edit Nama & Data Peserta (Real-time Live Sync)</span>
        </div>
        <button type="button" class="btn-close btn-close-white" onclick="toggleEditDataPanel()"></button>
    </div>

    <div class="row g-2 text-start">
        <!-- 1. Nama Lengkap Peserta -->
        <div class="col-md-8">
            <label class="form-label text-warning small fw-bold mb-1">
                <i class="bi bi-person-fill me-1"></i> Nama Lengkap Atlet / Peserta:
            </label>
            <input type="text" id="edit_nama_pemenang" class="form-control form-control-sm bg-dark text-white border-warning fw-bold fs-6" 
                value="<?= esc($firstItem['nama_pemenang'] ?? 'NAMA PESERTA') ?>" 
                placeholder="Ketik nama lengkap peserta..." 
                oninput="updateLiveField('nama', this.value)">
            <span class="text-white-50" style="font-size: 11px;">*Teks nama di sertifikat langsung berubah otomatis saat Anda mengetik.</span>
        </div>

        <!-- 2. Peringkat / Juara -->
        <div class="col-md-4">
            <label class="form-label text-white-50 small fw-bold mb-1">
                <i class="bi bi-trophy-fill text-warning me-1"></i> Sebagai Juara:
            </label>
            <select id="edit_peringkat" class="form-select form-select-sm bg-dark text-white border-secondary fw-bold" onchange="updateLiveJuara(this.value)">
                <?php $currentP = (int)($firstItem['peringkat'] ?? 1); ?>
                <option value="1" <?= $currentP === 1 ? 'selected' : '' ?>>Juara 1 (Satu) - Emas</option>
                <option value="2" <?= $currentP === 2 ? 'selected' : '' ?>>Juara 2 (Dua) - Perak</option>
                <option value="3" <?= $currentP === 3 ? 'selected' : '' ?>>Juara 3 (Tiga) - Perunggu</option>
                <option value="4" <?= $currentP === 4 ? 'selected' : '' ?>>Juara Harapan 1</option>
                <option value="5" <?= $currentP === 5 ? 'selected' : '' ?>>Juara Harapan 2</option>
            </select>
        </div>

        <input type="hidden" id="edit_asal_sekolah" value="<?= esc($firstItem['asal_sekolah'] ?: ($firstItem['asal_kota'] ?? '')) ?>">
        <input type="hidden" id="edit_asal_kota" value="">

        <!-- 5. Nomor & Kategori Lomba -->
        <div class="col-md-8">
            <label class="form-label text-white-50 small fw-bold mb-1">
                <i class="bi bi-flag-fill me-1"></i> Nomor & Kategori Lomba:
            </label>
            <input type="text" id="edit_nomor_lomba" class="form-control form-control-sm bg-dark text-white border-secondary" 
                value="<?= esc($firstLomba) ?>" 
                placeholder="Contoh: Lari 100 M KU 16-18 Putra" 
                oninput="updateLiveField('lomba', this.value)">
        </div>

        <!-- 6. Catatan Prestasi -->
        <div class="col-md-4">
            <label class="form-label text-white-50 small fw-bold mb-1">
                <i class="bi bi-stopwatch me-1"></i> Catatan Prestasi:
            </label>
            <input type="text" id="edit_prestasi" class="form-control form-control-sm bg-dark text-white border-secondary font-monospace" 
                value="<?= esc($firstPrestasi) ?>" 
                placeholder="Contoh: 0:00.00" 
                oninput="updateLiveField('prestasi', this.value)">
        </div>

        <!-- 7. Nomor Piagam -->
        <div class="col-md-12">
            <label class="form-label text-white-50 small fw-bold mb-1">
                <i class="bi bi-hash me-1"></i> Nomor Surat / Piagam:
            </label>
            <input type="text" id="edit_nomor_piagam" class="form-control form-control-sm bg-dark text-white border-secondary font-monospace" 
                value="<?= esc($firstCleanNomor) ?>" 
                placeholder="<?= $firstIs567 ? '001/sert/PASI.KOTA/IX/2026' : '001/SERT/PASIJATENG/IX/2026' ?>" 
                oninput="updateLiveField('nomor', this.value)">
        </div>

        <!-- Format Tanda Tangan: Triple vs Single Usia 5, 6, 7 th -->
        <div class="col-md-12 mt-2 pt-2 border-top border-secondary">
            <label class="form-label text-warning small fw-bold mb-1">
                <i class="bi bi-pen-fill me-1"></i> Format Tanda Tangan Pejabat:
            </label>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="live_ttd_mode" id="live_ttd_auto" value="auto" checked onchange="updateLiveTtdFormat(this.value)">
                    <label class="form-check-label small text-white" for="live_ttd_auto">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> <strong>Otomatis</strong> (Usia 5,6,7 th = Hanya Ketua PASI Pekalongan, Lainnya = Triple)
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="live_ttd_mode" id="live_ttd_single" value="single_pekalongan" onchange="updateLiveTtdFormat(this.value)">
                    <label class="form-check-label small text-warning fw-bold" for="live_ttd_single">
                        <i class="bi bi-person-fill text-warning me-1"></i> <strong>Hanya Ketua PASI Kota Pekalongan</strong> (Usia 5, 6, 7 th)
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="live_ttd_mode" id="live_ttd_triple" value="triple" onchange="updateLiveTtdFormat(this.value)">
                    <label class="form-check-label small text-white" for="live_ttd_triple">
                        <i class="bi bi-people-fill text-info me-1"></i> <strong>Triple Signatures</strong> (Korwil + Panitia + PASI Jateng)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Action Buttons inside editDataPanel -->
    <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top border-secondary">
        <div class="small text-white-50">
            <i class="bi bi-lightbulb-fill text-warning me-1"></i>
            <strong>Tips:</strong> Anda juga bisa langsung <u>klik teks di lembar piagam</u> untuk mengetik langsung (WYSIWYG)!
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="saveDataToUrl()" title="Simpan data peserta ini ke URL browser">
                <i class="bi bi-link-45deg me-1"></i> Simpan URL
            </button>
            <button type="button" class="btn btn-sm btn-success fw-bold rounded-pill px-4" onclick="toggleEditDataPanel()">
                <i class="bi bi-check-lg me-1"></i> Selesai
            </button>
        </div>
    </div>
</div>

<!-- Loop Lembar Piagam Penghargaan -->
<?php foreach ($piagamList as $idx => $item): ?>
    <?php 
    // Bersihkan nomor dari kemungkinan awalan "NO :" berulang & pastikan nomor induk selalu stabil 001 (tidak urut 002/003)
    $cleanNomor = trim(preg_replace('/^(NO|NOMOR)\s*:\s*/i', '', $item['nomor_piagam']));
    $cleanNomor = preg_replace('/^\d+\//', '001/', $cleanNomor);

    // Deteksi apakah nomor acara / kategori ini adalah Usia Dini 5, 6, 7 tahun (U 5, U 6, U 7 / KU 5-6 / JuarAKU)
    // Sesuai instruksi:
    // 1. Judul atas = KEJUARAAN ATLETIK USIA DINI 567 TAHUN TAHUN 2026
    // 2. Nomor = 001/sert/PASI.KOTA/IX/2026
    // 3. Pembuka = Pengurus Kota Persatuan Atletik Seluruh Indonesia (PASI) Kota Pekalongan dengan ini memberikan Piagam Penghargaan kepada :
    // 4. Nomor lomba = Lari 30 m usia 5 tahun putra / putri (dan seterusnya)
    // 5. Penutup = dalam Kejuaraan Atletik KU 567 Tahun Tingkat Kota Pekalongan, Kab. Pekalongan, dan Kab. Batang yang diselenggarakan tanggal 27 September 2026 di Stadion Hoegeng Kota Pekalongan.
    // 6. Prestasi = disesuaikan format 0:00.00
    // 7. TTD = HANYA Ketua Umum PASI Kota Pekalongan (Zaini, S.Pd.)
    $isUsia567 = false;
    $kuCheckStr = strtolower(($item['kategori_usia'] ?? '') . ' ' . ($item['nomor_lomba'] ?? ''));
    if (preg_match('/\b(u[\s\-_]?[567]|ku[\s\-_]?[567]|juaraku|usia[\s\-_]?dini|[567][\s\-_]?th|[567][\s\-_]?tahun)\b|5-6|567|juaraku/i', $kuCheckStr)) {
        $isUsia567 = true;
    }
    if (isset($item['hanya_ketua_pekalongan'])) {
        $isUsia567 = (bool)$item['hanya_ketua_pekalongan'];
    }

    if ($isUsia567) {
        $usiaSpecific = '567';
        // Deteksi umur spesifik 5, 6, atau 7 (prioritaskan nomor lomba spesifik)
        if (preg_match('/(?:usia|ku|u)\s*5\b|\b5\s*(?:th|tahun)\b/i', $item['nomor_lomba'] ?? '')) {
            $usiaSpecific = '5';
        } elseif (preg_match('/(?:usia|ku|u)\s*6\b|\b6\s*(?:th|tahun)\b/i', $item['nomor_lomba'] ?? '')) {
            $usiaSpecific = '6';
        } elseif (preg_match('/(?:usia|ku|u)\s*7\b|\b7\s*(?:th|tahun)\b/i', $item['nomor_lomba'] ?? '')) {
            $usiaSpecific = '7';
        } elseif (preg_match('/(?:usia|ku|u)\s*5\b|\b5\s*(?:th|tahun)\b/i', $kuCheckStr) && !preg_match('#5\s*[-–/&]\s*6|567#', $kuCheckStr)) {
            $usiaSpecific = '5';
        } elseif (preg_match('/(?:usia|ku|u)\s*6\b|\b6\s*(?:th|tahun)\b/i', $kuCheckStr) && !preg_match('#5\s*[-–/&]\s*6|6\s*[-–/&]\s*7|567#', $kuCheckStr)) {
            $usiaSpecific = '6';
        } elseif (preg_match('/(?:usia|ku|u)\s*7\b|\b7\s*(?:th|tahun)\b/i', $kuCheckStr) && !preg_match('#6\s*[-–/&]\s*7|567#', $kuCheckStr)) {
            $usiaSpecific = '7';
        }

        // Judul atas & kalimat penutup selalu Usia Dini 5, 6, 7 Tahun sesuai permintaan user
        $usiaLabelHeader = "USIA DINI 5, 6, 7 TAHUN";
        $usiaLabelPenutup = "Usia Dini 5, 6, 7 Tahun";

        $displayHeaderLines = [
            "KEJUARAAN",
            "ATLETIK",
            $usiaLabelHeader,
            "TAHUN " . esc($item['tahun'] ?? '2026')
        ];
        $cleanNomor = formatNomorPiagam567($cleanNomor, $idx);
        $displayKalimatPembuka = "Pengurus Kota Persatuan Atletik Seluruh Indonesia (PASI) Kota Pekalongan dengan ini memberikan Piagam Penghargaan kepada :";
        $displayNomorLomba = formatNomorLomba567($item['nomor_lomba'] ?? '', $usiaSpecific);
        $displayPrestasi = formatPrestasiPiagam($item['prestasi'] ?? '', $displayNomorLomba, $item['satuan'] ?? '');
        $displayKalimatPenutup = "dalam Kejuaraan Atletik {$usiaLabelPenutup} Tingkat Kota Pekalongan, Kab. Pekalongan, dan Kab. Batang yang diselenggarakan tanggal 27 September 2026 di Stadion Hoegeng Kota Pekalongan.";
    } else {
        $displayHeaderLines = explode("\n", trim($template['instansi_header'] ?: "PENGURUS PROVINSI PERSATUAN ATLETIK\nSELURUH INDONESIA\nJAWA TENGAH"));
        $displayKalimatPembuka = $template['kalimat_pembuka'] ?: 'Pengurus Provinsi Persatuan Atletik Seluruh Indonesia (PASI) Jawa Tengah dengan ini memberikan Piagam Penghargaan kepada :';
        $displayNomorLomba = $item['nomor_lomba'];
        $displayPrestasi = formatPrestasiPiagam($item['prestasi'] ?? '', $displayNomorLomba, $item['satuan'] ?? '');
        $tglStr = $item['tanggal'] ?: '27 September 2026';
        $tmptStr = $item['tempat'] ?: 'Stadion Hoegeng Kota Pekalongan';
        $displayKalimatPenutup = $template['kalimat_penghargaan'] ? str_replace(['{tanggal}', '{tempat}'], [$tglStr, $tmptStr], $template['kalimat_penghargaan']) : "dalam Kejuaraan Atletik Tingkat Eks Karesidenan Pekalongan yang diselenggarakan pada tanggal {$tglStr} di {$tmptStr}.";
    }

    $jabatanKetuaPekalongan = "Pengurus Kota PASI Pekalongan\nKetua Umum";
    $orientasi = $template['orientasi'] ?? 'landscape';
    $tipe      = $template['tipe'] ?? 'overlay';
    $bgFile    = $template['file_background'] ?? null;
    $hasBg     = !empty($bgFile) && file_exists(FCPATH . 'uploads/piagam/templates/' . $bgFile);
    ?>
    <div class="piagam-sheet <?= $isUsia567 ? 'is-usia-567' : '' ?> sheet-<?= esc($orientasi) ?>" id="sheet-<?= $idx ?>" data-is-usia-567="<?= $isUsia567 ? '1' : '0' ?>">
        <?php 
        // Resolve signature image URLs & info
        $img3 = $template['ttd_3_image'] ?? 'ttd_agus_widodo_pasi_jateng.png';
        if (empty($img3) || $img3 === 'ttd_rumini_clean.png' || $img3 === 'rumini_full_block.png' || $img3 === 'rumini_exact_stamp_sig.png' || $img3 === 'crop_rumini_stamp_scribble.png') {
            if (stripos($template['ttd_3_nama'] ?? '', 'Agus') !== false) {
                $img3 = 'ttd_agus_widodo_pasi_jateng.png';
            } else {
                $img3 = 'ttd1_pasi_combined.png';
            }
        }
        $url3 = null;
        if (!empty($img3)) {
            if (file_exists(FCPATH . 'uploads/piagam/ttd/' . $img3)) {
                $v3 = filemtime(FCPATH . 'uploads/piagam/ttd/' . $img3);
                $url3 = base_url('uploads/piagam/ttd/' . $img3) . '?v=' . $v3;
            } elseif (file_exists(FCPATH . 'assets/images/' . $img3)) {
                $v3 = filemtime(FCPATH . 'assets/images/' . $img3);
                $url3 = base_url('assets/images/' . $img3) . '?v=' . $v3;
            }
        }

        $img1 = $template['ttd_1_image'] ?? 'ttd_jumanto_pasi_pekalongan.png';
        if (empty($img1) || $img1 === 'ttd_jumanto_clean.png') {
            $img1 = 'ttd_jumanto_pasi_pekalongan.png';
        }
        $url1 = null;
        if (!empty($img1)) {
            if (file_exists(FCPATH . 'uploads/piagam/ttd/' . $img1)) {
                $v1 = filemtime(FCPATH . 'uploads/piagam/ttd/' . $img1);
                $url1 = base_url('uploads/piagam/ttd/' . $img1) . '?v=' . $v1;
            } elseif (file_exists(FCPATH . 'assets/images/' . $img1)) {
                $v1 = filemtime(FCPATH . 'assets/images/' . $img1);
                $url1 = base_url('assets/images/' . $img1) . '?v=' . $v1;
            }
        }

        $img2 = $template['ttd_2_image'] ?? 'ttd_zaini_pasi_pekalongan.png';
        if (empty($img2) || stripos($img2, 'bambang') !== false) {
            $img2 = 'ttd_zaini_pasi_pekalongan.png';
        }
        $url2 = null;
        if (!empty($img2)) {
            if (file_exists(FCPATH . 'uploads/piagam/ttd/' . $img2)) {
                $v2 = filemtime(FCPATH . 'uploads/piagam/ttd/' . $img2);
                $url2 = base_url('uploads/piagam/ttd/' . $img2) . '?v=' . $v2;
            } elseif (file_exists(FCPATH . 'assets/images/' . $img2)) {
                $v2 = filemtime(FCPATH . 'assets/images/' . $img2);
                $url2 = base_url('assets/images/' . $img2) . '?v=' . $v2;
            }
        }
        ?>

        <!-- CONTAINER 1: OVERLAY RESMI PASI BACKGROUND ASLI -->
        <div class="theme-variant theme-overlay" style="<?= ($tipe === 'overlay' || $hasBg) ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative; overflow:hidden;">
            <?php 
            $displayBg = $bgFile ?: 'template_pasi_clean.jpg';
            // Jika background adalah PDF, arahkan ke versi PNG yang sudah dikonversi atau konversi otomatis
            if (preg_match('/\.pdf$/i', $displayBg)) {
                $candidatePng = preg_replace('/\.pdf$/i', '.png', $displayBg);
                if (file_exists(FCPATH . 'uploads/piagam/templates/' . $candidatePng)) {
                    $displayBg = $candidatePng;
                } else {
                    $pdfFull = FCPATH . 'uploads/piagam/templates/' . $displayBg;
                    $pngFull = FCPATH . 'uploads/piagam/templates/' . $candidatePng;
                    if (file_exists($pdfFull)) {
                        @exec("gs -dNOPAUSE -dBATCH -sDEVICE=png16m -r300 -dFirstPage=1 -dLastPage=1 -sOutputFile=" . escapeshellarg($pngFull) . " " . escapeshellarg($pdfFull));
                        if (file_exists($pngFull) && filesize($pngFull) > 0) {
                            $displayBg = $candidatePng;
                        }
                    }
                }
            }
            $bgExists  = file_exists(FCPATH . 'uploads/piagam/templates/' . $displayBg);
            $bgUrl     = base_url('uploads/piagam/templates/' . esc($displayBg)) . '?v=' . ($bgExists ? filemtime(FCPATH . 'uploads/piagam/templates/' . $displayBg) : time());
            ?>
            <img src="<?= $bgUrl ?>" alt="Template Background" class="overlay-bg-image">

            <?php if (!empty($template['hide_title']) || empty(trim($template['judul'] ?? ''))): ?>
                <!-- MODE ADAPTIF CANVA: POSISI PRESISI SESUAI SHEET A4 PENUH (ANTI-MEPET & PITA DISEIMBANGKAN) -->
                
                <!-- ZONA 1: NOMOR PIAGAM (Tepat di bawah SERTIFIKAT PENGHARGAAN, di atas garis ornamen emas) -->
                <div class="canva-zone-nomor cert-drag-item" data-drag-key="nomor" data-drag-name="Nomor Piagam">
                    NOMOR : <span class="cert-live-nomor cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik langsung untuk edit nomor piagam"><?= esc($cleanNomor) ?></span>
                </div>

                <!-- ZONA 2: NAMA ATLET & KONTINGEN (Di ruang lapang antara "Dengan Bangga Diberikan Kepada" dan "Telah Meraih") -->
                <div class="canva-zone-athlete cert-drag-item" data-drag-key="nama" data-drag-name="Nama Atlet & Asal">
                    <div class="canva-athlete-name cert-live-nama cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik langsung untuk edit nama atlet/peserta" style="font-size: <?= (int)($template['font_size_nama'] ?: 27) ?>px;">
                        <?= esc($item['nama_pemenang']) ?>
                    </div>
                </div>

                <!-- ZONA 3: JUARA (TEPAT DI DALAM PITA EMAS CANVA / DISEIMBANGKAN SEMPURNA) -->
                <div class="canva-zone-ribbon cert-drag-item" data-drag-key="juara" data-drag-name="Gelar Juara">
                    <span class="cert-live-juara-ribbon cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik langsung untuk edit gelar juara">★ JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) ★</span>
                </div>

                <!-- ZONA 4: NOMOR LOMBA & PRESTASI (Tepat di bawah pita emas) -->
                <div class="canva-zone-event cert-drag-item" data-drag-key="lomba" data-drag-name="Nomor Lomba">
                    <span class="cert-live-lomba cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik langsung untuk edit lomba"><?= esc($displayNomorLomba) ?></span>
                    <?php if (!empty($displayPrestasi) && $displayPrestasi !== '-'): ?>
                        <span class="ms-1">&bull; Prestasi: <strong class="cert-live-prestasi cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik langsung untuk edit prestasi"><?= esc($displayPrestasi) ?></strong></span>
                    <?php endif; ?>
                </div>

                <!-- ZONA 5: PERNYATAAN / KALIMAT PENUTUP -->
                <div class="canva-zone-statement cert-drag-item" data-drag-key="penutup" data-drag-name="Keterangan Lomba">
                    <?= esc($displayKalimatPenutup) ?>
                </div>

            <?php else: ?>
                <!-- Container Konten Dinamis Sesuai Format Standar (Grid Polos / Background Kosongan) -->
                <div class="overlay-content-wrapper">
                    <!-- Header Atas: Instansi / Kejuaraan -->
                    <?php if (empty($template['hide_header']) && (!empty($displayHeaderLines) || !empty(trim($template['instansi_header'] ?? '')))): ?>
                    <div class="overlay-top-header cert-drag-item" data-drag-key="kop" data-drag-name="Kop Instansi">
                        <?php 
                        foreach ($displayHeaderLines as $hline): 
                            if (trim($hline) === '') continue;
                        ?>
                            <div class="overlay-header-line"><?= esc(trim($hline)) ?></div>
                        <?php endforeach; ?>
                        <?php if (!$isUsia567): ?>
                            <div class="overlay-header-line">TAHUN <?= esc($item['tahun'] ?? date('Y')) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="overlay-title-box">
                        <div class="overlay-title-text cert-drag-item" data-drag-key="judul" data-drag-name="Judul Piagam"><?= esc($template['judul'] ?: 'PIAGAM PENGHARGAAN') ?></div>
                        <div class="overlay-nomor-line cert-drag-item" data-drag-key="nomor" data-drag-name="Nomor Piagam">NOMOR : <span class="cert-live-nomor cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit nomor piagam"><?= esc($cleanNomor) ?></span></div>
                    </div>

                    <p class="overlay-intro-p cert-drag-item" data-drag-key="pembuka" data-drag-name="Kalimat Pembuka">
                        <?= esc($displayKalimatPembuka) ?>
                    </p>

                    <div class="overlay-data-list">
                        <div class="overlay-data-row cert-drag-item" data-drag-key="row_nama" data-drag-name="Nama Atlet">
                            <span class="lbl">Nama</span>
                            <span class="sep">:</span>
                            <span class="val val-nama cert-live-nama cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik langsung untuk edit nama peserta" style="font-size: <?= (int)($template['font_size_nama'] ?: 16.5) ?>px;"><?= esc($item['nama_pemenang']) ?></span>
                        </div>
                        <div class="overlay-data-row cert-drag-item" data-drag-key="row_lomba" data-drag-name="Nomor Lomba">
                            <span class="lbl">Nomor Lomba</span>
                            <span class="sep">:</span>
                            <span class="val cert-live-lomba cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit lomba"><?= esc($displayNomorLomba) ?></span>
                        </div>
                        <div class="overlay-data-row cert-drag-item" data-drag-key="row_juara" data-drag-name="Sebagai Juara">
                            <span class="lbl">Sebagai Juara</span>
                            <span class="sep">:</span>
                            <span class="val cert-live-juara cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit juara"><?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>)</span>
                        </div>
                        <div class="overlay-data-row cert-drag-item" data-drag-key="row_prestasi" data-drag-name="Prestasi">
                            <span class="lbl">Prestasi</span>
                            <span class="sep">:</span>
                            <span class="val cert-live-prestasi cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit prestasi"><?= esc($displayPrestasi) ?></span>
                        </div>
                    </div>

                    <p class="overlay-closing-p cert-drag-item" data-drag-key="penutup" data-drag-name="Kalimat Penutup">
                        <?= esc($displayKalimatPenutup) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Baris Tanda Tangan Resmi (Jika Portrait Menggunakan Format Segitiga Gambar 2, Jika Landscape 3 Kolom) -->
            <?php if ($orientasi === 'portrait'): ?>
                <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                <div class="portrait-official-signature-center sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> position: absolute; bottom: <?= 12 + (int)($template['offset_ttd'] ?? 0) ?>%; left: 50%; transform: translateX(-50%); width: 300px; text-align: center; z-index: 25;">
                    <div class="sig-unit-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                    <div class="sig-unit-img-box">
                        <?php if ($url2): ?>
                            <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="sig-unit-img">
                        <?php endif; ?>
                    </div>
                    <div class="sig-unit-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                </div>

                <!-- Triple Signatures (Korwil Kiri, Panitia Kanan, PASI Jateng Tengah Bawah) -->
                <div class="portrait-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? 'display:block;' : 'display:none;' ?> position: absolute; bottom: <?= 10 + (int)($template['offset_ttd'] ?? 0) ?>%; left: 50%; transform: translateX(-50%); width: 88%; z-index: 25;">
                    <div class="sig-top-pair">
                        <!-- TTD 1: Koordinator Wilayah (Kiri Atas) -->
                        <div class="sig-unit cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                            <div class="sig-unit-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                            <div class="sig-unit-img-box">
                                <?php if ($url1): ?>
                                    <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="sig-unit-img">
                                <?php endif; ?>
                            </div>
                            <div class="sig-unit-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                        </div>

                        <!-- TTD 2: Ketua Panitia (Kanan Atas) -->
                        <div class="sig-unit cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                            <div class="sig-unit-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                            <div class="sig-unit-img-box">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="sig-unit-img">
                                <?php endif; ?>
                            </div>
                            <div class="sig-unit-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                    </div>

                    <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah Bawah) -->
                    <div class="sig-unit sig-unit-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                        <div class="sig-unit-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,\nPengurus Provinsi PASI Jawa Tengah\nPlt. Ketua Umum")) ?></div>
                        <div class="sig-unit-img-box">
                            <?php if ($url3): ?>
                                <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="sig-unit-img">
                            <?php endif; ?>
                        </div>
                        <div class="sig-unit-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Single Signature Usia 5, 6, 7 th (Landscape): HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> position: absolute; bottom: <?= 12 + (int)($template['offset_ttd'] ?? 0) ?>mm; left: 50%; transform: translateX(-50%); width: 300px; text-align: center; z-index: 25;">
                    <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                    <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                        <?php if ($url2): ?>
                            <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                        <?php endif; ?>
                    </div>
                    <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                </div>

                <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? 'display:flex;' : 'display:none;' ?>">
                    <!-- TTD 1: Koordinator Wilayah -->
                    <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                        <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                        <div class="overlay-sig-img-box">
                            <?php if ($url1): ?>
                                <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                            <?php endif; ?>
                        </div>
                        <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                    </div>

                    <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                    <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                        <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,\nPengurus Provinsi PASI Jawa Tengah\nPlt. Ketua Umum")) ?></div>
                        <div class="overlay-sig-img-box">
                            <?php if ($url3): ?>
                                <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                            <?php endif; ?>
                        </div>
                        <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                    </div>

                    <!-- TTD 2: Ketua Panitia -->
                    <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                        <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                        <div class="overlay-sig-img-box">
                            <?php if ($url2): ?>
                                <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                            <?php endif; ?>
                        </div>
                        <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- CONTAINER 2: MODERN CYBER GRID (TECH & ATHLETIC SPORT GRID) -->
        <div class="theme-variant theme-cyber_grid" style="<?= ($tipe === 'cyber_grid') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="cyber-grid-frame">
                <div class="cyber-grid-canvas">
                    <div class="cyber-corner cc-tl"></div>
                    <div class="cyber-corner cc-tr"></div>
                    <div class="cyber-corner cc-bl"></div>
                    <div class="cyber-corner cc-br"></div>

                    <div class="cyber-crosshair ch-tl">+ [ PASI.01 ]</div>
                    <div class="cyber-crosshair ch-tr">[ 2026.ID ] +</div>
                    <div class="cyber-crosshair ch-bl">+ [ GRID.SEC ]</div>
                    <div class="cyber-crosshair ch-br">[ A4.STD ] +</div>

                    <!-- Header -->
                    <div class="text-center pt-2">
                        <div class="d-flex justify-content-center align-items-center gap-3 mb-1">
                            <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 48px; width: auto;">
                            <div class="text-start">
                                <div style="font-family: 'Space Grotesk', sans-serif; font-size: 13px; font-weight: 800; color: #004B87; letter-spacing: 1.5px; text-transform: uppercase;">
                                    <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                                </div>
                                <div style="font-size: 11px; font-weight: 700; color: #00ADB5; letter-spacing: 1px; text-transform: uppercase;">
                                    CHAMPIONSHIP MERIT CERTIFICATE SYSTEM
                                </div>
                            </div>
                        </div>

                        <h1 style="font-family: 'Montserrat', sans-serif; font-size: 29px; font-weight: 900; letter-spacing: 3px; color: #0A192F; margin: 3px 0 2px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div style="font-family: 'Space Grotesk', sans-serif; font-size: 13.5px; font-weight: 800; color: #0F172A; letter-spacing: 1.5px; line-height: 1.2;">
                            NO :
                        </div>
                        <div style="font-family: 'Space Grotesk', monospace; font-size: 12.5px; font-weight: 700; color: #00ADB5; letter-spacing: 1px; margin-top: 1px; line-height: 1.2;">
                            <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="text-center my-auto px-4">
                        <p class="text-muted small mb-1" style="font-family: 'Inter', sans-serif; font-size: 13.5px;">
                            Dengan ini memberikan apresiasi dan penghargaan atas prestasi istimewa kepada :
                        </p>

                        <div style="font-family: 'Montserrat', sans-serif; font-size: 32px; font-weight: 900; color: #004B87; text-transform: uppercase; letter-spacing: 1.5px; margin: 3px 0;">
                            <?= esc($item['nama_pemenang']) ?>
                        </div>



                        <div class="d-inline-flex align-items-center gap-2 px-4 py-1 rounded-pill mb-2" style="background: linear-gradient(135deg, #004B87 0%, #00ADB5 100%); color: #FFF; font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: 14px; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(0,75,135,0.25);">
                            <i class="bi bi-trophy-fill text-warning"></i> JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                        </div>

                        <p class="mx-auto mb-0" style="max-width: 820px; font-size: 14px; line-height: 1.6; color: #1E293B;">
                            Atas partisipasi dan prestasi luar biasa dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures -->
                    <!-- Signatures Resmi -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 3: ROYAL GUILLOCHE GOLD (MEWAH BERGENGSI) -->
        <div class="theme-variant theme-royal_guilloche" style="<?= ($tipe === 'royal_guilloche') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="royal-guilloche-frame">
                <div class="royal-guilloche-outer">
                    <div class="royal-guilloche-inner">
                        <div class="royal-rosette rr-tl">❖</div>
                        <div class="royal-rosette rr-tr">❖</div>
                        <div class="royal-rosette rr-bl">❖</div>
                        <div class="royal-rosette rr-br">❖</div>

                        <!-- Header -->
                        <div class="text-center pt-2">
                            <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 52px; width: auto;" class="mb-1">
                            <div style="font-family: 'Cinzel', serif; font-size: 13.5px; font-weight: 800; letter-spacing: 2px; color: #694C1B; text-transform: uppercase;">
                                <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                            </div>
                            <h1 style="font-family: 'Cinzel', serif; font-size: 32px; font-weight: 900; letter-spacing: 4px; color: #1E1B18; margin: 3px 0 2px 0;">
                                PIAGAM PENGHARGAAN
                            </h1>
                            <div style="font-family: 'Cinzel', serif; font-size: 13.5px; font-weight: 800; color: #8C6D37; letter-spacing: 2px; line-height: 1.2;">
                                NO :
                            </div>
                            <div style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 13px; font-weight: 700; color: #694C1B; letter-spacing: 1px; margin-top: 1px; line-height: 1.2;">
                                <?= esc($cleanNomor) ?>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="text-center my-auto px-4">
                            <p class="mb-1" style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 14.5px; color: #4A443A;">
                                Diberikan dengan penuh kehormatan dan kebanggaan tertinggi kepada :
                            </p>

                            <div style="font-family: 'Playfair Display', serif; font-size: 35px; font-weight: 900; color: #1A365D; letter-spacing: 1.5px; text-transform: uppercase; margin: 3px 0;">
                                <?= esc($item['nama_pemenang']) ?>
                            </div>



                            <div class="d-inline-flex align-items-center gap-2 px-4 py-1 rounded" style="background: #FEF3C7; border: 1.5px solid #D97706; color: #92400E; font-family: 'Cinzel', serif; font-weight: 900; font-size: 14.5px; letter-spacing: 1px;">
                                <i class="bi bi-star-fill text-warning"></i> JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                            </div>

                            <p class="mx-auto mb-0" style="max-width: 820px; font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6; color: #2D3748; margin-top: 8px;">
                                Atas dedikasi, sportivitas, dan kemenangan gemilang dalam ajang bergengsi <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                            </p>
                        </div>

                        <!-- Signatures -->
                        <!-- Signatures Resmi -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 4: ATHLETIC TRACK 8-LANE STRIPES (LINTASAN LARI TARTAN) -->
        <div class="theme-variant theme-athletic_stripes" style="<?= ($tipe === 'athletic_stripes') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="athletic-track-frame">
                <div class="athletic-track-border">
                    <!-- Header -->
                    <div class="text-center pt-2">
                        <div class="d-flex justify-content-between align-items-center px-3 mb-1">
                            <span class="track-lane-badge"><i class="bi bi-lightning-charge-fill"></i> LANE 01-08</span>
                            <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 50px; width: auto;">
                            <span class="track-lane-badge"><i class="bi bi-stopwatch-fill"></i> OFFICIAL TRACK</span>
                        </div>

                        <div style="font-family: 'Montserrat', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 1px; color: #A8201A; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Montserrat', sans-serif; font-size: 30px; font-weight: 900; letter-spacing: 2px; color: #0F172A; margin: 3px 0 2px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div style="font-family: 'Montserrat', sans-serif; font-size: 13px; font-weight: 800; color: #A8201A; letter-spacing: 1px; line-height: 1.2;">
                            NO :
                        </div>
                        <div style="font-family: 'Inter', sans-serif; font-size: 12.5px; font-weight: 700; color: #475569; margin-top: 1px; line-height: 1.2;">
                            <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="text-center my-auto px-4">
                        <p class="text-muted small mb-1" style="font-size: 14px;">
                            Diberikan kepada atlet berprestasi pada kejuaraan atletik :
                        </p>

                        <div style="font-family: 'Montserrat', sans-serif; font-size: 33px; font-weight: 900; color: #0F172A; text-transform: uppercase; letter-spacing: 1.5px; margin: 2px 0;">
                            <?= esc($item['nama_pemenang']) ?>
                        </div>



                        <div class="d-inline-flex align-items-center gap-2 px-4 py-1 rounded-pill mb-2" style="background: #A8201A; color: #FFF; font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 14px; letter-spacing: 1px;">
                            <i class="bi bi-award-fill text-warning"></i> JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                        </div>

                        <p class="mx-auto mb-0" style="max-width: 820px; font-size: 14px; line-height: 1.6; color: #1E293B;">
                            Sebagai Juara pada perlombaan <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures -->
                    <!-- Signatures Resmi -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 5: GEOMETRIC GOLD HEX GRID (POLIGON EMAS MEWAH) -->
        <div class="theme-variant theme-gold_grid" style="<?= ($tipe === 'gold_grid') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="gold-hex-frame">
                <div class="gold-hex-canvas">
                    <div class="hex-corner-diamond hcd-tl">◆</div>
                    <div class="hex-corner-diamond hcd-tr">◆</div>
                    <div class="hex-corner-diamond hcd-bl">◆</div>
                    <div class="hex-corner-diamond hcd-br">◆</div>

                    <!-- Header -->
                    <div class="text-center pt-2">
                        <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 50px; width: auto;" class="mb-1">
                        <div style="font-family: 'Cinzel', serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; color: #004B87; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Cinzel', serif; font-size: 30px; font-weight: 900; letter-spacing: 3px; color: #0A192F; margin: 3px 0 2px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div style="font-family: 'Cinzel', serif; font-size: 13px; font-weight: 800; color: #B45309; letter-spacing: 1.5px; line-height: 1.2;">
                            NO :
                        </div>
                        <div style="font-family: 'Inter', sans-serif; font-size: 12.5px; font-weight: 700; color: #1F2937; letter-spacing: 0.8px; margin-top: 1px; line-height: 1.2;">
                            <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="text-center my-auto px-4">
                        <p class="text-muted small mb-1" style="font-style: italic;">
                            Dengan ini menganugerahkan Piagam Apresiasi dan Kehormatan kepada :
                        </p>

                        <div style="font-family: 'Playfair Display', serif; font-size: 34px; font-weight: 900; color: #004B87; text-transform: uppercase; letter-spacing: 1px; margin: 3px 0;">
                            <?= esc($item['nama_pemenang']) ?>
                        </div>



                        <div class="d-inline-flex align-items-center gap-2 px-4 py-1 rounded mb-2" style="background: #FFFBEB; border: 1.5px solid #F59E0B; color: #B45309; font-family: 'Cinzel', serif; font-weight: 800; font-size: 14px;">
                            <i class="bi bi-trophy-fill text-warning"></i> JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                        </div>

                        <p class="mx-auto mb-0" style="max-width: 820px; font-size: 14px; line-height: 1.6; color: #1F2937;">
                            Atas dedikasi tinggi dan prestasi gemilang dalam ajang <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures -->
                    <!-- Signatures Resmi -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 6: MINIMALIST SWISS GRID (ARSITEKTUR BERSIH) -->
        <div class="theme-variant theme-swiss_grid" style="<?= ($tipe === 'swiss_grid') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="swiss-grid-frame">
                <div class="swiss-grid-canvas">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 border-dark">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 48px; width: auto;">
                            <div>
                                <div style="font-family: 'Space Grotesk', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 1px; color: #0F172A; text-transform: uppercase;">
                                    <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                                </div>
                                <div style="font-size: 11px; font-weight: 600; color: #64748B;">CERTIFICATE OF ATHLETIC ACHIEVEMENT</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-dark text-white px-3 py-1 font-monospace">A4 / SWISS GRID</span>
                            <div class="small fw-bold text-dark mt-1 font-monospace">NO :</div>
                            <div class="small fw-bold text-dark font-monospace"><?= esc($cleanNomor) ?></div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="text-center my-auto px-4">
                        <h1 style="font-family: 'Montserrat', sans-serif; font-size: 28px; font-weight: 900; letter-spacing: 2px; color: #0F172A; margin-bottom: 8px;">
                            PIAGAM PENGHARGAAN
                        </h1>

                        <p class="text-muted small mb-1">Diberikan secara terhormat kepada atlet :</p>

                        <div style="font-family: 'Montserrat', sans-serif; font-size: 34px; font-weight: 900; color: #0F172A; text-transform: uppercase; letter-spacing: 2px; margin: 4px 0;">
                            <?= esc($item['nama_pemenang']) ?>
                        </div>



                        <div class="d-inline-flex align-items-center gap-2 px-4 py-2 border border-2 border-dark rounded mb-2" style="background: #F8FAFC; color: #0F172A; font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: 15px;">
                            <i class="bi bi-trophy-fill text-warning"></i> JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                        </div>

                        <p class="mx-auto mb-0" style="max-width: 820px; font-size: 14px; line-height: 1.6; color: #334155;">
                            Atas keberhasilan pada kejuaraan <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang berlangsung di <strong><?= esc($item['tempat']) ?></strong> pada <strong><?= esc($item['tanggal']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTAINER 7: MODERN NAVY & GOLD (A4 LANDSCAPE VEKTOR) -->
        <div class="theme-variant theme-full_css" style="<?= ($tipe === 'full_css' && $isLandscape) ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="vector-landscape-frame">
                <div class="vector-border-outer">
                    <div class="vector-corner vc-tl"></div>
                    <div class="vector-corner vc-tr"></div>
                    <div class="vector-corner vc-bl"></div>
                    <div class="vector-corner vc-br"></div>

                    <!-- Header -->
                    <div class="text-center pt-2">
                        <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 52px; width: auto;" class="mb-1">
                        <div style="font-family: 'Montserrat', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 1px; color: #004B87; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Cinzel', serif; font-size: 28px; font-weight: 900; letter-spacing: 3px; color: #0A192F; margin: 4px 0 2px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div style="font-family: 'Montserrat', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 1.5px; color: #004B87; line-height: 1.2;">
                            NO :
                        </div>
                        <div style="font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 1px; color: #4B5563; margin-top: 1px; line-height: 1.2;">
                            <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="text-center my-auto px-4">
                        <p class="text-muted small mb-1" style="font-style: italic;">
                            <?= esc($template['kalimat_pembuka'] ?: 'Dengan ini memberikan penghargaan dan apresiasi setinggi-tingginya kepada :') ?>
                        </p>
                        
                        <div style="font-family: 'Playfair Display', serif; font-size: 34px; font-weight: 900; color: #004B87; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;">
                            <?= esc($item['nama_pemenang']) ?>
                        </div>
                        


                        <div class="d-inline-block px-3 py-1 mb-2 rounded" style="background: #FEF3C7; border: 1px solid #F59E0B; color: #92400E; font-weight: 800; font-size: 14px;">
                            <i class="bi bi-trophy-fill text-warning me-1"></i> JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                        </div>

                        <p class="mx-auto" style="max-width: 800px; font-size: 13.5px; line-height: 1.6; color: #1F2937; margin-top: 6px;">
                            Atas prestasi gemilang dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTAINER 8: PORTRAIT FORMAT RESMI EKS-KARESIDENAN -->
        <div class="theme-variant theme-portrait" style="<?= (!$hasBg && $tipe !== 'overlay' && ($orientasi === 'portrait' || $tipe === 'portrait_ekskari' || $currentTipe === 'portrait_ekskari')) ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="portrait-frame">
                <!-- SVG Vector Artwork: 100% Persis Scan Resmi PASI Jateng Eks-Kari -->
                <svg class="portrait-bg-svg" viewBox="0 0 584 848" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="goldBannerGrad_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#F59E0B"/>
                            <stop offset="25%" stop-color="#FBBF24"/>
                            <stop offset="70%" stop-color="#FCD34D"/>
                            <stop offset="100%" stop-color="#F59E0B"/>
                        </linearGradient>
                        <linearGradient id="cyanFloorGrad_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#80DEEA"/>
                            <stop offset="100%" stop-color="#4DD0E1"/>
                        </linearGradient>
                    </defs>

                    <!-- 1. Top-Right Cyan Hanging Trapezoid (Image 3) -->
                    <polygon points="400,0 447,0 447,85 400,60" fill="#4DD0E1"/>

                    <!-- 2. Bottom-Right 3D Blue Pillar (Image 2) -->
                    <polygon points="480,664 555,652 555,837 480,837" fill="#1565C0"/>

                    <!-- 3. Bottom Cyan Perspective Floor (Image 2) -->
                    <polygon points="185,789 480,762 555,754 555,837 185,837" fill="url(#cyanFloorGrad_<?= $idx ?>)"/>

                    <!-- 4. Bottom-Left 3D Pink Box (Image 2) -->
                    <!-- Top Face (Lighter Rose) -->
                    <polygon points="27,765 97,751 185,789 97,795" fill="#F06292"/>
                    <!-- Front Face (Vibrant Pink) -->
                    <polygon points="27,765 97,795 97,837 27,837" fill="#E91E63"/>
                    <!-- Right Face (Darker Pink Shading) -->
                    <polygon points="97,795 185,789 185,837 97,837" fill="#C2185B"/>

                    <!-- 5. Top Banner Background Shapes (Image 1) - Rata Horizontal -->
                    <!-- Pink Wedge (extends left outside frame) -->
                    <polygon points="27,260 70,260 27,330" fill="#E91E63"/>
                    <!-- Yellow Gold Banner (Rata Horizontal Y=260) -->
                    <polygon points="27,124 448,124 448,260 27,260" fill="url(#goldBannerGrad_<?= $idx ?>)"/>
                    <!-- Right Cyan Block (extends right outside frame) -->
                    <polygon points="448,124 555,124 555,238 448,260" fill="#4DD0E1"/>

                    <!-- 6. Black Perimeter Frame Line (Overlaps all decorative shapes) -->
                    <rect x="38" y="13" width="511" height="810" fill="none" stroke="#111827" stroke-width="2"/>
                </svg>

                <!-- Header Atas: Instansi / Kejuaraan -->
                <div class="portrait-top-header cert-drag-item" data-drag-key="kop" data-drag-name="Kop Instansi">
                    <?php 
                    foreach ($displayHeaderLines as $hline): 
                        if (trim($hline) === '') continue;
                    ?>
                        <div class="portrait-header-line"><?= esc(trim($hline)) ?></div>
                    <?php endforeach; ?>
                    <?php if (!$isUsia567): ?>
                        <div class="portrait-header-line">TAHUN <?= esc($item['tahun'] ?? date('Y')) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Gold Banner Text Overlay -->
                <div class="portrait-gold-banner-overlay cert-drag-item" data-drag-key="judul" data-drag-name="Banner Judul & Nomor">
                    <div class="portrait-banner-title">
                        PIAGAM<br>PENGHARGAAN
                    </div>
                    <div class="portrait-banner-nomor">
                        NOMOR : <span class="cert-live-nomor cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit nomor piagam"><?= esc($cleanNomor) ?></span>
                    </div>
                </div>

                <!-- Cyan Block PASI Shield Logo -->
                <div class="portrait-banner-pasi-box cert-drag-item" data-drag-key="logo" data-drag-name="Logo PASI">
                    <img src="<?= base_url('assets/images/pasi_logo_transparent.png') ?>" alt="Logo PASI" class="portrait-banner-logo">
                </div>

                <!-- Body Content -->
                <div class="portrait-content-body">
                    <p class="portrait-intro-p cert-drag-item" data-drag-key="pembuka" data-drag-name="Kalimat Pembuka">
                        <?= esc($template['kalimat_pembuka'] ?: 'Pengurus Provinsi Persatuan Atletik Seluruh Indonesia (PASI) Jawa Tengah dengan ini memberikan Piagam Penghargaan kepada :') ?>
                    </p>

                    <!-- Tabel 4 Data Atlet (Nama, Lomba, Juara, Prestasi) -->
                    <table class="portrait-data-table">
                        <tr class="cert-drag-item" data-drag-key="row_nama" data-drag-name="Nama Atlet">
                            <td class="td-label">Nama</td>
                            <td class="td-sep">:</td>
                            <td class="td-val val-nama cert-live-nama cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik langsung untuk edit nama"><?= esc($item['nama_pemenang']) ?></td>
                        </tr>
                        <tr class="cert-drag-item" data-drag-key="row_lomba" data-drag-name="Nomor Lomba">
                            <td class="td-label">Nomor Lomba</td>
                            <td class="td-sep">:</td>
                            <td class="td-val cert-live-lomba cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit lomba"><?= esc($displayNomorLomba) ?></td>
                        </tr>
                        <tr class="cert-drag-item" data-drag-key="row_juara" data-drag-name="Sebagai Juara">
                            <td class="td-label">Sebagai Juara</td>
                            <td class="td-sep">:</td>
                            <td class="td-val cert-live-juara cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit juara"><?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>)</td>
                        </tr>
                        <tr class="cert-drag-item" data-drag-key="row_prestasi" data-drag-name="Prestasi">
                            <td class="td-label">Prestasi</td>
                            <td class="td-sep">:</td>
                            <td class="td-val cert-live-prestasi cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit prestasi"><?= esc($displayPrestasi) ?></td>
                        </tr>
                    </table>

                    <!-- Kalimat Penutup & 6 Titik Vertikal -->
                    <div class="portrait-closing-box cert-drag-item" data-drag-key="penutup" data-drag-name="Kalimat Penutup">
                        <p class="portrait-closing-p">
                            <?= esc($displayKalimatPenutup) ?>
                        </p>
                        <div class="portrait-six-dots-col">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>
                    </div>
                </div>

                <!-- Bagian Tanda Tangan: 100% Persis Scan Resmi (Mengetahui, Ketua Umum PASI Jateng) -->
                <?php 
                $hasTtd1 = !empty($template['ttd_1_image']) || !empty(trim($template['ttd_1_nama'] ?? ''));
                $hasTtd2 = !empty($template['ttd_2_image']) || !empty(trim($template['ttd_2_nama'] ?? ''));
                $showTripleSig = ($template['ttd_mode'] ?? '') === 'triple' || (($template['ttd_mode'] ?? '') !== 'single' && ($hasTtd1 || $hasTtd2 || !empty($template['show_all_signatures'])));

                $img3 = $template['ttd_3_image'] ?? 'ttd_agus_widodo_pasi_jateng.png';
                if (empty($img3) || $img3 === 'ttd_rumini_clean.png' || $img3 === 'rumini_full_block.png' || $img3 === 'rumini_exact_stamp_sig.png' || $img3 === 'crop_rumini_stamp_scribble.png') {
                    if (stripos($template['ttd_3_nama'] ?? '', 'Agus') !== false) {
                        $img3 = 'ttd_agus_widodo_pasi_jateng.png';
                    } else {
                        $img3 = 'ttd1_pasi_combined.png';
                    }
                }
                $url3 = null;
                if (!empty($img3)) {
                    if (file_exists(FCPATH . 'uploads/piagam/ttd/' . $img3)) {
                        $v3 = filemtime(FCPATH . 'uploads/piagam/ttd/' . $img3);
                        $url3 = base_url('uploads/piagam/ttd/' . $img3) . '?v=' . $v3;
                    } elseif (file_exists(FCPATH . 'assets/images/' . $img3)) {
                        $v3 = filemtime(FCPATH . 'assets/images/' . $img3);
                        $url3 = base_url('assets/images/' . $img3) . '?v=' . $v3;
                    }
                }

                $img1 = $template['ttd_1_image'] ?? 'ttd_jumanto_pasi_pekalongan.png';
                if (empty($img1) || $img1 === 'ttd_jumanto_clean.png') {
                    $img1 = 'ttd_jumanto_pasi_pekalongan.png';
                }
                $url1 = null;
                if (!empty($img1)) {
                    if (file_exists(FCPATH . 'uploads/piagam/ttd/' . $img1)) {
                        $v1 = filemtime(FCPATH . 'uploads/piagam/ttd/' . $img1);
                        $url1 = base_url('uploads/piagam/ttd/' . $img1) . '?v=' . $v1;
                    } elseif (file_exists(FCPATH . 'assets/images/' . $img1)) {
                        $v1 = filemtime(FCPATH . 'assets/images/' . $img1);
                        $url1 = base_url('assets/images/' . $img1) . '?v=' . $v1;
                    }
                }

                $img2 = $template['ttd_2_image'] ?? 'ttd_zaini_pasi_pekalongan.png';
                if (empty($img2) || stripos($img2, 'bambang') !== false) {
                    $img2 = 'ttd_zaini_pasi_pekalongan.png';
                }
                $url2 = null;
                if (!empty($img2)) {
                    if (file_exists(FCPATH . 'uploads/piagam/ttd/' . $img2)) {
                        $v2 = filemtime(FCPATH . 'uploads/piagam/ttd/' . $img2);
                        $url2 = base_url('uploads/piagam/ttd/' . $img2) . '?v=' . $v2;
                    } elseif (file_exists(FCPATH . 'assets/images/' . $img2)) {
                        $v2 = filemtime(FCPATH . 'assets/images/' . $img2);
                        $url2 = base_url('assets/images/' . $img2) . '?v=' . $v2;
                    }
                }
                ?>

                                <!-- 1. Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                <div class="portrait-official-signature-center sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?>">
                    <div class="sig-unit-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                    <div class="sig-unit-img-box">
                        <?php if ($url2): ?>
                            <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="sig-unit-img">
                        <?php endif; ?>
                    </div>
                    <div class="sig-unit-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                </div>

                <!-- 2. Triple Signatures (Korwil Kiri, Panitia Kanan, PASI Jateng Tengah Bawah) untuk KU 10-18 -->
                <div class="portrait-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? 'display:block;' : 'display:none;' ?>">
                    <div class="sig-top-pair">
                        <div class="sig-unit cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                            <div class="sig-unit-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                            <div class="sig-unit-img-box">
                                <?php if ($url1): ?>
                                    <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="sig-unit-img">
                                <?php endif; ?>
                            </div>
                            <div class="sig-unit-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                        </div>

                        <div class="sig-unit cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                            <div class="sig-unit-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                            <div class="sig-unit-img-box">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="sig-unit-img">
                                <?php endif; ?>
                            </div>
                            <div class="sig-unit-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                    </div>

                    <div class="sig-unit sig-unit-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                        <div class="sig-unit-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,\nPengurus Provinsi PASI Jawa Tengah\nPlt. Ketua Umum")) ?></div>
                        <div class="sig-unit-img-box">
                            <?php if ($url3): ?>
                                <img src="<?= $url3 ?>" alt="TTD 3 & Stempel" class="sig-unit-img">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTAINER 8B: PORTRAIT PRESTIGE EKS-KARESIDENAN (ROYAL NAVY & GOLD METALLIC DENGAN ORNAMEN TAMBAHAN) -->
        <div class="theme-variant theme-portrait_prestige" style="<?= ($tipe === 'portrait_prestige') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="portrait-frame">
                <!-- SVG Vector Artwork: Palet Royal Navy, Shimmering Imperial Gold & Ruby Crimson + Ornamen Keamanan -->
                <svg class="portrait-bg-svg" viewBox="0 0 584 848" preserveAspectRatio="none">
                    <defs>
                        <!-- Banner Navy Deep Gradient -->
                        <linearGradient id="prestigeBannerGrad_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#061B33"/>
                            <stop offset="50%" stop-color="#0E335C"/>
                            <stop offset="100%" stop-color="#142B47"/>
                        </linearGradient>
                        <!-- Pure Shimmering Imperial Gold -->
                        <linearGradient id="prestigeGoldGrad_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#BF953F"/>
                            <stop offset="25%" stop-color="#FCF6BA"/>
                            <stop offset="50%" stop-color="#B38728"/>
                            <stop offset="75%" stop-color="#FBF5B7"/>
                            <stop offset="100%" stop-color="#AA771C"/>
                        </linearGradient>
                        <!-- Ruby Crimson Gradient -->
                        <linearGradient id="prestigeRubyGrad_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#991B1B"/>
                            <stop offset="50%" stop-color="#B91C1C"/>
                            <stop offset="100%" stop-color="#7F1D1D"/>
                        </linearGradient>
                        <!-- Metallic Gold Perspective Floor -->
                        <linearGradient id="prestigeFloorGrad_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#D4AF37"/>
                            <stop offset="50%" stop-color="#E5C378"/>
                            <stop offset="100%" stop-color="#99701E"/>
                        </linearGradient>
                        <!-- Fine Guilloche Watermark Grid -->
                        <pattern id="guillochePatternPrest_<?= $idx ?>" width="20" height="20" patternUnits="userSpaceOnUse">
                            <circle cx="10" cy="10" r="8" fill="none" stroke="#D4AF37" stroke-width="0.35" stroke-opacity="0.3"/>
                        </pattern>
                    </defs>

                    <!-- 0. Background Subtle Watermark (Guilloche Grid & PASI Silhouette) -->
                    <rect x="44" y="265" width="496" height="490" fill="url(#guillochePatternPrest_<?= $idx ?>)" opacity="0.3"/>
                    <image href="<?= base_url('assets/images/pasi-logo.png') ?>" x="192" y="340" width="200" height="200" opacity="0.04" preserveAspectRatio="xMidYMid meet"/>

                    <!-- 1. Top-Right Hanging Ornament (Imperial Gold & Crimson Accent) -->
                    <polygon points="395,0 447,0 447,88 395,62" fill="url(#prestigeGoldGrad_<?= $idx ?>)"/>
                    <polygon points="447,0 455,0 455,75 447,88" fill="url(#prestigeRubyGrad_<?= $idx ?>)"/>
                    <line x1="410" y1="0" x2="410" y2="68" stroke="#FFFFFF" stroke-width="1.2" opacity="0.7"/>
                    <line x1="430" y1="0" x2="430" y2="78" stroke="#FFFFFF" stroke-width="1.2" opacity="0.7"/>

                    <!-- 2. Bottom-Right 3D Pillar (Royal Navy dengan 3 Garis Putih Atletik / Track Lanes) -->
                    <polygon points="480,664 555,652 555,837 480,837" fill="#0A2540"/>
                    <polygon points="480,664 515,658 555,652 520,658" fill="url(#prestigeGoldGrad_<?= $idx ?>)"/>
                    <line x1="498" y1="676" x2="498" y2="828" stroke="#FFFFFF" stroke-width="2.2" opacity="0.8"/>
                    <line x1="518" y1="670" x2="518" y2="828" stroke="#FFFFFF" stroke-width="2.2" opacity="0.8"/>
                    <line x1="538" y1="664" x2="538" y2="828" stroke="#FFFFFF" stroke-width="2.2" opacity="0.8"/>

                    <!-- 3. Bottom Perspective Floor (Metallic Gold Shimmer) -->
                    <polygon points="185,789 480,762 555,754 555,837 185,837" fill="url(#prestigeFloorGrad_<?= $idx ?>)"/>

                    <!-- 4. Bottom-Left 3D Cube (Ruby Crimson, Shimmering Gold Top & Deep Wine Shading) -->
                    <polygon points="27,765 97,751 185,789 97,795" fill="url(#prestigeGoldGrad_<?= $idx ?>)"/>
                    <polygon points="27,765 97,795 97,837 27,837" fill="url(#prestigeRubyGrad_<?= $idx ?>)"/>
                    <polygon points="97,795 185,789 185,837 97,837" fill="#701A75"/>

                    <!-- 5. Top Banner Background Shapes -->
                    <!-- Left Wedge (Ruby Crimson dengan outline emas) -->
                    <polygon points="27,260 70,260 27,330" fill="url(#prestigeRubyGrad_<?= $idx ?>)"/>
                    <line x1="27" y1="330" x2="70" y2="260" stroke="#F59E0B" stroke-width="2.5"/>

                    <!-- Main Banner (Deep Royal Navy dengan Border Emas Mewah di Bawah) -->
                    <polygon points="27,124 448,124 448,260 27,260" fill="url(#prestigeBannerGrad_<?= $idx ?>)"/>
                    <line x1="27" y1="260" x2="448" y2="260" stroke="url(#prestigeGoldGrad_<?= $idx ?>)" stroke-width="4.5"/>

                    <!-- Right Wing Block (Imperial Gold Block pemegang Logo Perisai PASI) -->
                    <polygon points="448,124 555,124 555,238 448,260" fill="url(#prestigeGoldGrad_<?= $idx ?>)"/>
                    <line x1="448" y1="124" x2="555" y2="124" stroke="#0A2540" stroke-width="2"/>
                    <line x1="448" y1="260" x2="555" y2="238" stroke="#0A2540" stroke-width="2"/>

                    <!-- 6. Double Security Perimeter Frame Line with Corner Diamond Rosettes -->
                    <rect x="38" y="13" width="511" height="810" fill="none" stroke="#0A2540" stroke-width="2.5"/>
                    <rect x="42" y="17" width="503" height="802" fill="none" stroke="#D4AF37" stroke-width="1.2" stroke-dasharray="8,4" opacity="0.85"/>

                    <!-- 4 Ornamen Sudut Berlian Emas (Corner Diamond Rosettes) -->
                    <polygon points="42,13 46,17 42,21 38,17" fill="#D4AF37"/>
                    <polygon points="545,13 549,17 545,21 541,17" fill="#D4AF37"/>
                    <polygon points="42,815 46,819 42,823 38,819" fill="#D4AF37"/>
                    <polygon points="545,815 549,819 545,823 541,819" fill="#D4AF37"/>
                </svg>

                <!-- Header Atas: Instansi / Kejuaraan (Deep Royal Navy, Gagah & Berwibawa) -->
                <div class="portrait-top-header">
                    <?php 
                    foreach ($displayHeaderLines as $hline): 
                        if (trim($hline) === '') continue;
                    ?>
                        <div class="portrait-header-line" style="color: #071E3D; font-weight: 900; letter-spacing: 1.2px;"><?= esc(trim($hline)) ?></div>
                    <?php endforeach; ?>
                    <?php if (!$isUsia567): ?>
                        <div class="portrait-header-line" style="color: #8C6D37; font-weight: 800; letter-spacing: 1.5px;">TAHUN <?= esc($item['tahun'] ?? date('Y')) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Gold & Navy Banner Text Overlay -->
                <div class="portrait-gold-banner-overlay">
                    <div class="portrait-banner-title" style="color: #F8E7B9 !important; text-shadow: 0 2px 4px rgba(0,0,0,0.6), 0 0 14px rgba(245,158,11,0.35) !important; font-family: 'Cinzel', 'Montserrat', serif; letter-spacing: 2px;">
                        PIAGAM<br>PENGHARGAAN
                    </div>
                    <div class="portrait-banner-nomor" style="color: #FDE68A !important; font-weight: 800; letter-spacing: 1.8px; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">
                        NOMOR : <span class="cert-live-nomor cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit nomor piagam"><?= esc($cleanNomor) ?></span>
                    </div>
                </div>

                <!-- Gold Block PASI Shield Logo -->
                <div class="portrait-banner-pasi-box" style="filter: drop-shadow(0 3px 6px rgba(0,0,0,0.3));">
                    <img src="<?= base_url('assets/images/pasi_logo_transparent.png') ?>" alt="Logo PASI" class="portrait-banner-logo">
                </div>

                <!-- Body Content -->
                <div class="portrait-content-body">
                    <p class="portrait-intro-p" style="color: #1E293B; font-weight: 500;">
                        <?= esc($displayKalimatPembuka) ?>
                    </p>

                    <!-- Tabel 4 Data Atlet (Nama, Lomba, Juara, Prestasi) -->
                    <table class="portrait-data-table">
                        <tr>
                            <td class="td-label">Nama</td>
                            <td class="td-sep">:</td>
                            <td class="td-val val-nama cert-live-nama cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik langsung untuk edit nama" style="font-family: 'Cinzel', 'Montserrat', serif; font-size: <?= (int)($template['font_size_nama'] ?: 18) ?>px; font-weight: 900; color: #071E3D !important; letter-spacing: 1px;"><?= esc($item['nama_pemenang']) ?></td>
                        </tr>
                        <tr>
                            <td class="td-label">Nomor Lomba</td>
                            <td class="td-sep">:</td>
                            <td class="td-val cert-live-lomba cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit lomba" style="font-weight: 800; color: #0A2540;"><?= esc($displayNomorLomba) ?></td>
                        </tr>
                        <tr>
                            <td class="td-label">Sebagai Juara</td>
                            <td class="td-sep">:</td>
                            <td class="td-val">
                                <span class="portrait-prestige-juara-badge cert-live-juara-ribbon cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit juara">
                                    ★ JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) ★
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="td-label">Prestasi</td>
                            <td class="td-sep">:</td>
                            <td class="td-val cert-live-prestasi cert-editable-field" <?= $editableAttr ?> spellcheck="false" title="Klik untuk edit prestasi" style="font-weight: 700; color: #0A2540;"><?= esc($displayPrestasi) ?></td>
                        </tr>
                    </table>

                    <!-- Kalimat Penutup & 6 Titik Vertikal Berwarna Emas & Navy -->
                    <div class="portrait-closing-box">
                        <p class="portrait-closing-p" style="color: #334155; line-height: 1.5;">
                            <?= esc($displayKalimatPenutup) ?>
                        </p>
                        <div class="portrait-six-dots-col">
                            <span class="dot" style="background-color: #D4AF37 !important;"></span>
                            <span class="dot" style="background-color: #0A2540 !important;"></span>
                            <span class="dot" style="background-color: #D4AF37 !important;"></span>
                            <span class="dot" style="background-color: #0A2540 !important;"></span>
                            <span class="dot" style="background-color: #D4AF37 !important;"></span>
                            <span class="dot" style="background-color: #D4AF37 !important;"></span>
                        </div>
                    </div>
                </div>

                <!-- Bagian Tanda Tangan: Otomatis Single (Usia 5-7 th) vs Triple (KU 10-18) -->
                <!-- 1. Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                <div class="portrait-official-signature-center sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?>">
                    <div class="sig-unit-title" style="color: #071E3D; font-weight: 700;"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                    <div class="sig-unit-img-box">
                        <?php if ($url2): ?>
                            <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="sig-unit-img">
                        <?php endif; ?>
                    </div>
                    <div class="sig-unit-name" style="color: #071E3D; font-weight: 800;"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                </div>

                <!-- 2. Triple Signatures Resmi Eks-Karesidenan untuk KU 10-18 -->
                <div class="portrait-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? 'display:block;' : 'display:none;' ?>">
                    <div class="sig-top-pair">
                        <!-- TTD 1: Korwil -->
                        <div class="sig-unit cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                            <div class="sig-unit-title" style="color: #071E3D; font-weight: 700;"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                            <div class="sig-unit-img-box">
                                <?php if ($url1): ?>
                                    <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="sig-unit-img">
                                <?php endif; ?>
                            </div>
                            <div class="sig-unit-name" style="color: #071E3D; font-weight: 800;"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                        </div>

                        <!-- TTD 2: Ketua Panitia / Ketua PASI -->
                        <div class="sig-unit cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Ketua PASI (Kanan)">
                            <div class="sig-unit-title" style="color: #071E3D; font-weight: 700;"><?= nl2br(esc($template['ttd_2_jabatan'] ?: 'Ketua PASI')) ?></div>
                            <div class="sig-unit-img-box">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Panitia" class="sig-unit-img">
                                <?php endif; ?>
                            </div>
                            <div class="sig-unit-name" style="color: #071E3D; font-weight: 800;"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                    </div>

                    <!-- TTD 3: Mengetahui PASI Jateng (Tengah Bawah) -->
                    <div class="sig-unit sig-unit-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                        <div class="sig-unit-title" style="color: #071E3D; font-weight: 700;"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,\nPengurus Provinsi PASI Jawa Tengah\nPlt. Ketua Umum")) ?></div>
                        <div class="sig-unit-img-box">
                            <?php if ($url3): ?>
                                <img src="<?= $url3 ?>" alt="TTD 3 & Stempel Resmi PASI Jateng" class="sig-unit-img">
                            <?php endif; ?>
                        </div>
                        <div class="sig-unit-name" style="color: #071E3D; font-weight: 800;"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTAINER: RESMI FEDERASI PASI JAWA TENGAH (ANTI-CANVA / ROYAL GUILLOCHE PORTRAIT) -->
        <div class="theme-variant theme-official_federation" style="<?= ($tipe === 'official_federation') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            
            <!-- SVG Banknote & Guilloche Frame Layer -->
            <svg class="fed-bg-svg" viewBox="0 0 794 1123" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="goldGradFed_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#C5A059"/>
                        <stop offset="30%" stop-color="#FDF1A9"/>
                        <stop offset="70%" stop-color="#B88628"/>
                        <stop offset="100%" stop-color="#9A7B38"/>
                    </linearGradient>
                    <pattern id="guillochePattern_<?= $idx ?>" width="16" height="16" patternUnits="userSpaceOnUse">
                        <path d="M0,8 Q4,0 8,8 T16,8" fill="none" stroke="#C5A059" stroke-width="0.75" opacity="0.7"/>
                        <path d="M0,8 Q4,16 8,8 T16,8" fill="none" stroke="#0B2545" stroke-width="0.5" opacity="0.4"/>
                    </pattern>
                </defs>

                <!-- 1. Background Fill Soft Warm Ivory -->
                <rect x="0" y="0" width="794" height="1123" fill="#FFFFFF"/>
                <rect x="18" y="18" width="758" height="1087" fill="#FCFBF7"/>

                <!-- 2. Outer Deep Navy Frame Line -->
                <rect x="22" y="22" width="750" height="1079" rx="3" fill="none" stroke="#0B2545" stroke-width="4"/>

                <!-- 3. Guilloche Ribbon Border -->
                <rect x="28" y="28" width="738" height="1067" rx="2" fill="none" stroke="#C5A059" stroke-width="2"/>
                <rect x="33" y="33" width="728" height="1057" fill="none" stroke="url(#guillochePattern_<?= $idx ?>)" stroke-width="6"/>

                <!-- 4. Inner Fine Gold & Navy Hairline -->
                <rect x="38" y="38" width="718" height="1047" fill="none" stroke="#C5A059" stroke-width="1.2"/>
                <rect x="42" y="42" width="710" height="1039" fill="none" stroke="#0B2545" stroke-width="0.75" stroke-dasharray="4,2"/>

                <!-- 5. Four Royal Corner Rosettes (SVG Ornaments) -->
                <!-- Top-Left Corner (42, 42) -->
                <g transform="translate(42, 42)">
                    <rect x="-8" y="-8" width="16" height="16" fill="#0B2545"/>
                    <polygon points="0,-6 6,0 0,6 -6,0" fill="#FBBF24"/>
                    <path d="M 0,0 L 28,0 M 0,0 L 0,28" stroke="#C5A059" stroke-width="2"/>
                    <path d="M 4,4 Q 18,6 20,20 Q 6,18 4,4 Z" fill="url(#goldGradFed_<?= $idx ?>)" opacity="0.85"/>
                    <circle cx="16" cy="16" r="3" fill="#0B2545"/>
                </g>
                <!-- Top-Right Corner (752, 42) -->
                <g transform="translate(752, 42)">
                    <rect x="-8" y="-8" width="16" height="16" fill="#0B2545"/>
                    <polygon points="0,-6 6,0 0,6 -6,0" fill="#FBBF24"/>
                    <path d="M 0,0 L -28,0 M 0,0 L 0,28" stroke="#C5A059" stroke-width="2"/>
                    <path d="M -4,4 Q -18,6 -20,20 Q -6,18 -4,4 Z" fill="url(#goldGradFed_<?= $idx ?>)" opacity="0.85"/>
                    <circle cx="-16" cy="16" r="3" fill="#0B2545"/>
                </g>
                <!-- Bottom-Left Corner (42, 1081) -->
                <g transform="translate(42, 1081)">
                    <rect x="-8" y="-8" width="16" height="16" fill="#0B2545"/>
                    <polygon points="0,-6 6,0 0,6 -6,0" fill="#FBBF24"/>
                    <path d="M 0,0 L 28,0 M 0,0 L 0,-28" stroke="#C5A059" stroke-width="2"/>
                    <path d="M 4,-4 Q 18,-6 20,-20 Q 6,-18 4,-4 Z" fill="url(#goldGradFed_<?= $idx ?>)" opacity="0.85"/>
                    <circle cx="16" cy="-16" r="3" fill="#0B2545"/>
                </g>
                <!-- Bottom-Right Corner (752, 1081) -->
                <g transform="translate(752, 1081)">
                    <rect x="-8" y="-8" width="16" height="16" fill="#0B2545"/>
                    <polygon points="0,-6 6,0 0,6 -6,0" fill="#FBBF24"/>
                    <path d="M 0,0 L -28,0 M 0,0 L 0,-28" stroke="#C5A059" stroke-width="2"/>
                    <path d="M -4,-4 Q -18,-6 -20,-20 Q -6,-18 -4,-4 Z" fill="url(#goldGradFed_<?= $idx ?>)" opacity="0.85"/>
                    <circle cx="-16" cy="-16" r="3" fill="#0B2545"/>
                </g>

                <!-- Center Top & Bottom Diamond Emblems -->
                <g transform="translate(397, 28)">
                    <polygon points="0,-6 8,0 0,6 -8,0" fill="#C5A059"/>
                    <circle cx="0" cy="0" r="2.5" fill="#0B2545"/>
                </g>
                <g transform="translate(397, 1095)">
                    <polygon points="0,-6 8,0 0,6 -8,0" fill="#C5A059"/>
                    <circle cx="0" cy="0" r="2.5" fill="#0B2545"/>
                </g>
            </svg>

            <!-- Watermark PASI Logo di Tengah -->
            <div class="fed-watermark">
                <img src="<?= base_url('assets/images/pasi_logo_transparent.png') ?>" alt="Watermark PASI">
            </div>

            <!-- Konten Sertifikat Resmi -->
            <div class="fed-content-container">
                
                <!-- HEADER SECTION: KOP RESMI PENGPROV PASI JATENG -->
                <div class="fed-header-section">
                    <div class="fed-logo-box">
                        <img src="<?= base_url('assets/images/pasi_logo_transparent.png') ?>" alt="Logo PASI" class="fed-logo-img">
                    </div>
                    <?php if ($isUsia567): ?>
                        <div class="fed-instansi-title">
                            KEJUARAAN<br>ATLETIK<br><?= esc($usiaLabelHeader) ?>
                        </div>
                        <div class="fed-instansi-prov" style="letter-spacing: 2px;">
                            TAHUN <?= esc($item['tahun'] ?? '2026') ?>
                        </div>
                        <div class="fed-event-sub">
                            TINGKAT KOTA PEKALONGAN, KAB. PEKALONGAN, DAN KAB. BATANG
                        </div>
                    <?php else: ?>
                        <div class="fed-instansi-title">
                            PENGURUS PROVINSI PERSATUAN ATLETIK SELURUH INDONESIA
                        </div>
                        <div class="fed-instansi-prov">
                            JAWA TENGAH
                        </div>
                        <div class="fed-event-sub">
                            <?= esc($template['sub_header'] ?: 'KEJUARAAN ATLETIK KELOMPOK UMUR TINGKAT EKS-KARESIDENAN PEKALONGAN TAHUN ' . ($item['tahun'] ?? date('Y'))) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="fed-divider-ornament">
                        <span class="line"></span>
                        <span class="diamonds">◆ ❖ ◆</span>
                        <span class="line"></span>
                    </div>

                    <div class="fed-title-box">
                        <h1 class="fed-main-title"><?= esc($template['judul'] ?: 'SERTIFIKAT PENGHARGAAN') ?></h1>
                        <div class="fed-nomor-doc">NOMOR : <?= esc($cleanNomor) ?></div>
                    </div>
                </div>

                <!-- BODY SECTION: PENERIMA & PRESTASI JUARA -->
                <div class="fed-body-section">
                    <div class="fed-intro-text">
                        <?= esc($displayKalimatPembuka) ?>
                    </div>

                    <div class="fed-athlete-name">
                        <?= esc($item['nama_pemenang']) ?>
                    </div>

                    <div class="fed-merit-text">
                        Atas prestasi gemilang yang berhasil diraih sebagai :
                    </div>

                    <!-- CENTERPIECE: PLAQUE MEDALI JUARA & DETAIL LOMBA -->
                    <div class="fed-plaque-badge">
                        <div class="fed-badge-rank">
                            <span class="star">★</span> JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) <span class="star">★</span>
                        </div>
                        <div class="fed-badge-details">
                            <div class="item">
                                <span class="label">Nomor Lomba :</span>
                                <span class="val"><?= esc($displayNomorLomba) ?></span>
                            </div>
                            <?php if (!empty($item['prestasi']) && $item['prestasi'] !== '-'): ?>
                                <div class="item">
                                    <span class="label">Prestasi :</span>
                                    <span class="val"><?= esc($displayPrestasi) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="fed-closing-text">
                        <?= esc($displayKalimatPenutup) ?>
                    </p>
                </div>

                <!-- SIGNATURES SECTION: 3 PEJABAT BERWENANG PASI & PANITIA -->
                <div class="fed-signatures-section">
                    <div class="fed-date-city">
                        <?= $isUsia567 ? 'Pekalongan, 27 September 2026' : 'Slawi, ' . esc($item['tanggal']) ?>
                    </div>

                    <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                    <div class="fed-sig-center-cell sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:flex;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto;">
                        <div class="fed-sig-title">
                            <?= nl2br(esc($jabatanKetuaPekalongan)) ?>
                        </div>
                        <div class="fed-sig-img-box">
                            <?php if ($url2): ?>
                                <img src="<?= $url2 ?>" alt="TTD & Stempel PASI Kota Pekalongan">
                            <?php endif; ?>
                        </div>
                        <div class="fed-sig-name">
                            <?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?>
                        </div>
                    </div>

                    <!-- Triple Signatures untuk KU 10-18 -->
                    <div class="sig-container-triple" style="<?= !$isUsia567 ? 'display:block;' : 'display:none;' ?> width: 100%;">
                        <div class="fed-sig-grid">
                            <div class="fed-sig-cell cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="fed-sig-title">
                                    <?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?>
                                </div>
                                <div class="fed-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1">
                                    <?php endif; ?>
                                </div>
                                <div class="fed-sig-name">
                                    <?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?>
                                </div>
                            </div>

                            <div class="fed-sig-cell cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="fed-sig-title">
                                    <?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?>
                                </div>
                                <div class="fed-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2">
                                    <?php endif; ?>
                                </div>
                                <div class="fed-sig-name">
                                    <?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?>
                                </div>
                            </div>
                        </div>

                        <div class="fed-sig-center-cell cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                            <div class="fed-sig-title">
                                <?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,\nPengurus Provinsi PASI Jawa Tengah\nPlt. Ketua Umum")) ?>
                            </div>
                            <div class="fed-sig-img-box">
                                <?php if ($url3): ?>
                                    <img src="<?= $url3 ?>" alt="TTD 3 & Stempel">
                                <?php endif; ?>
                            </div>
                            <div class="fed-sig-name">
                                <?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- CONTAINER 9: CANVA LUXURY BLACK & GOLD WAVES (DANIEL GALLEGO STYLE) -->
        <div class="theme-variant theme-canva_waves" style="<?= ($tipe === 'canva_waves') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="canva-frame">
                <!-- SVG Vector Corners (Infinitely Sharp, High-Res Print) -->
                <svg class="canva-svg-corners" viewBox="0 0 1122 793" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="cwGold_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#C5A059"/>
                            <stop offset="30%" stop-color="#FDF1A9"/>
                            <stop offset="70%" stop-color="#B88628"/>
                            <stop offset="100%" stop-color="#8C6317"/>
                        </linearGradient>
                        <linearGradient id="cwDark_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#1E293B"/>
                            <stop offset="100%" stop-color="#090D16"/>
                        </linearGradient>
                    </defs>

                    <!-- Left & Top-Left Sweeping Waves -->
                    <path d="M 0,0 L 260,0 C 205,115 110,230 70,370 C 35,490 0,550 0,550 Z" fill="url(#cwDark_<?= $idx ?>)"/>
                    <path d="M 0,0 L 295,0 C 235,125 125,250 82,395 C 45,515 0,580 0,580 L 0,550 C 0,550 35,490 70,370 C 110,230 205,115 260,0 Z" fill="url(#cwGold_<?= $idx ?>)"/>
                    <path d="M 310,0 C 250,135 135,265 90,410 C 50,535 0,605 0,605" fill="none" stroke="url(#cwGold_<?= $idx ?>)" stroke-width="2.5" opacity="0.85"/>

                    <!-- Bottom-Right Subtle Corner Accent Only (Clear of Signatures) -->
                    <path d="M 1122,793 L 1020,793 C 1050,765 1090,735 1122,710 Z" fill="url(#cwDark_<?= $idx ?>)"/>
                    <path d="M 1122,793 L 1000,793 C 1035,760 1080,728 1122,695 L 1122,710 C 1090,735 1050,765 1020,793 Z" fill="url(#cwGold_<?= $idx ?>)"/>

                    <!-- Crown / Gold Emblem at Bottom Right -->
                    <g transform="translate(1055, 756) scale(0.9)">
                        <polygon points="-18,12 -12,-8 0,4 12,-8 18,12" fill="url(#cwGold_<?= $idx ?>)"/>
                        <circle cx="-12" cy="-11" r="2.5" fill="url(#cwGold_<?= $idx ?>)"/>
                        <circle cx="0" cy="1" r="2.5" fill="url(#cwGold_<?= $idx ?>)"/>
                        <circle cx="12" cy="-11" r="2.5" fill="url(#cwGold_<?= $idx ?>)"/>
                    </g>
                </svg>

                <!-- Content Area -->
                <div class="canva-content-wrapper">
                    <!-- Header -->
                    <div>
                        <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 50px; width: auto;" class="mb-1">
                        <div style="font-family: 'Cinzel', serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; color: #0F172A; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Cinzel', serif; font-size: 30px; font-weight: 900; letter-spacing: 4px; color: #000000; margin: 3px 0 3px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div class="canva-gold-pill-nomor">
                            <i class="bi bi-award text-warning"></i> NO. <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="my-auto py-1">
                        <p class="text-muted small mb-1" style="font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500;">
                            Piagam penghargaan ini dengan bangga diberikan kepada :
                        </p>

                        <div class="canva-name-underline-box">
                            <div class="canva-name-script">
                                <?= esc(ucwords(strtolower($item['nama_pemenang']))) ?>
                            </div>
                            <div class="canva-gold-divider-flair">
                                <span>&#10022;</span>
                            </div>
                        </div>



                        <div class="d-block my-2">
                            <div class="canva-juara-pill">
                                JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                            </div>
                        </div>

                        <p class="mx-auto" style="max-width: 820px; font-size: 14px; line-height: 1.55; color: #1E293B; margin-top: 2px;">
                            dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures -->
                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 10: CANVA MODERN MOCHA CURVES & SCATTER DOTS (MARIANA NAPOLITANI STYLE) -->
        <div class="theme-variant theme-canva_dots" style="<?= ($tipe === 'canva_dots') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="canva-frame">
                <!-- SVG Vector Corners (Infinitely Sharp, High-Res Print) -->
                <svg class="canva-svg-corners" viewBox="0 0 1122 793" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="cdGold_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#D4AF37"/>
                            <stop offset="50%" stop-color="#F3E5AB"/>
                            <stop offset="100%" stop-color="#997825"/>
                        </linearGradient>
                    </defs>

                    <!-- Top-Left Curved Corner Banner with Mocha & Gold Trim -->
                    <path d="M 0,0 L 260,0 C 200,40 130,120 70,210 C 25,275 0,330 0,330 Z" fill="#2A1B14"/>
                    <path d="M 0,0 L 290,0 C 225,45 148,135 80,230 C 30,300 0,360 0,360 L 0,330 C 0,330 25,275 70,210 C 130,120 200,40 260,0 Z" fill="url(#cdGold_<?= $idx ?>)"/>

                    <!-- 6-Dot Matrix in Top-Left Corner -->
                    <g fill="url(#cdGold_<?= $idx ?>)">
                        <circle cx="36" cy="32" r="4.5"/>
                        <circle cx="54" cy="32" r="4.5"/>
                        <circle cx="72" cy="32" r="4.5"/>
                        <circle cx="36" cy="48" r="4.5"/>
                        <circle cx="54" cy="48" r="4.5"/>
                        <circle cx="72" cy="48" r="4.5"/>
                    </g>

                    <!-- Bottom-Right Compact Curved Corner with Gold Trim (Clear of TTD 3) -->
                    <path d="M 1122,793 L 1010,793 C 1045,760 1080,710 1105,640 C 1118,600 1122,570 1122,570 Z" fill="#2A1B14"/>
                    <path d="M 1122,793 L 985,793 C 1025,755 1065,695 1092,625 C 1110,580 1122,545 1122,545 L 1122,570 C 1122,570 1118,600 1105,640 C 1080,710 1045,760 1010,793 Z" fill="url(#cdGold_<?= $idx ?>)"/>
                    <g transform="translate(1085, 765) scale(0.75)">
                        <polygon points="-18,12 -12,-8 0,4 12,-8 18,12" fill="url(#cdGold_<?= $idx ?>)"/>
                        <circle cx="-12" cy="-11" r="2.5" fill="url(#cdGold_<?= $idx ?>)"/>
                        <circle cx="0" cy="1" r="2.5" fill="url(#cdGold_<?= $idx ?>)"/>
                        <circle cx="12" cy="-11" r="2.5" fill="url(#cdGold_<?= $idx ?>)"/>
                    </g>

                    <!-- Scattered 45-degree Geometric Pills & Circles (Top-Right Margin) -->
                    <g transform="translate(1005, 110) rotate(-45)">
                        <rect x="-10" y="-85" width="22" height="70" rx="11" fill="#7C4A32"/>
                        <rect x="25" y="-55" width="22" height="55" rx="11" fill="url(#cdGold_<?= $idx ?>)"/>
                        <rect x="-35" y="-35" width="22" height="45" rx="11" fill="#2A1B14"/>
                        <rect x="52" y="-15" width="22" height="60" rx="11" fill="#7C4A32"/>
                        <circle cx="-10" cy="15" r="10" fill="#2A1B14"/>
                        <circle cx="25" cy="30" r="8" fill="url(#cdGold_<?= $idx ?>)"/>
                        <circle cx="-40" cy="45" r="11" fill="#7C4A32"/>
                        <circle cx="48" cy="72" r="9" fill="#2A1B14"/>
                    </g>

                    <!-- Scattered 45-degree Geometric Pills & Circles (Mid-Left Margin, Clear of Signatures) -->
                    <g transform="translate(35, 420) rotate(-45)">
                        <rect x="-10" y="-85" width="20" height="60" rx="10" fill="#7C4A32"/>
                        <rect x="22" y="-55" width="20" height="50" rx="10" fill="url(#cdGold_<?= $idx ?>)"/>
                        <rect x="-30" y="-35" width="20" height="40" rx="10" fill="#2A1B14"/>
                        <rect x="46" y="-15" width="20" height="52" rx="10" fill="#7C4A32"/>
                        <circle cx="-8" cy="15" r="9" fill="#2A1B14"/>
                        <circle cx="22" cy="28" r="7" fill="url(#cdGold_<?= $idx ?>)"/>
                        <circle cx="-32" cy="40" r="9" fill="#7C4A32"/>
                        <circle cx="42" cy="62" r="8" fill="#2A1B14"/>
                    </g>
                </svg>

                <!-- Content Area -->
                <div class="canva-content-wrapper">
                    <!-- Header -->
                    <div>
                        <div style="font-family: 'Montserrat', sans-serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; color: #7C4A32; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Montserrat', sans-serif; font-size: 34px; font-weight: 900; letter-spacing: 3px; color: #2A1B14; margin: 3px 0 1px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div style="font-family: 'Space Grotesk', sans-serif; font-size: 13.5px; font-weight: 800; color: #2A1B14; letter-spacing: 1.5px;">
                            Nomor: <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="my-auto py-1">
                        <p class="text-muted small mb-1" style="font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500;">
                            Dengan bangga dipersembahkan kepada :
                        </p>

                        <div class="canva-name-serif" style="color: #6C4A35; font-size: 36px; margin: 4px 0;">
                            <?= esc($item['nama_pemenang']) ?>
                        </div>

                        <div class="text-muted small mb-1" style="font-style: italic; font-weight: 600;">
                            sebagai
                        </div>

                        <div class="d-block my-2">
                            <div class="canva-juara-pill" style="background: #2A1B14; font-size: 14px;">
                                JUARA ( <?= $item['peringkat'] ?> ) <?= strtoupper(esc($item['peringkat_teks'])) ?>
                            </div>
                        </div>

                        <p class="mx-auto" style="max-width: 820px; font-size: 14px; line-height: 1.55; color: #374151; margin-top: 4px;">
                            Nomor <strong><?= esc($item['nomor_lomba']) ?></strong> dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures (Lengkap 3 Kolom) -->
                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 11: CANVA ROYAL ATHLETIC NAVY & GOLD (PASI OFFICIAL EDITION) -->
        <div class="theme-variant theme-canva_navy" style="<?= ($tipe === 'canva_navy') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="canva-frame">
                <!-- SVG Vector Corners (Infinitely Sharp, High-Res Print) -->
                <svg class="canva-svg-corners" viewBox="0 0 1122 793" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="cnGold_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#FFD700"/>
                            <stop offset="30%" stop-color="#FFF3B0"/>
                            <stop offset="70%" stop-color="#F59E0B"/>
                            <stop offset="100%" stop-color="#B45309"/>
                        </linearGradient>
                        <linearGradient id="cnNavy_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#002B49"/>
                            <stop offset="100%" stop-color="#004B87"/>
                        </linearGradient>
                    </defs>

                    <!-- Left & Top-Left Sweeping Waves in Navy & Olympic Gold -->
                    <path d="M 0,0 L 270,0 C 215,115 115,230 75,370 C 40,490 0,550 0,550 Z" fill="url(#cnNavy_<?= $idx ?>)"/>
                    <path d="M 0,0 L 305,0 C 245,125 130,250 88,395 C 48,515 0,580 0,580 L 0,550 C 0,550 40,490 75,370 C 115,230 215,115 270,0 Z" fill="url(#cnGold_<?= $idx ?>)"/>
                    <path d="M 320,0 C 260,135 140,265 95,410 C 55,535 0,605 0,605" fill="none" stroke="url(#cnGold_<?= $idx ?>)" stroke-width="2.5" opacity="0.85"/>

                    <!-- Bottom-Right Subtle Corner Accent Only (Clear of Signatures) -->
                    <path d="M 1122,793 L 1020,793 C 1050,765 1090,735 1122,710 Z" fill="url(#cnNavy_<?= $idx ?>)"/>
                    <path d="M 1122,793 L 1000,793 C 1035,760 1080,728 1122,695 L 1122,710 C 1090,735 1050,765 1020,793 Z" fill="url(#cnGold_<?= $idx ?>)"/>
                    <g transform="translate(1055, 756) scale(0.9)">
                        <polygon points="-18,12 -12,-8 0,4 12,-8 18,12" fill="url(#cnGold_<?= $idx ?>)"/>
                        <circle cx="-12" cy="-11" r="2.5" fill="url(#cnGold_<?= $idx ?>)"/>
                        <circle cx="0" cy="1" r="2.5" fill="url(#cnGold_<?= $idx ?>)"/>
                        <circle cx="12" cy="-11" r="2.5" fill="url(#cnGold_<?= $idx ?>)"/>
                    </g>
                </svg>

                <!-- Content Area -->
                <div class="canva-content-wrapper">
                    <!-- Header -->
                    <div>
                        <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 52px; width: auto;" class="mb-1">
                        <div style="font-family: 'Cinzel', serif; font-size: 13.5px; font-weight: 800; letter-spacing: 2px; color: #002B49; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Cinzel', serif; font-size: 30px; font-weight: 900; letter-spacing: 4px; color: #002B49; margin: 3px 0 3px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div class="canva-gold-pill-nomor" style="background: #002B49; border-color: #F59E0B; color: #FEF3C7;">
                            <i class="bi bi-trophy-fill text-warning"></i> NO. <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="my-auto py-1">
                        <p class="text-muted small mb-1" style="font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500;">
                            Dengan ini memberikan anugerah Piagam Penghargaan & Apresiasi kepada :
                        </p>

                        <div class="canva-name-underline-box">
                            <div class="canva-name-script" style="color: #002B49;">
                                <?= esc(ucwords(strtolower($item['nama_pemenang']))) ?>
                            </div>
                            <div class="canva-gold-divider-flair">
                                <span>&#10022;</span>
                            </div>
                        </div>



                        <div class="d-block my-2">
                            <div class="canva-juara-pill" style="background: #002B49;">
                                JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                            </div>
                        </div>

                        <p class="mx-auto" style="max-width: 820px; font-size: 14px; line-height: 1.55; color: #1E293B; margin-top: 2px;">
                            dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures -->
                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 12: CANVA EMERALD & OLYMPIC GOLD WAVES -->
        <div class="theme-variant theme-canva_emerald" style="<?= ($tipe === 'canva_emerald') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="canva-frame">
                <!-- SVG Vector Corners (Infinitely Sharp, High-Res Print) -->
                <svg class="canva-svg-corners" viewBox="0 0 1122 793" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="ceGold_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#C5A059"/>
                            <stop offset="30%" stop-color="#FDF1A9"/>
                            <stop offset="70%" stop-color="#B88628"/>
                            <stop offset="100%" stop-color="#8C6317"/>
                        </linearGradient>
                        <linearGradient id="ceGreen_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#064E3B"/>
                            <stop offset="100%" stop-color="#047857"/>
                        </linearGradient>
                    </defs>

                    <!-- Left & Top-Left Sweeping Waves in Emerald & Gold -->
                    <path d="M 0,0 L 270,0 C 215,115 115,230 75,370 C 40,490 0,550 0,550 Z" fill="url(#ceGreen_<?= $idx ?>)"/>
                    <path d="M 0,0 L 305,0 C 245,125 130,250 88,395 C 48,515 0,580 0,580 L 0,550 C 0,550 40,490 75,370 C 115,230 215,115 270,0 Z" fill="url(#ceGold_<?= $idx ?>)"/>
                    <path d="M 320,0 C 260,135 140,265 95,410 C 55,535 0,605 0,605" fill="none" stroke="url(#ceGold_<?= $idx ?>)" stroke-width="2.5" opacity="0.85"/>

                    <!-- Bottom-Right Subtle Corner Accent Only (Clear of Signatures) -->
                    <path d="M 1122,793 L 1020,793 C 1050,765 1090,735 1122,710 Z" fill="url(#ceGreen_<?= $idx ?>)"/>
                    <path d="M 1122,793 L 1000,793 C 1035,760 1080,728 1122,695 L 1122,710 C 1090,735 1050,765 1020,793 Z" fill="url(#ceGold_<?= $idx ?>)"/>
                    <g transform="translate(1055, 756) scale(0.9)">
                        <polygon points="-18,12 -12,-8 0,4 12,-8 18,12" fill="url(#ceGold_<?= $idx ?>)"/>
                        <circle cx="-12" cy="-11" r="2.5" fill="url(#ceGold_<?= $idx ?>)"/>
                        <circle cx="0" cy="1" r="2.5" fill="url(#ceGold_<?= $idx ?>)"/>
                        <circle cx="12" cy="-11" r="2.5" fill="url(#ceGold_<?= $idx ?>)"/>
                    </g>
                </svg>

                <!-- Content Area -->
                <div class="canva-content-wrapper">
                    <!-- Header -->
                    <div>
                        <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 52px; width: auto;" class="mb-1">
                        <div style="font-family: 'Cinzel', serif; font-size: 13.5px; font-weight: 800; letter-spacing: 2px; color: #064E3B; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Cinzel', serif; font-size: 30px; font-weight: 900; letter-spacing: 4px; color: #064E3B; margin: 3px 0 3px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div class="canva-gold-pill-nomor" style="background: #064E3B; border-color: #D4AF37; color: #FDF1A9;">
                            <i class="bi bi-star-fill text-warning"></i> NO. <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="my-auto py-1">
                        <p class="text-muted small mb-1" style="font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500;">
                            Piagam penghargaan ini dengan bangga diberikan kepada :
                        </p>

                        <div class="canva-name-underline-box">
                            <div class="canva-name-script" style="color: #064E3B;">
                                <?= esc(ucwords(strtolower($item['nama_pemenang']))) ?>
                            </div>
                            <div class="canva-gold-divider-flair">
                                <span>&#10022;</span>
                            </div>
                        </div>



                        <div class="d-block my-2">
                            <div class="canva-juara-pill" style="background: #064E3B;">
                                JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                            </div>
                        </div>

                        <p class="mx-auto" style="max-width: 820px; font-size: 14px; line-height: 1.55; color: #1E293B; margin-top: 2px;">
                            dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures -->
                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 13: CANVA ROYAL BLUE WAVES & GOLD BORDER (FRANCA MUTIA STYLE) -->
        <div class="theme-variant theme-canva_blue_gold" style="<?= ($tipe === 'canva_blue_gold') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="canva-frame">
                <svg class="canva-svg-corners" viewBox="0 0 1122 793" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="cbgGold_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#C5A059"/>
                            <stop offset="35%" stop-color="#FDF1A9"/>
                            <stop offset="70%" stop-color="#B88628"/>
                            <stop offset="100%" stop-color="#8C6317"/>
                        </linearGradient>
                        <linearGradient id="cbgBlue_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#002B49"/>
                            <stop offset="100%" stop-color="#004B87"/>
                        </linearGradient>
                    </defs>

                    <!-- Double Gold Rectangular Hairline Inner Border -->
                    <rect x="40" y="36" width="1042" height="732" fill="none" stroke="url(#cbgGold_<?= $idx ?>)" stroke-width="2"/>
                    <rect x="48" y="44" width="1026" height="716" fill="none" stroke="url(#cbgGold_<?= $idx ?>)" stroke-width="0.75" stroke-dasharray="8,4"/>

                    <!-- Top-Right Sweeping Royal Blue & Gold Waves -->
                    <path d="M 1122,0 L 860,0 C 915,80 990,170 1030,280 C 1065,370 1122,410 1122,410 Z" fill="url(#cbgBlue_<?= $idx ?>)"/>
                    <path d="M 1122,0 L 825,0 C 885,90 965,185 1010,300 C 1050,400 1122,440 1122,440 L 1122,410 C 1122,410 1065,370 1030,280 C 990,170 915,80 860,0 Z" fill="url(#cbgGold_<?= $idx ?>)"/>
                    <path d="M 805,0 C 865,95 945,195 995,315 C 1035,420 1122,460 1122,460" fill="none" stroke="url(#cbgGold_<?= $idx ?>)" stroke-width="2.5"/>

                    <!-- Bottom-Left Extreme Corner Only (Completely Clear of TTD 1) -->
                    <polygon points="0,793 75,793 0,718" fill="url(#cbgBlue_<?= $idx ?>)"/>
                    <polygon points="0,793 90,793 0,703" fill="none" stroke="url(#cbgGold_<?= $idx ?>)" stroke-width="2"/>
                </svg>

                <div class="canva-content-wrapper">
                    <div>
                        <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 50px; width: auto;" class="mb-1">
                        <div style="font-family: 'Cinzel', serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; color: #004B87; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Cinzel', serif; font-size: 32px; font-weight: 900; letter-spacing: 3px; color: #002B49; margin: 3px 0 2px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div class="canva-gold-pill-nomor" style="background: #002B49; border-color: #C5A059; color: #FDF1A9;">
                            <i class="bi bi-award-fill text-warning"></i> NO. <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <div class="my-auto py-1">
                        <p class="text-muted small mb-1" style="font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500;">
                            Sertifikat / Piagam Penghargaan ini diberikan kepada :
                        </p>

                        <div class="canva-name-underline-box">
                            <div class="canva-name-script" style="color: #002B49; font-size: 50px;">
                                <?= esc(ucwords(strtolower($item['nama_pemenang']))) ?>
                            </div>
                            <div class="canva-gold-divider-flair">
                                <span>&#10022;</span>
                            </div>
                        </div>



                        <div class="d-block my-2">
                            <div class="canva-juara-pill" style="background: #004B87;">
                                JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                            </div>
                        </div>

                        <p class="mx-auto" style="max-width: 820px; font-size: 14px; line-height: 1.55; color: #1E293B; margin-top: 2px;">
                            dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures (Lengkap 3 Tanda Tangan) -->
                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 14: CANVA EMERALD WAVES & GOLD DIAMOND CREST (NOAH SCHUMACHER STYLE) -->
        <div class="theme-variant theme-canva_emerald_diamond" style="<?= ($tipe === 'canva_emerald_diamond') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="canva-frame">
                <svg class="canva-svg-corners" viewBox="0 0 1122 793" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="cedGold_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#D4AF37"/>
                            <stop offset="30%" stop-color="#FFF3B0"/>
                            <stop offset="70%" stop-color="#C5A059"/>
                            <stop offset="100%" stop-color="#997825"/>
                        </linearGradient>
                        <linearGradient id="cedGreen_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#064E3B"/>
                            <stop offset="100%" stop-color="#047857"/>
                        </linearGradient>
                    </defs>

                    <!-- Top-Left Interlocking Gold Geometric Diamond Ribbon -->
                    <g transform="translate(65, 55)">
                        <polygon points="0,0 45,-25 90,0 45,25" fill="none" stroke="url(#cedGold_<?= $idx ?>)" stroke-width="5"/>
                        <polygon points="25,12 65,-10 105,12 65,34" fill="none" stroke="url(#cedGold_<?= $idx ?>)" stroke-width="3"/>
                        <polygon points="-12,8 28,-14 68,8 28,30" fill="none" stroke="url(#cedGreen_<?= $idx ?>)" stroke-width="4"/>
                    </g>

                    <!-- Top-Right Sweeping Emerald Wave & Gold Ribbon -->
                    <path d="M 1122,0 L 860,0 C 920,60 990,130 1045,230 C 1085,310 1122,360 1122,360 Z" fill="url(#cedGreen_<?= $idx ?>)"/>
                    <path d="M 1122,0 L 830,0 C 895,65 970,140 1030,245 C 1075,330 1122,385 1122,385 L 1122,360 C 1122,360 1085,310 1045,230 C 990,130 920,60 860,0 Z" fill="url(#cedGold_<?= $idx ?>)"/>

                    <!-- Bottom Corner Accent Only (Clear of Signatures) -->
                    <polygon points="1122,793 1030,793 1122,700" fill="url(#cedGreen_<?= $idx ?>)"/>
                    <polygon points="1122,793 1005,793 1122,675" fill="none" stroke="url(#cedGold_<?= $idx ?>)" stroke-width="2"/>
                </svg>

                <div class="canva-content-wrapper">
                    <div>
                        <div style="font-family: 'Cinzel', serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; color: #064E3B; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Cinzel', serif; font-size: 32px; font-weight: 900; letter-spacing: 3px; color: #064E3B; margin: 3px 0 2px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div class="canva-gold-pill-nomor" style="background: #064E3B; border-color: #D4AF37; color: #FDF1A9;">
                            <i class="bi bi-patch-check-fill text-warning"></i> NO. <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <div class="my-auto py-1">
                        <p class="text-muted small mb-1" style="font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500;">
                            Dengan bangga dipersembahkan kepada :
                        </p>

                        <div class="canva-name-underline-box">
                            <div class="canva-name-script" style="color: #064E3B; font-size: 50px;">
                                <?= esc(ucwords(strtolower($item['nama_pemenang']))) ?>
                            </div>
                            <div class="canva-gold-divider-flair">
                                <span>&#10022;</span>
                            </div>
                        </div>



                        <div class="d-block my-2">
                            <div class="canva-juara-pill" style="background: #064E3B;">
                                JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                            </div>
                        </div>

                        <p class="mx-auto" style="max-width: 820px; font-size: 14px; line-height: 1.55; color: #1E293B; margin-top: 2px;">
                            dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures (Lengkap 3 Tanda Tangan) -->
                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 15: CANVA CRIMSON RED & GOLD LUXURY RIBBONS (ERIKO FERDIANSYAH STYLE) -->
        <div class="theme-variant theme-canva_crimson_gold" style="<?= ($tipe === 'canva_crimson_gold') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="canva-frame">
                <svg class="canva-svg-corners" viewBox="0 0 1122 793" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="ccgCrimson_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#881337"/>
                            <stop offset="100%" stop-color="#4C0519"/>
                        </linearGradient>
                        <linearGradient id="ccgGold_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#D4AF37"/>
                            <stop offset="40%" stop-color="#FDF1A9"/>
                            <stop offset="100%" stop-color="#997825"/>
                        </linearGradient>
                    </defs>

                    <!-- Top-Left Crimson & Gold Wave (Clear of Signatures) -->
                    <path d="M 0,0 L 260,0 C 205,80 125,160 75,260 C 35,340 0,390 0,390 Z" fill="url(#ccgCrimson_<?= $idx ?>)"/>
                    <path d="M 0,0 L 290,0 C 230,90 140,175 88,280 C 45,360 0,410 0,410 L 0,390 C 0,390 35,340 75,260 C 125,160 205,80 260,0 Z" fill="url(#ccgGold_<?= $idx ?>)"/>

                    <!-- Bottom-Right Compact Crimson & Gold Wave (Clear of TTD 3) -->
                    <path d="M 1122,793 L 1000,793 C 1035,760 1075,710 1098,645 C 1112,605 1122,570 1122,570 Z" fill="url(#ccgCrimson_<?= $idx ?>)"/>
                    <path d="M 1122,793 L 975,793 C 1015,755 1058,698 1085,630 C 1102,588 1122,550 1122,550 L 1122,570 C 1122,570 1112,605 1098,645 C 1075,710 1035,760 1000,793 Z" fill="url(#ccgGold_<?= $idx ?>)"/>
                </svg>

                <div class="canva-content-wrapper">
                    <div>
                        <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 50px; width: auto;" class="mb-1">
                        <div style="font-family: 'Cinzel', serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; color: #881337; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Cinzel', serif; font-size: 32px; font-weight: 900; letter-spacing: 3px; color: #881337; margin: 3px 0 2px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div class="canva-gold-pill-nomor" style="background: #881337; border-color: #D4AF37; color: #FDF1A9;">
                            <i class="bi bi-trophy-fill text-warning"></i> NO. <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <div class="my-auto py-1">
                        <p class="text-muted small mb-1" style="font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500;">
                            Sertifikat / Piagam ini diberikan kepada :
                        </p>

                        <div class="canva-name-underline-box">
                            <div class="canva-name-script" style="color: #881337; font-size: 52px;">
                                <?= esc(ucwords(strtolower($item['nama_pemenang']))) ?>
                            </div>
                            <div class="canva-gold-divider-flair">
                                <span>&#10022;</span>
                            </div>
                        </div>



                        <div class="d-block my-2">
                            <div class="canva-juara-pill" style="background: #881337;">
                                JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                            </div>
                        </div>

                        <p class="mx-auto" style="max-width: 820px; font-size: 14px; line-height: 1.55; color: #1E293B; margin-top: 2px;">
                            dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures (Lengkap 3 Tanda Tangan) -->
                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CONTAINER 16: CANVA NAVY RIBBON BADGE & GEOMETRIC FOLDED CORNERS (JONATHAN PRATAMA STYLE) -->
        <div class="theme-variant theme-canva_navy_crest" style="<?= ($tipe === 'canva_navy_crest') ? 'display:block;' : 'display:none;' ?> width:100%; height:100%; position:relative;">
            <div class="canva-frame">
                <svg class="canva-svg-corners" viewBox="0 0 1122 793" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="cncGold_<?= $idx ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#D4AF37"/>
                            <stop offset="50%" stop-color="#FFF3B0"/>
                            <stop offset="100%" stop-color="#997825"/>
                        </linearGradient>
                    </defs>

                    <!-- Hanging Navy Ribbon Badge with Gold Rosette in Top-Left -->
                    <g transform="translate(65, 0)">
                        <polygon points="0,0 75,0 75,130 37.5,105 0,130" fill="#002B49"/>
                        <polygon points="8,0 67,0 67,118 37.5,98 8,118" fill="none" stroke="url(#cncGold_<?= $idx ?>)" stroke-width="2"/>
                        <!-- Gold Rosette Seal Medal -->
                        <circle cx="37.5" cy="58" r="30" fill="#FDF1A9" stroke="url(#cncGold_<?= $idx ?>)" stroke-width="3"/>
                        <circle cx="37.5" cy="58" r="23" fill="#D4AF37"/>
                        <circle cx="37.5" cy="58" r="19" fill="#FFFDF5" stroke="#997825" stroke-width="1.5"/>
                        <text x="37.5" y="63" font-family="'Cinzel', serif" font-size="11" font-weight="900" fill="#997825" text-anchor="middle">★</text>
                    </g>

                    <!-- Top-Right & Bottom-Right Folded Geometric Poly Corners in Navy & Gold -->
                    <polygon points="1122,0 970,0 1122,150" fill="#002B49"/>
                    <polygon points="1122,0 1010,0 1122,110" fill="url(#cncGold_<?= $idx ?>)"/>
                    <polygon points="1122,793 970,793 1122,643" fill="#002B49"/>
                    <polygon points="1122,793 1010,793 1122,683" fill="url(#cncGold_<?= $idx ?>)"/>
                </svg>

                <div class="canva-content-wrapper">
                    <div>
                        <div style="font-family: 'Cinzel', serif; font-size: 13px; font-weight: 800; letter-spacing: 2px; color: #002B49; text-transform: uppercase;">
                            <?= esc($template['instansi_header'] ?: 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH') ?>
                        </div>
                        <h1 style="font-family: 'Cinzel', serif; font-size: 32px; font-weight: 900; letter-spacing: 3px; color: #002B49; margin: 3px 0 2px 0;">
                            PIAGAM PENGHARGAAN
                        </h1>
                        <div class="canva-gold-pill-nomor" style="background: #002B49; border-color: #D4AF37; color: #FDF1A9;">
                            <i class="bi bi-award text-warning"></i> NO. <?= esc($cleanNomor) ?>
                        </div>
                    </div>

                    <div class="my-auto py-1">
                        <p class="text-muted small mb-1" style="font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500;">
                            Diberikan Kepada :
                        </p>

                        <div class="canva-name-underline-box">
                            <div class="canva-name-script" style="color: #002B49; font-size: 52px;">
                                <?= esc(ucwords(strtolower($item['nama_pemenang']))) ?>
                            </div>
                            <div class="canva-gold-divider-flair">
                                <span>&#10022;</span>
                            </div>
                        </div>



                        <div class="d-block my-2">
                            <div class="canva-juara-pill" style="background: #002B49;">
                                JUARA <?= $item['peringkat'] ?> (<?= strtoupper(esc($item['peringkat_teks'])) ?>) &bull; <?= esc($item['nomor_lomba']) ?>
                            </div>
                        </div>

                        <p class="mx-auto" style="max-width: 820px; font-size: 14px; line-height: 1.55; color: #1E293B; margin-top: 2px;">
                            dalam <strong><?= esc($item['nama_kejuaraan']) ?></strong> yang diselenggarakan pada tanggal <strong><?= esc($item['tanggal']) ?></strong> di <strong><?= esc($item['tempat']) ?></strong>.
                        </p>
                    </div>

                    <!-- Signatures (Lengkap 3 Tanda Tangan) -->
                    <!-- Signatures (Persis 100% dengan Piagam Resmi Awal PASI) -->
                                        <!-- Signatures Resmi (Disinkronkan dengan Format Resmi Pertama: Korwil, PLT PASI Jateng, Panitia) -->
                    <div class="sign-row-official" style="width: 100%; margin-top: auto; padding: 0 15px 4px 15px;">
                        <!-- Single Signature Usia 5, 6, 7 th: HANYA KETUA PASI KOTA PEKALONGAN SAJA -->
                        <div class="sig-container-single-pekalongan cert-drag-item" data-drag-key="ttd_pekalongan" data-drag-name="TTD Ketua PASI Kota Pekalongan" style="<?= $isUsia567 ? 'display:block;' : 'display:none;' ?> text-align: center; margin: 4px auto 0 auto; width: 300px;">
                            <div class="overlay-sig-title"><?= nl2br(esc($jabatanKetuaPekalongan)) ?></div>
                            <div class="overlay-sig-img-box" style="height: 125px; margin: -10px 0 -12px 0;">
                                <?php if ($url2): ?>
                                    <img src="<?= $url2 ?>" alt="TTD & Stempel Resmi PASI Kota Pekalongan" class="overlay-sig-img" style="height: 125px; max-height: 125px; max-width: 270px;">
                                <?php endif; ?>
                            </div>
                            <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                        </div>
                        <div class="overlay-signatures-triple sig-container-triple" style="<?= !$isUsia567 ? "display:flex !important;" : "display:none !important;" ?> position: relative !important; bottom: auto !important; left: auto !important; right: auto !important; width: 100%; margin: 0; padding: 0; display: flex !important; justify-content: space-between !important; align-items: flex-end !important;">
                            <!-- TTD 1: Koordinator Wilayah (Kiri) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kiri" data-drag-name="TTD Korwil (Kiri)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_1_jabatan'] ?: "Koordinator Wilayah\nEks-Karesidenan Pekalongan")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url1): ?>
                                        <img src="<?= $url1 ?>" alt="TTD 1 & Stempel PASI Pekalongan" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_1_nama'] ?: 'Jumanto, S.Pd., M.Si.') ?></div>
                            </div>

                            <!-- TTD 3: Mengetahui, PLT PASI Jateng (Tengah) -->
                            <div class="overlay-sig-col overlay-sig-col-center cert-drag-item" data-drag-key="ttd_tengah" data-drag-name="TTD PASI Jateng (Tengah)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_3_jabatan'] ?: "Mengetahui,
PLT PASI Jateng")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url3): ?>
                                        <img src="<?= $url3 ?>" alt="TTD 3 & Stempel PASI Jateng" class="overlay-sig-img overlay-sig-img-thick">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_3_nama'] ?: 'Dr. Agus Widodo Suripto, M. Pd.') ?></div>
                            </div>

                            <!-- TTD 2: Ketua Panitia (Kanan) -->
                            <div class="overlay-sig-col cert-drag-item" data-drag-key="ttd_kanan" data-drag-name="TTD Panitia (Kanan)">
                                <div class="overlay-sig-title"><?= nl2br(esc($template['ttd_2_jabatan'] ?: "Ketua PASI")) ?></div>
                                <div class="overlay-sig-img-box">
                                    <?php if ($url2): ?>
                                        <img src="<?= $url2 ?>" alt="TTD 2 & Stempel Ketua Panitia" class="overlay-sig-img">
                                    <?php endif; ?>
                                </div>
                                <div class="overlay-sig-name"><?= esc($template['ttd_2_nama'] ?: 'Zaini, S.Pd.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code & Segel Keamanan Verifikasi Keabsahan Resmi -->
        <?php if (!empty($item['verification_url'])): ?>
        <div class="qr-verify-box cert-security-seal-box" style="position: absolute; bottom: 10mm; right: 13mm; z-index: 50; text-align: center; background: #ffffff; padding: 6px 8px 5px 8px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.09); border: 1.5px solid #0284C7; width: 90px; box-sizing: border-box;">
            <div style="font-family: 'Inter', sans-serif; font-size: 6.5px; font-weight: 800; color: #0284C7; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 3px; display: flex; align-items: center; justify-content: center; gap: 3px; white-space: nowrap;">
                &bull; E-PIAGAM SAH &bull;
            </div>
            <div id="qr-<?= $idx ?>" style="width: 74px; height: 74px; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: #ffffff;">
                <?php if (!empty($item['qr_data_uri'])): ?>
                    <img src="<?= $item['qr_data_uri'] ?>" style="width: 74px; height: 74px; display: block; image-rendering: pixelated;" alt="QR Code">
                <?php endif; ?>
            </div>
            <div style="font-family: 'Inter', sans-serif; font-size: 5.8px; font-weight: 800; color: #0F172A; margin-top: 3px; letter-spacing: 0.4px; line-height: 1.15; text-transform: uppercase;">
                SCAN VERIFIKASI
            </div>
            <div style="font-family: 'Inter', sans-serif; font-size: 5px; font-weight: 700; color: #475569; letter-spacing: 0.3px; line-height: 1.1;">
                PASI KOTA PEKALONGAN
            </div>
        </div>
        <?php endif; ?>

        <!-- Pita Pengaman Microtext Resmi di Bagian Bawah Tiap Lembar -->
        <div class="cert-security-ribbon" style="position: absolute; bottom: 3mm; left: 12mm; right: 12mm; z-index: 45; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(15, 23, 42, 0.12); padding-top: 2px; font-family: 'Inter', monospace; font-size: 6px; color: rgba(15, 23, 42, 0.5); letter-spacing: 0.4px; text-transform: uppercase;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <span style="display: inline-block; width: 5px; height: 5px; border-radius: 50%; background: #16A34A;"></span>
                <span>DOKUMEN RESMI DIGITAL TERVERIFIKASI &bull; PASI KOTA PEKALONGAN &bull; NO: <?= esc($cleanNomor) ?></span>
            </div>
            <div>
                <span>SECURITY CODE: <?= strtoupper(substr(md5(($item['nomor_piagam'] ?? '') . ($item['nama_pemenang'] ?? '')), 0, 10)) ?> &bull; DILINDUNGI SISTEM</span>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal Galeri Desain Canva & Border Eksklusif -->
<div class="modal fade" id="modalCanvaTemplates" tabindex="-1" aria-labelledby="modalCanvaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%) !important;">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4"><i class="bi bi-palette-fill text-warning"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalCanvaLabel">
                            Katalog Desain Border Corner Canva (Lengkap 3 Tanda Tangan)
                        </h5>
                        <small class="text-white-50">Pilih model sudut bingkai estetik bergaya Canva. Semua model tetap menyertakan 3 tanda tangan lengkap.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-3">
                    <!-- Card 1: Canva Luxury Waves -->
                    <div class="col-md-6 col-lg-4">
                        <div class="canva-theme-card text-center" onclick="selectCanvaTheme('canva_waves')">
                            <div>
                                <div class="badge bg-dark text-warning mb-2 font-monospace px-3 py-1">BEST SELLER</div>
                                <h6 class="fw-bold text-dark mb-1">Luxury Black & Gold</h6>
                                <p class="text-muted small mb-2">Ombak kurva mewah hitam onyx & pita emas murni.</p>
                                <div class="rounded border p-2 mb-2 bg-white" style="height: 100px; overflow: hidden; position: relative;">
                                    <svg viewBox="0 0 300 200" width="100%" height="100%">
                                        <path d="M0,0 L80,0 C60,40 30,80 15,140 C5,180 0,200 0,200 Z" fill="#0B0F19"/>
                                        <path d="M0,0 L95,0 C70,45 38,90 20,150 C8,190 0,200 0,200 L0,180 C0,180 8,140 18,90 C32,50 60,10 80,0 Z" fill="#D4AF37"/>
                                        <path d="M0,200 L0,190 C60,185 120,178 180,185 C240,192 270,180 300,186 L300,200 Z" fill="#0B0F19"/>
                                    </svg>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-dark w-100 fw-bold rounded-pill"><i class="bi bi-stars me-1"></i> Terapkan Model</button>
                        </div>
                    </div>

                    <!-- Card 2: Canva Royal Blue Waves (Franca Mutia) -->
                    <div class="col-md-6 col-lg-4">
                        <div class="canva-theme-card text-center" onclick="selectCanvaTheme('canva_blue_gold')">
                            <div>
                                <div class="badge bg-primary text-white mb-2 font-monospace px-3 py-1">FRANCA MUTIA STYLE</div>
                                <h6 class="fw-bold text-dark mb-1">Royal Blue & Gold Border</h6>
                                <p class="text-muted small mb-2">Ombak biru royal diagonal & garis ganda emas halus.</p>
                                <div class="rounded border p-2 mb-2 bg-white" style="height: 100px; overflow: hidden; position: relative;">
                                    <svg viewBox="0 0 300 200" width="100%" height="100%">
                                        <rect x="10" y="10" width="280" height="180" fill="none" stroke="#D4AF37" stroke-width="1.5"/>
                                        <path d="M300,0 L220,0 C240,25 265,55 275,90 C285,120 300,130 300,130 Z" fill="#004B87"/>
                                        <path d="M0,200 L80,200 C60,175 35,145 25,110 C15,80 0,70 0,70 Z" fill="#004B87"/>
                                    </svg>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary w-100 fw-bold rounded-pill"><i class="bi bi-stars me-1"></i> Terapkan Model</button>
                        </div>
                    </div>

                    <!-- Card 3: Canva Emerald Diamond (Noah Schumacher) -->
                    <div class="col-md-6 col-lg-4">
                        <div class="canva-theme-card text-center" onclick="selectCanvaTheme('canva_emerald_diamond')">
                            <div>
                                <div class="badge bg-success text-white mb-2 font-monospace px-3 py-1">NOAH STYLE</div>
                                <h6 class="fw-bold text-dark mb-1">Emerald & Diamond Ribbon</h6>
                                <p class="text-muted small mb-2">Pita belah ketupat geometris emas & kurva zamrud.</p>
                                <div class="rounded border p-2 mb-2 bg-white" style="height: 100px; overflow: hidden; position: relative;">
                                    <svg viewBox="0 0 300 200" width="100%" height="100%">
                                        <g transform="translate(20, 20)">
                                            <polygon points="0,0 20,-10 40,0 20,10" fill="none" stroke="#D4AF37" stroke-width="2.5"/>
                                            <polygon points="12,5 32,-5 52,5 32,15" fill="none" stroke="#D4AF37" stroke-width="1.5"/>
                                        </g>
                                        <path d="M300,0 L220,0 C245,20 270,50 285,85 C295,110 300,125 300,125 Z" fill="#064E3B"/>
                                    </svg>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-success w-100 fw-bold rounded-pill"><i class="bi bi-stars me-1"></i> Terapkan Model</button>
                        </div>
                    </div>

                    <!-- Card 4: Canva Crimson Gold (Eriko Ferdiansyah) -->
                    <div class="col-md-6 col-lg-4">
                        <div class="canva-theme-card text-center" onclick="selectCanvaTheme('canva_crimson_gold')">
                            <div>
                                <div class="badge text-white mb-2 font-monospace px-3 py-1" style="background: #881337;">ERIKO STYLE</div>
                                <h6 class="fw-bold text-dark mb-1">Crimson Red & Gold Waves</h6>
                                <p class="text-muted small mb-2">Kurva merah marun megah & pita emas mewah.</p>
                                <div class="rounded border p-2 mb-2 bg-white" style="height: 100px; overflow: hidden; position: relative;">
                                    <svg viewBox="0 0 300 200" width="100%" height="100%">
                                        <path d="M0,0 L85,0 C65,30 40,65 25,105 C12,140 0,160 0,160 Z" fill="#881337"/>
                                        <path d="M300,200 L215,200 C235,170 260,135 275,95 C288,60 300,40 300,40 Z" fill="#881337"/>
                                    </svg>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger w-100 fw-bold rounded-pill"><i class="bi bi-stars me-1"></i> Terapkan Model</button>
                        </div>
                    </div>

                    <!-- Card 5: Canva Mocha Dots (Mariana Napolitani) -->
                    <div class="col-md-6 col-lg-4">
                        <div class="canva-theme-card text-center" onclick="selectCanvaTheme('canva_dots')">
                            <div>
                                <div class="badge text-white mb-2 font-monospace px-3 py-1" style="background: #7C4A32;">MARIANA STYLE</div>
                                <h6 class="fw-bold text-dark mb-1">Mocha Curves & Dots</h6>
                                <p class="text-muted small mb-2">Kurva cokelat espresso, dot matrix & scatter pills.</p>
                                <div class="rounded border p-2 mb-2 bg-white" style="height: 100px; overflow: hidden; position: relative;">
                                    <svg viewBox="0 0 300 200" width="100%" height="100%">
                                        <path d="M0,0 L80,0 C60,15 35,45 15,80 C5,105 0,120 0,120 Z" fill="#2A1B14"/>
                                        <path d="M300,200 L220,200 C240,185 265,155 285,120 C295,95 300,80 300,80 Z" fill="#2A1B14"/>
                                        <g transform="translate(265, 30) rotate(-45)">
                                            <rect x="-3" y="-20" width="6" height="18" rx="3" fill="#7C4A32"/>
                                            <circle cx="-3" cy="5" r="3" fill="#2A1B14"/>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-dark w-100 fw-bold rounded-pill"><i class="bi bi-stars me-1"></i> Terapkan Model</button>
                        </div>
                    </div>

                    <!-- Card 6: Canva Navy Ribbon & Crest (Jonathan Pratama) -->
                    <div class="col-md-6 col-lg-4">
                        <div class="canva-theme-card text-center" onclick="selectCanvaTheme('canva_navy_crest')">
                            <div>
                                <div class="badge bg-secondary text-white mb-2 font-monospace px-3 py-1">JONATHAN STYLE</div>
                                <h6 class="fw-bold text-dark mb-1">Navy Ribbon & Geometric Fold</h6>
                                <p class="text-muted small mb-2">Pita gantung navy berornamen emas & sudut lipat.</p>
                                <div class="rounded border p-2 mb-2 bg-white" style="height: 100px; overflow: hidden; position: relative;">
                                    <svg viewBox="0 0 300 200" width="100%" height="100%">
                                        <polygon points="20,0 45,0 45,50 32.5,40 20,50" fill="#002B49"/>
                                        <circle cx="32.5" cy="25" r="10" fill="#D4AF37"/>
                                        <polygon points="300,0 260,0 300,40" fill="#002B49"/>
                                        <polygon points="300,200 260,200 300,160" fill="#002B49"/>
                                    </svg>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary w-100 fw-bold rounded-pill"><i class="bi bi-stars me-1"></i> Terapkan Model</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-2 px-4 d-flex justify-content-between">
                <span class="small text-muted"><i class="bi bi-info-circle me-1"></i> Seluruh template vector SVG di atas dicetak dalam resolusi tinggi 300+ DPI tanpa pecah.</span>
                <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>

<!-- Script untuk Beralih Desain Border & Grid Secara Instan -->
<script>
function selectCanvaTheme(themeName) {
    const switcher = document.getElementById('themeSwitcher');
    if (switcher) {
        switcher.value = themeName;
    }
    switchCertificateTheme(themeName);
    const modalEl = document.getElementById('modalCanvaTemplates');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance.hide();
        }
    }
}

let currentPaperSize = 'F4'; // 'F4' (215x330mm) atau 'A4' (210x297mm)
let currentOrientation = '<?= $isLandscape ? 'landscape' : 'portrait' ?>';

function switchPaperSize(size) {
    currentPaperSize = size;
    applyPaperSize();
}

function applyPaperSize() {
    const isPortrait = (currentOrientation === 'portrait');
    let wMm, hMm;
    if (currentPaperSize === 'A4') {
        wMm = isPortrait ? 210 : 297;
        hMm = isPortrait ? 297 : 210;
    } else { // F4 / Folio (215 x 330 mm standar resmi piagam)
        wMm = isPortrait ? 215 : 330;
        hMm = isPortrait ? 330 : 215;
    }

    let dynStyle = document.getElementById('dynamic-paper-style');
    if (!dynStyle) {
        dynStyle = document.createElement('style');
        dynStyle.id = 'dynamic-paper-style';
        document.head.appendChild(dynStyle);
    }

    const pageSizeCss = (currentPaperSize === 'A4')
        ? `A4 ${isPortrait ? 'portrait' : 'landscape'}`
        : `${wMm}mm ${hMm}mm`;

    dynStyle.innerHTML = `
        @media print {
            @page {
                size: ${pageSizeCss} !important;
                margin: 0mm !important;
            }
            .piagam-sheet {
                width: ${wMm}mm !important;
                height: ${hMm}mm !important;
                margin: 0 !important;
            }
        }
    `;

    document.querySelectorAll('.piagam-sheet').forEach(sheet => {
        sheet.style.width = `${wMm}mm`;
        sheet.style.height = `${hMm}mm`;
    });
}

function switchCertificateTheme(themeName) {
    const sel = document.getElementById('themeSwitcher');
    if (sel && sel.selectedIndex >= 0) {
        const opt = sel.options[sel.selectedIndex];
        const tid = opt ? opt.getAttribute('data-id') : null;
        const currentTid = '<?= (int)($template['id'] ?? 0) ?>';

        // Jika berpindah ke template lain dengan ID berbeda, muat halaman secara penuh agar background & tata letaknya terload sempurna
        if (tid && tid !== currentTid) {
            const url = new URL(window.location.href);
            url.searchParams.set('template_id', tid);

            // Bawa data atlet/peserta aktif agar saat switch template teks TIDAK RESET ke "NAMA PESERTA"
            const liveData = getActiveParticipantData();
            if (liveData) {
                if (liveData.nama_pemenang) url.searchParams.set('nama_pemenang', liveData.nama_pemenang);
                if (liveData.asal_sekolah) url.searchParams.set('asal_sekolah', liveData.asal_sekolah);
                if (liveData.asal_kota) url.searchParams.set('asal_kota', liveData.asal_kota);
                if (liveData.nomor_lomba) url.searchParams.set('nomor_lomba', liveData.nomor_lomba);
                if (liveData.peringkat) url.searchParams.set('peringkat', liveData.peringkat);
                if (liveData.prestasi && liveData.prestasi !== '-') url.searchParams.set('prestasi', liveData.prestasi);
                if (liveData.nomor_piagam) url.searchParams.set('nomor_piagam', liveData.nomor_piagam);
            }

            window.location.href = url.toString();
            return;
        }
    }

    let isPortrait = false;
    if (sel && sel.selectedIndex >= 0) {
        const opt = sel.options[sel.selectedIndex];
        const ori = opt ? opt.getAttribute('data-orientasi') : null;
        if (ori) {
            isPortrait = (ori === 'portrait');
        } else {
            isPortrait = (themeName === 'portrait' || themeName === 'official_federation' || themeName === 'portrait_prestige');
        }
    } else {
        isPortrait = (themeName === 'portrait' || themeName === 'official_federation' || themeName === 'portrait_prestige');
    }

    currentOrientation = isPortrait ? 'portrait' : 'landscape';
    applyPaperSize();

    // Sembunyikan seluruh varian tema di dalam sheet
    document.querySelectorAll('.piagam-sheet').forEach(sheet => {
        const variants = sheet.querySelectorAll('.theme-variant');
        variants.forEach(v => v.style.display = 'none');

        // Tampilkan varian tema yang dipilih
        let activeVariant = sheet.querySelector('.theme-' + themeName);
        if (!activeVariant && themeName === 'portrait_ekskari') {
            activeVariant = sheet.querySelector('.theme-portrait');
        }
        if (activeVariant) {
            activeVariant.style.display = 'block';
        }
    });

    // Sinkronkan panel kalibrasi jika ada data template
    if (sel && sel.selectedIndex >= 0) {
        const opt = sel.options[sel.selectedIndex];
        if (opt) {
            const offTop = opt.getAttribute('data-offset-top');
            const offTtd = opt.getAttribute('data-offset-ttd');
            const fontNama = opt.getAttribute('data-font-nama');
            const hideHeader = opt.getAttribute('data-hide-header');
            const hideTitle = opt.getAttribute('data-hide-title');

            if (offTop !== null && document.getElementById('live_offset_top')) {
                document.getElementById('live_offset_top').value = offTop;
            }
            if (offTtd !== null && document.getElementById('live_offset_ttd')) {
                document.getElementById('live_offset_ttd').value = offTtd;
            }
            if (fontNama !== null && document.getElementById('live_font_nama')) {
                document.getElementById('live_font_nama').value = fontNama;
            }
            if (hideHeader !== null && document.getElementById('live_hide_header')) {
                document.getElementById('live_hide_header').checked = (hideHeader === '1');
            }
            if (hideTitle !== null && document.getElementById('live_hide_title')) {
                document.getElementById('live_hide_title').checked = (hideTitle === '1');
            }
            applyLiveOffset();
        }
    }
}

function toggleEditDataPanel() {
    const p = document.getElementById('editDataPanel');
    if (p) {
        const isHidden = (p.style.display === 'none' || p.style.display === '');
        p.style.display = isHidden ? 'block' : 'none';
        if (isHidden) {
            setTimeout(() => {
                const inp = document.getElementById('edit_nama_pemenang');
                if (inp) {
                    inp.focus();
                    inp.select();
                }
            }, 100);
        }
    }
}

function getActiveParticipantData() {
    const namaInp = document.getElementById('edit_nama_pemenang');
    const namaEl = document.querySelector('.cert-live-nama, .val-nama, .canva-athlete-name');
    const namaVal = (namaInp && namaInp.value) ? namaInp.value.trim() : (namaEl ? namaEl.innerText.trim() : '');

    return {
        nama_pemenang: namaVal,
        asal_sekolah: document.getElementById('edit_asal_sekolah')?.value?.trim() || '',
        asal_kota: document.getElementById('edit_asal_kota')?.value?.trim() || 'Kota Pekalongan',
        nomor_lomba: document.getElementById('edit_nomor_lomba')?.value?.trim() || document.querySelector('.cert-live-lomba')?.innerText?.trim() || '',
        peringkat: document.getElementById('edit_peringkat')?.value || '1',
        prestasi: document.getElementById('edit_prestasi')?.value?.trim() || '-',
        nomor_piagam: document.getElementById('edit_nomor_piagam')?.value?.trim() || ''
    };
}

function saveDataToUrl() {
    const data = getActiveParticipantData();
    const url = new URL(window.location.href);
    if (data.nama_pemenang) url.searchParams.set('nama_pemenang', data.nama_pemenang);
    if (data.asal_sekolah) url.searchParams.set('asal_sekolah', data.asal_sekolah);
    if (data.asal_kota) url.searchParams.set('asal_kota', data.asal_kota);
    if (data.nomor_lomba) url.searchParams.set('nomor_lomba', data.nomor_lomba);
    if (data.peringkat) url.searchParams.set('peringkat', data.peringkat);
    if (data.prestasi && data.prestasi !== '-') url.searchParams.set('prestasi', data.prestasi);
    if (data.nomor_piagam) url.searchParams.set('nomor_piagam', data.nomor_piagam);
    window.history.replaceState({}, '', url.toString());

    alert('Data peserta berhasil disimpan! URL browser telah diperbarui.');
}

function detectKotaFromSchool(name) {
    if (!name) return 'Kota Pekalongan';
    const n = name.toUpperCase();
    if (/(BREBES|BUMIAYU|TONJONG|LARANGAN|KETANGGUNGAN|BULAKAMBA|JATIBARANG|SONGGOM|PAGUYANGAN|SIRAMPOG|LOSARI|TANJUNG|BANJARHARJO|SALEM|BANTARKAWUNG|BREGAS)/.test(n)) return 'Kabupaten Brebes';
    if (/(PEMALANG|COMAL|PETARUKAN|ULUJAMI|AMPELGADING|BODEH|MOGA|RANDUDONGKAL|BELIK|WATUKUMPUL|BANTARBOLANG|WARUNGPRING|SIMA|KARANGTALOK|JATINGARANG)/.test(n)) return 'Kabupaten Pemalang';
    if (/(BATANG|KANDEMAN|WARUNGASEM|BANDAR|SUBAH|LIMPUNG|GRINGSING|BANYUPUTIH|PROYONANGGAN|KLIDANG|REBAN|BLADO|TERSONO|BAWANG|WONOTUNGGAL|PECALUNGAN|LSS|LSSC|SAAM UNIVERSE|KARTIKA AZAKA|BAKTI BATANG|CAHAYA QURAN)/.test(n)) return 'Kabupaten Batang';
    if (/SDN?\s+KAUMAN\s+0[136]/.test(n)) return 'Kabupaten Batang';
    if (/(IHSANIYAH TEGAL|KOTA TEGAL)/.test(n)) return 'Kota Tegal';
    if (/(SLAWI|ADIWERNA|TARUB|DUKUHTURI|KRAMAT|SURADADI|WARUREJA|TALANG|PANGKAH|KEDUNGBANTENG|BUMIJAWA|MARGASARI|BALAPULANG|LEBAKSIU|JATINEGARA|PAGERBARANG|DUKUHWARU|KAB\.?\s*TEGAL|KABUPATEN TEGAL|TEMBOK KIDUL)/.test(n)) {
        if (/(KRAMAT\s*SARI|KRAMATSARI)/.test(n)) return 'Kota Pekalongan';
        return 'Kabupaten Tegal';
    }
    if (n.includes('TEGAL') && !n.includes('TEGALREJO')) return 'Kabupaten Tegal';
    if (/(KAB\.?\s*PEKALONGAN|KAJEN|KEDUNGWUNI|WIRADESA|BOJONG|SRAGI|KESESI|DORO|KARANGANYAR|KARANGDADAP|WONOPRINGGO|BUARAN|WONOKERTO|PANINGGARAN|PETUNGKRIYONO|LEBAKBARANG|TALUN|PEKAJANGAN|BLIGO|SAPUGARUT|GEMBONG|BABALAN|LEGOKCLILE|JAJARWAYANG|BOJONGMINGGIR|NOTOGIWANG|BONDANSARI|WANGANDOWO|KRANJI|SIWALAN|API-API|KARANG ASAM|PAESAN|KEMPLONG|BEBEL|ALMALIKI|GUMAWANG|PURI|BOJONG RAYA)/.test(n)) return 'Kabupaten Pekalongan';
    return 'Kota Pekalongan';
}

function updateLiveTtdFormat(mode) {
    document.querySelectorAll('.piagam-sheet').forEach(sheet => {
        let is567 = (sheet.getAttribute('data-is-usia-567') === '1');
        if (mode === 'single_pekalongan') {
            is567 = true;
        } else if (mode === 'triple') {
            is567 = false;
        } else if (mode === 'auto') {
            const lombaText = sheet.querySelector('.cert-live-lomba')?.innerText || '';
            if (/\b(u[\s\-_]?[567]|ku[\s\-_]?[567]|juaraku|usia[\s\-_]?dini|[567][\s\-_]?th|[567][\s\-_]?tahun)\b|5-6|567|juaraku/i.test(lombaText)) {
                is567 = true;
            } else {
                is567 = (sheet.getAttribute('data-is-usia-567') === '1');
            }
        }

        const singleEls = sheet.querySelectorAll('.sig-container-single-pekalongan');
        const tripleEls = sheet.querySelectorAll('.sig-container-triple');

        singleEls.forEach(el => {
            if (el.classList.contains('fed-sig-center-cell')) {
                el.style.display = is567 ? 'flex' : 'none';
            } else {
                el.style.display = is567 ? 'block' : 'none';
            }
        });
        tripleEls.forEach(el => {
            if (el.classList.contains('overlay-signatures-triple')) {
                el.style.display = is567 ? 'none' : 'flex';
            } else {
                el.style.display = is567 ? 'none' : 'block';
            }
        });
    });
}

function updateLiveField(field, val) {
    if (field === 'nama') {
        const els = document.querySelectorAll('.cert-live-nama, .val-nama, .canva-athlete-name, .canva-name-script, .canva-name-serif, .fed-athlete-name, .td-val.val-nama');
        els.forEach(el => {
            el.innerText = val;
        });
        const inp = document.getElementById('edit_nama_pemenang');
        if (inp && inp.value !== val) inp.value = val;
    } else if (field === 'sekolah') {
        updateCompositeAsal();
    } else if (field === 'kota') {
        updateCompositeAsal();
    } else if (field === 'lomba') {
        document.querySelectorAll('.cert-live-lomba').forEach(el => el.innerText = val);
        const inp = document.getElementById('edit_nomor_lomba');
        if (inp && inp.value !== val) inp.value = val;

        // Jika mode TTD 'auto', perbarui format TTD sesuai nomor lomba yang diketik
        const activeRadio = document.querySelector('input[name="live_ttd_mode"]:checked');
        if (!activeRadio || activeRadio.value === 'auto') {
            updateLiveTtdFormat('auto');
        }
    } else if (field === 'prestasi') {
        document.querySelectorAll('.cert-live-prestasi').forEach(el => el.innerText = val || '-');
        const inp = document.getElementById('edit_prestasi');
        if (inp && inp.value !== val) inp.value = val;
    } else if (field === 'nomor') {
        document.querySelectorAll('.cert-live-nomor').forEach(el => el.innerText = val);
        const inp = document.getElementById('edit_nomor_piagam');
        if (inp && inp.value !== val) inp.value = val;
    }
}

function updateCompositeAsal() {
    const sekolah = document.getElementById('edit_asal_sekolah')?.value?.trim() || '';
    const textVal = sekolah || 'Independen';

    document.querySelectorAll('.cert-live-asal').forEach(el => {
        el.innerText = textVal;
    });
    document.querySelectorAll('.canva-athlete-origin strong').forEach(el => {
        el.innerText = textVal;
    });
}

function updateLiveJuara(rank) {
    const r = parseInt(rank) || 1;
    const texts = {
        1: { teks: 'Satu', romawi: 'I' },
        2: { teks: 'Dua', romawi: 'II' },
        3: { teks: 'Tiga', romawi: 'III' },
        4: { teks: 'Empat', romawi: 'IV' },
        5: { teks: 'Lima', romawi: 'V' }
    };
    const t = texts[r] || { teks: String(r), romawi: String(r) };

    document.querySelectorAll('.cert-live-juara').forEach(el => {
        el.innerText = `${r} (${t.teks.toUpperCase()})`;
    });
    document.querySelectorAll('.cert-live-juara-ribbon, .canva-zone-ribbon span').forEach(el => {
        el.innerText = `★ JUARA ${r} (${t.teks.toUpperCase()}) ★`;
    });
}

// Event listener untuk inline WYSIWYG editing langsung di lembar sertifikat
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('cert-live-nama') || e.target.classList.contains('val-nama') || e.target.classList.contains('canva-athlete-name')) {
        const val = e.target.innerText;
        const inp = document.getElementById('edit_nama_pemenang');
        if (inp) inp.value = val;
        document.querySelectorAll('.cert-live-nama, .val-nama, .canva-athlete-name, .canva-name-script').forEach(el => {
            if (el !== e.target) el.innerText = val;
        });
    } else if (e.target.classList.contains('cert-live-asal')) {
        const val = e.target.innerText;
        const inp = document.getElementById('edit_asal_sekolah');
        if (inp) inp.value = val;
        document.querySelectorAll('.cert-live-asal').forEach(el => {
            if (el !== e.target) el.innerText = val;
        });
        document.querySelectorAll('.canva-athlete-origin strong').forEach(el => {
            if (el !== e.target) el.innerText = val;
        });
    } else if (e.target.classList.contains('cert-live-lomba')) {
        const val = e.target.innerText;
        const inp = document.getElementById('edit_nomor_lomba');
        if (inp) inp.value = val;
        document.querySelectorAll('.cert-live-lomba').forEach(el => {
            if (el !== e.target) el.innerText = val;
        });
    } else if (e.target.classList.contains('cert-live-prestasi')) {
        const val = e.target.innerText;
        const inp = document.getElementById('edit_prestasi');
        if (inp) inp.value = val;
        document.querySelectorAll('.cert-live-prestasi').forEach(el => {
            if (el !== e.target) el.innerText = val;
        });
    } else if (e.target.classList.contains('cert-live-nomor')) {
        const val = e.target.innerText;
        const inp = document.getElementById('edit_nomor_piagam');
        if (inp) inp.value = val;
        document.querySelectorAll('.cert-live-nomor').forEach(el => {
            if (el !== e.target) el.innerText = val;
        });
    }
});

function toggleAdjusterPanel() {
    const p = document.getElementById('adjusterPanel');
    if (p) {
        p.style.display = (p.style.display === 'none' || p.style.display === '') ? 'block' : 'none';
    }
}

function changeOffset(type, delta) {
    if (type === 'top') {
        const el = document.getElementById('live_offset_top');
        el.value = parseInt(el.value || 0) + delta;
    } else if (type === 'ttd') {
        const el = document.getElementById('live_offset_ttd');
        el.value = parseInt(el.value || 0) + delta;
    } else if (type === 'nama') {
        const el = document.getElementById('live_font_nama');
        el.value = parseInt(el.value || 26) + delta;
    } else if (type === 'stempel') {
        const el = document.getElementById('live_stempel_size');
        el.value = Math.max(60, parseInt(el.value || 130) + delta);
    }
    applyLiveOffset();
}

function applyLiveOffset() {
    const offsetTop = parseInt(document.getElementById('live_offset_top')?.value || 0);
    const offsetTtd = parseInt(document.getElementById('live_offset_ttd')?.value || 0);
    const fontNama = parseInt(document.getElementById('live_font_nama')?.value || 26);
    const stempelSize = parseInt(document.getElementById('live_stempel_size')?.value || 130);
    const hideHeader = document.getElementById('live_hide_header')?.checked;
    const hideTitle = document.getElementById('live_hide_title')?.checked;

    // Terapkan ukuran stempel & ttd secara realtime ke seluruh varian
    document.querySelectorAll('.portrait-signatures-triple .sig-unit-img, .portrait-official-signature-center .sig-unit-img, .overlay-sig-img, .fed-sig-img-box img').forEach(img => {
        img.style.height = stempelSize + 'px';
        img.style.maxHeight = stempelSize + 'px';
        img.style.maxWidth = Math.round(stempelSize * 2.2) + 'px';
    });
    document.querySelectorAll('.portrait-signatures-triple .sig-unit-img-box, .portrait-official-signature-center .sig-unit-img-box, .overlay-sig-img-box, .fed-sig-img-box').forEach(box => {
        box.style.height = stempelSize + 'px';
    });
    document.querySelectorAll('.portrait-signatures-triple .sig-unit-center .sig-unit-img').forEach(img => {
        const centerSize = Math.round(stempelSize * 1.08);
        img.style.height = centerSize + 'px';
        img.style.maxHeight = centerSize + 'px';
        img.style.maxWidth = Math.round(centerSize * 2.2) + 'px';
    });
    document.querySelectorAll('.portrait-signatures-triple .sig-unit-center .sig-unit-img-box').forEach(box => {
        box.style.height = Math.round(stempelSize * 1.08) + 'px';
    });
    document.querySelectorAll('.piagam-sheet.is-usia-567 .sig-unit-img, .piagam-sheet.is-usia-567 .overlay-sig-img').forEach(img => {
        const usia567Size = Math.round(stempelSize * 1.31);
        img.style.setProperty('height', usia567Size + 'px', 'important');
        img.style.setProperty('max-height', (usia567Size + 5) + 'px', 'important');
        img.style.setProperty('max-width', Math.round(usia567Size * 2.25) + 'px', 'important');
    });
    document.querySelectorAll('.piagam-sheet.is-usia-567 .sig-unit-img-box, .piagam-sheet.is-usia-567 .overlay-sig-img-box').forEach(box => {
        const usia567Size = Math.round(stempelSize * 1.31);
        box.style.setProperty('height', usia567Size + 'px', 'important');
    });

    // 1. TANDA TANGAN & STEMPEL (Real-time untuk SEMUA template: Portrait, Landscape, Canva, Official)
    document.querySelectorAll('.overlay-signatures-triple').forEach(s => {
        s.style.bottom = (12 + offsetTtd) + 'mm';
    });
    document.querySelectorAll('.portrait-signatures-triple').forEach(s => {
        s.style.bottom = 'calc(10% + ' + offsetTtd + 'mm)';
    });
    document.querySelectorAll('.sign-row-official').forEach(s => {
        s.style.transform = `translateY(${-offsetTtd}mm)`;
    });
    document.querySelectorAll('.fed-signatures-section').forEach(s => {
        s.style.transform = `translateY(${-offsetTtd}mm)`;
    });
    document.querySelectorAll('.portrait-official-signature-center').forEach(s => {
        s.style.bottom = 'calc(10% + ' + offsetTtd + 'mm)';
    });

    // 2. ISI TEKS & DATA ATLET (Real-time untuk SEMUA template)
    document.querySelectorAll('.overlay-content-wrapper').forEach(w => {
        w.style.top = (30 + offsetTop) + 'mm';
        w.style.bottom = (50 + offsetTtd) + 'mm';
    });
    document.querySelectorAll('.portrait-content-body').forEach(b => {
        b.style.transform = `translateY(${offsetTop}mm)`;
    });
    document.querySelectorAll('.fed-body').forEach(b => {
        b.style.transform = `translateY(${offsetTop}mm)`;
    });
    document.querySelectorAll('.canva-content-wrapper > .my-auto').forEach(b => {
        b.style.transform = `translateY(${offsetTop}mm)`;
    });
    document.querySelectorAll('.swiss-grid-canvas > .my-auto, .gold-hex-canvas > .my-auto, .vector-border-outer > .my-auto, .cyber-content-wrapper > .my-auto').forEach(b => {
        b.style.transform = `translateY(${offsetTop}mm)`;
    });

    // 3. Canva zones (Overlay Canva legacy)
    document.querySelectorAll('.canva-zone-nomor').forEach(w => {
        w.style.top = 'calc(28.5% + ' + offsetTop + 'mm)';
    });
    document.querySelectorAll('.canva-zone-athlete').forEach(w => {
        w.style.top = 'calc(40.5% + ' + offsetTop + 'mm)';
    });
    document.querySelectorAll('.canva-zone-ribbon').forEach(w => {
        w.style.top = 'calc(60.2% + ' + offsetTop + 'mm)';
    });
    document.querySelectorAll('.canva-zone-event').forEach(w => {
        w.style.top = 'calc(65.8% + ' + offsetTop + 'mm)';
    });
    document.querySelectorAll('.canva-zone-statement').forEach(w => {
        w.style.top = 'calc(70.0% + ' + offsetTop + 'mm)';
    });

    // 4. Ukuran Font Nama Atlet (Real-time)
    document.querySelectorAll('.overlay-athlete-name, .canva-athlete-name, .val-nama, .td-val.val-nama, .canva-name-script, .fed-name-script').forEach(n => {
        n.style.fontSize = fontNama + 'px';
    });

    // 5. Toggle Header / Kop
    document.querySelectorAll('.overlay-top-header, .portrait-top-header, .fed-kop').forEach(h => {
        h.style.display = hideHeader ? 'none' : '';
    });

    // 6. Toggle Title / Judul
    document.querySelectorAll('.overlay-title-box, .portrait-gold-banner-overlay, .fed-title-section').forEach(t => {
        t.style.display = hideTitle ? 'none' : '';
    });
}

function saveTemplatePosition() {
    const btn = document.getElementById('btnSavePosition');
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    btn.disabled = true;

    const sel = document.getElementById('themeSwitcher');
    let templateId = <?= (int)($template['id'] ?? 0) ?>;
    if (sel && sel.selectedIndex >= 0) {
        const opt = sel.options[sel.selectedIndex];
        const tid = opt ? opt.getAttribute('data-id') : null;
        if (tid) templateId = parseInt(tid);
    }

    const payload = new FormData();
    payload.append('template_id', templateId);
    payload.append('offset_top', document.getElementById('live_offset_top').value);
    payload.append('offset_ttd', document.getElementById('live_offset_ttd').value);
    payload.append('font_size_nama', document.getElementById('live_font_nama').value);
    payload.append('hide_header', document.getElementById('live_hide_header').checked ? 1 : 0);
    payload.append('hide_title', document.getElementById('live_hide_title').checked ? 1 : 0);

    fetch('<?= base_url('piagam/update-template-position') ?>', {
        method: 'POST',
        body: payload
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Tersimpan!';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-light', 'text-success');
        
        // Perbarui data attribute di option yang aktif
        if (sel && sel.selectedIndex >= 0) {
            const opt = sel.options[sel.selectedIndex];
            if (opt) {
                opt.setAttribute('data-offset-top', document.getElementById('live_offset_top').value);
                opt.setAttribute('data-offset-ttd', document.getElementById('live_offset_ttd').value);
                opt.setAttribute('data-font-nama', document.getElementById('live_font_nama').value);
                opt.setAttribute('data-hide-header', document.getElementById('live_hide_header').checked ? '1' : '0');
                opt.setAttribute('data-hide-title', document.getElementById('live_hide_title').checked ? '1' : '0');
            }
        }
        applyLiveOffset();

        setTimeout(() => {
            btn.innerHTML = origHtml;
            btn.classList.remove('btn-light', 'text-success');
            btn.classList.add('btn-success');
            btn.disabled = false;
        }, 2000);
    })
    .catch(err => {
        btn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Gagal!';
        setTimeout(() => {
            btn.innerHTML = origHtml;
            btn.disabled = false;
        }, 2000);
    });
}

/* =========================================================================
   SISTEM MODE GESER ELEMEN (INTERACTIVE DRAG & DROP CERTIFICATE DESIGNER)
   ========================================================================= */
let isDragMode = false;
const currentTemplateId = '<?= (int)($template['id'] ?? 0) ?>';
let customOffsets = {};
let activeDragKey = null;

const serverSavedOffsets = <?= !empty($template['custom_offsets']) ? $template['custom_offsets'] : '{}' ?>;

function persistCustomOffsetsLocally() {
    const isPageUsia567 = (document.querySelector('.piagam-sheet.is-usia-567') !== null);
    const storageKey = 'piagam_offsets_tpl_' + currentTemplateId + (isPageUsia567 ? '_usia567' : '_umum');
    try {
        localStorage.setItem(storageKey, JSON.stringify(customOffsets));
        if (customOffsets['__hide_title'] !== undefined) {
            localStorage.setItem('piagam_hide_title_' + currentTemplateId, customOffsets['__hide_title']);
        }
        if (customOffsets['__hide_labels'] !== undefined) {
            localStorage.setItem('piagam_hide_labels_' + currentTemplateId, customOffsets['__hide_labels']);
        }
        if (customOffsets['__hide_ttd_center'] !== undefined) {
            localStorage.setItem('piagam_hide_ttd_center_' + currentTemplateId, customOffsets['__hide_ttd_center']);
        }
    } catch(e) {}
}

let autoSaveTimer = null;
function scheduleAutoSaveToServer() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        const isPageUsia567 = (document.querySelector('.piagam-sheet.is-usia-567') !== null);
        const targetCategory = isPageUsia567 ? 'usia_567' : 'umum';

        serverSavedOffsets[targetCategory] = Object.assign({}, serverSavedOffsets[targetCategory], customOffsets);

        const payload = new FormData();
        payload.append('template_id', currentTemplateId);
        payload.append('custom_offsets', JSON.stringify(serverSavedOffsets));

        fetch('<?= base_url('piagam/update-template-position') ?>', {
            method: 'POST',
            body: payload
        }).then(r => r.json()).then(data => {
            console.log('Posisi tersimpan otomatis ke server:', data);
        }).catch(err => {
            console.warn('Gagal sinkron server:', err);
        });
    }, 400);
}

function loadStoredOffsets() {
    try {
        const isPageUsia567 = (document.querySelector('.piagam-sheet.is-usia-567') !== null);
        const targetCategory = isPageUsia567 ? 'usia_567' : 'umum';

        // Server adalah Single Source of Truth mutlak!
        customOffsets = Object.assign({}, serverSavedOffsets[targetCategory] || {});

        const storageKey = 'piagam_offsets_tpl_' + currentTemplateId + (isPageUsia567 ? '_usia567' : '_umum');
        try {
            localStorage.setItem(storageKey, JSON.stringify(customOffsets));
        } catch(e) {}

        applyAllCustomOffsets();

        // Restore hide flags langsung dari server
        const hideTitle = customOffsets.__hide_title;
        if (hideTitle === '1' || hideTitle === true) {
            const el = document.getElementById('drag_hide_title');
            if (el) el.checked = true;
            document.querySelectorAll('.overlay-title-box, .overlay-title-text').forEach(e => e.style.display = 'none');
        } else if (hideTitle === '0' || hideTitle === false) {
            const el = document.getElementById('drag_hide_title');
            if (el) el.checked = false;
            document.querySelectorAll('.overlay-title-box, .overlay-title-text').forEach(e => e.style.display = '');
        }

        const hideLabels = customOffsets.__hide_labels;
        if (hideLabels === '1' || hideLabels === true) {
            const el = document.getElementById('drag_hide_labels');
            if (el) el.checked = true;
            document.body.classList.add('hide-data-labels');
        } else if (hideLabels === '0' || hideLabels === false) {
            const el = document.getElementById('drag_hide_labels');
            if (el) el.checked = false;
            document.body.classList.remove('hide-data-labels');
        }

        const hideTtdCenter = customOffsets.__hide_ttd_center;
        if (hideTtdCenter === '1' || hideTtdCenter === true) {
            const el = document.getElementById('drag_hide_ttd_center');
            if (el) el.checked = true;
            document.body.classList.add('hide-ttd-center');
        } else if (hideTtdCenter === '0' || hideTtdCenter === false) {
            const el = document.getElementById('drag_hide_ttd_center');
            if (el) el.checked = false;
            document.body.classList.remove('hide-ttd-center');
        }
    } catch(e) {
        console.warn('Error loading custom offsets:', e);
    }
}

function toggleHideTitle(checked) {
    document.querySelectorAll('.overlay-title-box, .overlay-title-text').forEach(el => {
        el.style.display = checked ? 'none' : '';
    });
    localStorage.setItem('piagam_hide_title_' + currentTemplateId, checked ? '1' : '0');
}

function toggleHideLabels(checked) {
    if (checked) {
        document.body.classList.add('hide-data-labels');
    } else {
        document.body.classList.remove('hide-data-labels');
    }
    localStorage.setItem('piagam_hide_labels_' + currentTemplateId, checked ? '1' : '0');
}

function toggleHideTtdCenter(checked) {
    if (checked) {
        document.body.classList.add('hide-ttd-center');
    } else {
        document.body.classList.remove('hide-ttd-center');
    }
    localStorage.setItem('piagam_hide_ttd_center_' + currentTemplateId, checked ? '1' : '0');
}

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

    // Highlight target visual pada piagam
    document.querySelectorAll('.cert-drag-item').forEach(el => {
        if (el.getAttribute('data-drag-key') === key) {
            el.classList.add('is-active-target');
        } else {
            el.classList.remove('is-active-target');
        }
    });

    // Sinkronkan nilai dropdown selector
    const sel = document.getElementById('dragElementSelector');
    if (sel && sel.value !== key) {
        sel.value = key;
    }

    updateDragCoordinateDisplay();
}

function selectDragElement(key) {
    if (!key) return;
    setActiveDragElement(key);

    // Scroll otomatis ke elemen jika di luar viewport
    const target = document.querySelector(`.cert-drag-item[data-drag-key="${key}"]`);
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function applyAllCustomOffsets() {
    const isPageUsia567 = (document.querySelector('.piagam-sheet.is-usia-567') !== null);
    const offsetsUmum = (serverSavedOffsets && serverSavedOffsets.umum) ? serverSavedOffsets.umum : (isPageUsia567 ? {} : customOffsets);
    const offsets567 = (serverSavedOffsets && serverSavedOffsets.usia_567) ? serverSavedOffsets.usia_567 : (isPageUsia567 ? customOffsets : {});

    // 1. Terapkan ke sheet non-usia-567
    for (const [key, offset] of Object.entries(offsetsUmum)) {
        if (!offset || key.startsWith('__')) continue;
        const x = offset.x || 0;
        const y = offset.y || 0;
        document.querySelectorAll(`.piagam-sheet:not(.is-usia-567) .cert-drag-item[data-drag-key="${key}"]`).forEach(el => {
            el.style.setProperty('transform', `translate(${x}px, ${y}px)`, 'important');
        });
    }

    // 2. Terapkan ke sheet usia-567
    for (const [key, offset] of Object.entries(offsets567)) {
        if (!offset || key.startsWith('__')) continue;
        const x = offset.x || 0;
        const y = offset.y || 0;
        document.querySelectorAll(`.piagam-sheet.is-usia-567 .cert-drag-item[data-drag-key="${key}"]`).forEach(el => {
            el.style.setProperty('transform', `translate(${x}px, ${y}px)`, 'important');
        });
    }

    updateDragCoordinateDisplay();
}

function toggleDragMode(forceState) {
    if (document.body.classList.contains('is-public-view')) {
        return; // Matikan pemindahan/geser pada tampilan publik
    }
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
        document.querySelectorAll('#btnToggleDrag').forEach(b => {
            b.classList.remove('btn-outline-warning');
            b.classList.add('btn-warning', 'text-dark', 'fw-bold');
            b.innerHTML = '<i class="bi bi-check-lg"></i> Selesai Geser';
        });
        if (!activeDragKey) {
            activeDragKey = 'row_nama';
        }
        setActiveDragElement(activeDragKey);
    } else {
        body.classList.remove('drag-mode-active');
        if (bar) bar.style.display = 'none';
        document.querySelectorAll('#btnToggleDrag').forEach(b => {
            b.classList.remove('btn-warning', 'text-dark');
            b.classList.add('btn-outline-warning');
            b.innerHTML = '<i class="bi bi-arrows-move text-warning"></i> <span id="dragBtnLabel">Mode Geser Elemen</span>';
        });
        document.querySelectorAll('.cert-drag-item').forEach(el => {
            el.classList.remove('is-active-target', 'is-dragging');
        });
    }
}

function initDraggableItems() {
    if (document.body.classList.contains('is-public-view')) {
        return; // Matikan pemindahan/drag pada tampilan publik
    }
    let draggedElement = null;
    let dragKey = null;
    let startX = 0, startY = 0;
    let initTranslateX = 0, initTranslateY = 0;

    function onPointerDown(e) {
        if (!isDragMode) return;
        const target = e.target.closest('.cert-drag-item');
        if (!target) return;

        // Cegah event bubbling
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

        // Pointer Events modern menangani mouse, pen, dan touch sekaligus tanpa duplikasi
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

        // Sinkronisasi live ke seluruh lembar sertifikat dengan !important
        document.querySelectorAll(`.cert-drag-item[data-drag-key="${dragKey}"]`).forEach(el => {
            el.style.setProperty('transform', `translate(${newX}px, ${newY}px)`, 'important');
        });

        updateDragCoordinateDisplay();

        if (e.cancelable) e.preventDefault();
    }

    function onPointerUp(e) {
        if (!draggedElement) return;
        draggedElement.classList.remove('is-dragging');
        draggedElement = null;

        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pointerup', onPointerUp);
        window.removeEventListener('pointercancel', onPointerUp);

        // Auto-persist immediately on drag release!
        persistCustomOffsetsLocally();
        scheduleAutoSaveToServer();
    }

    document.querySelectorAll('.cert-drag-item').forEach(el => {
        el.removeEventListener('pointerdown', onPointerDown);
        el.addEventListener('pointerdown', onPointerDown);
    });
}

function nudgeActiveElementMm(dmmX, dmmY) {
    if (!activeDragKey) {
        setActiveDragElement('row_nama');
    }
    const dpxX = mmToPx(dmmX);
    const dpxY = mmToPx(dmmY);
    nudgeActiveElementPx(dpxX, dpxY);
}

function nudgeActiveElementPx(dx, dy) {
    if (!activeDragKey) {
        setActiveDragElement('row_nama');
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
            z-index: 9999;
            background-image: 
                linear-gradient(to right, rgba(14, 165, 233, 0.12) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(14, 165, 233, 0.12) 1px, transparent 1px),
                linear-gradient(to right, rgba(14, 165, 233, 0.3) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(14, 165, 233, 0.3) 1px, transparent 1px);
            background-size: 
                ${PX_PER_MM * 2}px ${PX_PER_MM * 2}px,
                ${PX_PER_MM * 2}px ${PX_PER_MM * 2}px,
                ${PX_PER_MM * 10}px ${PX_PER_MM * 10}px,
                ${PX_PER_MM * 10}px ${PX_PER_MM * 10}px;
        `;
        sheet.appendChild(grid);
    });

    if (btn) {
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-info', 'text-dark', 'fw-bold');
    }
}

function saveCustomDragOffsets(evt) {
    const ev = evt || window.event;
    const btn = (ev && ev.target) ? ev.target.closest('button') : document.querySelector('button[onclick*="saveCustomDragOffsets"]');
    const orig = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan ke Server...';
    }

    // Catat checkbox hide ke customOffsets
    customOffsets['__hide_title'] = document.getElementById('drag_hide_title')?.checked ? '1' : '0';
    customOffsets['__hide_labels'] = document.getElementById('drag_hide_labels')?.checked ? '1' : '0';
    customOffsets['__hide_ttd_center'] = document.getElementById('drag_hide_ttd_center')?.checked ? '1' : '0';

    const isPageUsia567 = (document.querySelector('.piagam-sheet.is-usia-567') !== null);
    const targetCategory = isPageUsia567 ? 'usia_567' : 'umum';

    serverSavedOffsets[targetCategory] = Object.assign({}, serverSavedOffsets[targetCategory], customOffsets);

    const storageKey = 'piagam_offsets_tpl_' + currentTemplateId + (isPageUsia567 ? '_usia567' : '_umum');
    try {
        localStorage.setItem(storageKey, JSON.stringify(customOffsets));
        localStorage.setItem('piagam_hide_title_' + currentTemplateId, customOffsets['__hide_title']);
        localStorage.setItem('piagam_hide_labels_' + currentTemplateId, customOffsets['__hide_labels']);
        localStorage.setItem('piagam_hide_ttd_center_' + currentTemplateId, customOffsets['__hide_ttd_center']);
    } catch(e) {}

    const payload = new FormData();
    payload.append('template_id', currentTemplateId);
    payload.append('custom_offsets', JSON.stringify(serverSavedOffsets));

    fetch('<?= base_url('piagam/update-template-position') ?>', {
        method: 'POST',
        body: payload
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Posisi Tersimpan di Server!';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-light', 'text-success');
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.classList.add('btn-success');
                btn.classList.remove('btn-light', 'text-success');
                btn.disabled = false;
            }, 1800);
        }
    })
    .catch(err => {
        console.error('Gagal simpan ke DB:', err);
        if (btn) {
            btn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Gagal Simpan!';
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.disabled = false;
            }, 1800);
        }
    });
}

function resetCustomDragOffsets() {
    if (!confirm('Kembalikan seluruh posisi elemen (Nama, Juara, TTD, dll) ke posisi default awal?')) return;
    const isPageUsia567 = (document.querySelector('.piagam-sheet.is-usia-567') !== null);
    const targetSheets = isPageUsia567 ? '.piagam-sheet.is-usia-567' : '.piagam-sheet:not(.is-usia-567)';
    const storageKey = 'piagam_offsets_tpl_' + currentTemplateId + (isPageUsia567 ? '_usia567' : '_umum');

    customOffsets = {};
    try {
        localStorage.removeItem(storageKey);
        localStorage.removeItem('piagam_hide_title_' + currentTemplateId);
        localStorage.removeItem('piagam_hide_labels_' + currentTemplateId);
        localStorage.removeItem('piagam_hide_ttd_center_' + currentTemplateId);
    } catch(e) {}
    
    // Hapus inline transform hanya pada target sheet kategori ini
    document.querySelectorAll(`${targetSheets} .cert-drag-item`).forEach(el => {
        el.style.removeProperty('transform');
        el.style.transform = '';
    });
    const sStyle = document.getElementById('server-custom-offsets-style');
    if (sStyle) sStyle.innerHTML = '';

    toggleHideTitle(false);
    toggleHideLabels(false);
    toggleHideTtdCenter(false);
    const chk1 = document.getElementById('drag_hide_title');
    if (chk1) chk1.checked = false;
    const chk2 = document.getElementById('drag_hide_labels');
    if (chk2) chk2.checked = false;
    const chk3 = document.getElementById('drag_hide_ttd_center');
    if (chk3) chk3.checked = false;

    // Reset hanya namespace kategori ini di database
    let fullOffsets = {};
    if (serverSavedOffsets && typeof serverSavedOffsets === 'object') {
        if (serverSavedOffsets.usia_567 || serverSavedOffsets.umum) {
            fullOffsets = JSON.parse(JSON.stringify(serverSavedOffsets));
        } else {
            fullOffsets = { umum: JSON.parse(JSON.stringify(serverSavedOffsets)), usia_567: {} };
        }
    }
    if (isPageUsia567) {
        fullOffsets.usia_567 = {};
    } else {
        fullOffsets.umum = {};
    }

    const payload = new FormData();
    payload.append('template_id', currentTemplateId);
    payload.append('context', 'kejurwil');
    payload.append('custom_offsets', JSON.stringify(fullOffsets));
    fetch('<?= base_url('piagam/update-template-position') ?>', {
        method: 'POST',
        body: payload
    }).then(() => {
        if (serverSavedOffsets && typeof serverSavedOffsets === 'object') {
            if (isPageUsia567) {
                serverSavedOffsets.usia_567 = {};
            } else {
                serverSavedOffsets.umum = {};
            }
        }
    });

    updateDragCoordinateDisplay();
}

// Dukungan tombol panah keyboard saat Mode Geser aktif (1mm biasa, 5mm dengan Shift)
window.addEventListener('keydown', (e) => {
    if (!isDragMode) return;
    if (['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) return;

    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
        e.preventDefault();
        const stepMm = e.shiftKey ? 5 : 1;
        switch (e.key) {
            case 'ArrowUp':    nudgeActiveElementMm(0, -stepMm); break;
            case 'ArrowDown':  nudgeActiveElementMm(0, stepMm); break;
            case 'ArrowLeft':  nudgeActiveElementMm(-stepMm, 0); break;
            case 'ArrowRight': nudgeActiveElementMm(stepMm, 0); break;
        }
    }
});

// Inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    loadStoredOffsets();
    initDraggableItems();
});
</script>

<!-- QR Code Library & Initialization -->
<script src="<?= base_url('assets/js/qrcode.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($piagamList as $idx => $item): ?>
    <?php if (!empty($item['verification_url'])): ?>
    (function() {
        const qrContainer = document.getElementById("qr-<?= $idx ?>");
        if (qrContainer && !qrContainer.querySelector('img')) {
            new QRCode(qrContainer, {
                text: "<?= esc($item['verification_url']) ?>",
                width: 74,
                height: 74,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.L
            });
        }
    })();
    <?php endif; ?>
    <?php endforeach; ?>
});
</script>

<script>
// Fungsi Print & Simpan PDF Resmi
function triggerPrintCertificate() {
    window.print();
}

function downloadCertificatePdf() {
    <?php if (!empty($sekolah['id'])): ?>
        window.location.href = '<?= base_url('piagam/download-sekolah-pdf/' . $sekolah['id']) ?>';
        return;
    <?php else: ?>
        const rawSchool = '<?= !empty($sekolah["nama"]) ? addslashes(preg_replace('/[^a-zA-Z0-9_\- ]/', '', $sekolah["nama"])) : "Piagam" ?>';
        const paperSize = document.getElementById('paperSizeSwitcher')?.value || 'F4';
        const cleanFileName = `E-Piagam_${rawSchool.replace(/\s+/g, '_')}_${paperSize}`;
        
        // Set judul halaman agar saat browser membuka dialog print (Save as PDF), nama berkas otomatis sesuai
        document.title = cleanFileName;
        window.print();
    <?php endif; ?>
}

// Tangani aksi otomatis jika ada parameter URL (?print=1 atau ?download=1)
window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode') || urlParams.get('action') || (urlParams.has('print') ? 'print' : (urlParams.has('download') ? 'download' : ''));
    if (mode === 'print') {
        setTimeout(function() {
            window.print();
        }, 500);
    } else if (mode === 'download') {
        <?php if (!empty($sekolah['id'])): ?>
            window.location.href = '<?= base_url('piagam/download-sekolah-pdf/' . $sekolah['id']) ?>';
        <?php else: ?>
            setTimeout(function() {
                downloadCertificatePdf();
            }, 500);
        <?php endif; ?>
    }
});
</script>

</body>
</html>
