<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--text-main);">
            <i class="bi bi-file-earmark-check-fill text-primary me-2"></i>Verifikasi Keabsahan Akta Kelahiran
        </h4>
        <p class="text-muted small mb-0">
            Periksa berkas akta kelahiran atlet, kesesuaian tanggal lahir, dan validasi keabsahan Kelompok Umur (KU).
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('atlet/batch-register') ?>" class="btn btn-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-person-plus-fill"></i>
            <span>Pendaftaran Massal</span>
        </a>
        <a href="<?= base_url('atlet') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Data Atlet</span>
        </a>
    </div>
</div>

<!-- Statistik Verifikasi Akta -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Menunggu Review</div>
                    <h3 class="fw-bold mb-0 text-warning"><?= esc($stats['menunggu_verifikasi'] ?? 0) ?></h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width: 44px; height: 44px; font-size: 1.3rem;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Valid / Disetujui</div>
                    <h3 class="fw-bold mb-0 text-success"><?= esc($stats['valid'] ?? 0) ?></h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 44px; height: 44px; font-size: 1.3rem;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Perlu Perbaikan</div>
                    <h3 class="fw-bold mb-0 text-danger"><?= esc($stats['perlu_perbaikan'] ?? 0) ?></h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger" style="width: 44px; height: 44px; font-size: 1.3rem;">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 border-start border-4 border-secondary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Belum Upload</div>
                    <h3 class="fw-bold mb-0 text-secondary"><?= esc($stats['belum_upload'] ?? 0) ?></h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary" style="width: 44px; height: 44px; font-size: 1.3rem;">
                    <i class="bi bi-file-earmark-x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar Card -->
