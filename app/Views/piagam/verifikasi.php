<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Piagam — <?= $valid ? esc($winner['nama_atlet'] ?? 'PASI') : 'Tidak Valid' ?></title>
    <meta name="description" content="Halaman verifikasi keabsahan piagam/sertifikat kejuaraan atletik PASI Jawa Tengah.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <style>
        :root { --primary: #0052CC; --success: #36B37E; --danger: #DE350B; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f5e9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .verify-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
        }
        .verify-header {
            padding: 28px 24px;
            text-align: center;
        }
        .verify-header.valid {
            background: linear-gradient(135deg, #36B37E, #00875A);
            color: #fff;
        }
        .verify-header.invalid {
            background: linear-gradient(135deg, #DE350B, #BF2600);
            color: #fff;
        }
        .verify-icon {
            font-size: 52px;
            display: block;
            margin-bottom: 8px;
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        @keyframes popIn {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .verify-body { padding: 24px; }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #6B778C; font-weight: 500; }
        .info-value { color: #172B4D; font-weight: 700; text-align: right; max-width: 60%; }
        .verify-footer {
            background: #FAFBFC;
            padding: 16px 24px;
            text-align: center;
            border-top: 1px solid #DFE1E6;
        }
        .badge-juara { display: inline-block; padding: 4px 14px; border-radius: 20px; font-weight: 800; font-size: 13px; }
        .badge-juara-1 { background: linear-gradient(135deg, #FFD700, #FFA000); color: #5D4037; }
        .badge-juara-2 { background: linear-gradient(135deg, #C0C0C0, #9E9E9E); color: #37474F; }
        .badge-juara-3 { background: linear-gradient(135deg, #CD7F32, #A0522D); color: #fff; }
        .logo-pasi { width: 48px; height: 48px; object-fit: contain; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="verify-card">
        <?php if ($valid): ?>
            <!-- VALID -->
            <div class="verify-header valid">
                <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="PASI" class="logo-pasi">
                <span class="verify-icon">✅</span>
                <h4 class="fw-bold mb-1">SERTIFIKAT TERVERIFIKASI</h4>
                <p class="mb-0 small opacity-75">Piagam ini diterbitkan secara resmi</p>
            </div>
            <div class="verify-body">
                <div class="text-center mb-3">
                    <?php
                    $rank = (int)($winner['peringkat'] ?? 1);
                    $badgeClass = $rank === 1 ? 'badge-juara-1' : ($rank === 2 ? 'badge-juara-2' : 'badge-juara-3');
                    ?>
                    <span class="badge-juara <?= $badgeClass ?>">
                        🏆 Juara <?= $juaraText['romawi'] ?? $rank ?> (<?= esc($juaraText['teks'] ?? '') ?>)
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label"><i class="bi bi-person-fill me-1"></i> Nama Atlet</span>
                    <span class="info-value"><?= esc($winner['nama_atlet'] ?? '-') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-trophy-fill me-1"></i> Kejuaraan</span>
                    <span class="info-value"><?= esc($kejuaraan['nama'] ?? '-') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-flag-fill me-1"></i> Nomor Acara</span>
                    <span class="info-value"><?= esc($event['nama_acara'] ?? '-') ?></span>
                </div>
                <?php if (!empty($winner['prestasi']) && $winner['prestasi'] !== '-'): ?>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-stopwatch-fill me-1"></i> Prestasi</span>
                    <span class="info-value"><?= esc($winner['prestasi']) ?> <?= esc($event['satuan_hasil'] ?? '') ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-geo-alt-fill me-1"></i> Tempat</span>
                    <span class="info-value"><?= esc($kejuaraan['tempat'] ?? '-') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-calendar3 me-1"></i> Tanggal</span>
                    <span class="info-value"><?= esc($tanggal ?? '-') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-shield-check me-1"></i> Kode Verifikasi</span>
                    <span class="info-value font-monospace small"><?= esc($code) ?></span>
                </div>
            </div>
            <div class="verify-footer">
                <small class="text-muted">
                    <i class="bi bi-patch-check-fill text-success me-1"></i>
                    Piagam ini diterbitkan resmi oleh PASI Provinsi Jawa Tengah
                </small>
            </div>

        <?php else: ?>
            <!-- NOT VALID -->
            <div class="verify-header invalid">
                <span class="verify-icon">❌</span>
                <h4 class="fw-bold mb-1">TIDAK TERVERIFIKASI</h4>
                <p class="mb-0 small opacity-75">Kode verifikasi tidak ditemukan</p>
            </div>
            <div class="verify-body text-center">
                <div class="py-3">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 48px;"></i>
                    <p class="mt-3 text-muted"><?= esc($error ?? 'Piagam dengan kode ini tidak ditemukan. Piagam mungkin tidak sah atau sudah tidak berlaku.') ?></p>
                    <div class="bg-light rounded p-2 mb-3">
                        <small class="text-muted">Kode yang dicari:</small><br>
                        <strong class="font-monospace"><?= esc($code ?? '-') ?></strong>
                    </div>
                </div>
            </div>
            <div class="verify-footer">
                <a href="<?= base_url('/') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-house me-1"></i> Kembali ke Beranda
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
