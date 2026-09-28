<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> — <?= esc($kejuaraan['nama'] ?? 'Kejurwil Atletik') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/pasi-logo.png') ?>">
    
    <!-- Google Fonts: Inter & Oswald for Athletic Numbers -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,600;0,700;0,800;0,900;1,700;1,800;1,900&family=Oswald:wght@700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">

    <?php 
    // Default konfigurasi template & teks header
    $templateDesign = $_GET['template_design'] ?? 'red'; // Default Template 2 (Merah Atletik sesuai foto user)
    $bibTextLine1   = $_GET['bib_text_line1'] ?? 'Kejurwil Atletik';
    $bibTextLine2   = $_GET['bib_text_line2'] ?? ($kejuaraan['nama'] ?? 'Pekalongan 2026');
    $bibFontSize    = (int)($_GET['bib_font_size'] ?? 115);
    $bibOffsetY     = (int)($_GET['bib_offset_y'] ?? 0);
    $customBgUrl    = $_GET['custom_bg_url'] ?? '';
    $hideHeader     = (int)($_GET['hide_header'] ?? 0);
    $layout         = $layout ?? '2_per_page';

    // Warna default per template
    $defaultColor = ($templateDesign === 'navy') ? '#1a237e' : '#D8001D';
    $headerColor  = $_GET['header_color'] ?? $defaultColor;
    ?>

    <style>
        :root {
            --bib-color: <?= esc($headerColor) ?>;
            --bib-white: #ffffff;
            --bib-gold: #FFD700;
            --bib-offset-y: <?= $bibOffsetY ?>px;
            --bib-font-size: <?= $bibFontSize ?>pt;
        }

        body {
            background-color: #0F172A;
            font-family: 'Inter', sans-serif;
            color: #1E293B;
            margin: 0;
            padding: 0;
        }

        /* Toolbar on Screen */
        .controls-toolbar {
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 10px 18px;
        }

        /* Printable Preview Area */
        .print-workspace {
            padding: 20px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        /* ======================================= */
        /* Sheet container simulating A4 Landscape */
        /* ======================================= */
        .sheet-page {
            width: 297mm;
            min-height: 205mm;
            max-height: 210mm;
            background: #FFFFFF;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            padding: 5mm 6mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: row;
            justify-content: space-around;
            align-items: center;
            position: relative;
            page-break-after: always;
            break-after: page;
        }

        /* 1 per sheet mode (A4 Landscape single large bib) */
        .sheet-page.layout-single {
            justify-content: center;
        }
        .sheet-page.layout-single .bib-plate {
            width: 250mm;
            height: 192mm;
        }
        .sheet-page.layout-single .bib-number {
            font-size: calc(var(--bib-font-size) * 1.6) !important;
        }
        .sheet-page.layout-single .bib-header-text-1 {
            font-size: 26pt !important;
        }
        .sheet-page.layout-single .bib-header-text-2 {
            font-size: 22pt !important;
        }
        .sheet-page.layout-single .bib-logo {
            width: 58px !important;
            height: 58px !important;
        }

        /* A5 Portrait mode */
        .sheet-page.layout-a5 {
            width: 148.5mm;
            min-height: 207mm;
            max-height: 210mm;
            justify-content: center;
            padding: 4mm;
        }
        .sheet-page.layout-a5 .bib-plate {
            width: 136mm;
            height: 194mm;
        }

        /* Middle Cut Guide Line (for 2-per-page) */
        .cut-guide {
            position: absolute;
            top: 4mm;
            bottom: 4mm;
            left: 50%;
            width: 0;
            border-left: 1.5px dashed #94A3B8;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
            pointer-events: none;
            color: #64748B;
            font-size: 11px;
            font-weight: 700;
        }
        .cut-guide span {
            background: #fff;
            padding: 2px 4px;
            transform: rotate(-90deg);
            white-space: nowrap;
        }

        /* ============================================= */
        /* The Athletic Bib Plate Container              */
        /* ============================================= */
        .bib-plate {
            width: 138mm;
            height: 194mm;
            background-color: var(--bib-white);
            border: 2.5px solid var(--bib-color);
            border-radius: 4px;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Template 2 (Red Athletic / Sesuai Foto User) */
        .theme-red .bib-plate {
            border-color: var(--bib-color);
        }

        /* Top Header Area (~38% height) */
        .bib-header-area {
            background-color: var(--bib-color);
            padding: 8mm 6mm 6mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            text-align: center;
        }

        /* PASI Logo */
        .bib-logo {
            width: 44px;
            height: 44px;
            object-fit: contain;
            margin-bottom: 4px;
            filter: drop-shadow(0 2px 3px rgba(0,0,0,0.25));
        }

        /* Event Text Line 1 & Line 2 (Italic Bold Sans) */
        .bib-header-text-1 {
            color: var(--bib-white);
            font-family: 'Inter', sans-serif;
            font-size: 17pt;
            font-weight: 800;
            font-style: italic;
            letter-spacing: 0.2px;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .bib-header-text-2 {
            color: var(--bib-white);
            font-family: 'Inter', sans-serif;
            font-size: 15pt;
            font-weight: 800;
            font-style: italic;
            letter-spacing: 0.2px;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            margin-top: 1px;
        }

        /* Separator Line (Emas di Navy, atau Flat di Red) */
        .bib-separator {
            height: 3px;
            background: linear-gradient(90deg, var(--bib-color), var(--bib-gold), var(--bib-color));
            flex-shrink: 0;
        }
        .theme-red .bib-separator {
            display: none; /* Template Merah clean tanpa garis emas */
        }

        /* White Number Area (~60% height) */
        .bib-number-area {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bib-white);
            position: relative;
            padding: 2mm 4mm;
            overflow: hidden;
        }

        /* Custom template overlay mode (background transparan) */
        .theme-custom .bib-number-area {
            background-color: transparent !important;
        }

        .bib-number {
            font-family: 'Oswald', 'Arial Black', 'Impact', sans-serif;
            font-size: var(--bib-font-size);
            font-weight: 700;
            line-height: 0.88;
            color: #000000;
            letter-spacing: 3px;
            text-align: center;
            user-select: none;
            transform: translateY(var(--bib-offset-y));
            transition: transform 0.1s ease;
        }

        /* Bottom Accent Bar */
        .bib-bottom-bar {
            height: 7mm;
            background-color: var(--bib-color);
            flex-shrink: 0;
        }

        /* Mode Sembunyikan Header Bawaan (Khusus Background Kustom yang sudah ada kop) */
        .hide-header .bib-header-area,
        .hide-header .bib-separator,
        .hide-header .bib-bottom-bar {
            display: none !important;
        }
        .hide-header .bib-number-area {
            height: 100% !important;
            flex-grow: 1 !important;
        }

        /* ============== */
        /* Print Rules    */
        /* ============== */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #FFFFFF !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-workspace {
                padding: 0 !important;
                gap: 0 !important;
            }
            .sheet-page {
                width: 100% !important;
                min-height: 198mm !important;
                max-height: 200mm !important;
                box-shadow: none !important;
                padding: 2mm 4mm !important;
                margin: 0 !important;
                page-break-after: always !important;
                break-after: page !important;
            }
            .sheet-page.layout-a5 {
                min-height: 202mm !important;
                max-height: 210mm !important;
            }
            .bib-plate {
                box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .bib-header-area {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .bib-bottom-bar {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .bib-separator {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .bib-number {
                color: #000000 !important;
            }
            .cut-guide {
                border-left-color: #888 !important;
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

        #toolbarContainer,
        #debug-icon,
        #debug-bar,
        .ci-debug-toolbar {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }
    </style>
    <style id="dynamic-page-style">
        <?php if ($layout === 'a5_portrait'): ?>
        @page { size: A5 portrait; margin: 2mm; }
        <?php else: ?>
        @page { size: A4 landscape; margin: 3mm 4mm; }
        <?php endif; ?>
    </style>
</head>
<body>

<!-- TOOLBAR (Screen Only) -->
<div class="controls-toolbar no-print">
    <div class="container-fluid">
        <form id="formBibSettings" method="get" action="<?= base_url('atlet/cetak-nomor-lomba') ?>" class="row g-2 align-items-center justify-content-between">
            <input type="hidden" name="custom_bg_url" id="inputCustomBgUrl" value="<?= esc($customBgUrl) ?>">

            <div class="col-auto d-flex align-items-center gap-3">
                <a href="<?= base_url('atlet') ?>" class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <div>
                    <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2" style="font-size: 16px;">
                        <i class="bi bi-123 text-warning"></i> Cetak Nomor Dada
                    </h5>
                    <small class="text-white-50" style="font-size: 11px;">
                        Total: <strong><?= count($atlets) ?> nomor</strong> &bull; 
                        Estimasi: <strong><?= ceil(count($atlets) / ($layout === '1_per_page' ? 1 : ($layout === 'a5_portrait' ? 1 : 2))) ?> lembar</strong>
                    </small>
                </div>
            </div>

            <div class="col-auto d-flex align-items-center gap-2 flex-wrap">
                <!-- Pilihan Template Desain -->
                <div>
                    <select name="template_design" id="selectTemplateDesign" class="form-select form-select-sm fw-bold border-warning text-dark bg-warning" style="min-width: 170px;" onchange="handleTemplateChange(this.value)">
                        <option value="red" <?= $templateDesign === 'red' ? 'selected' : '' ?>>🔴 Template 2 (Merah Atletik)</option>
                        <option value="navy" <?= $templateDesign === 'navy' ? 'selected' : '' ?>>🔵 Template 1 (Biru Navy & Emas)</option>
                        <option value="custom" <?= $templateDesign === 'custom' ? 'selected' : '' ?>>🖼️ Template Kustom (Upload)</option>
                    </select>
                </div>

                <!-- Teks Baris 1 & Baris 2 (Header) -->
                <div class="d-flex gap-1">
                    <input type="text" name="bib_text_line1" id="inputLine1" class="form-control form-control-sm" 
                           placeholder="Baris 1 (Kejurwil Atletik)"
                           value="<?= esc($bibTextLine1) ?>"
                           title="Teks Baris 1 (atas)" style="width: 140px;">
                    <input type="text" name="bib_text_line2" id="inputLine2" class="form-control form-control-sm" 
                           placeholder="Baris 2 (Pekalongan 2026)"
                           value="<?= esc($bibTextLine2) ?>"
                           title="Teks Baris 2 (bawah)" style="width: 135px;">
                </div>

                <!-- Warna Header -->
                <div class="d-flex align-items-center gap-1" title="Warna pita header & aksen bawah">
                    <input type="color" name="header_color" id="inputHeaderColor" class="form-control form-control-sm form-control-color" value="<?= esc($headerColor) ?>" style="width: 34px; height: 31px; cursor: pointer;">
                </div>

                <!-- Tombol Upload Background Kustom -->
                <button type="button" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalUploadBg">
                    <i class="bi bi-cloud-arrow-up-fill"></i> Upload Desain
                </button>

                <!-- Filter Sekolah -->
                <div style="max-width: 150px;">
                    <select name="sekolah_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Sekolah --</option>
                        <?php foreach ($sekolahs as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $selectedSekolah == $s['id'] ? 'selected' : '' ?>>
                                <?= esc($s['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Rentang Nomor -->
                <div class="d-flex align-items-center gap-1">
                    <input type="number" name="start_no" class="form-control form-control-sm text-center font-monospace" placeholder="Dari" value="<?= esc($startNo ?? '') ?>" style="width: 60px;">
                    <span class="text-white-50 small">-</span>
                    <input type="number" name="end_no" class="form-control form-control-sm text-center font-monospace" placeholder="Sampai" value="<?= esc($endNo ?? '') ?>" style="width: 60px;">
                </div>

                <!-- Layout Selector -->
                <div>
                    <select name="layout" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="2_per_page" <?= $layout === '2_per_page' ? 'selected' : '' ?>>2 per A4 (½ A4 Potong Tengah)</option>
                        <option value="a5_portrait" <?= $layout === 'a5_portrait' ? 'selected' : '' ?>>Kertas A5 Langsung</option>
                        <option value="1_per_page" <?= $layout === '1_per_page' ? 'selected' : '' ?>>1 Besar per A4 Penuh</option>
                    </select>
                </div>

                <!-- Ukuran Angka & Offset Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sliders"></i> Ukuran
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg" style="width: 260px;">
                        <label class="form-label small fw-bold text-dark mb-1">Ukuran Angka (Font Size)</label>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <input type="range" name="bib_font_size" id="rangeFontSize" class="form-range" min="80" max="160" value="<?= $bibFontSize ?>" oninput="updateFontSize(this.value)">
                            <span id="labelFontSize" class="badge bg-primary font-monospace"><?= $bibFontSize ?>pt</span>
                        </div>

                        <label class="form-label small fw-bold text-dark mb-1">Geser Posisi Angka (Offset Y)</label>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <input type="range" name="bib_offset_y" id="rangeOffsetY" class="form-range" min="-40" max="60" value="<?= $bibOffsetY ?>" oninput="updateOffsetY(this.value)">
                            <span id="labelOffsetY" class="badge bg-dark font-monospace"><?= $bibOffsetY ?>px</span>
                        </div>

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="hide_header" id="checkHideHeader" value="1" <?= $hideHeader ? 'checked' : '' ?> onchange="toggleHeaderVisibility(this.checked)">
                            <label class="form-check-label small fw-semibold text-dark" for="checkHideHeader">
                                Sembunyikan Header Bawaan
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Apply Filter -->
                <button type="submit" class="btn btn-sm btn-info fw-bold text-white d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-repeat"></i> Refresh
                </button>

                <!-- Print Button -->
                <button type="button" id="btnPrint" class="btn btn-sm btn-success fw-bold px-3 d-inline-flex align-items-center gap-1 shadow">
                    <i class="bi bi-printer-fill"></i> Cetak
                </button>
            </div>
        </form>
    </div>
</div>

<!-- PRINT WORKSPACE -->
<div class="print-workspace theme-<?= esc($templateDesign) ?> <?= $hideHeader ? 'hide-header' : '' ?>" id="printWorkspace">
    <?php if (empty($atlets)): ?>
        <div class="card p-5 text-center my-5 no-print" style="max-width: 500px; background: #1e293b; color: #fff; border: 1px solid rgba(255,255,255,0.1);">
            <i class="bi bi-exclamation-circle text-warning fs-1 mb-3"></i>
            <h5 class="fw-bold">Tidak Ada Nomor Atlet Ditemukan</h5>
            <p class="text-muted small mb-4">Pastikan atlet sudah memiliki Nomor Dada. Anda dapat men-generate nomor dada terlebih dahulu dari menu Data Atlet.</p>
            <a href="<?= base_url('atlet') ?>" class="btn btn-primary">Kembali ke Data Atlet</a>
        </div>
    <?php else: ?>
        <?php 
        $perPage = ($layout === '1_per_page') ? 1 : (($layout === 'a5_portrait') ? 1 : 2);
        $chunks  = array_chunk($atlets, $perPage);
        $logoUrl = base_url('assets/images/pasi-logo.png');
        ?>

        <?php foreach ($chunks as $pageIndex => $pageAtlets): ?>
            <div class="sheet-page <?= $layout === '1_per_page' ? 'layout-single' : ($layout === 'a5_portrait' ? 'layout-a5' : '') ?>">
                <?php if ($layout === '2_per_page'): ?>
                    <div class="cut-guide">
                        <span>✂ POTONG</span>
                        <span>✂ POTONG</span>
                        <span>✂ POTONG</span>
                    </div>
                <?php endif; ?>

                <?php foreach ($pageAtlets as $a): ?>
                    <?php 
                    $bibNumber = esc(ltrim($a['no_dada'] ?: '000', "'"));
                    ?>
                    <div class="bib-plate" style="<?= !empty($customBgUrl) ? "background-image: url('" . esc($customBgUrl) . "');" : "" ?>">
                        <!-- Header Area (Warna Merah / Navy) -->
                        <div class="bib-header-area">
                            <img src="<?= $logoUrl ?>" alt="PASI" class="bib-logo" onerror="this.style.display='none'">
                            <div class="bib-header-text-1"><?= esc($bibTextLine1) ?></div>
                            <?php if (!empty($bibTextLine2)): ?>
                                <div class="bib-header-text-2"><?= esc($bibTextLine2) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Separator Line (Emas di Template 1) -->
                        <div class="bib-separator"></div>

                        <!-- Area Nomor Putih -->
                        <div class="bib-number-area">
                            <div class="bib-number"><?= $bibNumber ?></div>
                        </div>

                        <!-- Bottom Bar (Merah / Navy) -->
                        <div class="bib-bottom-bar"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- =============================================================== -->
<!-- MODAL UPLOAD TEMPLATE BACKGROUND KUSTOM                         -->
<!-- =============================================================== -->
<div class="modal fade no-print" id="modalUploadBg" tabindex="-1" aria-labelledby="modalUploadBgLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fs-6 fw-bold d-flex align-items-center gap-2" id="modalUploadBgLabel">
                    <i class="bi bi-cloud-arrow-up-fill text-info"></i> Upload Template Background Nomor Dada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Unggah gambar background nomor dada yang telah didesain di Canva, Photoshop, atau CorelDraw (format JPG/PNG/WebP). Gambar akan langsung dipasang di semua nomor atlet.
                </p>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Pilih File Gambar Background</label>
                    <input type="file" id="fileBibBg" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <small class="text-muted" style="font-size: 11px;">
                        Rekomendasi rasio: <strong>138 x 194 mm</strong> (sekitar 1630 x 2290 px pada 300 DPI).
                    </small>
                </div>

                <div class="card bg-light border p-3 mb-3">
                    <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-gear-fill me-1 text-primary"></i> Pengaturan Tampilan Desain Kustom</h6>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="modalCheckHideHeader" value="1" <?= $hideHeader ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-semibold text-dark" for="modalCheckHideHeader">
                            Sembunyikan Header & Garis Bawaan (Cetak Nomor Saja)
                        </label>
                    </div>
                    <small class="text-muted d-block" style="font-size: 11px;">
                        Centang ini jika gambar template yang Anda upload sudah memiliki desain kop acara dan logo sponsor sendiri.
                    </small>
                </div>

                <!-- Preview Thumbnail -->
                <div id="previewBgBox" class="text-center p-2 border rounded bg-white" style="display: none;">
                    <span class="badge bg-success mb-2"><i class="bi bi-check-circle me-1"></i> Gambar Terpilih</span>
                    <img id="previewBgImg" src="" class="img-fluid rounded border shadow-sm" style="max-height: 160px;">
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="resetCustomBg()">
                    <i class="bi bi-x-circle me-1"></i> Hapus Background
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="applyCustomBg()">
                        <i class="bi bi-check2-circle me-1"></i> Terapkan Desain
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Penanganan Ganti Pilihan Template
function handleTemplateChange(template) {
    const inputColor = document.getElementById('inputHeaderColor');
    const inputLine1 = document.getElementById('inputLine1');
    const inputLine2 = document.getElementById('inputLine2');
    const workspace  = document.getElementById('printWorkspace');

    workspace.classList.remove('theme-red', 'theme-navy', 'theme-custom');
    workspace.classList.add('theme-' + template);

    if (template === 'red') {
        inputColor.value = '#D8001D';
        document.documentElement.style.setProperty('--bib-color', '#D8001D');
    } else if (template === 'navy') {
        inputColor.value = '#1a237e';
        document.documentElement.style.setProperty('--bib-color', '#1a237e');
    } else if (template === 'custom') {
        var modal = new bootstrap.Modal(document.getElementById('modalUploadBg'));
        modal.show();
    }
}

// Live update Header Color
document.getElementById('inputHeaderColor')?.addEventListener('input', function(e) {
    document.documentElement.style.setProperty('--bib-color', e.target.value);
});

// Live update Baris 1 & Baris 2
document.getElementById('inputLine1')?.addEventListener('input', function(e) {
    document.querySelectorAll('.bib-header-text-1').forEach(el => el.textContent = e.target.value);
});
document.getElementById('inputLine2')?.addEventListener('input', function(e) {
    document.querySelectorAll('.bib-header-text-2').forEach(el => el.textContent = e.target.value);
});

// Live update Ukuran Font
function updateFontSize(val) {
    document.getElementById('labelFontSize').textContent = val + 'pt';
    document.documentElement.style.setProperty('--bib-font-size', val + 'pt');
}

// Live update Offset Y
function updateOffsetY(val) {
    document.getElementById('labelOffsetY').textContent = val + 'px';
    document.documentElement.style.setProperty('--bib-offset-y', val + 'px');
}

// Toggle Sembunyikan Header Bawaan
function toggleHeaderVisibility(hide) {
    const ws = document.getElementById('printWorkspace');
    if (hide) {
        ws.classList.add('hide-header');
    } else {
        ws.classList.remove('hide-header');
    }
}

// Preview file background yang dipilih
let uploadedBgDataUrl = '';
document.getElementById('fileBibBg')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
            uploadedBgDataUrl = evt.target.result;
            document.getElementById('previewBgImg').src = uploadedBgDataUrl;
            document.getElementById('previewBgBox').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Terapkan background kustom ke semua bib-plate
function applyCustomBg() {
    const hideHeaderCheck = document.getElementById('modalCheckHideHeader').checked;
    document.getElementById('checkHideHeader').checked = hideHeaderCheck;
    toggleHeaderVisibility(hideHeaderCheck);

    const fileInput = document.getElementById('fileBibBg');
    if (fileInput.files.length > 0) {
        // Upload file ke server via AJAX agar tersimpan permanen
        const formData = new FormData();
        formData.append('template_bg', fileInput.files[0]);

        fetch('<?= base_url('atlet/upload-template-no-dada') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('inputCustomBgUrl').value = data.url;
                applyBgToPlates(data.url);
            } else {
                // Fallback gunakan local Data URL
                if (uploadedBgDataUrl) {
                    applyBgToPlates(uploadedBgDataUrl);
                }
            }
        })
        .catch(() => {
            if (uploadedBgDataUrl) {
                applyBgToPlates(uploadedBgDataUrl);
            }
        });
    } else if (uploadedBgDataUrl) {
        applyBgToPlates(uploadedBgDataUrl);
    }

    // Set select template ke custom
    document.getElementById('selectTemplateDesign').value = 'custom';
    document.getElementById('printWorkspace').classList.remove('theme-red', 'theme-navy');
    document.getElementById('printWorkspace').classList.add('theme-custom');

    const modalEl = document.getElementById('modalUploadBg');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

function applyBgToPlates(bgUrl) {
    document.querySelectorAll('.bib-plate').forEach(plate => {
        plate.style.backgroundImage = "url('" + bgUrl + "')";
    });
}

// Reset background kustom
function resetCustomBg() {
    document.getElementById('inputCustomBgUrl').value = '';
    document.getElementById('fileBibBg').value = '';
    document.getElementById('previewBgBox').style.display = 'none';
    uploadedBgDataUrl = '';
    document.querySelectorAll('.bib-plate').forEach(plate => {
        plate.style.backgroundImage = 'none';
    });
    document.getElementById('selectTemplateDesign').value = 'red';
    handleTemplateChange('red');
    
    const modalEl = document.getElementById('modalUploadBg');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

// Cetak Lembar Dokumen
document.getElementById('btnPrint')?.addEventListener('click', function(e) {
    e.preventDefault();
    const layout = '<?= esc($layout) ?>';
    const style = document.getElementById('dynamic-page-style');
    
    if (layout === 'a5_portrait') {
        style.innerHTML = '@page { size: A5 portrait; margin: 2mm; }';
    } else {
        style.innerHTML = '@page { size: A4 landscape; margin: 3mm 4mm; }';
    }
    setTimeout(() => window.print(), 120);
});
</script>

</body>
</html>