<div class="card-custom mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= base_url('atlet/verifikasi-akta') ?>" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status Akta</option>
                    <option value="menunggu_verifikasi" <?= $statusFilter === 'menunggu_verifikasi' ? 'selected' : '' ?>>⏳ Menunggu Verifikasi</option>
                    <option value="valid" <?= $statusFilter === 'valid' ? 'selected' : '' ?>>✅ Valid / Disetujui</option>
                    <option value="perlu_perbaikan" <?= $statusFilter === 'perlu_perbaikan' ? 'selected' : '' ?>>⚠️ Perlu Perbaikan</option>
                    <option value="belum_upload" <?= $statusFilter === 'belum_upload' ? 'selected' : '' ?>>❌ Belum Upload</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="sekolah_id" class="form-select form-select-sm select2" onchange="this.form.submit()">
                    <option value="">Semua Kontingen / Sekolah</option>
                    <?php foreach ($sekolahs as $sk): ?>
                        <option value="<?= $sk['id'] ?>" <?= $sekolahId == $sk['id'] ? 'selected' : '' ?>>
                            <?= esc($sk['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atlet / no dada..." value="<?= esc($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="col-md-1 text-end">
                <a href="<?= base_url('atlet/verifikasi-akta') ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="ps-3">#</th>
                        <th>No Dada & Atlet</th>
                        <th>Kontingen / Sekolah</th>
                        <th>Tgl Lahir & Usia</th>
                        <th>Kategori KU</th>
                        <th>Berkas Akta</th>
                        <th>Status Verifikasi</th>
                        <th class="text-center pe-3" width="160">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($atletList)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                Tidak ada data atlet yang sesuai dengan filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($atletList as $at): 
                            // Hitung usia aktual
                            $usia = '-';
                            if (!empty($at['tanggal_lahir'])) {
                                $tgl = new \DateTime($at['tanggal_lahir']);
                                $now = new \DateTime();
                                $usia = $now->diff($tgl)->y . ' tahun';
                            }
                        ?>
                            <tr id="atlet_row_<?= $at['id'] ?>">
                                <td class="ps-3 text-muted"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($at['no_dada'])): ?>
                                            <span class="badge bg-dark font-monospace me-2 px-2 py-1"><?= esc($at['no_dada']) ?></span>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold" style="color: var(--text-main); font-size: 13.5px;">
                                                <?= esc($at['nama']) ?>
                                            </div>
                                            <small class="badge <?= $at['gender'] === 'L' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' ?> px-1.5 py-0.5">
                                                <?= $at['gender'] === 'L' ? 'Putra' : 'Putri' ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 170px;">
                                        <?= esc($at['nama_sekolah'] ?? '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <div><?= !empty($at['tanggal_lahir']) ? date('d/m/Y', strtotime($at['tanggal_lahir'])) : '-' ?></div>
                                    <small class="text-muted fw-bold"><?= esc($usia) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($at['nama_ku'])): ?>
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                            <?= esc($at['nama_ku']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($at['akta_file'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2" onclick='previewAktaFile(<?= json_encode($at) ?>)'>
                                            <i class="bi bi-file-earmark-image"></i>
                                            <span>Lihat Berkas</span>
                                        </button>
                                    <?php elseif (!empty($at['akta_url'])): ?>
                                        <a href="<?= esc($at['akta_url']) ?>" target="_blank" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1 py-1 px-2">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                            <span>Link Drive</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                            <i class="bi bi-x-circle me-1"></i> Belum Ada
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td id="status_cell_<?= $at['id'] ?>">
                                    <?php if ($at['akta_status'] === 'valid'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i> Valid
                                        </span>
                                    <?php elseif ($at['akta_status'] === 'menunggu_verifikasi'): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                            <i class="bi bi-hourglass-split me-1"></i> Menunggu
                                        </span>
                                    <?php elseif ($at['akta_status'] === 'perlu_perbaikan'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" title="<?= esc($at['akta_catatan_verifikasi'] ?? '') ?>">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Perbaikan
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                            Belum Upload
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($at['akta_catatan_verifikasi'])): ?>
                                        <small class="d-block text-danger mt-1" style="font-size: 11px;">
                                            <em>Catatan: <?= esc($at['akta_catatan_verifikasi']) ?></em>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-success" title="Setujui (Valid)" onclick="quickApprove(<?= $at['id'] ?>)">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" title="Minta Perbaikan / Tolak" onclick="openRejectModal(<?= $at['id'] ?>, '<?= esc(addslashes($at['nama'])) ?>')">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" title="Reset Status" onclick="resetStatus(<?= $at['id'] ?>)">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Preview Berkas Akta -->
<div class="modal fade" id="modalPreviewAkta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="previewNamaAtlet">Preview Akta Kelahiran</h5>
                    <small class="text-muted" id="previewDetailAtlet"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3" style="min-height: 380px; background: #000;">
                <div id="previewMediaContainer" class="d-flex align-items-center justify-content-center" style="height: 480px;">
                    <!-- Image or PDF container -->
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <a href="#" id="downloadAktaBtn" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-download me-1"></i> Unduh Berkas
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-warning" id="modalBtnReject">
                        <i class="bi bi-exclamation-triangle me-1"></i> Minta Perbaikan
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="modalBtnApprove">
                        <i class="bi bi-check-circle me-1"></i> Setujui Akta (Valid)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Catatan Perbaikan Akta -->
<div class="modal fade" id="modalRejectAkta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger">Minta Perbaikan Berkas Akta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2">
                    Atlet: <strong id="rejectNamaAtlet"></strong>
                </p>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 13px;">Alasan / Catatan Perbaikan <span class="text-danger">*</span></label>
                    <textarea id="catatanPerbaikanInput" class="form-control" rows="3" placeholder="contoh: Foto buram/tidak terbaca, atau tahun kelahiran pada akta tidak sesuai dengan formulir."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger fw-semibold" onclick="submitReject()">
                    Kirim Permintaan Perbaikan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRejectAtletId = null;

function sendUpdateStatus(atletId, status, catatan = '') {
    const formData = new FormData();
    formData.append('atlet_id', atletId);
    formData.append('akta_status', status);
    formData.append('catatan', catatan);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url('atlet/update-akta-status') ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            updateStatusCell(atletId, status, catatan);
        } else {
            alert(data.message || 'Gagal memperbarui status.');
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan koneksi.');
    });
}

function updateStatusCell(atletId, status, catatan) {
    const cell = document.getElementById(`status_cell_${atletId}`);
    if (!cell) return;

    let badgeHtml = '';
    if (status === 'valid') {
        badgeHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Valid</span>`;
    } else if (status === 'perlu_perbaikan') {
        badgeHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perbaikan</span>`;
    } else if (status === 'menunggu_verifikasi') {
        badgeHtml = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><i class="bi bi-hourglass-split me-1"></i> Menunggu</span>`;
    } else {
        badgeHtml = `<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Belum Upload</span>`;
    }

    if (catatan) {
        badgeHtml += `<small class="d-block text-danger mt-1" style="font-size: 11px;"><em>Catatan: ${catatan}</em></small>`;
    }

    cell.innerHTML = badgeHtml;
}

function quickApprove(atletId) {
    if (confirm('Setujui dan nyatakan akta kelahiran atlet ini VALID?')) {
        sendUpdateStatus(atletId, 'valid');
    }
}

function resetStatus(atletId) {
    if (confirm('Reset status verifikasi atlet ini ke Menunggu Verifikasi?')) {
        sendUpdateStatus(atletId, 'menunggu_verifikasi');
    }
}

function openRejectModal(atletId, nama) {
    currentRejectAtletId = atletId;
    document.getElementById('rejectNamaAtlet').textContent = nama;
    document.getElementById('catatanPerbaikanInput').value = '';
    new bootstrap.Modal(document.getElementById('modalRejectAkta')).show();
}

function submitReject() {
    const catatan = document.getElementById('catatanPerbaikanInput').value.trim();
    if (!catatan) {
        alert('Mohon isi alasan / catatan perbaikan.');
        return;
    }
    sendUpdateStatus(currentRejectAtletId, 'perlu_perbaikan', catatan);
    bootstrap.Modal.getInstance(document.getElementById('modalRejectAkta')).hide();
}

function previewAktaFile(atlet) {
    document.getElementById('previewNamaAtlet').textContent = atlet.nama;
    document.getElementById('previewDetailAtlet').textContent = `Kontingen: ${atlet.nama_sekolah || '-'} | KU: ${atlet.nama_ku || '-'}`;

    const container = document.getElementById('previewMediaContainer');
    const fileUrl = '<?= base_url('atlet/view-akta/') ?>' + atlet.id;
    document.getElementById('downloadAktaBtn').href = fileUrl;

    const ext = (atlet.akta_file || '').split('.').pop().toLowerCase();
    if (ext === 'pdf') {
        container.innerHTML = `<iframe src="${fileUrl}" style="width: 100%; height: 100%; border: none;"></iframe>`;
    } else {
        container.innerHTML = `<img src="${fileUrl}" alt="Akta Kelahiran" style="max-height: 100%; max-width: 100%; object-fit: contain; border-radius: 4px;">`;
    }

    document.getElementById('modalBtnApprove').onclick = function() {
        sendUpdateStatus(atlet.id, 'valid');
        bootstrap.Modal.getInstance(document.getElementById('modalPreviewAkta')).hide();
    };

    document.getElementById('modalBtnReject').onclick = function() {
        bootstrap.Modal.getInstance(document.getElementById('modalPreviewAkta')).hide();
        openRejectModal(atlet.id, atlet.nama);
    };

    new bootstrap.Modal(document.getElementById('modalPreviewAkta')).show();
}
</script>

<?= $this->endSection() ?>
