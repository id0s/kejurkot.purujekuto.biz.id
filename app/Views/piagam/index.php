<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header Gadget -->
<div class="card-custom mb-4 p-4" style="background: linear-gradient(135deg, rgba(0, 82, 204, 0.08) 0%, rgba(255, 171, 0, 0.12) 100%); border: 1px solid #DFE1E6; border-radius: 4px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 4px; background: #0052CC; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 1px 3px rgba(9,30,66,0.2);">
                <i class="bi bi-award-fill text-warning"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-amber">
                        <i class="bi bi-patch-check-fill me-1"></i> Piagam & Sertifikat Resmi
                    </span>
                    <span class="text-muted" style="font-size: 12px;"><?= esc($activeKejuaraan['nama'] ?? 'Kejurwil Atletik 2026') ?></span>
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
            <a href="<?= base_url('piagam/templates') ?>" class="btn btn-warning fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-dark">
                <i class="bi bi-pen-fill"></i>
                <span>Pengaturan TTD & Format Piagam</span>
            </a>
            
            <button type="button" class="btn btn-secondary-custom d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCustomPiagam">
                <i class="bi bi-pencil-square text-success"></i>
                <span>Sesuaikan Piagam Mandiri</span>
            </button>

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
                        <a href="<?= base_url('piagam/cetak-event/' . $ev['id'] . ($defaultTemplate ? '?template_id=' . $defaultTemplate['id'] : '')) ?>" target="_blank" class="btn btn-sm btn-primary-gradient d-inline-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-printer-fill"></i> Cetak Juara 1-3
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
                                    <th>Asal Daerah</th>
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
                                        </td>
                                        <td>
                                            <?= esc($w['nama_sekolah'] ?: 'Independen') ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= esc($w['asal_daerah'] ?: 'Kota Pekalongan') ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-primary font-monospace fs-6"><?= esc($w['prestasi']) ?></strong>
                                            <small class="text-muted"><?= esc($w['satuan_hasil']) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 btn-customize"
                                                data-nama="<?= esc($w['nama_atlet']) ?>"
                                                data-sekolah="<?= esc($w['nama_sekolah'] ?: 'Independen') ?>"
                                                data-kota="<?= esc($w['asal_daerah'] ?: 'Kota Pekalongan') ?>"
                                                data-lomba="<?= esc($ev['nama_acara']) ?>"
                                                data-peringkat="<?= $p ?>"
                                                data-prestasi="<?= esc($w['prestasi']) ?>"
                                                data-kejuaraan="<?= esc($activeKejuaraan['nama'] ?? 'KEJURWIL ATLETIK KOTA PEKALONGAN 2026') ?>"
                                                data-tempat="<?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan') ?>"
                                                data-tanggal="29 Agustus 2026">
                                                <i class="bi bi-pencil-square"></i> Sesuaikan & Cetak
                                            </button>
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
                        <!-- Pilihan Template -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-dark">Pilih Template Piagam <span class="text-danger">*</span></label>
                            <select name="template_id" class="form-select" id="custom_template_id">
                                <?php foreach ($templates as $tpl): ?>
                                    <option value="<?= $tpl['id'] ?>" <?= ($defaultTemplate && $defaultTemplate['id'] == $tpl['id']) ? 'selected' : '' ?>>
                                        <?= esc($tpl['nama']) ?> (<?= strtoupper($tpl['orientasi']) ?> - <?= strtoupper($tpl['tipe']) ?>) <?= $tpl['is_default'] ? '★ DEFAULT' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

                        <!-- Asal Sekolah & Asal Kota -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Asal Sekolah / Kontingen</label>
                            <input type="text" name="asal_sekolah" id="custom_asal_sekolah" class="form-control" placeholder="Contoh: SMP NEGERI 1 PEKALONGAN">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Asal Kabupaten / Kota</label>
                            <input type="text" name="asal_kota" id="custom_asal_kota" class="form-control" value="Kota Pekalongan" placeholder="Contoh: Kota Pekalongan">
                        </div>

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
                            <label class="form-label fw-bold small text-dark">Tanggal Dilaksanakan / Terbit</label>
                            <input type="text" name="tanggal" id="custom_tanggal" class="form-control" value="29 Agustus 2026" placeholder="Contoh: 29 Agustus 2026">
                        </div>

                        <!-- Nama Kejuaraan -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Nama Kejuaraan</label>
                            <input type="text" name="nama_kejuaraan" id="custom_nama_kejuaraan" class="form-control" value="<?= esc($activeKejuaraan['nama'] ?? 'KEJURWIL ATLETIK KOTA PEKALONGAN 2026') ?>">
                        </div>

                        <!-- Nomor Piagam -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Nomor Surat / Piagam</label>
                            <input type="text" name="nomor_piagam" id="custom_nomor_piagam" class="form-control font-monospace" value="001/SERT/PASIJATENG/IX/<?= date('Y') ?>">
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
                                    <input type="text" name="ttd_1_jabatan" class="form-control form-control-sm mb-1" placeholder="Jabatan" value="A.n Ketua Umum PASI Jateng, Sekretaris Umum">
                                    <input type="text" name="ttd_1_nama" class="form-control form-control-sm" placeholder="Nama Lengkap" value="Dr. Hermahayu, M.Si">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Penandatangan 2 (Tengah)</label>
                                    <input type="text" name="ttd_2_jabatan" class="form-control form-control-sm mb-1" placeholder="Jabatan" value="Koordinator Wilayah">
                                    <input type="text" name="ttd_2_nama" class="form-control form-control-sm" placeholder="Nama Lengkap" value="Drs. Sukono, M.Or.">
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
            document.getElementById('custom_tanggal').value       = this.dataset.tanggal || '29 Agustus 2026';

            modal.show();
        });
    });
});
</script>

<?= $this->endSection() ?>
