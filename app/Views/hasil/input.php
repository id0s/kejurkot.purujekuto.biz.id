<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Event Detail Header -->
<div class="card-custom mb-4 p-4" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.15) 0%, rgba(59, 130, 246, 0.1) 100%); border-color: rgba(6, 182, 212, 0.3);">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-amber fs-6">Acara <?= esc($event['nomor_acara']) ?></span>
                <span class="badge <?= $event['babak'] === 'Final' ? 'badge-emerald' : 'badge-amber' ?> fs-6">
                    Babak: <?= esc($event['babak']) ?>
                </span>
                <span class="badge badge-blue fs-6">
                    Penilaian: <?= esc($event['satuan_hasil'] ?? 'Waktu') ?>
                </span>
                <?php if ($event['gender'] === 'L'): ?>
                    <span class="badge badge-blue fs-6"><i class="bi bi-gender-male me-1"></i> Male (Putra)</span>
                <?php elseif ($event['gender'] === 'P'): ?>
                    <span class="badge badge-rose fs-6" style="background-color: #e11d48;"><i class="bi bi-gender-female me-1"></i> Female (Putri)</span>
                <?php else: ?>
                    <span class="badge badge-purple fs-6"><i class="bi bi-people-fill me-1"></i> MIX (Campuran)</span>
                <?php endif; ?>
            </div>
            <?php 
            $gSuffix = ($event['gender'] === 'L') ? 'Male' : (($event['gender'] === 'P') ? 'Female' : 'MIX');
            $evTitle = esc($event['nama_acara']);
            if (stripos($evTitle, 'male') === false && stripos($evTitle, 'female') === false && stripos($evTitle, 'mix') === false) {
                $evTitle .= ' ' . $gSuffix;
            }
            ?>
            <h3 class="fw-bold text-white mb-1"><?= $evTitle ?></h3>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Satuan penilaian: <strong class="text-info"><?= esc($event['satuan_hasil'] ?? 'Waktu') ?></strong>.
                <?php if ($isFieldEvent ?? false): ?>
                    Nomor Lapangan (Tolak Peluru / Lompat Jauh / Lempar Lembing): Setiap atlet berhak mendapatkan 3x percobaan giliran. Angka paling jauh diambil sebagai prestasi terbaik.
                <?php elseif (($event['satuan_hasil'] ?? '') === 'Waktu'): ?>
                    Catat waktu dalam detik atau format menit.detik (misal: <code>19.45</code> atau <code>12.30</code>). Angka lebih kecil = lebih cepat.
                <?php else: ?>
                    Catat jarak dalam meter (misal: <code>5.33</code> atau <code>12.50</code>). Angka lebih besar = lebih jauh.
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= base_url('hasil') ?>" class="btn btn-secondary-custom">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap
            </a>
            <a href="<?= base_url('hasil/cetak-hasil/' . $event['id']) ?>" target="_blank" class="btn btn-warning fw-bold text-dark d-inline-flex align-items-center gap-1 shadow-sm" title="Cetak Lembar Hasil Resmi Perlombaan (Format Meet Manager)">
                <i class="bi bi-printer-fill"></i> Cetak Lembar Hasil (Meet Manager)
            </a>
            <a href="<?= base_url('hasil/form-lapangan/' . $event['id']) ?>" target="_blank" class="btn btn-outline-warning text-warning d-inline-flex align-items-center gap-1" title="Cetak Formulir Blangko Pertandingan Lapangan untuk Juri">
                <i class="bi bi-file-earmark-spreadsheet"></i> Blangko Form Lapangan (Juri)
            </a>
            <a href="<?= base_url('piagam/cetak-event/' . $event['id']) ?>" target="_blank" class="btn btn-secondary-custom d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-award-fill text-warning"></i> Cetak Piagam Juara
            </a>
            <?php if (strcasecmp($event['babak'], 'Final') === 0 && !empty($pesertaList)): ?>
                <a href="<?= base_url('hasil/reset-final/' . $event['id']) ?>" class="btn btn-outline-danger fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="return confirm('PERINGATAN:\nApakah Anda yakin ingin MERESET (MENGOSONGKAN) seluruh peserta di Babak Final ini?\n\nGunakan ini jika sebelumnya salah klik loloskan atau ingin mengulang seleksi dari babak penyisihan.')">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Finalis
                </a>
            <?php endif; ?>
            <a href="<?= base_url('hasil/auto-rank/' . $event['id']) ?>" class="btn btn-primary-gradient" onclick="return confirm('Hitung otomatis ranking 1, 2, 3 dan medali berdasarkan catatan prestasi yang telah diinput?')">
                <i class="bi bi-magic me-1"></i> Auto Ranking & Medali
            </a>
        </div>
    </div>
</div>

