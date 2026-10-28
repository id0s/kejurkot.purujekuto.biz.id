<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
// Tentukan tanggal dan format piagam sesuai kejuaraan aktif
$tanggalKejuaraan = '27 September 2026';
if (!empty($activeKejuaraan['tanggal_mulai'])) {
    $tglTime = strtotime($activeKejuaraan['tanggal_mulai']);
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $tanggalKejuaraan = date('j', $tglTime) . ' ' . ($bulanIndo[(int)date('n', $tglTime)] ?? '') . ' ' . date('Y', $tglTime);
} elseif (!empty($activeKejuaraan['id']) && (int)$activeKejuaraan['id'] === 2) {
    $tanggalKejuaraan = '29 Agustus 2026';
}

$nomorPiagamDefault = (!empty($activeKejuaraan['id']) && (int)$activeKejuaraan['id'] === 2)
    ? '001/SERT/PASI.KOTA/VIII/' . date('Y')
    : '001/SERT/PASIJATENG/IX/' . date('Y');
?>

<!-- Header Gadget -->
<div class="card-custom mb-4 p-4" style="background: linear-gradient(135deg, rgba(0, 82, 204, 0.08) 0%, rgba(255, 171, 0, 0.12) 100%); border: 1px solid #DFE1E6; border-radius: 4px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 4px; background: #0052CC; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 1px 3px rgba(9,30,66,0.2);">
                <i class="bi bi-award-fill text-warning"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span class="badge badge-amber">
                        <i class="bi bi-patch-check-fill me-1"></i> Piagam & Sertifikat Resmi
                    </span>
                    <span class="text-muted" style="font-size: 12px;"><?= esc($activeKejuaraan['nama'] ?? 'Kejurwil Atletik 2026') ?></span>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditTanggalKejuaraan" title="Klik untuk edit dan simpan tanggal kejuaraan langsung ke database">
                        <i class="bi bi-calendar-check-fill text-primary"></i>
                        <span id="headerTglDisplay">Tanggal: <?= esc($tanggalKejuaraan) ?></span>
                        <i class="bi bi-pencil-fill text-primary ms-1" style="font-size: 10px;"></i>
                    </button>
                </div>
                <h3 class="fw-bold mb-0" style="color: #172B4D; font-size: 20px; letter-spacing: -0.3px;">
                    Penerbitan Piagam Penghargaan Juara
                </h3>
                <p class="text-muted mb-0 small mt-1">
                    Cetak piagam penghargaan juara (1, 2, 3) per nomor mata lomba, sesuaikan teks/nama/tempat, atau gunakan template kustom yang diunggah.
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary fw-bold d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditTanggalKejuaraan">
                <i class="bi bi-calendar-date-fill text-primary"></i>
                <span>Edit & Simpan Tanggal</span>
            </button>

            <a href="<?= base_url('piagam/templates') ?>" class="btn btn-warning fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-dark">
                <i class="bi bi-pen-fill"></i>
                <span>Pengaturan TTD & Format Piagam</span>
            </a>
            
            <button type="button" class="btn btn-secondary-custom d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCustomPiagam">
                <i class="bi bi-pencil-square text-success"></i>
                <span>Sesuaikan Piagam Mandiri</span>
            </button>

            <a href="<?= base_url('piagam/sekolah') ?>" class="btn btn-primary fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-white">
                <i class="bi bi-building-check fs-6"></i>
                <span>Cetak & Download per Sekolah</span>
            </a>

            <?php if ($totalPemenang > 0): ?>
                <a href="<?= base_url('piagam/cetak-all' . ($defaultTemplate ? '?template_id=' . $defaultTemplate['id'] : '')) ?>" target="_blank" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-printer-fill fs-6"></i>
                    <span>Cetak Semua Piagam Juara (<?= $totalPemenang ?>)</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Notifikasi Flashdata -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2"></i>
        <div><?= session()->getFlashdata('success') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
        <div><?= session()->getFlashdata('error') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Bar Filter & Template Aktif Info -->
<div class="card-custom mb-4 p-3" style="background: #FFFFFF; border: 1px solid #DFE1E6;">
    <div class="row align-items-center g-3">
        <div class="col-lg-7">
            <form method="get" action="<?= base_url('piagam') ?>" class="d-flex flex-wrap align-items-center gap-2">
                <label class="form-label mb-0 fw-semibold text-muted small">Filter Mata Lomba:</label>
                <select name="event_id" class="form-select form-select-sm" style="max-width: 320px;" onchange="this.form.submit()">
                    <option value="">-- Tampilkan Semua Nomor Acara --</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= $ev['id'] ?>" <?= ($filterEventId == $ev['id']) ? 'selected' : '' ?>>
                            #<?= $ev['nomor_acara'] ?> - <?= esc($ev['nama_acara']) ?> (<?= $ev['babak'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($filterEventId)): ?>
                    <a href="<?= base_url('piagam') ?>" class="btn btn-sm btn-link text-muted text-decoration-none">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="col-lg-5 text-lg-end">
            <div class="d-inline-flex align-items-center gap-2 bg-light px-3 py-1 rounded border">
                <span class="text-muted small">Template Aktif Default:</span>
                <span class="badge badge-blue fw-semibold">
                    <i class="bi bi-file-earmark-check me-1"></i>
                    <?= esc($defaultTemplate['nama'] ?? 'Template Standar') ?>
                </span>
                <a href="<?= base_url('piagam/templates') ?>" class="small text-primary text-decoration-none fw-semibold">Ubah</a>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Nomor Lomba & Kartu Pemenang -->
