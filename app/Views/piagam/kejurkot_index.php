<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$tglKejurkotFormatted = '29 Agustus 2026';
if (!empty($kejuaraan['tanggal_mulai'])) {
    $tglTime = strtotime($kejuaraan['tanggal_mulai']);
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $tglKejurkotFormatted = date('j', $tglTime) . ' ' . ($bulanIndo[(int)date('n', $tglTime)] ?? '') . ' ' . date('Y', $tglTime);
}
?>

<!-- Header -->
<div class="card-custom mb-4 p-4" style="background: linear-gradient(135deg, rgba(0, 82, 204, 0.08) 0%, rgba(255, 171, 0, 0.12) 100%); border: 1px solid #DFE1E6; border-radius: 8px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 52px; height: 52px; border-radius: 8px; background: linear-gradient(135deg, #0052CC, #0747A6); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.6rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,82,204,0.3);">
                <i class="bi bi-award-fill text-warning"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span class="badge bg-warning text-dark fw-bold">
                        <i class="bi bi-star-fill me-1"></i> Kejurkot Pekalongan 2026
                    </span>
                    <span class="badge bg-primary">
                        <i class="bi bi-shield-check me-1"></i> Terpisah & Mandiri
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditTanggalKejurkot" title="Klik untuk edit dan simpan tanggal Kejurkot ke database">
                        <i class="bi bi-calendar-check-fill text-primary"></i>
                        <span id="badgeKejurkotTglDisplay">Tanggal: <?= esc($tglKejurkotFormatted) ?></span>
                        <i class="bi bi-pencil-fill text-primary ms-1" style="font-size: 9px;"></i>
                    </button>
                </div>
                <h4 class="fw-bold mb-1 text-dark">Piagam Kejuaraan Atletik Wali Kota Pekalongan</h4>
                <p class="text-muted small mb-0">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Stadion Hoegeng Kota Pekalongan &bull; 
                    <i class="bi bi-calendar-event me-1 text-primary"></i> <span id="textKejurkotTgl"><?= esc($tglKejurkotFormatted) ?></span> &bull;
                    <i class="bi bi-person-badge me-1 text-success"></i> TTD: Zaini, S.Pd. (Ketua)
                </p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalEditTanggalKejurkot">
                <i class="bi bi-calendar-date me-1"></i> Edit & Simpan Tanggal
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                <i class="bi bi-file-earmark-spreadsheet me-1 text-success"></i> Import Excel / Teks
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPemenang">
                <i class="bi bi-plus-circle me-1"></i> Tambah Pemenang
            </button>
            <?php if (!empty($pemenang)): ?>
            <a href="<?= base_url('piagam-kejurkot/cetak') ?>" target="_blank" class="btn btn-success fw-bold shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak Semua Piagam (<?= count($pemenang) ?>)
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Alert Notifikasi Flashdata -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div><?= session()->getFlashdata('success') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div><?= session()->getFlashdata('error') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Info Cards Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 4px solid #0052CC !important; background: #fff;">
            <div class="text-muted small fw-semibold text-uppercase">Total Pemenang</div>
            <div class="h3 fw-bold text-dark mt-1 mb-0"><?= count($pemenang) ?></div>
            <div class="small text-muted mt-1">Sertifikat siap cetak</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 4px solid #FFAB00 !important; background: #fff;">
            <div class="text-muted small fw-semibold text-uppercase">Template Background</div>
            <div class="fw-bold text-dark mt-1 mb-0 text-truncate" title="Trophy PASI Pekalongan 2026">Trophy PASI (Biru-Emas)</div>
            <div class="small text-muted mt-1"><span class="badge bg-success py-0 px-1 me-1" style="font-size: 10px;">F4 Folio</span> & A4 Landscape &bull; Full Bleed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 4px solid #36B37E !important; background: #fff;">
            <div class="text-muted small fw-semibold text-uppercase">Tanda Tangan</div>
            <div class="fw-bold text-dark mt-1 mb-0">Zaini, S.Pd.</div>
            <div class="small text-muted mt-1">Ketua Pengkot PASI Pekalongan</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 4px solid #6554C0 !important; background: #fff;">
            <div class="text-muted small fw-semibold text-uppercase">Format Nomor</div>
            <div class="fw-bold text-primary mt-1 mb-0 small font-monospace"><?= esc($nextNomorFormatted) ?></div>
            <div class="small text-muted mt-1">Nomor otomatis berurutan</div>
        </div>
    </div>
</div>