<?php if (!empty($penyisihanEvent)): ?>
    <div class="alert alert-secondary py-2 px-3 mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-0 shadow-sm" style="background: rgba(148, 163, 184, 0.15); border-left: 4px solid #38bdf8 !important;">
        <div>
            <i class="bi bi-link-45deg text-info fs-5 me-1"></i>
            Nomor lomba ini memiliki Babak Penyisihan: <strong>Acara #<?= $penyisihanEvent['nomor_acara'] ?> (<?= esc($penyisihanEvent['nama_acara']) ?>)</strong>.
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= base_url('hasil/input/' . $penyisihanEvent['id']) ?>" class="btn btn-sm btn-outline-info">
                <i class="bi bi-box-arrow-left me-1"></i> Buka Penyisihan #<?= $penyisihanEvent['nomor_acara'] ?>
            </a>
            <?php if (!empty($pesertaList)): ?>
                <a href="<?= base_url('hasil/reset-final/' . $event['id']) ?>" class="btn btn-sm btn-danger fw-bold" onclick="return confirm('PERINGATAN:\nApakah Anda yakin ingin MENGOSONGKAN (RESET) seluruh peserta di Babak Final ini?\n\nGunakan ini jika sebelumnya salah klik atau ingin merevisi input penyisihan.')">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Peserta Final (0 Atlet)
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (strcasecmp($event['babak'], 'Penyisihan') === 0): ?>
    <?php if (!empty($finalEvent) && $finalPesertaCount > 0): ?>
        <div class="alert alert-warning py-3 px-4 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-sm" style="border-left: 5px solid #f59e0b !important; border-radius: 4px; background: rgba(245, 158, 11, 0.15);">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-check-circle-fill me-1"></i> FINAL SUDAH DIGENERATE</span>
                    <strong class="text-white">Babak Final (Acara #<?= $finalEvent['nomor_acara'] ?>) Berisi <?= $finalPesertaCount ?> Atlet</strong>
                </div>
                <p class="mb-0 text-muted small">
                    Atlet tercepat telah diloloskan ke Babak Final. Jika ada <strong>salah klik</strong> atau <strong>salah input waktu</strong> pada penyisihan, silakan reset peserta babak final untuk mengosongkannya kembali (0 atlet).
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= base_url('hasil/input/' . $finalEvent['id']) ?>" class="btn btn-info text-dark fw-bold text-nowrap d-inline-flex align-items-center gap-1 shadow-sm px-3">
                    <i class="bi bi-box-arrow-up-right"></i> Buka Acara Final #<?= $finalEvent['nomor_acara'] ?>
                </a>
                <a href="<?= base_url('hasil/reset-final/' . $event['id']) ?>" class="btn btn-danger fw-bold text-nowrap d-inline-flex align-items-center gap-2 shadow-sm px-3" onclick="return confirm('PERINGATAN:\nApakah Anda yakin ingin MERESET Babak Final (Acara #<?= $finalEvent['nomor_acara'] ?>)?\n\nPeserta babak final akan dikosongkan (0 atlet) agar Anda dapat memperbaiki nilai penyisihan ini lalu meloloskan ulang atlet yang benar.')">
                    <i class="bi bi-arrow-counterclockwise fs-5"></i>
                    <span>Reset Final (Acara #<?= $finalEvent['nomor_acara'] ?>)</span>
                </a>
                <a href="<?= base_url('hasil/qualify-to-final/' . $event['id']) ?>" class="btn btn-outline-warning fw-bold text-nowrap d-inline-flex align-items-center gap-1 shadow-sm px-3" onclick="return confirm('Perbarui/Loloskan ulang atlet ke Babak Final sekarang?')">
                    <i class="bi bi-arrow-repeat"></i> Perbarui Final
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info py-3 px-4 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-sm" style="background: linear-gradient(135deg, rgba(0, 82, 204, 0.1) 0%, rgba(59, 130, 246, 0.15) 100%); border: 1px solid #B3D4FF; border-left: 5px solid #0052CC !important; border-radius: 4px;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary fw-bold">BABAK PENYISIHAN</span>
                    <strong class="text-white">Kualifikasi ke Babak Final Otomatis</strong>
                </div>
                <p class="mb-0 text-muted small">
                    Setelah catatan waktu penyisihan diinput, Anda dapat langsung meloloskan 8 pelari tercepat ke <strong>Babak Final</strong>. Nomor lintasan 1 s/d 8 akan diatur secara otomatis sesuai unggulan atletik resmi.
                </p>
            </div>
            <a href="<?= base_url('hasil/qualify-to-final/' . $event['id']) ?>" class="btn btn-primary fw-bold text-nowrap d-inline-flex align-items-center gap-2 shadow-sm px-3" onclick="return confirm('Loloskan 8 pelari tercepat dari babak penyisihan ini ke Babak Final? Nomor acara Final dan lintasan akan dibuatkan otomatis.')">
                <i class="bi bi-lightning-charge-fill text-warning fs-5"></i>
                <span>Loloskan 8 Terbaik ke Babak Final &rarr;</span>
            </a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($isFieldEvent ?? false): ?>
    <div class="alert alert-warning py-2 px-3 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 border-0 shadow-sm" style="background: rgba(255, 171, 0, 0.15); border-left: 4px solid #FFAB00 !important; color: #7A4F01;">
        <div>
            <i class="bi bi-trophy-fill text-warning me-2 fs-5"></i>
            <strong>Aturan Nomor Lapangan (3x Percobaan):</strong>
            Ketik hasil <strong>Percobaan 1, 2, dan 3</strong> (meter). Sistem otomatis memilih lemparan/lompatan yang <strong>PALING JAUH (TERBAIK)</strong>.
        </div>
        <a href="<?= base_url('hasil/form-lapangan/' . $event['id']) ?>" target="_blank" class="btn btn-sm btn-dark text-warning fw-bold text-nowrap d-inline-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-spreadsheet-fill"></i> Buka Blangko Kertas Lapangan
        </a>
    </div>
<?php endif; ?>

<!-- Input Form Card -->
<div class="card-custom">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-bold text-white">
            <i class="bi bi-stopwatch-fill me-2 text-warning"></i> Form Pencatatan Hasil Perlombaan
        </span>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1" id="btnResetAllRank" title="Kosongkan semua peringkat & medali di tabel ini">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Reset Peringkat</span>
            </button>
            <?php if (!($isFieldEvent ?? false)): ?>
                <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" id="btnResetAllTime" title="Reset / kosongkan semua catatan waktu & peringkat di tabel ini">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset Semua Waktu</span>
                </button>
            <?php endif; ?>
            <small class="text-muted d-none d-lg-inline">Isi catatan prestasi lalu klik tombol Simpan Semua Hasil</small>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pesertaList)): ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-people fs-1 d-block mb-2"></i>
                Belum ada atlet yang terdaftar di acara ini.
                <div class="mt-3">
                    <a href="<?= base_url('event/peserta/' . $event['id']) ?>" class="btn btn-sm btn-primary-gradient">
                        <i class="bi bi-person-plus-fill me-1"></i> Tambahkan Peserta Sekarang
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form action="<?= base_url('hasil/save/' . $event['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="table-responsive">
                    <table class="table table-dark-custom mb-0">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">Gil/Hit</th>
                                <th width="85" class="text-center">No Dada</th>
                                <th>Nama Atlet</th>
                                <th>Asal Sekolah</th>
                                <?php if ($isFieldEvent ?? false): ?>
                                    <th width="90" class="text-center text-info" style="background: rgba(13, 110, 253, 0.1);">P1 (m)</th>
                                    <th width="90" class="text-center text-info" style="background: rgba(13, 110, 253, 0.1);">P2 (m)</th>
                                    <th width="90" class="text-center text-info" style="background: rgba(13, 110, 253, 0.1);">P3 (m)</th>
                                    <th width="115" class="text-center text-warning" style="background: rgba(255, 171, 0, 0.15);">TERBAIK (Jauh)</th>
                                <?php else: ?>
                                    <th width="210" class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <i class="bi bi-stopwatch text-info"></i>
                                            <span>Prestasi (Waktu)</span>
                                        </div>
                                        <small class="text-muted fw-normal" style="font-size: 0.72rem;">(M:SS.xx &bull; misal 5:07.74)</small>
                                    </th>
                                <?php endif; ?>
                                <th width="110">Status</th>
                                <th width="105" class="text-center">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <span class="text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.8rem;">Peringkat</span>
                                        <button type="button" class="btn btn-outline-warning py-0 px-2 btn-reset-all-rank-col mt-1" title="Kosongkan Semua Peringkat & Medali" style="font-size: 0.68rem; line-height: 1.35; border-radius: 4px;">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                                        </button>
                                    </div>
                                </th>
                                <th width="130">Medali</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $currentHeat = null;
                            $isFinal = ($event['babak'] === 'Final');
                            $allHeatsList = array_unique(array_filter(array_map(function($x) { return (int)($x['heat'] ?: 1); }, $pesertaList)));
                            $hasMultipleHeats = count($allHeatsList) > 1;
                            $hitCount = 0;
                            $colspan = ($isFieldEvent ?? false) ? 12 : 9;
                            ?>
                            <?php if ($isFinal && !$hasMultipleHeats): ?>
                                <tr style="background: rgba(25, 135, 84, 0.15); border-top: 2px solid #198754;">
                                    <td colspan="<?= $colspan ?>" class="py-2 px-3 fw-bold text-success fs-6">
                                        <i class="bi bi-trophy-fill me-2"></i> BABAK FINAL (<?= count($pesertaList) ?> Finalis)
                                    </td>
                                </tr>
                            <?php elseif ($isFinal && $hasMultipleHeats): ?>
                                <tr style="background: rgba(25, 135, 84, 0.15); border-top: 2px solid #198754;">
                                    <td colspan="<?= $colspan ?>" class="py-2 px-3 fw-bold text-success fs-6">
                                        <i class="bi bi-trophy-fill me-2"></i> BABAK FINAL &bull; COMPILED BY TIME (<?= count($allHeatsList) ?> Hit &bull; <?= count($pesertaList) ?> Atlet)
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($pesertaList as $p): ?>
                                <?php 
                                $pHeat = (int)($p['heat'] ?: 1);
                                if ($hasMultipleHeats && $currentHeat !== $pHeat):
                                    $currentHeat = $pHeat;
                                ?>
                                    <tr style="background: rgba(13, 110, 253, 0.15); border-top: 2px solid #0d6efd;">
                                        <td colspan="<?= $colspan ?>" class="py-2 px-3 fw-bold text-primary fs-6">
                                            <i class="bi bi-flag-fill me-2"></i> HIT <?= $currentHeat ?> <?= $isFinal ? '<span class="badge bg-success ms-2 font-monospace" style="font-size: 0.72rem; vertical-align: middle;">FINAL</span>' : '' ?>
                                        </td>
                                    </tr>
                                <?php elseif (!$isFinal && !$hasMultipleHeats && $currentHeat !== $pHeat):
                                    $currentHeat = $pHeat;
                                ?>
                                    <tr style="background: rgba(13, 110, 253, 0.15); border-top: 2px solid #0d6efd;">
                                        <td colspan="<?= $colspan ?>" class="py-2 px-3 fw-bold text-primary fs-6">
                                            <i class="bi bi-flag-fill me-2"></i> Hit <?= $currentHeat ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <tr>
                                    <td class="text-center align-middle">
                                        <?php if ($hasMultipleHeats): ?>
                                            <span class="badge badge-primary">Hit <?= $pHeat ?></span>
                                        <?php elseif ($isFinal): ?>
                                            <span class="badge badge-emerald">Final</span>
                                        <?php else: ?>
                                            <span class="badge badge-primary">Hit <?= $pHeat ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($p['lane'])): ?>
                                            <span class="badge badge-blue">L<?= esc($p['lane']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-amber font-monospace fw-bold fs-6">
                                            <?= esc(ltrim($p['no_dada'], "'") ?: '-') ?>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <?php 
                                        $isRelayEvent = (bool)preg_match('/(estafet|4\s*x|5\s*x|relay)/i', ($event['nama_acara'] ?? '') . ' ' . ($event['kategori_lomba'] ?? ''));
                                        ?>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="fw-bold text-white"><?= esc($p['nama_atlet']) ?></div>
                                            <?php if ($isRelayEvent): ?>
                                                <a href="<?= base_url('event/peserta/' . $event['id']) ?>" class="btn btn-outline-warning btn-sm py-0 px-2 fw-semibold" title="Buka kelola peserta untuk edit tim/pelari estafet">
                                                    <i class="bi bi-pencil-square me-1"></i>Edit Pelari
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($p['catatan_atlet'])): ?>
                                            <div class="text-info mt-1" style="font-size: 12.5px; line-height: 1.35;">
                                                <i class="bi bi-people-fill me-1"></i><?= esc($p['catatan_atlet']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <small class="text-muted"><?= ($p['gender'] ?? '') === 'L' ? 'Putra (Male)' : (($p['gender'] ?? '') === 'Campuran' ? 'Campuran (MIX)' : 'Putri (Female)') ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <small class="text-muted"><?= esc($p['nama_sekolah'] ?: '-') ?></small>
                                    </td>

                                    <?php if ($isFieldEvent ?? false): ?>
                                        <!-- Kolom 3x Percobaan untuk Nomor Lapangan -->
                                        <td class="align-middle p-1">
                                            <input type="text" name="hasil[<?= $p['id'] ?>][p1]" class="form-control form-control-sm text-center font-monospace trial-input trial-p1" value="<?= esc($p['p1'] ?? '') ?>" placeholder="P1 (m)">
                                        </td>
                                        <td class="align-middle p-1">
                                            <input type="text" name="hasil[<?= $p['id'] ?>][p2]" class="form-control form-control-sm text-center font-monospace trial-input trial-p2" value="<?= esc($p['p2'] ?? '') ?>" placeholder="P2 (m)">
                                        </td>
                                        <td class="align-middle p-1">
                                            <input type="text" name="hasil[<?= $p['id'] ?>][p3]" class="form-control form-control-sm text-center font-monospace trial-input trial-p3" value="<?= esc($p['p3'] ?? '') ?>" placeholder="P3 (m)">
                                        </td>
                                        <td class="align-middle p-1">
                                            <!-- Otomatis mengambil yang paling jauh -->
                                            <input type="text" name="hasil[<?= $p['id'] ?>][prestasi]" class="form-control form-control-sm text-center fw-bold text-warning font-monospace best-trial-input" value="<?= esc($p['prestasi'] ?? '') ?>" placeholder="Terbaik" style="background: rgba(255, 171, 0, 0.1); border-color: #FFAB00;">
                                        </td>
                                    <?php else: ?>
                                        <?php
                                        $curPrestasi = trim($p['prestasi'] ?? '');
                                        ?>
                                        <td class="align-middle text-center p-1" style="min-width: 155px;">
                                            <div class="input-group input-group-sm mx-auto time-picker-group" style="max-width: 155px;">
                                                <span class="input-group-text bg-dark border-secondary text-info px-2" title="Waktu">
                                                    <i class="bi bi-stopwatch"></i>
                                                </span>
                                                <input type="text"
                                                       name="hasil[<?= $p['id'] ?>][prestasi]"
                                                       id="prestasi_<?= $p['id'] ?>"
                                                       class="form-control form-control-sm text-center fw-bold font-monospace time-input-field border-secondary"
                                                       placeholder="0:00.00"
                                                       value="<?= esc($curPrestasi) ?>"
                                                       autocomplete="off"
                                                       data-row-id="<?= $p['id'] ?>"
                                                       data-nama="<?= esc($p['nama_atlet']) ?>"
                                                       data-dada="<?= esc(ltrim($p['no_dada'], "'") ?: '-') ?>"
                                                       data-sekolah="<?= esc($p['nama_sekolah'] ?: '-') ?>"
                                                       style="font-size: 0.95rem; letter-spacing: 0.5px; background: #0f172a; color: #38bdf8; cursor: pointer;">
                                                <button type="button" class="btn btn-outline-danger btn-reset-row-time px-2" title="Reset Waktu Atlet Ini (Kosongkan)" style="<?= empty($curPrestasi) ? 'display: none;' : '' ?>">
                                                    <i class="bi bi-x-circle-fill"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-open-scroll px-2" title="Buka Pilihan Scroll Waktu">
                                                    <i class="bi bi-chevron-down small"></i>
                                                </button>
                                            </div>
                                        </td>
                                    <?php endif; ?>

                                    <td class="align-middle">
                                        <select name="hasil[<?= $p['id'] ?>][status_prestasi]" class="form-select form-select-sm">
                                            <option value="Valid" <?= ($p['status_prestasi'] ?? 'Valid') === 'Valid' ? 'selected' : '' ?>>Valid</option>
                                            <option value="DNS" <?= ($p['status_prestasi'] ?? '') === 'DNS' ? 'selected' : '' ?>>DNS</option>
                                            <option value="DNF" <?= ($p['status_prestasi'] ?? '') === 'DNF' ? 'selected' : '' ?>>DNF</option>
                                            <option value="DQ" <?= ($p['status_prestasi'] ?? '') === 'DQ' ? 'selected' : '' ?>>DQ</option>
                                            <option value="NM" <?= ($p['status_prestasi'] ?? '') === 'NM' ? 'selected' : '' ?>>NM (Foul/Gagal)</option>
                                        </select>
                                    </td>
                                    <td class="align-middle text-center p-1">
                                        <div class="input-group input-group-sm mx-auto rank-picker-group" style="max-width: 95px;">
                                            <input type="number" 
                                                   name="hasil[<?= $p['id'] ?>][peringkat]" 
                                                   class="form-control form-control-sm text-center fw-bold rank-input-field" 
                                                   value="<?= esc($p['peringkat'] ?? '') ?>" 
                                                   placeholder="Rank" 
                                                   min="1"
                                                   style="background: #0f172a; color: #facc15;">
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-reset-row-rank px-1" 
                                                    title="Reset Peringkat Atlet Ini" 
                                                    style="<?= empty($p['peringkat']) ? 'display: none;' : '' ?>">
                                                <i class="bi bi-x-circle-fill" style="font-size: 0.78rem;"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <select name="hasil[<?= $p['id'] ?>][medali]" class="form-select form-select-sm">
                                            <option value="None" <?= ($p['medali'] ?? 'None') === 'None' ? 'selected' : '' ?>>-</option>
                                            <option value="Emas" <?= ($p['medali'] ?? '') === 'Emas' ? 'selected' : '' ?>>🥇 Emas</option>
                                            <option value="Perak" <?= ($p['medali'] ?? '') === 'Perak' ? 'selected' : '' ?>>🥈 Perak</option>
                                            <option value="Perunggu" <?= ($p['medali'] ?? '') === 'Perunggu' ? 'selected' : '' ?>>🥉 Perunggu</option>
                                        </select>
                                    </td>
                                    <td class="align-middle">
                                        <input type="text" name="hasil[<?= $p['id'] ?>][keterangan]" class="form-control form-control-sm" value="<?= esc($p['ket_hasil'] ?? '') ?>" placeholder="Catatan juri">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-dark border-top d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important;">
                    <span class="text-muted small">
                        <i class="bi bi-shield-check text-success me-1"></i> Data hasil akan langsung terintegrasi dengan Buku Acara & Klasemen Medali.
                    </span>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('hasil') ?>" class="btn btn-secondary-custom btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary-gradient px-4">
                            <i class="bi bi-save-fill me-1"></i> Simpan Semua Hasil
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- CSS Styles untuk Floating Scroll Time Picker -->
<style>
.time-scroll-popover {
    position: absolute;
    z-index: 9999;
    background: #0f172a;
    color: #f8fafc;
    border: 1px solid rgba(6, 182, 212, 0.45);
    border-radius: 12px;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.65), 0 0 15px rgba(6, 182, 212, 0.25);
    padding: 12px;
    width: 315px;
    user-select: none;
    animation: tspFadeIn 0.15s ease-out;
}
@keyframes tspFadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
.scroll-drum-container {
    display: flex;
    gap: 6px;
    background: #090d16;
    padding: 6px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.scroll-drum-box {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.drum-label {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.6px;
    text-align: center;
    color: #94a3b8;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.scroll-drum-col {
    height: 160px;
    overflow-y: scroll;
    scroll-behavior: smooth;
    text-align: center;
    scrollbar-width: thin;
    scrollbar-color: #334155 transparent;
    padding: 64px 0; /* Memberi ruang agar item teratas dan terbawah bisa berada di tengah */
}
.scroll-drum-col::-webkit-scrollbar {
    width: 4px;
}
.scroll-drum-col::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 4px;
}
.scroll-drum-item {
    height: 32px;
    line-height: 32px;
    font-size: 0.95rem;
    font-weight: 700;
    font-family: monospace;
    color: #64748b;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.12s ease;
}
.scroll-drum-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #f8fafc;
}
.scroll-drum-item.active {
    background: #0284c7;
    color: #ffffff;
    font-weight: 900;
    box-shadow: 0 0 10px rgba(2, 132, 199, 0.7);
    transform: scale(1.06);
}
.btn-xs {
    padding: 0.15rem 0.4rem;
    font-size: 0.75rem;
    line-height: 1.2;
}
</style>