<div class="space-y-4">
    <?php 
    $eventCount = 0;
    foreach ($pemenangPerEvent as $evId => $item): 
        $ev = $item['event'];
        $winners = $item['winners'];
        $hasWinners = !empty($winners);
        $eventCount++;
    ?>
        <div class="card-custom mb-3" style="background: #FFFFFF; border: 1px solid #DFE1E6;">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center py-2 px-3 gap-2" style="background: #FAFBFC; border-bottom: 1px solid #DFE1E6;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark font-monospace fs-6 px-2 py-1">
                        #<?= esc($ev['nomor_acara']) ?>
                    </span>
                    <div>
                        <strong class="text-dark fs-6"><?= esc($ev['nama_acara']) ?></strong>
                        <span class="badge badge-purple ms-2"><?= esc($ev['babak']) ?></span>
                        <span class="text-muted small ms-2">
                            &bull; <?= esc($ev['kategori_lomba'] ?? 'Atletik') ?> 
                            &bull; Gender: <?= ($ev['gender'] ?? '') === 'L' ? 'Putra (Male)' : 'Putri (Female)' ?>
                        </span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <?php if ($hasWinners): ?>
                        <?php 
                        $isEvUsia567 = false;
                        $evKuCheck = strtolower(($ev['kategori_usia'] ?? '') . ' ' . ($ev['nama_acara'] ?? ''));
                        if (($ev['kelompok_umur_id'] ?? 0) == 1 || preg_match('/\b(u[\s\-_]?[567]|ku[\s\-_]?[567]|juaraku|usia[\s\-_]?dini|[567][\s\-_]?th|[567][\s\-_]?tahun)\b|5-6|567|juaraku/i', $evKuCheck)) {
                            $isEvUsia567 = true;
                        }
                        $targetTplId = $isEvUsia567 ? 22 : (($defaultTemplate && $defaultTemplate['id'] != 22) ? (int)$defaultTemplate['id'] : 21);
                        ?>
                        <a href="<?= base_url('piagam/cetak-event/' . $ev['id'] . '?template_id=' . $targetTplId) ?>" target="_blank" class="btn btn-sm <?= $isEvUsia567 ? 'btn-warning text-dark fw-bold' : 'btn-primary-gradient' ?> d-inline-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-printer-fill"></i> Cetak Juara 1-3 <?= $isEvUsia567 ? '<span class="badge bg-dark text-warning ms-1">Usia Dini 567</span>' : '' ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('hasil/input/' . $ev['id']) ?>" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1">
                            <i class="bi bi-pencil"></i> Input Hasil Lomba
                        </a>
                    <?php endif; ?>

                    <a href="<?= base_url('hasil/input/' . $ev['id']) ?>" class="btn btn-sm btn-secondary-custom" title="Buka detail hasil">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-3">
                <?php if (!$hasWinners): ?>
                    <div class="text-center py-3 text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Belum ada data juara yang diinput untuk nomor acara ini. 
                        <a href="<?= base_url('hasil/input/' . $ev['id']) ?>" class="text-primary fw-semibold">Input Hasil & Auto-Rank</a> terlebih dahulu agar piagam juara terisi otomatis.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Peringkat</th>
                                    <th>Nama Atlet / Pemenang</th>
                                    <th>Asal Sekolah / Kontingen</th>
                                    <th style="width: 120px;">Prestasi</th>
                                    <th style="width: 180px;" class="text-end">Aksi Penyesuaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($winners as $w): 
                                    $p = (int)$w['peringkat'];
                                    $badgeMedal = 'badge-blue';
                                    $iconMedal = 'bi-award';
                                    $medalLabel = "Juara {$p}";
                                    if ($p === 1) {
                                        $badgeMedal = 'badge-amber';
                                        $iconMedal = 'bi-trophy-fill text-warning';
                                        $medalLabel = 'Juara 1 (Emas)';
                                    } elseif ($p === 2) {
                                        $badgeMedal = 'bg-secondary text-white';
                                        $iconMedal = 'bi-award-fill text-light';
                                        $medalLabel = 'Juara 2 (Perak)';
                                    } elseif ($p === 3) {
                                        $badgeMedal = 'bg-warning-subtle text-warning-emphasis border border-warning';
                                        $iconMedal = 'bi-award-fill text-warning';
                                        $medalLabel = 'Juara 3 (Perunggu)';
                                    }
                                ?>
                                    <tr>
                                        <td>
                                            <span class="badge <?= $badgeMedal ?> d-inline-flex align-items-center gap-1">
                                                <i class="bi <?= $iconMedal ?>"></i>
                                                <?= $medalLabel ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-dark"><?= esc($w['nama_atlet']) ?></strong>
                                            <div class="text-muted" style="font-size: 11px;">
                                                No. Dada: <?= esc($w['nomor_dada'] ?: '-') ?> &bull; Seri <?= esc($w['seri'] ?: 1) ?> / Lintasan <?= esc($w['no_lintasan'] ?: '-') ?>
                                            </div>
                                            <?php if (!empty($w['catatan_atlet'])): ?>
                                                <div class="text-primary small mt-1" style="font-size: 11.5px; line-height: 1.3;">
                                                    <i class="bi bi-people-fill me-1"></i><strong class="text-dark">Pelari:</strong> <?= esc(preg_replace('/^[Pp]elari\s*:\s*/i', '', $w['catatan_atlet'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= esc($w['nama_sekolah'] ?: 'Independen') ?>
                                        </td>
                                        <td>
                                            <strong class="text-primary font-monospace fs-6"><?= esc(formatPrestasiPiagam($w['prestasi'], $ev['nama_acara'])) ?></strong>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <?php if (!empty($w['akta_url'])): ?>
                                                    <a href="<?= esc($w['akta_url']) ?>" target="_blank" rel="noopener noreferrer" 
                                                       class="btn btn-sm btn-outline-warning text-dark shadow-sm d-inline-flex align-items-center gap-1" 
                                                       title="Buka Mentahan Google Drive Peserta" style="font-size: 11px;">
                                                        <i class="fa-brands fa-google-drive text-warning"></i>
                                                        <span class="d-none d-lg-inline">GDrive</span>
                                                    </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm <?= !empty($w['akta_file']) ? 'btn-info text-white' : (!empty($w['akta_url']) ? 'btn-outline-info' : 'btn-outline-secondary') ?> d-inline-flex align-items-center gap-1 btn-preview-akta"
                                                    data-atlet-id="<?= (int)($w['atlet_id'] ?? 0) ?>"
                                                    data-nama="<?= esc($w['nama_atlet']) ?>"
                                                    title="Lihat & Verifikasi Akta Kelahiran Resmi">
                                                    <i class="bi bi-file-earmark-person-fill"></i>
                                                    <span>Akta</span>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 btn-customize"
                                                data-nama="<?= esc($w['nama_atlet']) ?>"
                                                data-sekolah="<?= esc($w['nama_sekolah'] ?: 'Independen') ?>"
                                                data-kota="<?= esc($w['asal_daerah'] ?: 'Kota Pekalongan') ?>"
                                                data-lomba="<?= esc($ev['nama_acara']) ?>"
                                                data-peringkat="<?= $p ?>"
                                                data-prestasi="<?= esc(formatPrestasiPiagam($w['prestasi'], $ev['nama_acara'])) ?>"
                                                data-kejuaraan="<?= esc($activeKejuaraan['nama'] ?? 'KEJURWIL ATLETIK KOTA PEKALONGAN 2026') ?>"
                                                data-tempat="<?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan') ?>"
                                                data-tanggal="<?= esc($tanggalKejuaraan) ?>"
                                                data-is-usia-567="<?= $isEvUsia567 ? '1' : '0' ?>">
                                                <i class="bi bi-pencil-square"></i> Sesuaikan & Cetak
                                            </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($eventCount === 0): ?>
        <div class="card-custom p-5 text-center text-muted">
            <i class="bi bi-journal-x fs-1 mb-2 d-block text-secondary"></i>
            <h5>Tidak ada acara perlombaan ditemukan.</h5>
            <p class="small mb-0">Pastikan nomor acara telah dibuat di menu Jadwal / Acara Lomba.</p>
        </div>
    <?php endif; ?>
</div>

<!-- =============================================================== -->
<!-- MODAL CEPAT EDIT & SIMPAN TANGGAL KEJUARAAN -->
<!-- =============================================================== -->
<div class="modal fade" id="modalEditTanggalKejuaraan" tabindex="-1" aria-labelledby="modalEditTanggalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formQuickEditTanggal" action="<?= base_url('piagam/update-tanggal') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="kejuaraan_id" id="quickKejuaraanId" value="<?= esc($activeKejuaraan['id'] ?? 1) ?>">

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fs-6 fw-bold d-flex align-items-center gap-2" id="modalEditTanggalLabel">
                        <i class="bi bi-calendar-check-fill text-warning"></i> Edit & Simpan Tanggal Kejuaraan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small rounded-1 mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Perubahan tanggal yang disimpan di sini akan langsung disimpan ke database dan otomatis sinkron ke seluruh piagam, jadwal acara, serta buku acara.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">Kejuaraan Aktif:</label>
                        <div class="form-control form-control-sm bg-light fw-bold text-dark text-truncate">
                            <?= esc($activeKejuaraan['nama'] ?? 'Kejuaraan Atletik') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">
                            Pilih Tanggal Pelaksanaan <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-sm-5">
                                <input type="date" id="quickPickerTgl" class="form-control form-control-sm" value="<?= !empty($activeKejuaraan['tanggal_mulai']) ? date('Y-m-d', strtotime($activeKejuaraan['tanggal_mulai'])) : '2026-09-27' ?>">
                            </div>
                            <div class="col-sm-7">
                                <input type="text" name="tanggal" id="quickInputTglText" class="form-control form-control-sm fw-bold" value="<?= esc($tanggalKejuaraan) ?>" placeholder="Contoh: 27 September 2026" required>
                            </div>
                        </div>
                        <div class="d-flex gap-1 mt-2 flex-wrap">
                            <span class="text-muted small me-1">Pilihan Cepat:</span>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 quick-tgl-shortcut" data-val="2026-09-27" data-text="27 September 2026" style="font-size: 11px;">27 Sept 2026 (Karesidenan)</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 quick-tgl-shortcut" data-val="2026-08-29" data-text="29 Agustus 2026" style="font-size: 11px;">29 Agus 2026 (Kejurkot)</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">Tempat Dilaksanakan:</label>
                        <input type="text" name="tempat" id="quickInputTempat" class="form-control form-control-sm" value="<?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan') ?>" placeholder="Contoh: Stadion Hoegeng Kota Pekalongan">
                    </div>

                    <div id="alertQuickEditTanggal" class="mt-2" style="display: none;"></div>
                </div>

                <div class="modal-footer bg-light py-2 px-3 border-top">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitQuickEditTanggal" class="btn btn-primary fw-bold d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-floppy-fill"></i>
                        <span>Simpan Tanggal ke Database</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =============================================================== -->
<!-- MODAL SESUAIKAN & CETAK PIAGAM (Lengkap dengan 1. Nama, 2. Tempat, dll) -->
<!-- =============================================================== -->
<div class="modal fade" id="modalCustomPiagam" tabindex="-1" aria-labelledby="modalCustomLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('piagam/cetak-custom') ?>" method="post" target="_blank">
                <?= csrf_field() ?>

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fs-6 fw-bold d-flex align-items-center gap-2" id="modalCustomLabel">
                        <i class="bi bi-award-fill text-warning"></i> Sesuaikan Data Piagam Penghargaan Sebelum Cetak
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small rounded-1 mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Anda dapat menyesuaikan nama pemenang, tempat dilaksanakan, tanggal, nomor piagam, dan penandatangan sesuai kebutuhan sebelum dicetak.
                    </div>

                    <div class="row g-3">
                        <!-- Kategori Piagam: Umum vs Usia Dini -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-dark d-block mb-1">Kategori Piagam Sesuai Kelompok Umur:</label>
                            <div class="btn-group w-100" role="group" id="btnGroupKategoriPiagam">
                                <input type="radio" class="btn-check" name="kategori_piagam_radio" id="radioKatUmum" value="umum" checked autocomplete="off" onchange="filterModalTemplates('umum')">
                                <label class="btn btn-outline-primary btn-sm fw-semibold" for="radioKatUmum">
                                    <i class="bi bi-award-fill text-primary me-1"></i> Kejurwil Umum (KU 10 s/d Dewasa)
                                </label>

                                <input type="radio" class="btn-check" name="kategori_piagam_radio" id="radioKat567" value="usia567" autocomplete="off" onchange="filterModalTemplates('usia567')">
                                <label class="btn btn-outline-warning text-dark btn-sm fw-bold" for="radioKat567">
                                    <i class="bi bi-trophy-fill text-warning me-1"></i> Khusus Usia Dini (5, 6, 7 Tahun)
                                </label>
                            </div>
                        </div>

                        <!-- Pilihan Template -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-dark">Pilih Template Piagam <span class="text-danger">*</span></label>
                            <select name="template_id" class="form-select fw-semibold" id="custom_template_id">
                                <?php foreach ($templates as $tpl): 
                                    $isTpl567 = ((int)$tpl['id'] === 22);
                                ?>
                                    <option value="<?= $tpl['id'] ?>" 
                                        data-is-567="<?= $isTpl567 ? '1' : '0' ?>"
                                        <?= ($defaultTemplate && $defaultTemplate['id'] == $tpl['id']) ? 'selected' : '' ?>>
                                        <?= esc($tpl['nama']) ?> (<?= strtoupper($tpl['orientasi']) ?> - <?= strtoupper($tpl['tipe']) ?>) <?= $tpl['is_default'] ? '★ DEFAULT' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted small" id="templateNote">Template disesuaikan otomatis dengan kategori yang dipilih.</div>
                        </div>

                        <!-- 1. NAMA PEMENANG -->
                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-dark">
                                1. Nama Lengkap Pemenang <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_pemenang" id="custom_nama_pemenang" class="form-control fw-bold" placeholder="Contoh: DONNA ARTANTI MAHADANA" required>
                            <small class="text-muted" style="font-size: 11px;">Akan tercetak sebagai nama utama di piagam penghargaan.</small>
                        </div>

                        <!-- Peringkat / Juara -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-dark">Sebagai Juara <span class="text-danger">*</span></label>
                            <select name="peringkat" id="custom_peringkat" class="form-select fw-bold">
                                <option value="1">Juara 1 (Satu) - Emas</option>
                                <option value="2">Juara 2 (Dua) - Perak</option>
                                <option value="3">Juara 3 (Tiga) - Perunggu</option>
                                <option value="4">Juara Harapan 1</option>
                                <option value="5">Juara Harapan 2</option>
                            </select>
                        </div>

                        <input type="hidden" name="asal_sekolah" id="custom_asal_sekolah" value="">
                        <input type="hidden" name="asal_kota" id="custom_asal_kota" value="">

                        <!-- Mata Lomba & Prestasi -->
                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-dark">Nomor & Kategori Usia Lomba <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_lomba" id="custom_nomor_lomba" class="form-control" placeholder="Contoh: LARI 100 M KU 16-18 PUTRA" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-dark">Catatan Prestasi</label>
                            <input type="text" name="prestasi" id="custom_prestasi" class="form-control font-monospace" placeholder="Contoh: 9.14 m atau 8.23 dtk">
                        </div>

                        <hr class="my-2">

                        <!-- 2. TEMPAT DILAKSANAKAN -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">
                                2. Tempat Dilaksanakan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="tempat" id="custom_tempat" class="form-control" value="<?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan') ?>" placeholder="Contoh: Stadion Hoegeng Kota Pekalongan" required>
                        </div>

                        <!-- Tanggal Dilaksanakan -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-dark mb-0">Tanggal Dilaksanakan / Terbit</label>
                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 fw-semibold" id="btnQuickSaveTanggalCustom" style="font-size: 11px;" title="Langsung simpan tanggal ini ke database sekarang">
                                    <i class="bi bi-floppy-fill me-1"></i> Simpan ke Database
                                </button>
                            </div>
                            <input type="text" name="tanggal" id="custom_tanggal" class="form-control" value="<?= esc($tanggalKejuaraan) ?>" placeholder="Contoh: <?= esc($tanggalKejuaraan) ?>">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="simpan_ke_database" value="1" id="checkSimpanTglDb" checked>
                                <label class="form-check-label text-muted" for="checkSimpanTglDb" style="font-size: 11px;">
                                    Simpan tanggal ini secara permanen ke database sebagai tanggal resmi
                                </label>
                            </div>
                            <div id="alertSaveTglCustom" class="mt-1" style="display: none;"></div>
                        </div>

                        <!-- Nama Kejuaraan -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Nama Kejuaraan</label>
                            <input type="text" name="nama_kejuaraan" id="custom_nama_kejuaraan" class="form-control" value="<?= esc($activeKejuaraan['nama'] ?? 'KEJURWIL ATLETIK KOTA PEKALONGAN 2026') ?>">
                        </div>

                        <!-- Nomor Piagam -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Nomor Surat / Piagam</label>
                            <input type="text" name="nomor_piagam" id="custom_nomor_piagam" class="form-control font-monospace" value="<?= esc($nomorPiagamDefault) ?>">
                        </div>

                        <!-- Penandatangan Override Toggle -->
                        <div class="col-md-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="override_ttd" value="1" id="toggleOverrideTTD">
                                <label class="form-check-label fw-semibold text-dark small" for="toggleOverrideTTD">
                                    Sesuaikan Nama & Jabatan Penandatangan (Opsional)
                                </label>
                            </div>
                        </div>

                        <!-- Section Penandatangan (Hidden by Default) -->
                        <div id="sectionPenandatangan" class="col-md-12 p-3 bg-light border rounded" style="display: none;">
                            <h6 class="fw-bold text-dark small mb-3">
                                <i class="bi bi-pen me-1 text-primary"></i> Data Penandatangan Piagam
                            </h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Penandatangan 1 (Kiri/Pusat)</label>
                                    <input type="text" name="ttd_1_jabatan" class="form-control form-control-sm mb-1" placeholder="Jabatan" value="Koordinator Wilayah Eks-Karesidenan Pekalongan">
                                    <input type="text" name="ttd_1_nama" class="form-control form-control-sm" placeholder="Nama Lengkap" value="Jumanto, S.Pd., M.Si.">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Penandatangan 2 (Tengah)</label>
                                    <input type="text" name="ttd_2_jabatan" class="form-control form-control-sm mb-1" placeholder="Jabatan" value="Koordinator Wilayah">
                                    <input type="text" name="ttd_2_nama" class="form-control form-control-sm" placeholder="Nama Lengkap" value="Zaini, S.Pd.">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Penandatangan 3 (Kanan)</label>
                                    <input type="text" name="ttd_3_jabatan" class="form-control form-control-sm mb-1" placeholder="Jabatan" value="Ketua Penyelenggara">
                                    <input type="text" name="ttd_3_nama" class="form-control form-control-sm" placeholder="Nama Lengkap" value="Waluyo, S.Pd">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-3 border-top">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-printer-fill fs-6"></i>
                        <span>Buka & Cetak Piagam Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle override TTD
    const toggleTTD = document.getElementById('toggleOverrideTTD');
    const secTTD = document.getElementById('sectionPenandatangan');
    if (toggleTTD && secTTD) {
        toggleTTD.addEventListener('change', function() {
            secTTD.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Fungsi filter template modal sesuai kategori (Umum vs Usia Dini 567)
    window.filterModalTemplates = function(mode, isLocked = false) {
        const tplSelect = document.getElementById('custom_template_id');
        const radioUmum = document.getElementById('radioKatUmum');
        const radio567  = document.getElementById('radioKat567');
        const noteEl    = document.getElementById('templateNote');

        if (mode === 'usia567') {
            if (radio567) radio567.checked = true;
            if (radioUmum) radioUmum.disabled = isLocked;
            if (radio567) radio567.disabled = false;

            Array.from(tplSelect.options).forEach(opt => {
                const is567 = (opt.getAttribute('data-is-567') === '1');
                if (is567) {
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });
            tplSelect.value = '22';
            if (noteEl) noteEl.innerHTML = '<span class="text-warning-emphasis fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Terkunci untuk Kategori Usia Dini (Template Trophy 5, 6, 7 Th)</span>';
        } else {
            if (radioUmum) radioUmum.checked = true;
            if (radio567) radio567.disabled = isLocked;
            if (radioUmum) radioUmum.disabled = false;

            let firstValidVal = null;
            Array.from(tplSelect.options).forEach(opt => {
                const is567 = (opt.getAttribute('data-is-567') === '1');
                if (is567) {
                    opt.hidden = true;
                    opt.disabled = true;
                } else {
                    opt.hidden = false;
                    opt.disabled = false;
                    if (!firstValidVal) firstValidVal = opt.value;
                }
            });
            if (tplSelect.value === '22' || !tplSelect.value) {
                tplSelect.value = firstValidVal || '13';
            }
            if (noteEl) noteEl.innerHTML = '<span class="text-primary fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Terkunci untuk Kategori Kejurwil Umum (Template Eks-Karesidenan / Canva)</span>';
        }
    };

    // Tombol "Sesuaikan Piagam Mandiri" di header gadget
    const btnOpenMandiri = document.querySelector('[data-bs-target="#modalCustomPiagam"]');
    if (btnOpenMandiri) {
        btnOpenMandiri.addEventListener('click', function() {
            filterModalTemplates('umum', false);
        });
    }

    // Handle tombol "Sesuaikan & Cetak" dari tabel
    const btns = document.querySelectorAll('.btn-customize');
    const modalEl = document.getElementById('modalCustomPiagam');
    const modal = new bootstrap.Modal(modalEl);

    btns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('custom_nama_pemenang').value = this.dataset.nama || '';
            document.getElementById('custom_asal_sekolah').value  = this.dataset.sekolah || '';
            document.getElementById('custom_asal_kota').value     = this.dataset.kota || 'Kota Pekalongan';
            document.getElementById('custom_nomor_lomba').value   = this.dataset.lomba || '';
            document.getElementById('custom_peringkat').value     = this.dataset.peringkat || '1';
            document.getElementById('custom_prestasi').value      = this.dataset.prestasi || '';
            document.getElementById('custom_nama_kejuaraan').value = this.dataset.kejuaraan || '';
            document.getElementById('custom_tempat').value        = this.dataset.tempat || '';
            document.getElementById('custom_tanggal').value       = this.dataset.tanggal || '<?= esc($tanggalKejuaraan) ?>';

            // Kunci ketat kategori: Usia Dini HANYA Template 22, Umum TIDAK BISA Template 22
            const isUsia567 = (this.dataset.isUsia567 === '1');
            filterModalTemplates(isUsia567 ? 'usia567' : 'umum', true);

            modal.show();
        });
    });

    // Helper konversi format YYYY-MM-DD ke Indonesia
    function formatDateIndo(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        const y = parts[0], m = parseInt(parts[1], 10), d = parseInt(parts[2], 10);
        const bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return `${d} ${bulan[m] || ''} ${y}`.trim();
    }

    // Modal Quick Edit Tanggal Kejuaraan
    const quickPicker = document.getElementById('quickPickerTgl');
    const quickText   = document.getElementById('quickInputTglText');
    if (quickPicker && quickText) {
        quickPicker.addEventListener('change', function() {
            if (this.value) {
                quickText.value = formatDateIndo(this.value);
            }
        });
    }

    // Pilihan cepat tombol tanggal di modal
    document.querySelectorAll('.quick-tgl-shortcut').forEach(btn => {
        btn.addEventListener('click', function() {
            const val = this.dataset.val;
            const txt = this.dataset.text;
            if (quickPicker) quickPicker.value = val;
            if (quickText) quickText.value = txt;
        });
    });

    // AJAX Form Submit Quick Edit Tanggal
    const formQuickTgl = document.getElementById('formQuickEditTanggal');
    if (formQuickTgl) {
        formQuickTgl.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSubmit = document.getElementById('btnSubmitQuickEditTanggal');
            const alertBox  = document.getElementById('alertQuickEditTanggal');
            const formData  = new FormData(this);

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
            alertBox.style.display = 'none';

            fetch('<?= base_url('piagam/update-tanggal') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-floppy-fill"></i> <span>Simpan Tanggal ke Database</span>';

                if (res.status === 'success') {
                    alertBox.className = 'alert alert-success py-2 px-3 small';
                    alertBox.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + res.message;
                    alertBox.style.display = 'block';

                    // Update UI teks langsung tanpa perlu reload
                    const newTglFormatted = res.tanggal_formatted;
                    const headerTgl = document.getElementById('headerTglDisplay');
                    if (headerTgl) headerTgl.textContent = 'Tanggal: ' + newTglFormatted;

                    const customTglInput = document.getElementById('custom_tanggal');
                    if (customTglInput) customTglInput.value = newTglFormatted;

                    document.querySelectorAll('.btn-customize').forEach(b => {
                        b.dataset.tanggal = newTglFormatted;
                        if (res.tempat) b.dataset.tempat = res.tempat;
                    });

                    // Tutup modal otomatis setelah 1 detik
                    setTimeout(() => {
                        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalEditTanggalKejuaraan'));
                        if (modalInstance) modalInstance.hide();
                    }, 1000);
                } else {
                    alertBox.className = 'alert alert-danger py-2 px-3 small';
                    alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (res.message || 'Gagal menyimpan.');
                    alertBox.style.display = 'block';
                }
            })
            .catch(err => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-floppy-fill"></i> <span>Simpan Tanggal ke Database</span>';
                alertBox.className = 'alert alert-danger py-2 px-3 small';
                alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi kesalahan jaringan saat menyimpan.';
                alertBox.style.display = 'block';
            });
        });
    }

    // Tombol "Simpan ke Database" langsung dari dalam Modal Custom Piagam
    const btnQuickSaveCustom = document.getElementById('btnQuickSaveTanggalCustom');
    if (btnQuickSaveCustom) {
        btnQuickSaveCustom.addEventListener('click', function() {
            const tglVal = document.getElementById('custom_tanggal')?.value.trim();
            const tempatVal = document.getElementById('custom_tempat')?.value.trim();
            const alertBox = document.getElementById('alertSaveTglCustom');

            if (!tglVal) {
                alert('Silakan isi tanggal terlebih dahulu.');
                return;
            }

            btnQuickSaveCustom.disabled = true;
            btnQuickSaveCustom.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
            if (alertBox) alertBox.style.display = 'none';

            const fd = new FormData();
            fd.append('tanggal', tglVal);
            if (tempatVal) fd.append('tempat', tempatVal);
            fd.append('kejuaraan_id', '<?= esc($activeKejuaraan['id'] ?? 1) ?>');

            fetch('<?= base_url('piagam/update-tanggal') ?>', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                btnQuickSaveCustom.disabled = false;
                btnQuickSaveCustom.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Tersimpan!';
                btnQuickSaveCustom.className = 'btn btn-sm btn-success py-0 px-2 fw-semibold';

                if (res.status === 'success') {
                    if (alertBox) {
                        alertBox.className = 'alert alert-success py-1 px-2 small mb-0 mt-1';
                        alertBox.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Tanggal tersimpan permanen ke database: <strong>' + res.tanggal_formatted + '</strong>';
                        alertBox.style.display = 'block';
                    }

                    const headerTgl = document.getElementById('headerTglDisplay');
                    if (headerTgl) headerTgl.textContent = 'Tanggal: ' + res.tanggal_formatted;

                    document.querySelectorAll('.btn-customize').forEach(b => {
                        b.dataset.tanggal = res.tanggal_formatted;
                        if (res.tempat) b.dataset.tempat = res.tempat;
                    });

                    setTimeout(() => {
                        btnQuickSaveCustom.innerHTML = '<i class="bi bi-floppy-fill me-1"></i> Simpan ke Database';
                        btnQuickSaveCustom.className = 'btn btn-sm btn-outline-success py-0 px-2 fw-semibold';
                    }, 3000);
                } else {
                    if (alertBox) {
                        alertBox.className = 'alert alert-danger py-1 px-2 small mb-0 mt-1';
                        alertBox.textContent = res.message || 'Gagal menyimpan.';
                        alertBox.style.display = 'block';
                    }
                }
            })
            .catch(err => {
                btnQuickSaveCustom.disabled = false;
                btnQuickSaveCustom.innerHTML = '<i class="bi bi-floppy-fill me-1"></i> Simpan ke Database';
                if (alertBox) {
                    alertBox.className = 'alert alert-danger py-1 px-2 small mb-0 mt-1';
                    alertBox.textContent = 'Terjadi kesalahan jaringan saat menyimpan.';
                    alertBox.style.display = 'block';
                }
            });
        });
    }
});
</script>


