<?php
if (!function_exists('esc')) {
    function esc($data) {
        return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatNomorPiagam567_pdf')) {
    function formatNomorPiagam567_pdf($cleanNomor, $idx = 0) {
        $nomor = trim((string)$cleanNomor);
        $nomor = preg_replace('/PASI\.?JATENG/i', 'PASI.KOTA', $nomor);
        $nomor = preg_replace('/\/SERT\//i', '/sert/', $nomor);
        if (!preg_match('/PASI\.KOTA/i', $nomor)) {
            return "001/sert/PASI.KOTA/IX/2026";
        }
        return preg_replace('/^\d+\//', '001/', $nomor);
    }
}

if (!function_exists('formatNomorLomba567_pdf')) {
    function formatNomorLomba567_pdf($rawLomba, $usiaSpecific = '') {
        if (empty($rawLomba)) return '';
        $clean = preg_replace('/juaraku\s*/i', '', $rawLomba);
        $clean = preg_replace('/\bu[\s\-_]?5\b|\bku[\s\-_]?5\b/i', 'Usia 5 Tahun', $clean);
        $clean = preg_replace('/\bu[\s\-_]?6\b|\bku[\s\-_]?6\b/i', 'Usia 6 Tahun', $clean);
        $clean = preg_replace('/\bu[\s\-_]?7\b|\bku[\s\-_]?7\b/i', 'Usia 7 Tahun', $clean);
        $clean = preg_replace('/\bfemale\b/i', 'Putri', $clean);
        $clean = preg_replace('/\bmale\b/i', 'Putra', $clean);
        $clean = preg_replace('/\b567\b/i', '', $clean);
        $res = trim(preg_replace('/\s+/', ' ', $clean));

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

if (!function_exists('formatPrestasiPiagam_pdf')) {
    function formatPrestasiPiagam_pdf($rawPrestasi, $nomorLomba = '', $satuan = '') {
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

        if (preg_match('/^(\d+):(\d{2})(\.\d{1,2})?$/', $cleaned, $m)) {
            $dec = isset($m[3]) ? $m[3] : '.00';
            if (strlen($dec) === 2) {
                $dec .= '0';
            }
            return $m[1] . ':' . $m[2] . $dec;
        }

        if (preg_match('/^(\d+)\.(\d{1,2})$/', $cleaned, $m)) {
            $sec = (int)$m[1];
            $dec = $m[2];
            if (strlen($dec) === 1) {
                $dec .= '0';
            }
            return sprintf('0:%02d.%s', $sec, $dec);
        }

        if (ctype_digit($cleaned)) {
            return sprintf('0:%02d.00', (int)$cleaned);
        }

        return $p;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= esc($title ?? 'E-Piagam Penghargaan') ?></title>
<style>
    @page {
        size: 609.45pt 935.43pt; /* F4 Folio Portrait (215mm x 330mm) */
        margin: 0;
        padding: 0;
    }
    * {
        box-sizing: border-box;
    }
    body {
        margin: 0;
        padding: 0;
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #111827;
        background-color: #ffffff;
    }
    .sheet-page {
        position: relative;
        width: 609.45pt;
        height: 935.43pt;
        page-break-after: always;
        overflow: hidden;
    }
    .sheet-page:last-child {
        page-break-after: avoid;
    }
    .bg-layer {
        position: absolute;
        top: 0;
        left: 0;
        width: 609.45pt;
        height: 935.43pt;
        z-index: 1;
    }
    .content-layer {
        position: absolute;
        top: 0;
        left: 0;
        width: 609.45pt;
        height: 935.43pt;
        z-index: 10;
    }

    /* Header / Kop Instansi - Sesuai Live Cetak Browser */
    .kop-container {
        position: absolute;
        top: 136pt;
        left: 35pt;
        right: 35pt;
        text-align: center;
    }
    .kop-line {
        font-size: 11pt;
        font-weight: bold;
        letter-spacing: 0.9pt;
        color: #111827;
        text-transform: uppercase;
        line-height: 1.3;
    }

    /* Judul Piagam & Nomor - Sesuai Live Cetak Browser */
    .title-container {
        position: absolute;
        top: 188pt;
        left: 35pt;
        right: 35pt;
        text-align: center;
    }
    .title-text {
        font-size: 27pt;
        font-weight: bold;
        letter-spacing: 2.6pt;
        color: #0A2540;
        text-transform: uppercase;
        margin: 0;
        line-height: 1.1;
    }
    .nomor-text {
        font-size: 11.5pt;
        font-weight: bold;
        letter-spacing: 1.1pt;
        color: #004B87;
        text-transform: uppercase;
        margin-top: 4pt;
    }

    /* Kalimat Pembuka - Sesuai Live Cetak Browser */
    .pembuka-container {
        position: absolute;
        top: 246pt;
        left: 45pt;
        right: 45pt;
        text-align: center;
        font-size: 10.5pt;
        font-weight: bold;
        line-height: 1.45;
        color: #1E293B;
    }

    /* Data Atlet Table - Sesuai Live Cetak Browser */
    .data-table-container {
        position: absolute;
        top: 288pt;
        left: 75pt;
        right: 65pt;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table td {
        padding: 3.5pt 2pt;
        vertical-align: middle;
    }
    .td-lbl {
        width: 105pt;
        font-size: 11.5pt;
        font-weight: bold;
        color: #1E293B;
        white-space: nowrap;
    }
    .td-sep {
        width: 14pt;
        font-size: 12pt;
        font-weight: bold;
        color: #1E293B;
        text-align: center;
    }
    .td-val {
        font-size: 11.5pt;
        font-weight: bold;
        color: #000000;
        text-transform: uppercase;
    }
    .td-val-nama {
        font-size: 19.5pt;
        font-weight: bold;
        color: #000000;
        letter-spacing: 0.5pt;
    }

    /* Kalimat Penutup - Sesuai Live Cetak Browser */
    .penutup-container {
        position: absolute;
        top: 422pt;
        left: 50pt;
        right: 50pt;
        text-align: center;
        font-size: 10pt;
        font-weight: bold;
        line-height: 1.45;
        color: #1E293B;
    }

    /* Signatures: Triple Segitiga (Non Usia Dini / Umum) - Ukuran Stempel Asli Besar */
    .sig-top-table-container {
        position: absolute;
        top: 488pt;
        left: 35pt;
        right: 35pt;
    }
    .sig-top-table {
        width: 100%;
        border-collapse: collapse;
    }
    .sig-top-table td {
        vertical-align: top;
        text-align: center;
        width: 50%;
        padding: 0 12pt;
    }

    .sig-center-table-container {
        position: absolute;
        top: 610pt;
        left: 140pt;
        right: 140pt;
    }
    .sig-center-table {
        width: 100%;
        border-collapse: collapse;
    }
    .sig-center-table td {
        vertical-align: top;
        text-align: center;
        width: 100%;
    }

    .sig-title {
        font-size: 8.8pt;
        font-weight: bold;
        color: #111827;
        line-height: 1.25;
        min-height: 24pt;
    }
    /* Ukuran Stempel Asli Besar (125px = 94pt, PASI Jateng = 135px = 101pt) */
    .sig-img-large {
        height: 94pt;
        max-width: 195pt;
        margin: -8pt auto -10pt auto;
        display: block;
    }
    .sig-img-center-large {
        height: 101pt;
        max-width: 200pt;
        margin: -10pt auto -12pt auto;
        display: block;
    }
    .sig-name {
        font-size: 10pt;
        font-weight: bold;
        color: #111827;
        border-bottom: 1.2pt solid #111827;
        padding-bottom: 1.5pt;
        display: inline-block;
        white-space: nowrap;
    }

    /* Signatures: Single Pekalongan (Khusus Usia Dini 5, 6, 7 th) - Ukuran Asli 170px = 128pt */
    .signatures-single-container {
        position: absolute;
        top: 510pt;
        left: 120pt;
        right: 120pt;
        text-align: center;
    }
    .sig-single-title {
        font-size: 11.5pt;
        font-weight: bold;
        color: #0b1a30;
        line-height: 1.35;
    }
    .sig-single-img-large {
        height: 128pt;
        max-width: 285pt;
        margin: -10pt auto -12pt auto;
        display: block;
    }
    .sig-single-name {
        font-size: 13pt;
        font-weight: bold;
        color: #0b1a30;
        border-bottom: 1.2pt solid #0b1a30;
        padding-bottom: 1.5pt;
        display: inline-block;
    }

    /* QR Code & Segel Verifikasi (Sesuai Persis Cetak Browser) */
    .qr-box {
        position: absolute;
        bottom: 28pt;
        right: 37pt;
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid #94a3b8;
        border-radius: 4pt;
        padding: 4pt 5pt 3pt 5pt;
        text-align: center;
        width: 68pt;
    }
    .qr-header {
        font-size: 5.5pt;
        font-weight: bold;
        color: #0284c7;
        letter-spacing: 0.4pt;
        margin-bottom: 2pt;
        text-transform: uppercase;
    }
    .qr-img {
        width: 57pt;
        height: 57pt;
        margin: 0 auto;
        display: block;
    }
    .qr-footer-1 {
        font-size: 4.2pt;
        font-weight: bold;
        color: #475569;
        margin-top: 2pt;
        letter-spacing: 0.2pt;
    }
    .qr-footer-2 {
        font-size: 3.8pt;
        color: #94a3b8;
    }

    /* Microtext Security Ribbon (Sesuai Persis Cetak Browser) */
    .security-ribbon {
        position: absolute;
        bottom: 8.5pt;
        left: 34pt;
        right: 34pt;
        border-top: 0.5pt solid #cbd5e1;
        padding-top: 2pt;
        font-size: 4.5pt;
        color: #64748b;
        letter-spacing: 0.3pt;
        text-transform: uppercase;
    }
    .ribbon-table {
        width: 100%;
        border-collapse: collapse;
    }
    .ribbon-table td {
        padding: 0;
    }
</style>
</head>
<body>

<?php foreach ($piagamList as $idx => $item): ?>
    <?php
    $kuCheckStr = strtolower(($item['kategori_usia'] ?? '') . ' ' . ($item['nomor_lomba'] ?? ''));
    $isUsia567 = false;
    if (preg_match('/\b(u[\s\-_]?[567]|ku[\s\-_]?[567]|juaraku|usia[\s\-_]?dini|[567][\s\-_]?th|[567][\s\-_]?tahun)\b|5-6|567|juaraku/i', $kuCheckStr)) {
        $isUsia567 = true;
    }
    if (isset($item['hanya_ketua_pekalongan'])) {
        $isUsia567 = (bool)$item['hanya_ketua_pekalongan'];
    }

    $rawNomor = $item['nomor_piagam'] ?? '001/SERT/PASIJATENG/IX/2026';

    if ($isUsia567) {
        $usiaSpecific = '567';
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

        $displayHeaderLines = [
            "KEJUARAAN ATLETIK",
            "USIA DINI 5, 6, 7 TAHUN",
            "TAHUN 2026"
        ];
        $nomorPiagam = formatNomorPiagam567_pdf($rawNomor, $idx);
        $displayKalimatPembuka = "Pengurus Kota Persatuan Atletik Seluruh Indonesia (PASI) Kota Pekalongan dengan ini memberikan Piagam Penghargaan kepada :";
        $lomba = formatNomorLomba567_pdf($item['nomor_lomba'] ?? '', $usiaSpecific);
        $prestasi = formatPrestasiPiagam_pdf($item['prestasi'] ?? '', $lomba, $item['satuan'] ?? '');
        $penutup = "dalam Kejuaraan Atletik Usia Dini 5, 6, 7 Tahun Tingkat Kota Pekalongan, Kab. Pekalongan, dan Kab. Batang yang diselenggarakan tanggal 27 September 2026 di Stadion Hoegeng Kota Pekalongan.";
    } else {
        $displayHeaderLines = [
            "KEJUARAAN ATLETIK",
            "TINGKAT EKS KARESIDENAN",
            "TAHUN 2026"
        ];
        $nomorPiagam = preg_replace('/^\d+\//', '001/', $rawNomor);
        if (empty($nomorPiagam) || !str_contains($nomorPiagam, '/')) {
            $nomorPiagam = "001/SERT/PASIJATENG/IX/2026";
        }
        $displayKalimatPembuka = "Pengurus Provinsi Persatuan Atletik Seluruh Indonesia (PASI) Jawa Tengah dengan ini memberikan Piagam Penghargaan kepada :";
        $lomba = $item['nomor_lomba'] ?? '';
        $prestasi = formatPrestasiPiagam_pdf($item['prestasi'] ?? '', $lomba, $item['satuan'] ?? '');
        $tglStr = $item['tanggal'] ?? '27 September 2026';
        $tmptStr = $item['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan';
        $penutup = "dalam Kejuaraan Atletik Tingkat Eks Karesidenan Pekalongan yang diselenggarakan pada tanggal {$tglStr} di {$tmptStr}.";
    }

    $juaraText = ($item['peringkat'] ?? 1) . ' (' . strtoupper(esc($item['peringkat_teks'] ?? '')) . ')';
    ?>

    <div class="sheet-page">
        <!-- Background Layer -->
        <?php if (!empty($bgDataUri)): ?>
            <img src="<?= $bgDataUri ?>" class="bg-layer" alt="Background">
        <?php endif; ?>

        <!-- Content Layer -->
        <div class="content-layer">
            <!-- Kop Instansi -->
            <div class="kop-container">
                <?php foreach ($displayHeaderLines as $hLine): ?>
                    <div class="kop-line"><?= esc($hLine) ?></div>
                <?php endforeach; ?>
            </div>

            <!-- Judul Piagam -->
            <div class="title-container">
                <div class="title-text">PIAGAM PENGHARGAAN</div>
                <div class="nomor-text">NOMOR : <?= esc($nomorPiagam) ?></div>
            </div>

            <!-- Kalimat Pembuka -->
            <div class="pembuka-container">
                <?= esc($displayKalimatPembuka) ?>
            </div>

            <!-- Data Table Atlet -->
            <div class="data-table-container">
                <table class="data-table">
                    <tr>
                        <td class="td-lbl">Nama</td>
                        <td class="td-sep">:</td>
                        <td class="td-val td-val-nama"><?= esc($item['nama_pemenang']) ?></td>
                    </tr>
                    <tr>
                        <td class="td-lbl">Nomor Lomba</td>
                        <td class="td-sep">:</td>
                        <td class="td-val"><?= esc($lomba) ?></td>
                    </tr>
                    <tr>
                        <td class="td-lbl">Sebagai Juara</td>
                        <td class="td-sep">:</td>
                        <td class="td-val"><?= esc($juaraText) ?></td>
                    </tr>
                    <tr>
                        <td class="td-lbl">Prestasi</td>
                        <td class="td-sep">:</td>
                        <td class="td-val"><?= esc($prestasi) ?></td>
                    </tr>
                </table>
            </div>

            <!-- Kalimat Penutup -->
            <div class="penutup-container">
                <?= esc($penutup) ?>
            </div>

            <!-- Tanda Tangan -->
            <?php if ($isUsia567): ?>
                <!-- TTD Tunggal: Ketua PASI Kota Pekalongan (Stempel Asli Ukuran Besar 170px = 128pt) -->
                <div class="signatures-single-container">
                    <div class="sig-single-title">Pengurus Kota PASI Pekalongan<br>Ketua Umum</div>
                    <?php if (!empty($sig2DataUri)): ?>
                        <div><img src="<?= $sig2DataUri ?>" class="sig-single-img-large" alt="TTD"></div>
                    <?php endif; ?>
                    <div class="sig-single-name">Zaini, S.Pd.</div>
                </div>
            <?php else: ?>
                <!-- Triple Signatures: Segitiga (Korwil Kiri, Panitia Kanan, PASI Jateng Tengah Bawah - Ukuran Asli Besar) -->
                <div class="sig-top-table-container">
                    <table class="sig-top-table">
                        <tr>
                            <!-- TTD 1: Korwil Kiri -->
                            <td>
                                <div class="sig-title">Koordinator Wilayah<br>Eks-Karesidenan Pekalongan</div>
                                <?php if (!empty($sig1DataUri)): ?>
                                    <div><img src="<?= $sig1DataUri ?>" class="sig-img-large" alt="TTD 1"></div>
                                <?php endif; ?>
                                <div class="sig-name">Jumanto, S.Pd., M.Si.</div>
                            </td>

                            <!-- TTD 2: Ketua Panitia Kanan -->
                            <td>
                                <div class="sig-title">Ketua Panitia Pelaksana<br>&nbsp;</div>
                                <?php if (!empty($sig2DataUri)): ?>
                                    <div><img src="<?= $sig2DataUri ?>" class="sig-img-large" alt="TTD 2"></div>
                                <?php endif; ?>
                                <div class="sig-name">Zaini, S.Pd.</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="sig-center-table-container">
                    <table class="sig-center-table">
                        <tr>
                            <!-- TTD 3: Mengetahui PASI Jateng Tengah Bawah -->
                            <td>
                                <div class="sig-title">Mengetahui,<br>Pengurus Provinsi PASI Jawa Tengah<br>Plt. Ketua Umum</div>
                                <?php if (!empty($sig3DataUri)): ?>
                                    <div><img src="<?= $sig3DataUri ?>" class="sig-img-center-large" alt="TTD 3"></div>
                                <?php endif; ?>
                                <div class="sig-name">Dr. Agus Widodo Suripto, M. Pd.</div>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>

            <!-- QR Code Segel Keabsahan -->
            <?php if (!empty($item['qr_data_uri'])): ?>
                <div class="qr-box">
                    <div class="qr-header">&bull; E-PIAGAM SAH &bull;</div>
                    <img src="<?= $item['qr_data_uri'] ?>" class="qr-img" alt="QR Code">
                    <div class="qr-footer-1">SCAN VERIFIKASI</div>
                    <div class="qr-footer-2">PASI PEKALONGAN</div>
                </div>
            <?php endif; ?>

            <!-- Pita Pengaman Microtext -->
            <div class="security-ribbon">
                <table class="ribbon-table">
                    <tr>
                        <td style="text-align: left;">
                            &bull; DOKUMEN RESMI DIGITAL TERVERIFIKASI &bull; PASI KOTA PEKALONGAN &bull; NO: <?= esc($nomorPiagam) ?>
                        </td>
                        <td style="text-align: right;">
                            SECURITY CODE: <?= strtoupper(substr(md5(($item['nomor_piagam'] ?? '') . ($item['nama_pemenang'] ?? '')), 0, 10)) ?> &bull; DILINDUNGI SISTEM
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php endforeach; ?>

</body>
</html>
