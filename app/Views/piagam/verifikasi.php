<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Piagam Resmi &bull; <?= $valid ? esc($winner['nama_atlet'] ?? 'PASI') : 'Tidak Valid' ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/pasi-logo.png') ?>">
    <meta name="theme-color" content="#0b57d0">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px 16px;
            min-height: 100vh;
            background: #0b57d0;
            font-family: 'Roboto', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .srikandi-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.22);
            max-width: 500px;
            width: 100%;
            padding: 32px 24px;
            margin-bottom: 20px;
        }

        /* Top Brand Header */
        .brand-header {
            text-align: center;
            margin-bottom: 22px;
        }

        .logo-emblem {
            width: 72px;
            height: 72px;
            object-fit: contain;
            margin-bottom: 10px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .brand-title {
            font-family: 'Inter', sans-serif;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 2px;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }

        .brand-sub {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.8px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .sys-caption {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #475569;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .sys-app-title {
            font-size: 12.5px;
            font-weight: 800;
            letter-spacing: 0.4px;
            color: #0f172a;
            text-transform: uppercase;
            line-height: 1.35;
        }

        /* Hash Box */
        .hash-box {
            background: #f1f3f5;
            border-radius: 12px;
            padding: 13px 16px;
            margin-bottom: 22px;
        }

        .hash-label {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 4px;
        }

        .hash-val {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 12.5px;
            font-weight: 700;
            color: #dc2626;
            word-break: break-all;
            line-height: 1.35;
        }

        /* Section Headings */
        .section-heading {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        /* Detail Box */
        .detail-box {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 18px;
            background: #ffffff;
        }

        .signer-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #e0f2fe;
            color: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .avatar-trophy {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #fef3c7;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .signer-name {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.25;
        }

        /* Table Key Value */
        .kv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .kv-table td {
            padding: 3.5px 0;
            vertical-align: top;
        }

        .kv-table .kv-label {
            width: 34%;
            color: #64748b;
            font-weight: 500;
            white-space: nowrap;
        }

        .kv-table .kv-colon {
            width: 4%;
            color: #0f172a;
            font-weight: 700;
            text-align: center;
        }

        .kv-table .kv-value {
            width: 62%;
            color: #0f172a;
            font-weight: 700;
            line-height: 1.35;
        }

        /* Validation Box */
        .validation-section {
            margin-top: 6px;
        }

        .validation-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 3px;
        }

        .validation-desc {
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .dashed-box {
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 12px 14px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }

        /* Soft UI Gradient Button */
        .btn-validate {
            background: linear-gradient(135deg, #17c1e8 0%, #2152ff 100%);
            color: #ffffff;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(33, 82, 255, 0.35);
            transition: all 0.2s ease;
        }

        .btn-validate:hover {
            color: #ffffff;
            opacity: 0.95;
            box-shadow: 0 6px 18px rgba(33, 82, 255, 0.45);
        }

        /* Footer */
        .srikandi-footer {
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 10px;
            padding-bottom: 16px;
        }

        .srikandi-footer a {
            color: #ffffff;
            text-decoration: underline;
            font-weight: 600;
        }

        /* Invalid Header */
        .invalid-badge {
            background: #fee2e2;
            color: #b91c1c;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin-bottom: 18px;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>

    <div class="srikandi-card">
        <!-- Logo & Identitas Aplikasi -->
        <div class="brand-header">
            <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" class="logo-emblem">
            <h1 class="brand-title">PASI</h1>
            <div class="brand-sub">Persatuan Atletik Seluruh Indonesia</div>

            <div class="sys-caption">Naskah ini dikelola dengan aplikasi</div>
            <div class="sys-app-title">Sistem Informasi Kearsipan & Piagam Atletik Terintegrasi</div>
        </div>

        <?php if ($valid): ?>
            <!-- Nilai Hash -->
            <div class="hash-box">
                <div class="hash-label">Nilai Hash</div>
                <div class="hash-val"><?= esc($hash ?? hash('sha256', $code)) ?></div>
            </div>

            <!-- Naskah ini telah tertandatangani oleh -->
            <?php 
            $countSigners = count($signers ?? []);
            if ($countSigners > 1): ?>
                <div class="section-heading">Naskah ini telah tertandatangani secara sah oleh <?= $countSigners ?> Penandatangan :</div>
            <?php else: ?>
                <div class="section-heading">Naskah ini telah tertandatangani oleh :</div>
            <?php endif; ?>

            <?php foreach (($signers ?? []) as $s): ?>
            <div class="detail-box">
                <div class="signer-header">
                    <div class="avatar-circle">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="signer-name"><?= esc($s['nama']) ?></div>
                    </div>
                </div>
                <table class="kv-table">
                    <tr>
                        <td class="kv-label">Jabatan</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value"><?= esc($s['jabatan']) ?></td>
                    </tr>
                    <tr>
                        <td class="kv-label">Unit Kerja</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value"><?= esc($s['unit']) ?></td>
                    </tr>
                    <tr>
                        <td class="kv-label">Instansi</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value"><?= esc($s['instansi']) ?></td>
                    </tr>
                    <tr>
                        <td class="kv-label">Ditandatangani pada</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value"><?= esc($s['waktu']) ?></td>
                    </tr>
                </table>
            </div>
            <?php endforeach; ?>

            <!-- Detail Piagam & Pemenang -->
            <div class="section-heading">Informasi Penerima Piagam :</div>
            <div class="detail-box">
                <div class="signer-header">
                    <div class="avatar-trophy">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                    <div>
                        <div class="signer-name"><?= esc($winner['nama_atlet'] ?? '-') ?></div>
                    </div>
                </div>
                <table class="kv-table">
                    <tr>
                        <td class="kv-label">No. Piagam</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value font-monospace" style="color: #0f172a;"><?= esc($nomor_piagam ?? ($winner['nomor_piagam'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="kv-label">Asal Kontingen</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value"><?= esc($winner['sekolah'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="kv-label">Kejuaraan</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value"><?= esc($kejuaraan['nama'] ?? 'Kejuaraan Atletik PASI') ?></td>
                    </tr>
                    <tr>
                        <td class="kv-label">Nomor Lomba</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value"><?= esc($event['nama_acara'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="kv-label">Peringkat</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value" style="color: #b45309;">
                            Juara <?= $juaraText['romawi'] ?? ($winner['peringkat'] ?? 'I') ?> (<?= esc($juaraText['teks'] ?? ($winner['peringkat_teks'] ?? 'Pertama')) ?>)
                        </td>
                    </tr>
                    <?php if (!empty($winner['prestasi']) && $winner['prestasi'] !== '-'): ?>
                    <tr>
                        <td class="kv-label">Prestasi / Waktu</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value"><?= esc($winner['prestasi']) ?> <?= esc($event['satuan_hasil'] ?? '') ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="kv-label">Tempat & Tanggal</td>
                        <td class="kv-colon">:</td>
                        <td class="kv-value"><?= esc($kejuaraan['tempat'] ?? 'Stadion Hoegeng') ?>, <?= esc($tanggal ?? (!empty($kejuaraan['id']) && (int)$kejuaraan['id'] === 2 ? '29 Agustus 2026' : '27 September 2026')) ?></td>
                    </tr>
                </table>
            </div>

            <!-- Validasi Keaslian Naskah -->
            <div class="validation-section">
                <div class="validation-title">Validasi Keaslian Naskah</div>
                <div class="validation-desc">
                    Piagam penghargaan ini terverifikasi asli dan tercatat resmi dalam basis data Kejuaraan Atletik PASI.
                </div>

                <div class="dashed-box">
                    <i class="bi bi-file-earmark-check-fill text-primary fs-5"></i>
                    <span>Status: Piagam Sah & Terdaftar Resmi</span>
                </div>

                <?php if (!empty($cetak_url)): ?>
                <a href="<?= esc($cetak_url) ?>" target="_blank" class="btn-validate">
                    <i class="bi bi-patch-check-fill"></i>
                    <span>Lihat Salinan Piagam Resmi</span>
                </a>
                <?php else: ?>
                <div class="btn-validate" style="cursor: default;">
                    <i class="bi bi-patch-check-fill"></i>
                    <span>Piagam Sah Terverifikasi</span>
                </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- STATUS TIDAK VALID -->
            <div class="invalid-badge">
                <i class="bi bi-x-circle-fill fs-1 d-block mb-2"></i>
                <h5 class="fw-bold mb-1">Naskah Tidak Ditemukan</h5>
                <p class="small mb-0 opacity-85"><?= esc($error ?? 'Kode verifikasi tidak valid atau tidak terdaftar.') ?></p>
            </div>

            <div class="hash-box">
                <div class="hash-label">Kode Verifikasi / Hash</div>
                <div class="hash-val"><?= esc($code ?? '-') ?></div>
            </div>

            <div class="validation-desc text-center">
                Dokumen piagam atau sertifikat dengan kode tersebut tidak ditemukan dalam pangkalan data resmi PASI. Harap periksa kembali QR Code atau hubungi panitia penyelenggara kejuaraan.
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer Copyright -->
    <div class="srikandi-footer">
        Copyright &copy; 2026 <a href="<?= base_url() ?>">Pengurus Kota PASI Kota Pekalongan</a>
    </div>

</body>
</html>