<!-- Tabel Daftar Pemenang -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-list-stars text-primary me-1"></i> Data Pemenang Piagam Kejurkot
            </h6>
            <span class="badge bg-light text-dark border"><?= count($pemenang) ?> data</span>
            <?php if (!empty($groups)): ?>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= count($groups) ?> Nomor Lomba Terpisah</span>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-1">
                <label for="selectNomorLomba" class="form-label small fw-semibold text-muted mb-0 d-none d-md-inline text-nowrap">
                    <i class="bi bi-funnel-fill text-primary me-1"></i> Pisahkan:
                </label>
                <select id="selectNomorLomba" class="form-select form-select-sm fw-semibold" style="min-width: 250px;">
                    <option value="all">Semua Nomor Lomba (1 s/d <?= count($groups ?? []) ?>) &bull; <?= count($pemenang) ?> Data</option>
                    <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $gNum => $gData): ?>
                        <option value="<?= $gNum ?>">Nomor <?= $gNum ?> (<?= $gData['count'] ?> Atlet) - <?= esc($gData['title']) ?></option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari nama / nomor lomba..." style="width: 210px;">
        </div>
    </div>

    <!-- Quick Badges / Pill Selector Antar Nomor Lomba -->
    <?php if (!empty($groups)): ?>
    <div class="px-3 py-2 bg-light border-bottom d-flex align-items-center gap-1 overflow-auto" style="white-space: nowrap; scrollbar-width: thin;">
        <span class="small fw-bold text-muted me-2"><i class="bi bi-tags-fill me-1 text-primary"></i> Nomor Lomba:</span>
        <button type="button" class="btn btn-sm btn-primary py-0 px-2 fw-semibold btn-filter-pill active" data-filter="all">
            Semua (<?= count($pemenang) ?>)
        </button>
        <?php foreach ($groups as $gNum => $gData): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-semibold btn-filter-pill bg-white" data-filter="<?= $gNum ?>" title="<?= esc($gData['title']) ?>">
                <?= $gNum ?>. <span class="badge rounded-pill bg-light text-dark border ms-1"><?= $gData['count'] ?></span>
            </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tablePemenang">
            <thead class="table-light text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">
                <tr>
                    <th class="text-center" style="width: 45px;">No</th>
                    <th>No. Sertifikat</th>
                    <th>Nama Pemenang</th>
                    <th>Asal Kontingen / Sekolah</th>
                    <th>Nomor Lomba</th>
                    <th class="text-center">Juara</th>
                    <th>Prestasi</th>
                    <th class="text-center">QR Verif</th>
                    <th class="text-end" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groups)): ?>
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        Belum ada data pemenang untuk Kejuaraan Wali Kota 2026.<br>
                        Klik tombol <strong>"Tambah Pemenang"</strong> atau <strong>"Import Excel / Teks"</strong> di atas untuk memulai.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($groups as $gNum => $gData): ?>
                <!-- Section Header Pembatas Nomor Lomba -->
                <tr class="table-group-header" data-group="<?= $gNum ?>">
                    <td colspan="9" class="p-0 border-0 bg-transparent">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 mt-2 mb-1 rounded-2 shadow-sm" style="background: linear-gradient(90deg, #EBF3FC 0%, #F5F7FB 100%); border-left: 5px solid #0052CC; border-top: 1px solid #D0E2FF; border-bottom: 1px solid #D0E2FF; border-right: 1px solid #E1E8F0;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-primary px-2.5 py-1.5 fs-6 fw-bold shadow-sm">
                                    <i class="bi bi-bookmark-fill me-1"></i> NOMOR <?= $gNum ?>
                                </span>
                                <span class="fw-bold text-dark fs-6"><?= esc($gData['title']) ?></span>
                                <span class="badge bg-white text-primary border border-primary-subtle fw-bold">
                                    <?= $gData['count'] ?> Pemenang
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?= base_url('piagam-kejurkot/cetak?nomor=' . $gNum) ?>" target="_blank" class="btn btn-sm btn-outline-success fw-bold py-1 px-2.5 bg-white shadow-sm" title="Cetak hanya piagam untuk Nomor <?= $gNum ?>">
                                    <i class="bi bi-printer-fill me-1"></i> Cetak Nomor <?= $gNum ?> (<?= $gData['count'] ?>)
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Item Pemenang di bawah Nomor Lomba ini -->
                <?php foreach ($gData['items'] as $itemIdx => $p): ?>
                <tr class="table-item-row" data-group="<?= $gNum ?>">
                    <td class="text-center text-muted fw-semibold">
                        <span class="badge bg-light text-dark border"><?= $itemIdx + 1 ?></span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border font-monospace">
                            <?= esc($p['nomor_piagam'] ?: '-') ?>
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?= esc($p['nama_pemenang']) ?></div>
                    </td>
                    <td>
                        <span class="text-secondary"><?= esc($p['asal_sekolah'] ?: $p['asal_kota'] ?: '-') ?></span>
                    </td>
                    <td>
                        <span class="fw-bold text-primary"><?= esc($p['nomor_lomba'] ?: '-') ?></span>
                    </td>
                    <td class="text-center">
                        <?php 
                        $pk = (int)($p['peringkat'] ?? 1);
                        if ($pk === 1): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill me-1"></i> Juara I</span>
                        <?php elseif ($pk === 2): ?>
                            <span class="badge bg-secondary text-white"><i class="bi bi-award-fill me-1"></i> Juara II</span>
                        <?php elseif ($pk === 3): ?>
                            <span class="badge text-white" style="background: #CD7F32;"><i class="bi bi-award-fill me-1"></i> Juara III</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border">Juara <?= $pk ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border font-monospace"><?= esc($p['prestasi'] ?: '-') ?></span>
                    </td>
                    <td class="text-center">
                        <?php if (!empty($p['uuid'])): ?>
                            <a href="<?= base_url('verifikasi/piagam/' . $p['uuid']) ?>" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2" title="Cek Verifikasi QR">
                                <i class="bi bi-qr-code"></i>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="<?= base_url('piagam-kejurkot/cetak/' . $p['id']) ?>" target="_blank" class="btn btn-success" title="Cetak Piagam Ini">
                                <i class="bi bi-printer-fill"></i>
                            </a>
                            <button type="button" class="btn btn-outline-primary btn-edit" 
                                data-id="<?= $p['id'] ?>"
                                data-nama="<?= esc($p['nama_pemenang']) ?>"
                                data-asal="<?= esc($p['asal_sekolah'] ?: $p['asal_kota']) ?>"
                                data-lomba="<?= esc($p['nomor_lomba']) ?>"
                                data-peringkat="<?= $p['peringkat'] ?>"
                                data-peringkat-teks="<?= esc($p['peringkat_teks']) ?>"
                                data-prestasi="<?= esc($p['prestasi']) ?>"
                                data-nomor="<?= esc($p['nomor_piagam']) ?>"
                                title="Edit Data">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <form action="<?= base_url('piagam-kejurkot/delete/' . $p['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pemenang ini?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah / Edit Pemenang -->
<div class="modal fade" id="modalTambahPemenang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('piagam-kejurkot/save') ?>" method="POST" id="formPemenang">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id" value="">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">
                        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pemenang Piagam
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Pemenang / Atlet <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pemenang" id="input_nama" class="form-control" required placeholder="Contoh: AHMAD FAUZI">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold small">Asal Sekolah / Kontingen</label>
                            <input type="text" name="asal_sekolah" id="input_asal" class="form-control" placeholder="Contoh: SMPN 1 Pekalongan">
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold small">Asal Kota</label>
                            <input type="text" name="asal_kota" id="input_kota" class="form-control" value="Kota Pekalongan">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nomor Lomba <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_lomba" id="input_lomba" class="form-control" required placeholder="Contoh: Lari 100m Putra Kelompok Umur 12">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Sebagai Juara</label>
                            <select name="peringkat" id="input_peringkat" class="form-select">
                                <option value="1">Juara I</option>
                                <option value="2">Juara II</option>
                                <option value="3">Juara III</option>
                                <option value="4">Juara IV</option>
                                <option value="5">Juara V</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Peringkat Teks</label>
                            <input type="text" name="peringkat_teks" id="input_peringkat_teks" class="form-control" value="Pertama">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold small">Prestasi (Waktu/Jarak)</label>
                            <input type="text" name="prestasi" id="input_prestasi" class="form-control" placeholder="12.35 dtk / -">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Nomor Sertifikat</label>
                        <input type="text" name="nomor_piagam" id="input_nomor" class="form-control font-monospace" placeholder="<?= esc($nextNomorFormatted) ?>">
                        <small class="text-muted" style="font-size: 11px;">Kosongkan jika ingin dibuat otomatis berurutan.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold" id="btnSubmit">Simpan Pemenang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Data Excel / Teks -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('piagam-kejurkot/import') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Data Pemenang Kejurkot
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Anda dapat mengunggah file spreadsheet (.csv, .xlsx, .xls) atau langsung tempel (paste) baris data dari Excel.
                        <br><strong>Urutan kolom:</strong> Nama [TAB] Asal Kontingen/Sekolah [TAB] Nomor Lomba [TAB] Juara (1/2/3) [TAB] Prestasi [TAB] Nomor Sertifikat (opsional)
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Opsi 1: Unggah File Excel / CSV</label>
                        <input type="file" name="file_excel" class="form-control" accept=".csv,.xlsx,.xls,.txt">
                    </div>

                    <div class="mb-3 text-center">
                        <span class="badge bg-secondary">--- ATAU ---</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Opsi 2: Salin & Tempel Baris Tabel (Copy-Paste dari Excel)</label>
                        <textarea name="data_teks" rows="6" class="form-control font-monospace small" placeholder="AHMAD FAUZI&#9;SMPN 1 PEKALONGAN&#9;Lari 100m Putra&#9;1&#9;12.45 dtk
BUDI SANTOSO&#9;SMPN 2 PEKALONGAN&#9;Lari 100m Putra&#9;2&#9;12.80 dtk"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success fw-bold">Mulai Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit & Simpan Tanggal Kejurkot -->
<div class="modal fade" id="modalEditTanggalKejurkot" tabindex="-1" aria-labelledby="modalEditTanggalKejurkotLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formQuickEditTanggalKejurkot" action="<?= base_url('piagam/update-tanggal') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="kejuaraan_id" value="<?= esc($kejuaraan['id'] ?? 2) ?>">

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fs-6 fw-bold d-flex align-items-center gap-2" id="modalEditTanggalKejurkotLabel">
                        <i class="bi bi-calendar-check-fill text-warning"></i> Edit & Simpan Tanggal Kejurkot
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small rounded-1 mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Tanggal akan langsung disimpan permanen ke database dan diterapkan pada seluruh piagam Kejurkot Kota Pekalongan.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">
                            Pilih Tanggal Pelaksanaan <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-sm-5">
                                <input type="date" id="quickPickerTglKejurkot" class="form-control form-control-sm" value="<?= !empty($kejuaraan['tanggal_mulai']) ? date('Y-m-d', strtotime($kejuaraan['tanggal_mulai'])) : '2026-08-29' ?>">
                            </div>
                            <div class="col-sm-7">
                                <input type="text" name="tanggal" id="quickInputTglKejurkot" class="form-control form-control-sm fw-bold" value="<?= esc($tglKejurkotFormatted) ?>" placeholder="Contoh: 29 Agustus 2026" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">Tempat Pelaksanaan:</label>
                        <input type="text" name="tempat" class="form-control form-control-sm" value="<?= esc($kejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan') ?>" placeholder="Contoh: Stadion Hoegeng Kota Pekalongan">
                    </div>

                    <div id="alertEditTanggalKejurkot" class="mt-2" style="display: none;"></div>
                </div>

                <div class="modal-footer bg-light py-2 px-3 border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitTanggalKejurkot" class="btn btn-primary fw-bold d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-floppy-fill"></i>
                        <span>Simpan Tanggal ke Database</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter & Search Logic terintegrasi
    const searchInput = document.getElementById('searchInput');
    const selectNomor = document.getElementById('selectNomorLomba');
    const filterPills = document.querySelectorAll('.btn-filter-pill');
    const groupHeaders = document.querySelectorAll('.table-group-header');
    const itemRows = document.querySelectorAll('.table-item-row');

    function applyFilterAndSearch() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const activeGroup = selectNomor ? selectNomor.value : 'all';

        // Hitung jumlah baris yang terlihat di setiap kelompok
        const groupVisibleCount = {};

        itemRows.forEach(row => {
            const rowGroup = row.getAttribute('data-group');
            const matchesGroup = (activeGroup === 'all' || activeGroup === rowGroup);
            const matchesSearch = !query || row.innerText.toLowerCase().includes(query);

            if (matchesGroup && matchesSearch) {
                row.style.display = '';
                groupVisibleCount[rowGroup] = (groupVisibleCount[rowGroup] || 0) + 1;
            } else {
                row.style.display = 'none';
            }
        });

        // Tampilkan/sembunyikan header kelompok
        groupHeaders.forEach(header => {
            const headerGroup = header.getAttribute('data-group');
            const hasVisibleItems = (groupVisibleCount[headerGroup] || 0) > 0;
            header.style.display = hasVisibleItems ? '' : 'none';
        });

        // Sinkronisasi status aktif tombol pills
        filterPills.forEach(pill => {
            const pillFilter = pill.getAttribute('data-filter');
            if (pillFilter === activeGroup) {
                pill.classList.remove('btn-outline-secondary', 'bg-white');
                pill.classList.add('btn-primary', 'active');
            } else {
                pill.classList.remove('btn-primary', 'active');
                pill.classList.add('btn-outline-secondary', 'bg-white');
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilterAndSearch);
    }

    if (selectNomor) {
        selectNomor.addEventListener('change', applyFilterAndSearch);
    }

    filterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            const target = this.getAttribute('data-filter');
            if (selectNomor) {
                selectNomor.value = target;
            }
            applyFilterAndSearch();
        });
    });

    // Auto sync peringkat to peringkat_teks
    const peringkatMap = {
        '1': 'Pertama',
        '2': 'Kedua',
        '3': 'Ketiga',
        '4': 'Keempat',
        '5': 'Kelima'
    };
    const selPeringkat = document.getElementById('input_peringkat');
    const txtPeringkat = document.getElementById('input_peringkat_teks');
    if (selPeringkat && txtPeringkat) {
        selPeringkat.addEventListener('change', function() {
            txtPeringkat.value = peringkatMap[this.value] || ('Ke-' + this.value);
        });
    }

    // Edit button click
    const editBtns = document.querySelectorAll('.btn-edit');
    const modalEl = document.getElementById('modalTambahPemenang');
    const modal = new bootstrap.Modal(modalEl);

    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Edit Data Pemenang';
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('input_nama').value = this.dataset.nama;
            document.getElementById('input_asal').value = this.dataset.asal;
            document.getElementById('input_lomba').value = this.dataset.lomba;
            document.getElementById('input_peringkat').value = this.dataset.peringkat;
            document.getElementById('input_peringkat_teks').value = this.dataset.peringkatTeks;
            document.getElementById('input_prestasi').value = this.dataset.prestasi;
            document.getElementById('input_nomor').value = this.dataset.nomor;
            document.getElementById('btnSubmit').innerText = 'Simpan Perubahan';
            modal.show();
        });
    });

    // Reset modal when opened for create
    modalEl.addEventListener('hidden.bs.modal', function() {
        document.getElementById('formPemenang').reset();
        document.getElementById('edit_id').value = '';
        document.getElementById('modalTitle').innerHTML = '<i class="bi bi-person-plus-fill me-1"></i> Tambah Pemenang Piagam';
        document.getElementById('btnSubmit').innerText = 'Simpan Pemenang';
    });

    // Quick Edit Tanggal Kejurkot AJAX
    const pickerKejurkot = document.getElementById('quickPickerTglKejurkot');
    const inputKejurkot  = document.getElementById('quickInputTglKejurkot');
    if (pickerKejurkot && inputKejurkot) {
        pickerKejurkot.addEventListener('change', function() {
            if (this.value) {
                const parts = this.value.split('-');
                if (parts.length === 3) {
                    const y = parts[0], m = parseInt(parts[1], 10), d = parseInt(parts[2], 10);
                    const bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    inputKejurkot.value = `${d} ${bulan[m] || ''} ${y}`.trim();
                }
            }
        });
    }

    const formKejurkotTgl = document.getElementById('formQuickEditTanggalKejurkot');
    if (formKejurkotTgl) {
        formKejurkotTgl.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSubmit = document.getElementById('btnSubmitTanggalKejurkot');
            const alertBox  = document.getElementById('alertEditTanggalKejurkot');
            const formData  = new FormData(this);

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
            if (alertBox) alertBox.style.display = 'none';

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
                    if (alertBox) {
                        alertBox.className = 'alert alert-success py-2 px-3 small';
                        alertBox.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + res.message;
                        alertBox.style.display = 'block';
                    }

                    const badgeEl = document.getElementById('badgeKejurkotTglDisplay');
                    if (badgeEl) badgeEl.textContent = 'Tanggal: ' + res.tanggal_formatted;

                    const textEl = document.getElementById('textKejurkotTgl');
                    if (textEl) textEl.textContent = res.tanggal_formatted;

                    setTimeout(() => {
                        const mInstance = bootstrap.Modal.getInstance(document.getElementById('modalEditTanggalKejurkot'));
                        if (mInstance) mInstance.hide();
                    }, 1000);
                } else {
                    if (alertBox) {
                        alertBox.className = 'alert alert-danger py-2 px-3 small';
                        alertBox.textContent = res.message || 'Gagal menyimpan.';
                        alertBox.style.display = 'block';
                    }
                }
            })
            .catch(err => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-floppy-fill"></i> <span>Simpan Tanggal ke Database</span>';
                if (alertBox) {
                    alertBox.className = 'alert alert-danger py-2 px-3 small';
                    alertBox.textContent = 'Terjadi kesalahan jaringan saat menyimpan.';
                    alertBox.style.display = 'block';
                }
            });
        });
    }
});
</script>

<?= $this->endSection() ?>