<!-- ========================================== -->
<!-- MODAL PREVIEW AKTA KELAHIRAN (HIGH SECURITY) -->
<!-- ========================================== -->
<div class="modal fade" id="modalPreviewAkta" tabindex="-1" aria-labelledby="modalPreviewAktaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border: 1px solid #0052CC; border-radius: 8px; overflow: hidden;">
            <div class="modal-header text-white py-3 px-4" style="background: linear-gradient(135deg, #0747A6 0%, #0052CC 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-lock-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalPreviewAktaLabel" style="font-size: 17px; letter-spacing: -0.2px;">
                            Verifikasi Dokumen Akta Kelahiran Atlet
                        </h5>
                        <div class="d-flex align-items-center gap-2 mt-1" style="font-size: 11.5px;">
                            <span class="badge bg-warning text-dark"><i class="bi bi-shield-fill-check me-1"></i>UU PDP No. 27/2022</span>
                            <span class="text-white-50">Kerahasiaan data pribadi dilindungi &bull; Akses tercatat panitia</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Banner Security & Instruksi -->
                <div class="py-2 px-3 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 12px;">
                    <div class="text-muted d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill text-primary fs-6"></i>
                        <span>Pastikan ejaan nama di piagam <strong>sesuai 100%</strong> dengan nama di kutipan Akta Kelahiran resmi.</span>
                    </div>
                    <span id="aktaAuditStamp" class="badge bg-dark font-monospace" style="font-size: 11px;">SECURE-AUDIT-ACTIVE</span>
                </div>

                <div class="row g-0">
                    <!-- Kolom Kiri: Metadata Atlet & Koreksi Nama -->
                    <div class="col-lg-4 p-3 border-end bg-light" style="font-size: 13px;">
                        <!-- Card Data Atlet -->
                        <div class="card mb-3 shadow-sm border-0" style="border: 1px solid #DFE1E6 !important;">
                            <div class="card-header py-2 px-3 bg-white fw-bold d-flex justify-content-between align-items-center border-bottom">
                                <span><i class="bi bi-person-badge-fill text-primary me-1"></i> Data Atlet di Sistem</span>
                                <span id="badgeDocStatus" class="badge bg-secondary">Memuat...</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Nama di Database:</label>
                                    <strong id="dispNamaAtlet" class="fs-6 text-dark text-break">-</strong>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="text-muted small d-block mb-0" style="font-size: 11px;">No. Dada:</label>
                                        <span id="dispNoDada" class="badge bg-dark font-monospace">-</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block mb-0" style="font-size: 11px;">Jenis Kelamin:</label>
                                        <span id="dispGender" class="text-dark fw-semibold">-</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Tanggal Lahir:</label>
                                    <span id="dispTglLahir" class="text-dark fw-bold">-</span>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Kelompok Umur:</label>
                                    <span id="dispKU" class="badge bg-primary text-white">-</span>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Asal Sekolah / Klub:</label>
                                    <span id="dispSekolah" class="text-dark fw-semibold text-break">-</span>
                                </div>
                                <div>
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Asal Daerah (Kab/Kota):</label>
                                    <span id="dispDaerah" class="badge bg-info text-dark border">-</span>
                                </div>
                                <div class="mt-3 pt-2 border-top">
                                    <label class="text-muted small d-block mb-1" style="font-size: 11px; font-weight: 600;">
                                        <i class="bi bi-google text-danger me-1"></i> Mentahan Link Google Drive:
                                    </label>
                                    <a id="btnModalRawDrive" href="#" target="_blank" rel="noopener noreferrer" 
                                       class="btn btn-sm btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" 
                                       style="font-size: 11.5px; display: none;">
                                        <i class="fa-brands fa-google-drive text-warning"></i>
                                        <span>Buka Mentahan Google Drive</span>
                                        <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                    <div id="boxNoRawDrive" class="badge bg-light text-secondary border w-100 py-2 text-start" style="font-size: 11px; display: none;">
                                        <i class="bi bi-info-circle me-1"></i> Tidak ada link Google Drive di formulir
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Koreksi Nama Langsung Sesuai Akte -->
                        <div class="card shadow-sm border-0" style="border: 1px solid #FFE082 !important; background: #FFFDE7;">
                            <div class="card-header py-2 px-3 fw-bold text-dark border-bottom" style="background: #FFF8E1;">
                                <i class="bi bi-pencil-square text-warning me-1"></i> Koreksi Nama Sesuai Akte
                            </div>
                            <div class="card-body p-3">
                                <p class="text-muted mb-2" style="line-height: 1.35; font-size: 11.5px;">
                                    Jika terdapat perbedaan ejaan (misal <code>RIZQI</code> &rarr; <code>RISQI</code>), simpan nama yang benar di bawah ini agar piagam otomatis tersinkronisasi.
                                </p>
                                <form id="formKoreksiNama">
                                    <input type="hidden" id="editAtletId" name="atlet_id">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1" style="font-size: 11.5px;">Nama Baru (Huruf Kapital):</label>
                                        <input type="text" id="editNamaBaru" name="nama" class="form-control form-control-sm text-uppercase fw-bold" placeholder="NAMA LENGKAP ATLET" required>
                                    </div>
                                    <button type="submit" id="btnSimpanNama" class="btn btn-warning btn-sm fw-bold w-100 shadow-sm d-flex align-items-center justify-content-center gap-1 text-dark">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Perbarui Nama di Database</span>
                                    </button>
                                </form>
                                <div id="koreksiAlert" class="mt-2" style="display: none;"></div>
                            </div>
                        </div>
                        <!-- Card Upload / Ganti Berkas Akta Baru -->
                        <div class="card shadow-sm border-0 mt-3" style="border: 1px solid #BBDEFB !important; background: #F0F7FF;">
                            <div class="card-header py-2 px-3 fw-bold text-dark border-bottom d-flex justify-content-between align-items-center" style="background: #E3F2FD;">
                                <span><i class="bi bi-cloud-arrow-up-fill text-primary me-1"></i> Unggah / Ganti Berkas</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">Event Baru / Update</span>
                            </div>
                            <div class="card-body p-3">
                                <p class="text-muted mb-2" style="line-height: 1.35; font-size: 11px;">
                                    Upload scan foto akte (JPG/PNG/PDF maks 15MB) atau masukkan link Google Drive untuk memperbarui berkas atlet ini.
                                </p>
                                <form id="formUploadAkta" enctype="multipart/form-data">
                                    <input type="hidden" id="uploadAtletId" name="atlet_id">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1" style="font-size: 11px;">Pilih File Fisik (JPG / PNG / PDF):</label>
                                        <input type="file" id="fileInputAkta" name="file_akta" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1" style="font-size: 11px;">Atau Tautan Google Drive:</label>
                                        <input type="url" id="urlInputAkta" name="akta_url" class="form-control form-control-sm" placeholder="https://drive.google.com/file/d/...">
                                    </div>
                                    <button type="submit" id="btnSubmitUpload" class="btn btn-primary btn-sm fw-bold w-100 shadow-sm d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-upload"></i>
                                        <span>Simpan & Tampilkan Dokumen</span>
                                    </button>
                                </form>
                                <div id="uploadAlert" class="mt-2" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Secure Document Viewer -->
                    <div class="col-lg-8 p-3" style="background: #1e1e1e;">
                        <!-- Toolbar Viewer -->
                        <div class="d-flex justify-content-between align-items-center mb-2 px-3 py-2 bg-dark rounded text-white" style="font-size: 12px; border: 1px solid #333;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary font-monospace" id="badgeViewerType">VIEWER</span>
                                <span class="text-white-50" id="viewerInfoText">Memuat dokumen...</span>
                            </div>
                            <div class="d-flex align-items-center gap-1" id="viewerControls" style="display: none;">
                                <a id="btnViewerDriveTop" href="#" target="_blank" rel="noopener noreferrer" 
                                   class="btn btn-sm btn-warning text-dark py-0 px-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm me-1" 
                                   style="display: none; font-size: 11px;" title="Buka Link Mentahan Google Drive Form Peserta">
                                    <i class="fa-brands fa-google-drive text-warning"></i>
                                    <span>Mentahan GDrive</span>
                                    <i class="bi bi-box-arrow-up-right" style="font-size: 9px;"></i>
                                </a>
                                <span class="badge bg-secondary font-monospace me-1" id="zoomLevelBadge" title="Tingkat Pembesaran">100%</span>
                                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnZoomIn" title="Perbesar (+ / Scroll Up)"><i class="bi bi-zoom-in"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnZoomOut" title="Perkecil (- / Scroll Down)"><i class="bi bi-zoom-out"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnRotate" title="Putar 90 Derajat"><i class="bi bi-arrow-clockwise"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnResetZoom" title="Reset Posisi & Zoom"><i class="bi bi-arrows-fullscreen"></i></button>
                            </div>
                        </div>

                        <!-- Viewer Screen with Security Watermark -->
                        <div id="aktaViewerContainer" class="position-relative d-flex align-items-center justify-content-center bg-black rounded" style="min-height: 520px; max-height: 640px; overflow: hidden; user-select: none; cursor: grab;" oncontextmenu="return false;">
                            <!-- Watermark Overlay (Anti-Capture / Official Stamp) -->
                            <div id="aktaWatermarkOverlay" class="position-absolute w-100 h-100 pointer-events-none d-flex flex-column align-items-center justify-content-around py-4" style="z-index: 10; opacity: 0.15; pointer-events: none; overflow: hidden; transform: rotate(-25deg); color: #fff; font-size: 13px; font-weight: bold; letter-spacing: 2px;">
                                <span>DOKUMEN RAHASIA &bull; VERIFIKASI KEJURWIL ATLETIK</span>
                                <span>PERLINDUNGAN DATA PRIBADI &bull; HANYA AKSES PANITIA RESMI</span>
                                <span>DOKUMEN RAHASIA &bull; VERIFIKASI KEJURWIL ATLETIK</span>
                            </div>

                            <!-- Spinner Loading -->
                            <div id="aktaLoadingSpinner" class="text-center text-white py-5">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div class="small">Mengambil berkas secara aman dari server terlindung...</div>
                            </div>

                            <!-- Image View -->
                            <img id="aktaViewerImg" src="" alt="Akta Kelahiran" class="img-fluid rounded transition" style="display: none; max-width: 95%; max-height: 600px; transition: transform 0.2s ease; cursor: grab;" draggable="false">

                            <!-- PDF View -->
                            <iframe id="aktaViewerIframe" src="" style="width: 100%; height: 580px; border: none; display: none;"></iframe>

                            <!-- Google Drive Fallback View -->
                            <div id="aktaDriveFallback" class="text-center text-white p-4" style="display: none; max-width: 480px;">
                                <div style="width: 60px; height: 60px; border-radius: 12px; background: rgba(0, 82, 204, 0.25); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; border: 1px solid rgba(0, 82, 204, 0.5);">
                                    <i class="bi bi-google text-warning fs-2"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Dokumen Google Drive Formulir</h6>
                                <p class="text-white-50 small mb-3">
                                    Berkas diunggah peserta melalui formulir pendaftaran online resmi. Klik tombol di bawah untuk membuka dokumen di Google Drive:
                                </p>
                                <a id="btnOpenDrive" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow fw-semibold">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    <span>Buka Dokumen di Google Drive</span>
                                </a>
                                <div class="mt-3 text-white-50" style="font-size: 11.5px;">
                                    <i class="bi bi-lock-fill text-warning me-1"></i> Diperlukan login akun Google panitia jika berkas memiliki izin akses terbatas.
                                </div>
                            </div>

                            <!-- Not Available View -->
                            <div id="aktaNotFound" class="text-center text-white p-4" style="display: none; max-width: 420px;">
                                <i class="bi bi-file-earmark-x text-secondary fs-1 mb-2 d-block"></i>
                                <h6 class="fw-bold mb-1 text-white-50">Dokumen Belum Tersedia</h6>
                                <p class="text-secondary small mb-0">
                                    Dokumen Akta Kelahiran atlet ini belum diunggah atau tidak ditemukan di rekapan formulir pendaftaran.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer py-2 px-3 bg-light justify-content-between">
                <span class="text-muted small" style="font-size: 11.5px;">
                    <i class="bi bi-shield-check text-success me-1"></i> Dokumen dilindungi sistem isolasi storage server (tidak dapat diakses publik secara langsung).
                </span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Viewer State Variables
    // Viewer State Variables
    let currentZoom = 1;
    let currentRotation = 0;
    let panX = 0;
    let panY = 0;
    let isDragging = false;
    let startX = 0;
    let startY = 0;

    const imgEl = document.getElementById('aktaViewerImg');
    const iframeEl = document.getElementById('aktaViewerIframe');
    const driveFallbackEl = document.getElementById('aktaDriveFallback');
    const notFoundEl = document.getElementById('aktaNotFound');
    const spinnerEl = document.getElementById('aktaLoadingSpinner');
    const controlsEl = document.getElementById('viewerControls');
    const badgeTypeEl = document.getElementById('badgeViewerType');
    const infoTextEl = document.getElementById('viewerInfoText');
    const containerEl = document.getElementById('aktaViewerContainer');
    const zoomLevelBadge = document.getElementById('zoomLevelBadge');

    function applyTransform(withTransition = true) {
        if (imgEl) {
            imgEl.style.transition = withTransition ? 'transform 0.15s ease-out' : 'none';
            imgEl.style.transform = `translate(${panX}px, ${panY}px) scale(${currentZoom}) rotate(${currentRotation}deg)`;
            if (zoomLevelBadge) {
                zoomLevelBadge.textContent = Math.round(currentZoom * 100) + '%';
            }
        }
    }

    // Zoom Controls
    document.getElementById('btnZoomIn')?.addEventListener('click', function() {
        if (currentZoom < 5) {
            currentZoom = Math.min(5, Math.round((currentZoom + 0.25) * 100) / 100);
            applyTransform(true);
        }
    });

    document.getElementById('btnZoomOut')?.addEventListener('click', function() {
        if (currentZoom > 0.4) {
            currentZoom = Math.max(0.4, Math.round((currentZoom - 0.25) * 100) / 100);
            applyTransform(true);
        }
    });

    document.getElementById('btnRotate')?.addEventListener('click', function() {
        currentRotation = (currentRotation + 90) % 360;
        applyTransform(true);
    });

    document.getElementById('btnResetZoom')?.addEventListener('click', function() {
        currentZoom = 1;
        currentRotation = 0;
        panX = 0;
        panY = 0;
        applyTransform(true);
    });

    // Drag / Pan Mouse Events (Klik & Geser)
    if (containerEl) {
        containerEl.addEventListener('mousedown', function(e) {
            if (e.button !== 0 || !imgEl || imgEl.style.display === 'none') return;
            isDragging = true;
            startX = e.clientX - panX;
            startY = e.clientY - panY;
            containerEl.style.cursor = 'grabbing';
            imgEl.style.cursor = 'grabbing';
            e.preventDefault();
        });

        window.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            panX = e.clientX - startX;
            panY = e.clientY - startY;
            applyTransform(false); // Smooth direct tracking tanpa lag
        });

        window.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                if (containerEl) containerEl.style.cursor = 'grab';
                if (imgEl) imgEl.style.cursor = 'grab';
            }
        });

        // Mouse Wheel Zoom (Scroll untuk Zoom In/Out)
        containerEl.addEventListener('wheel', function(e) {
            if (!imgEl || imgEl.style.display === 'none') return;
            e.preventDefault();
            const step = 0.15;
            if (e.deltaY < 0) {
                currentZoom = Math.min(5, Math.round((currentZoom + step) * 100) / 100);
            } else {
                currentZoom = Math.max(0.4, Math.round((currentZoom - step) * 100) / 100);
            }
            applyTransform(true);
        }, { passive: false });

        // Touch Drag Events untuk Touchscreen / HP / Tablet
        let touchX = 0, touchY = 0;
        containerEl.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1 && imgEl && imgEl.style.display !== 'none') {
                isDragging = true;
                touchX = e.touches[0].clientX - panX;
                touchY = e.touches[0].clientY - panY;
            }
        }, { passive: true });

        window.addEventListener('touchmove', function(e) {
            if (!isDragging || e.touches.length !== 1) return;
            panX = e.touches[0].clientX - touchX;
            panY = e.touches[0].clientY - touchY;
            applyTransform(false);
        }, { passive: true });

        window.addEventListener('touchend', function() {
            isDragging = false;
        });
    }

    // Preview Akta Handler
    const modalPreviewAkta = new bootstrap.Modal(document.getElementById('modalPreviewAkta'));
    let activeRowNamaEl = null;

    document.querySelectorAll('.btn-preview-akta').forEach(btn => {
        btn.addEventListener('click', function() {
            const atletId = this.dataset.atletId;
            const nama = this.dataset.nama;
            activeRowNamaEl = this.closest('tr')?.querySelector('td strong.text-dark');

            // Reset modal UI
            document.getElementById('editAtletId').value = atletId;
            document.getElementById('editNamaBaru').value = nama;
            document.getElementById('dispNamaAtlet').textContent = nama;
            document.getElementById('dispNoDada').textContent = '-';
            document.getElementById('dispGender').textContent = '-';
            document.getElementById('dispTglLahir').textContent = '-';
            document.getElementById('dispKU').textContent = '-';
            document.getElementById('dispSekolah').textContent = '-';
            document.getElementById('dispDaerah').textContent = '-';
            document.getElementById('koreksiAlert').style.display = 'none';
            const btnRawDrive = document.getElementById('btnModalRawDrive');
            const boxNoDrive = document.getElementById('boxNoRawDrive');
            const btnViewerTop = document.getElementById('btnViewerDriveTop');
            if (btnRawDrive) btnRawDrive.style.display = 'none';
            if (boxNoDrive) boxNoDrive.style.display = 'none';
            if (btnViewerTop) btnViewerTop.style.display = 'none';

            // Reset viewer
            currentZoom = 1;
            currentRotation = 0;
            panX = 0;
            panY = 0;
            applyTransform(true);
            controlsEl.style.display = 'none';
            imgEl.style.display = 'none';
            imgEl.src = '';
            iframeEl.style.display = 'none';
            iframeEl.src = '';
            driveFallbackEl.style.display = 'none';
            notFoundEl.style.display = 'none';
            spinnerEl.style.display = 'block';
            badgeTypeEl.textContent = 'MEMUAT';
            badgeTypeEl.className = 'badge bg-secondary font-monospace';
            infoTextEl.textContent = 'Mengambil data atlet...';

            modalPreviewAkta.show();

            // Fetch Data dari API Secure
            fetch(`<?= base_url('piagam/preview-akta') ?>/${atletId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                spinnerEl.style.display = 'none';
                if (res.status !== 'success') {
                    notFoundEl.style.display = 'block';
                    infoTextEl.textContent = res.message || 'Gagal memuat dokumen.';
                    return;
                }

                const a = res.atlet;
                document.getElementById('dispNamaAtlet').textContent = a.nama;
                document.getElementById('editNamaBaru').value = a.nama;
                document.getElementById('dispNoDada').textContent = a.no_dada;
                document.getElementById('dispGender').textContent = a.gender;
                document.getElementById('dispTglLahir').textContent = a.tanggal_lahir;
                document.getElementById('dispKU').textContent = a.nama_ku;
                document.getElementById('dispSekolah').textContent = a.nama_sekolah;
                document.getElementById('dispDaerah').textContent = a.asal_daerah;

                // Stamp audit
                if (res.watermark) {
                    document.getElementById('aktaAuditStamp').textContent = res.watermark.split('•')[2]?.trim() || 'SECURE-AUDIT';
                }

                // Cek ketersediaan file
                // Tampilkan tombol mentahan Google Drive jika tersedia
                if (a.akta_url) {
                    if (btnRawDrive) {
                        btnRawDrive.href = a.akta_url;
                        btnRawDrive.style.display = 'inline-flex';
                    }
                    if (boxNoDrive) boxNoDrive.style.display = 'none';
                    if (btnViewerTop) {
                        btnViewerTop.href = a.akta_url;
                        btnViewerTop.style.display = 'inline-flex';
                    }
                } else {
                    if (btnRawDrive) btnRawDrive.style.display = 'none';
                    if (boxNoDrive) boxNoDrive.style.display = 'block';
                    if (btnViewerTop) btnViewerTop.style.display = 'none';
                }

                if (a.has_local && a.view_url) {
                    document.getElementById('badgeDocStatus').className = 'badge bg-success';
                    document.getElementById('badgeDocStatus').textContent = 'Server Terlindung';

                    if (a.file_type === 'pdf') {
                        iframeEl.src = a.view_url;
                        iframeEl.style.display = 'block';
                        badgeTypeEl.textContent = 'DOKUMEN PDF';
                        badgeTypeEl.className = 'badge bg-danger font-monospace';
                        infoTextEl.textContent = 'Kutipan Akta Format PDF';
                    } else {
                        imgEl.src = a.view_url;
                        imgEl.style.display = 'block';
                        controlsEl.style.display = 'flex';
                        badgeTypeEl.textContent = 'FOTO / SCAN ASLI';
                        badgeTypeEl.className = 'badge bg-success font-monospace';
                        infoTextEl.textContent = 'Format Gambar: Geser (drag) untuk melihat bagian lain & Scroll mouse untuk zoom';
                    }
                } else if (a.akta_url) {
                    document.getElementById('badgeDocStatus').className = 'badge bg-warning text-dark';
                    document.getElementById('badgeDocStatus').textContent = 'Google Drive';

                    driveFallbackEl.style.display = 'block';
                    document.getElementById('btnOpenDrive').href = a.akta_url;
                    badgeTypeEl.textContent = 'GOOGLE DRIVE';
                    badgeTypeEl.className = 'badge bg-primary font-monospace';
                    infoTextEl.textContent = 'Tersedia tautan Google Drive';
                } else {
                    document.getElementById('badgeDocStatus').className = 'badge bg-secondary';
                    document.getElementById('badgeDocStatus').textContent = 'Tidak Ada';
                    notFoundEl.style.display = 'block';
                    badgeTypeEl.textContent = 'KOSONG';
                    infoTextEl.textContent = 'Berkas belum diunggah.';
                }
            })
            .catch(err => {
                spinnerEl.style.display = 'none';
                notFoundEl.style.display = 'block';
                infoTextEl.textContent = 'Terjadi kesalahan jaringan.';
            });
        });
    });

    // Form Koreksi Nama AJAX
    const formKoreksi = document.getElementById('formKoreksiNama');
    formKoreksi?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanNama');
        const alertBox = document.getElementById('koreksiAlert');
        const atletId = document.getElementById('editAtletId').value;
        const namaBaru = document.getElementById('editNamaBaru').value.trim();

        if (!namaBaru) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        alertBox.style.display = 'none';

        const formData = new FormData();
        formData.append('atlet_id', atletId);
        formData.append('nama', namaBaru);

        fetch('<?= base_url('piagam/update-nama-atlet') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Perbarui Nama di Database</span>';
            if (res.status === 'success') {
                alertBox.className = 'alert alert-success py-2 px-3 mb-0 small';
                alertBox.textContent = res.message;
                alertBox.style.display = 'block';

                document.getElementById('dispNamaAtlet').textContent = res.nama;

                // Update text di baris tabel secara real-time
                if (activeRowNamaEl) {
                    activeRowNamaEl.textContent = res.nama;
                }

                // Update dataset pada tombol sesuaikan & preview
                document.querySelectorAll(`.btn-preview-akta[data-atlet-id="${atletId}"]`).forEach(b => {
                    b.dataset.nama = res.nama;
                });
                document.querySelectorAll(`.btn-customize`).forEach(b => {
                    if (b.dataset.nama && b.dataset.nama.toUpperCase() === document.getElementById('dispNamaAtlet').textContent.toUpperCase()) {
                        b.dataset.nama = res.nama;
                    }
                });
            } else {
                alertBox.className = 'alert alert-danger py-2 px-3 mb-0 small';
                alertBox.textContent = res.message || 'Gagal menyimpan.';
                alertBox.style.display = 'block';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Perbarui Nama di Database</span>';
            alertBox.className = 'alert alert-danger py-2 px-3 mb-0 small';
            alertBox.textContent = 'Terjadi kesalahan jaringan saat menyimpan.';
            alertBox.style.display = 'block';
        });
    });

    // Form Upload Berkas Akta Baru
    const formUpload = document.getElementById('formUploadAkta');
    formUpload?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitUpload');
        const alertBox = document.getElementById('uploadAlert');
        const atletId = document.getElementById('editAtletId').value;
        document.getElementById('uploadAtletId').value = atletId;

        const fileInput = document.getElementById('fileInputAkta');
        const urlInput = document.getElementById('urlInputAkta');

        if (!fileInput.files.length && !urlInput.value.trim()) {
            alertBox.className = 'alert alert-warning py-2 px-3 mb-0 small';
            alertBox.textContent = 'Pilih berkas dokumen atau masukkan tautan Google Drive.';
            alertBox.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah...';
        alertBox.style.display = 'none';

        const formData = new FormData(this);
        formData.set('atlet_id', atletId);

        fetch('<?= base_url('piagam/upload-akta') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> <span>Simpan & Tampilkan Dokumen</span>';
            if (res.status === 'success') {
                alertBox.className = 'alert alert-success py-2 px-3 mb-0 small';
                alertBox.textContent = res.message;
                alertBox.style.display = 'block';

                fileInput.value = '';
                urlInput.value = '';

                // Update tombol baris di tabel agar aktif
                document.querySelectorAll(`.btn-preview-akta[data-atlet-id="${atletId}"]`).forEach(b => {
                    b.className = 'btn btn-sm btn-info text-white d-inline-flex align-items-center gap-1 btn-preview-akta';
                });

                // Reload preview dokumen di modal secara otomatis
                const btnActive = document.querySelector(`.btn-preview-akta[data-atlet-id="${atletId}"]`);
                if (btnActive) {
                    setTimeout(() => btnActive.click(), 600);
                }
            } else {
                alertBox.className = 'alert alert-danger py-2 px-3 mb-0 small';
                alertBox.textContent = res.message || 'Gagal mengunggah berkas.';
                alertBox.style.display = 'block';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> <span>Simpan & Tampilkan Dokumen</span>';
            alertBox.className = 'alert alert-danger py-2 px-3 mb-0 small';
            alertBox.textContent = 'Terjadi kesalahan jaringan saat mengunggah berkas.';
            alertBox.style.display = 'block';
        });
    });

});
</script>

<?= $this->endSection() ?>
