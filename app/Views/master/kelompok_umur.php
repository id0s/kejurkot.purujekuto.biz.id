<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Kelompok Umur (KU)</h4>
        <p class="text-muted small mb-0">Atur batasan rentang usia untuk pengelompokan peserta secara otomatis</p>
    </div>
    <button class="btn btn-primary-gradient d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalKU" onclick="resetFormKU()">
        <i class="bi bi-plus-lg"></i>
        <span>Tambah KU</span>
    </button>
</div>

<div class="card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark-custom">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Kelompok Umur</th>
                        <th class="text-center">Rentang Usia</th>
                        <th>Keterangan / Kategori Sekolah</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($kus as $k): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td class="fw-bold text-white">
                                <span class="badge badge-purple fs-6"><?= esc($k['nama']) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-blue"><?= $k['usia_min'] ?> s/d <?= $k['usia_max'] ?> Tahun</span>
                            </td>
                            <td class="text-muted"><?= esc($k['keterangan'] ?: '-') ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" title="Edit" onclick='editKU(<?= json_encode($k) ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="<?= base_url('kelompok-umur/delete/' . $k['id']) ?>" class="btn btn-outline-danger btn-delete" data-title="<?= esc($k['nama']) ?>" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form KU -->
<div class="modal fade" id="modalKU" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('kelompok-umur/save') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="ku_id">

                <div class="modal-header">
                    <h5 class="modal-title text-white" id="modalKUTitle">Tambah Kelompok Umur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="ku_nama" class="form-control" placeholder="Misal: KU 10-11" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Usia Minimal (Tahun) <span class="text-danger">*</span></label>
                            <input type="number" name="usia_min" id="ku_usia_min" class="form-control" min="3" max="25" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Usia Maksimal (Tahun) <span class="text-danger">*</span></label>
                            <input type="number" name="usia_max" id="ku_usia_max" class="form-control" min="3" max="25" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" id="ku_keterangan" class="form-control" placeholder="Misal: SD / MI Kelas Atas">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-gradient">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function resetFormKU() {
    $('#modalKUTitle').text('Tambah Kelompok Umur');
    $('#ku_id').val('');
    $('#ku_nama').val('');
    $('#ku_usia_min').val('');
    $('#ku_usia_max').val('');
    $('#ku_keterangan').val('');
}

function editKU(data) {
    $('#modalKUTitle').text('Edit Kelompok Umur');
    $('#ku_id').val(data.id);
    $('#ku_nama').val(data.nama);
    $('#ku_usia_min').val(data.usia_min);
    $('#ku_usia_max').val(data.usia_max);
    $('#ku_keterangan').val(data.keterangan);
    const m = new bootstrap.Modal(document.getElementById('modalKU'));
    m.show();
}
</script>
<?= $this->endSection() ?>
