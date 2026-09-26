<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-white mb-1"><?= $kejuaraan ? 'Edit Kejuaraan' : 'Tambah Kejuaraan Baru' ?></h4>
                <p class="text-muted small mb-0">Isi formulir informasi kejuaraan perlombaan atletik</p>
            </div>
            <a href="<?= base_url('kejuaraan') ?>" class="btn btn-secondary-custom">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card-custom">
            <div class="card-body p-4">
                <form action="<?= base_url('kejuaraan/save' . ($kejuaraan ? '/' . $kejuaraan['id'] : '')) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Kejuaraan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="<?= old('nama', $kejuaraan['nama'] ?? '') ?>" placeholder="Misal: KEJURWIL ATLETIK KOTA PEKALONGAN 2026" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tahun Penyelenggaraan <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" class="form-control" value="<?= old('tahun', $kejuaraan['tahun'] ?? date('Y')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi / Tempat Perlombaan <span class="text-danger">*</span></label>
                            <input type="text" name="tempat" class="form-control" value="<?= old('tempat', $kejuaraan['tempat'] ?? '') ?>" placeholder="Misal: Stadion Hoegeng" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?= old('tanggal_mulai', $kejuaraan['tanggal_mulai'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="<?= old('tanggal_selesai', $kejuaraan['tanggal_selesai'] ?? date('Y-m-d')) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Penyelenggara / Panitia</label>
                            <input type="text" name="penyelenggara" class="form-control" value="<?= old('penyelenggara', $kejuaraan['penyelenggara'] ?? '') ?>" placeholder="Misal: PASI Kota Pekalongan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Kejuaraan</label>
                            <select name="status" class="form-select">
                                <option value="Draft" <?= old('status', $kejuaraan['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="Aktif" <?= old('status', $kejuaraan['status'] ?? '') === 'Aktif' ? 'selected' : '' ?>>Aktif (Event Utama)</option>
                                <option value="Selesai" <?= old('status', $kejuaraan['status'] ?? '') === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi / Keterangan Tambahan</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Informasi singkat seputar kejuaraan..."><?= old('deskripsi', $kejuaraan['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('kejuaraan') ?>" class="btn btn-secondary-custom">Batal</a>
                        <button type="submit" class="btn btn-primary-gradient px-4">
                            <i class="bi bi-save me-1"></i> Simpan Kejuaraan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
