<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--text-main);">Data Master Cabang Olahraga (Cabor)</h4>
        <p class="text-muted small mb-0">Kelola daftar cabang olahraga peserta kejuaraan dan admin penanggung jawab</p>
    </div>
    <button class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCabor" onclick="resetFormCabor()">
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Cabor</span>
    </button>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="ps-3">#</th>
                        <th>Kode</th>
                        <th>Nama Cabang Olahraga</th>
                        <th>Pengurus / Penanggung Jawab</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th class="text-center pe-3" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($caborList as $c): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $no++ ?></td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">
                                    <?= esc($c['kode']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 text-primary" style="width: 32px; height: 32px; background: rgba(0, 82, 204, 0.1);">
                                        <i class="bi <?= esc($c['icon'] ?: 'bi-trophy') ?>"></i>
                                    </div>
                                    <span class="fw-bold" style="color: var(--text-main); font-size: 14px;">
                                        <?= esc($c['nama']) ?>
                                    </span>
                                </div>
                            </td>
                            <td><?= esc($c['penanggung_jawab'] ?: '-') ?></td>
                            <td><?= esc($c['kontak'] ?: '-') ?></td>
                            <td>
                                <?php if ($c['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" title="Edit" onclick='editCabor(<?= json_encode($c) ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="<?= base_url('cabor/delete/' . $c['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus Cabor <?= esc($c['nama']) ?>?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Cabor -->
<div class="modal fade" id="modalCabor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalCaborTitle">Tambah Cabang Olahraga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('cabor/save') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="caborId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Kode Cabor <span class="text-danger">*</span></label>
                        <input type="text" name="kode" id="caborKode" class="form-control font-monospace" placeholder="contoh: atletik, renang, panahan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Nama Cabang Olahraga <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="caborNama" class="form-control" placeholder="contoh: Atletik" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Pengurus / Penanggung Jawab</label>
                        <input type="text" name="penanggung_jawab" id="caborPj" class="form-control" placeholder="contoh: PASI Kota Pekalongan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Kontak</label>
                        <input type="text" name="kontak" id="caborKontak" class="form-control" placeholder="contoh: 08123456789">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Icon Bootstrap</label>
                        <input type="text" name="icon" id="caborIcon" class="form-control font-monospace" placeholder="contoh: bi-trophy, bi-stopwatch, bi-water">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="caborActive" value="1" checked>
                        <label class="form-check-label fw-semibold" for="caborActive" style="font-size: 13px;">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetFormCabor() {
    document.getElementById('modalCaborTitle').textContent = 'Tambah Cabang Olahraga';
    document.getElementById('caborId').value = '';
    document.getElementById('caborKode').value = '';
    document.getElementById('caborNama').value = '';
    document.getElementById('caborPj').value = '';
    document.getElementById('caborKontak').value = '';
    document.getElementById('caborIcon').value = 'bi-trophy';
    document.getElementById('caborActive').checked = true;
}

function editCabor(cabor) {
    document.getElementById('modalCaborTitle').textContent = 'Edit Cabang Olahraga';
    document.getElementById('caborId').value = cabor.id;
    document.getElementById('caborKode').value = cabor.kode;
    document.getElementById('caborNama').value = cabor.nama;
    document.getElementById('caborPj').value = cabor.penanggung_jawab || '';
    document.getElementById('caborKontak').value = cabor.kontak || '';
    document.getElementById('caborIcon').value = cabor.icon || 'bi-trophy';
    document.getElementById('caborActive').checked = cabor.is_active == 1;
    new bootstrap.Modal(document.getElementById('modalCabor')).show();
}
</script>

<?= $this->endSection() ?>