<!-- Floating Scroll Time Picker Popover (Pilihan Scroll Langsung Muncul) -->
<div id="globalTimeScrollPicker" class="time-scroll-popover shadow-lg" style="display: none;">
    <!-- Athlete Info Header -->
    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary border-opacity-50">
        <div class="small text-truncate me-2" style="max-width: 250px;">
            <i class="bi bi-stopwatch text-info me-1"></i>
            <span id="tspAthleteInfo" class="fw-bold text-white small">Atlet</span>
        </div>
        <button type="button" class="btn-close btn-close-white btn-sm" id="tspBtnClose" aria-label="Tutup" style="font-size: 0.65rem;"></button>
    </div>

    <!-- Live Big Time Preview (Format M:SS.xx -> misal 5:07.74) -->
    <div class="text-center py-1 mb-2 rounded bg-black bg-opacity-60 border border-info border-opacity-30 font-monospace fw-bold fs-3 text-info" id="tspLivePreview">
        <span id="tspPrevMin">0</span>:<span id="tspPrevSec">00</span>.<span id="tspPrevMs">00</span>
    </div>

    <!-- Scroll Columns Container (Drum Rollers: MENIT, DETIK, RATUSAN) -->
    <div class="scroll-drum-container mb-2">
        <!-- Column Menit (0 - 59) -->
        <div class="scroll-drum-box flex-grow-1" id="tspBoxMin">
            <div class="drum-label">MENIT (M)</div>
            <div class="scroll-drum-col" id="tspColMin">
                <?php for ($m = 0; $m <= 59; $m++): $mStr = sprintf('%02d', $m); ?>
                    <div class="scroll-drum-item" data-val="<?= $mStr ?>"><?= $m ?>m</div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Column Detik (00 - 59) -->
        <div class="scroll-drum-box flex-grow-1" id="tspBoxSec">
            <div class="drum-label">DETIK (S)</div>
            <div class="scroll-drum-col" id="tspColSec">
                <?php for ($s = 0; $s <= 59; $s++): $sStr = sprintf('%02d', $s); ?>
                    <div class="scroll-drum-item" data-val="<?= $sStr ?>"><?= $sStr ?>s</div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Column Ratusan (.00 - .99) -->
        <div class="scroll-drum-box flex-grow-1" id="tspBoxMs">
            <div class="drum-label">RATUSAN (.xx)</div>
            <div class="scroll-drum-col" id="tspColMs">
                <?php for ($ms = 0; $ms <= 99; $ms++): $msStr = sprintf('%02d', $ms); ?>
                    <div class="scroll-drum-item" data-val="<?= $msStr ?>">.<?= $msStr ?></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Quick Adjustment Steppers (+ / -) -->
    <div class="d-flex justify-content-center gap-1 mb-1">
        <button type="button" class="btn btn-xs btn-dark border border-secondary px-1 tsp-step-btn" data-step="-60" title="-1 Menit">-1m</button>
        <button type="button" class="btn btn-xs btn-dark border border-secondary px-1 tsp-step-btn" data-step="-1" title="-1 Detik">-1s</button>
        <button type="button" class="btn btn-xs btn-dark border border-secondary px-1 tsp-step-btn" data-step="-0.1" title="-0.1 Detik">-0.1s</button>
        <button type="button" class="btn btn-xs btn-dark border border-secondary px-1 tsp-step-btn" data-step="0.1" title="+0.1 Detik">+0.1s</button>
        <button type="button" class="btn btn-xs btn-dark border border-secondary px-1 tsp-step-btn" data-step="1" title="+1 Detik">+1s</button>
        <button type="button" class="btn btn-xs btn-dark border border-secondary px-1 tsp-step-btn" data-step="60" title="+1 Menit">+1m</button>
    </div>

    <!-- Quick Ratusan Presets (.00, .25, .50, .75) -->
    <div class="d-flex justify-content-center align-items-center gap-1 mb-2">
        <span class="text-muted" style="font-size: 0.68rem;">Ratusan:</span>
        <button type="button" class="btn btn-xs btn-outline-info py-0 px-1 tsp-quick-ms" data-ms="00">.00</button>
        <button type="button" class="btn btn-xs btn-outline-info py-0 px-1 tsp-quick-ms" data-ms="25">.25</button>
        <button type="button" class="btn btn-xs btn-outline-info py-0 px-1 tsp-quick-ms" data-ms="50">.50</button>
        <button type="button" class="btn btn-xs btn-outline-info py-0 px-1 tsp-quick-ms" data-ms="75">.75</button>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-1 justify-content-between pt-2 border-top border-secondary border-opacity-50 align-items-center">
        <button type="button" class="btn btn-outline-danger btn-sm px-2 fw-bold d-inline-flex align-items-center gap-1" id="tspBtnReset" title="Reset catatan waktu atlet ini ke kosong">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Reset Waktu</span>
        </button>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-primary-gradient btn-sm px-3 fw-bold" id="tspBtnDone">
                <i class="bi bi-check2"></i> Selesai
            </button>
            <button type="button" class="btn btn-warning btn-sm px-2 fw-bold text-dark" id="tspBtnNext" title="Simpan dan langsung buka atlet baris berikutnya">
                Lanjut &darr;
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    // 1. Live Auto-Calculate untuk Nomor Lapangan (P1, P2, P3)
    $('.trial-input').on('input', function () {
        const row = $(this).closest('tr');
        const p1 = row.find('.trial-p1').val().trim().replace(',', '.');
        const p2 = row.find('.trial-p2').val().trim().replace(',', '.');
        const p3 = row.find('.trial-p3').val().trim().replace(',', '.');

        let maxVal = null;
        [p1, p2, p3].forEach(function (v) {
            const num = parseFloat(v);
            if (!isNaN(num) && num > 0) {
                if (maxVal === null || num > maxVal) {
                    maxVal = num;
                }
            }
        });

        const bestInput = row.find('.best-trial-input');
        if (maxVal !== null) {
            bestInput.val(maxVal.toFixed(2));
        } else if (p1.toUpperCase() === 'X' && p2.toUpperCase() === 'X' && p3.toUpperCase() === 'X') {
            bestInput.val('NM');
        }
    });

    // 2. Interactive Floating Scroll Time Picker Handler (Format M:SS.xx -> misal 5:07.74)
    let activeInput = null;
    let currentMin = '00';
    let currentSec = '00';
    let currentMs  = '00';
    let isScrollUpdating = false;

    function parseTimeString(val) {
        let m = 0, s = 0, ms = 0;
        if (!val || ['DNS', 'DNF', 'DQ', 'NM', '-'].includes(val.toUpperCase().trim())) {
            return { min: '00', sec: '00', ms: '00' };
        }
        val = val.trim();

        if (val.includes(':')) {
            // Misal 5:07.74 atau 5:07
            const parts = val.split(':');
            m = parseInt(parts[0], 10) || 0;
            const right = parts[1] || '';
            if (right.includes('.') || right.includes(',')) {
                const secParts = right.split(/[.,]/);
                s = parseInt(secParts[0], 10) || 0;
                ms = parseInt(secParts[1] ? secParts[1].padEnd(2, '0').substring(0, 2) : '0', 10) || 0;
            } else {
                s = parseInt(right, 10) || 0;
                ms = 0;
            }
        } else if (val.includes('.') || val.includes(',')) {
            // Misal 5.07.74 atau 59.42
            const parts = val.split(/[.,]/);
            if (parts.length >= 3) {
                m = parseInt(parts[0], 10) || 0;
                s = parseInt(parts[1], 10) || 0;
                ms = parseInt(parts[2].padEnd(2, '0').substring(0, 2), 10) || 0;
            } else if (parts.length === 2) {
                const p0 = parseInt(parts[0], 10) || 0;
                const p1 = parseInt(parts[1].padEnd(2, '0').substring(0, 2), 10) || 0;
                if (p0 >= 60) {
                    m = Math.floor(p0 / 60);
                    s = p0 % 60;
                    ms = p1;
                } else {
                    m = 0;
                    s = p0;
                    ms = p1;
                }
            }
        } else {
            // Hanya digit angka: misal 50774 atau 1174 atau 59
            const digits = val.replace(/\D/g, '');
            if (digits.length >= 5) {
                // misal 50774 -> m: 5, s: 07, ms: 74
                ms = parseInt(digits.slice(-2), 10) || 0;
                s  = parseInt(digits.slice(-4, -2), 10) || 0;
                m  = parseInt(digits.slice(0, -4), 10) || 0;
            } else if (digits.length === 3 || digits.length === 4) {
                // misal 1174 -> m: 0, s: 11, ms: 74
                ms = parseInt(digits.slice(-2), 10) || 0;
                s  = parseInt(digits.slice(0, -2), 10) || 0;
                m  = 0;
            } else if (digits.length > 0) {
                const num = parseInt(digits, 10) || 0;
                if (num >= 60) {
                    m = Math.floor(num / 60);
                    s = num % 60;
                    ms = 0;
                } else {
                    m = 0;
                    s = num;
                    ms = 0;
                }
            }
        }

        m = Math.min(59, Math.max(0, m));
        s = Math.min(59, Math.max(0, s));
        ms = Math.min(99, Math.max(0, ms));

        return {
            min: String(m).padStart(2, '0'),
            sec: String(s).padStart(2, '0'),
            ms:  String(ms).padStart(2, '0')
        };
    }

    function formatTimeOutput(min, sec, ms) {
        if (!min && !sec && !ms) return '';
        const m = parseInt(min, 10) || 0;
        const s = String(parseInt(sec, 10) || 0).padStart(2, '0');
        const msec = String(parseInt(ms, 10) || 0).padStart(2, '0');
        return m + ':' + s + '.' + msec;
    }

    function scrollToVal(colEl, val) {
        const item = colEl.find(`.scroll-drum-item[data-val="${val}"]`);
        if (item.length > 0) {
            colEl.find('.scroll-drum-item').removeClass('active');
            item.addClass('active');
            const targetScroll = item.position().top + colEl.scrollTop() - (colEl.height() / 2) + (item.height() / 2);
            isScrollUpdating = true;
            colEl.stop().animate({ scrollTop: targetScroll }, 180, function () {
                setTimeout(() => { isScrollUpdating = false; }, 50);
            });
        }
    }

    function updateLivePreview(syncInput = true) {
        const m = parseInt(currentMin, 10) || 0;
        const s = currentSec || '00';
        const ms = currentMs || '00';

        $('#tspPrevMin').text(m);
        $('#tspPrevSec').text(s);
        $('#tspPrevMs').text(ms);

        if (activeInput && syncInput) {
            const formatted = formatTimeOutput(currentMin, currentSec, currentMs);
            activeInput.val(formatted).trigger('change');
            activeInput.closest('.time-picker-group').find('.btn-reset-row-time').show();
        }
    }

    function openScrollPicker(input) {
        activeInput = input;
        const popover = $('#globalTimeScrollPicker');
        const nama = input.data('nama') || 'Atlet';
        const dada = input.data('dada') || '-';

        $('#tspAthleteInfo').html(`No Dada: <strong class="text-warning">${dada}</strong> &bull; <strong>${nama}</strong>`);

        const rawVal = (input.val() || '').trim();
        const parsed = parseTimeString(rawVal);
        currentMin = parsed.min;
        currentSec = parsed.sec;
        currentMs  = parsed.ms;

        // If input already has a value, sync preview and keep it
        // If input was empty, show 0:00.00 in preview but don't force write into input until user scrolls or clicks
        updateLivePreview(rawVal !== '');

        // Position popover right under the input group
        const group = input.closest('.time-picker-group');
        const offset = group.offset();
        const popHeight = 330;
        const windowHeight = $(window).height();
        const scrollTop = $(window).scrollTop();

        let topPos = offset.top + group.outerHeight() + 6;
        if (topPos + popHeight > scrollTop + windowHeight && offset.top - popHeight > scrollTop) {
            topPos = offset.top - popHeight - 6;
        }

        let leftPos = offset.left + (group.outerWidth() / 2) - 157;
        leftPos = Math.max(8, Math.min(leftPos, $(window).width() - 325));

        popover.css({
            top: topPos + 'px',
            left: leftPos + 'px'
        }).fadeIn(120);

        setTimeout(() => {
            scrollToVal($('#tspColMin'), currentMin);
            scrollToVal($('#tspColSec'), currentSec);
            scrollToVal($('#tspColMs'), currentMs);
        }, 50);
    }

    // Trigger buka picker saat klik input atau tombol chevron
    $(document).on('click focus', '.time-input-field', function () {
        openScrollPicker($(this));
    });

    $(document).on('click', '.btn-open-scroll', function (e) {
        e.stopPropagation();
        const input = $(this).closest('.time-picker-group').find('.time-input-field');
        openScrollPicker(input);
    });

    // Klik pada item scroll drum
    $(document).on('click', '.scroll-drum-item', function () {
        const col = $(this).closest('.scroll-drum-col');
        const val = $(this).data('val');

        if (col.is('#tspColMin')) {
            currentMin = String(val).padStart(2, '0');
        } else if (col.is('#tspColSec')) {
            currentSec = String(val).padStart(2, '0');
        } else if (col.is('#tspColMs')) {
            currentMs = String(val).padStart(2, '0');
        }

        scrollToVal(col, val);
        updateLivePreview();
    });

    // Detect scroll stopped to snap to closest item
    $('.scroll-drum-col').on('scroll', function () {
        if (isScrollUpdating) return;
        const col = $(this);
        clearTimeout(col.data('scrollTimer'));
        col.data('scrollTimer', setTimeout(function () {
            const centerY = col.scrollTop() + (col.height() / 2);
            let closestItem = null;
            let closestDist = Infinity;

            col.find('.scroll-drum-item').each(function () {
                const item = $(this);
                const itemCenter = item.position().top + col.scrollTop() + (item.height() / 2);
                const dist = Math.abs(itemCenter - centerY);
                if (dist < closestDist) {
                    closestDist = dist;
                    closestItem = item;
                }
            });

            if (closestItem && closestDist < 30) {
                const val = closestItem.data('val');
                col.find('.scroll-drum-item').removeClass('active');
                closestItem.addClass('active');

                if (col.is('#tspColMin')) {
                    currentMin = String(val).padStart(2, '0');
                } else if (col.is('#tspColSec')) {
                    currentSec = String(val).padStart(2, '0');
                } else if (col.is('#tspColMs')) {
                    currentMs = String(val).padStart(2, '0');
                }
                updateLivePreview();
            }
        }, 120));
    });

    // Quick Ratusan Preset Buttons (.00, .25, .50, .75)
    $(document).on('click', '.tsp-quick-ms', function () {
        const msVal = String($(this).data('ms')).padStart(2, '0');
        currentMs = msVal;
        scrollToVal($('#tspColMs'), currentMs);
        updateLivePreview();
    });

    // Steppers (+ / -)
    $('.tsp-step-btn').on('click', function () {
        const step = parseFloat($(this).data('step')) || 0;
        const m = parseInt(currentMin, 10) || 0;
        const s = parseInt(currentSec, 10) || 0;
        const ms = parseInt(currentMs, 10) || 0;

        let totalSec = (m * 60) + s + (ms / 100);
        totalSec = Math.max(0, Math.min(3599.99, totalSec + step));

        const newM = Math.floor(totalSec / 60);
        const rem = totalSec - (newM * 60);
        const newS = Math.floor(rem);
        const newMs = Math.round((rem - newS) * 100);

        currentMin = String(newM).padStart(2, '0');
        currentSec = String(newS).padStart(2, '0');
        currentMs  = String(Math.min(99, newMs)).padStart(2, '0');

        scrollToVal($('#tspColMin'), currentMin);
        scrollToVal($('#tspColSec'), currentSec);
        scrollToVal($('#tspColMs'), currentMs);
        updateLivePreview();
    });

    // Typing directly into time input field
    $(document).on('input', '.time-input-field', function () {
        const input = $(this);
        const val = input.val();
        if (!val) {
            currentMin = '00';
            currentSec = '00';
            currentMs  = '00';
            $('#tspPrevMin').text('0');
            $('#tspPrevSec').text('00');
            $('#tspPrevMs').text('00');
            return;
        }
        const parsed = parseTimeString(val);

        currentMin = parsed.min;
        currentSec = parsed.sec;
        currentMs  = parsed.ms;

        scrollToVal($('#tspColMin'), currentMin);
        scrollToVal($('#tspColSec'), currentSec);
        scrollToVal($('#tspColMs'), currentMs);

        $('#tspPrevMin').text(parseInt(currentMin, 10) || 0);
        $('#tspPrevSec').text(currentSec);
        $('#tspPrevMs').text(currentMs);
    });

    // Re-format cleanly on blur
    $(document).on('blur', '.time-input-field', function () {
        const val = $(this).val().trim();
        if (val && !['DNS', 'DNF', 'DQ', 'NM', '-'].includes(val.toUpperCase())) {
            const parsed = parseTimeString(val);
            $(this).val(formatTimeOutput(parsed.min, parsed.sec, parsed.ms));
        }
    });

    // Keyboard navigation (Enter / ArrowDown to next athlete, ArrowUp to previous athlete)
    $(document).on('keydown', '.time-input-field', function (e) {
        if (e.key === 'Enter' || e.key === 'ArrowDown') {
            e.preventDefault();
            const val = $(this).val().trim();
            if (val && !['DNS', 'DNF', 'DQ', 'NM', '-'].includes(val.toUpperCase())) {
                const parsed = parseTimeString(val);
                $(this).val(formatTimeOutput(parsed.min, parsed.sec, parsed.ms));
            }
            const currentTr = $(this).closest('tr');
            const nextTr = currentTr.nextAll('tr').has('.time-input-field').first();
            if (nextTr.length > 0) {
                const nextInput = nextTr.find('.time-input-field');
                openScrollPicker(nextInput);
                nextInput.focus().select();
            } else {
                closeScrollPicker();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const currentTr = $(this).closest('tr');
            const prevTr = currentTr.prevAll('tr').has('.time-input-field').first();
            if (prevTr.length > 0) {
                const prevInput = prevTr.find('.time-input-field');
                openScrollPicker(prevInput);
                prevInput.focus().select();
            }
        }
    });

    // Reset / Kosongkan dari Popover (Waktu + Peringkat + Medali baris ini)
    $('#tspBtnReset').on('click', function () {
        currentMin = '00';
        currentSec = '00';
        currentMs  = '00';
        if (activeInput) {
            const row = activeInput.closest('tr');
            activeInput.val('').trigger('change');
            row.find('input[name*="[peringkat]"]').val('').trigger('change');
            row.find('select[name*="[medali]"]').val('None').trigger('change');
            activeInput.closest('.time-picker-group').find('.btn-reset-row-time').hide();
            row.find('.btn-reset-row-rank').hide();
        }
        $('#tspPrevMin').text('0');
        $('#tspPrevSec').text('00');
        $('#tspPrevMs').text('00');
        scrollToVal($('#tspColMin'), '00');
        scrollToVal($('#tspColSec'), '00');
        scrollToVal($('#tspColMs'), '00');
    });

    // Reset tombol silang per baris (Waktu + Peringkat + Medali baris ini)
    $(document).on('click', '.btn-reset-row-time', function (e) {
        e.stopPropagation();
        const group = $(this).closest('.time-picker-group');
        const input = group.find('.time-input-field');
        const row = $(this).closest('tr');

        input.val('').trigger('input').trigger('change');
        row.find('input[name*="[peringkat]"]').val('').trigger('change');
        row.find('select[name*="[medali]"]').val('None').trigger('change');
        row.find('.btn-reset-row-rank').hide();
        $(this).hide();

        if (activeInput && activeInput[0] === input[0]) {
            currentMin = '00';
            currentSec = '00';
            currentMs  = '00';
            $('#tspPrevMin').text('0');
            $('#tspPrevSec').text('00');
            $('#tspPrevMs').text('00');
            scrollToVal($('#tspColMin'), '00');
            scrollToVal($('#tspColSec'), '00');
            scrollToVal($('#tspColMs'), '00');
        }
    });

    // Toggle tampilan tombol reset di baris saat ketik manual
    $(document).on('input change', '.time-input-field', function () {
        const btnReset = $(this).closest('.time-picker-group').find('.btn-reset-row-time');
        if ($(this).val().trim() !== '') {
            btnReset.show();
        } else {
            btnReset.hide();
        }
    });

    // Reset tombol silang peringkat per baris
    $(document).on('click', '.btn-reset-row-rank', function (e) {
        e.stopPropagation();
        const row = $(this).closest('tr');
        row.find('.rank-input-field').val('').trigger('input').trigger('change');
        row.find('select[name*="[medali]"]').val('None').trigger('change');
        $(this).hide();
    });

    // Toggle tampilan tombol reset peringkat di baris saat ketik manual
    $(document).on('input change', '.rank-input-field', function () {
        const btnReset = $(this).closest('.rank-picker-group').find('.btn-reset-row-rank');
        if ($(this).val().trim() !== '') {
            btnReset.show();
        } else {
            btnReset.hide();
        }
    });

    // Reset Semua Peringkat & Medali pada Tabel
    $(document).on('click', '#btnResetAllRank, .btn-reset-all-rank-col', function () {
        if (confirm('Apakah Anda yakin ingin mengosongkan semua isian Peringkat dan Medali di tabel ini?')) {
            $('input[name*="[peringkat]"]').val('').trigger('change');
            $('select[name*="[medali]"]').val('None').trigger('change');
            $('.btn-reset-row-rank').hide();
        }
    });

    // Reset Semua Waktu & Peringkat pada Form
    $('#btnResetAllTime').on('click', function () {
        if (confirm('Apakah Anda yakin ingin mengosongkan / mereset semua input catatan waktu dan peringkat di tabel ini?')) {
            $('.time-input-field').val('').trigger('change');
            $('input[name*="[peringkat]"]').val('').trigger('change');
            $('select[name*="[medali]"]').val('None').trigger('change');
            $('.btn-reset-row-time').hide();
            $('.btn-reset-row-rank').hide();
            closeScrollPicker();
        }
    });

    // Selesai / Close
    function closeScrollPicker() {
        $('#globalTimeScrollPicker').fadeOut(100);
        activeInput = null;
    }

    $('#tspBtnDone, #tspBtnClose').on('click', function () {
        closeScrollPicker();
    });

    // Lanjut ke atlet berikutnya
    $('#tspBtnNext').on('click', function () {
        if (!activeInput) return;
        const currentTr = activeInput.closest('tr');
        const nextTr = currentTr.nextAll('tr').has('.time-input-field').first();

        if (nextTr.length > 0) {
            const nextInput = nextTr.find('.time-input-field');
            openScrollPicker(nextInput);
            nextInput.focus();
        } else {
            closeScrollPicker();
            alert('Semua atlet pada daftar ini telah selesai diatur.');
        }
    });

    // Close when clicking outside popover and inputs
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#globalTimeScrollPicker, .time-picker-group').length) {
            closeScrollPicker();
        }
    });

    // Close on Escape key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeScrollPicker();
        }
    });
});
</script>
<?= $this->endSection() ?>
