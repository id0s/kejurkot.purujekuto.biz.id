<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                    <?= $user ? 'Edit Pengguna' : 'Tambah Pengguna Baru' ?>
                </h4>
                <p class="text-muted small mb-0">Atur hak akses login, peran (role), dan penugasan cabang olahraga</p>
            </div>
            <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <div class="card-custom">
            <div class="card-body p-4">
                <form action="<?= $user ? base_url('users/update/' . $user['id']) : base_url('users/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <!-- Username -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-at"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="contoh: admin_atletik" value="<?= old('username', $user['username'] ?? '') ?>" required>
                            </div>
                            <small class="text-muted">Gunakan huruf kecil tanpa spasi.</small>
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="contoh: Ahmad Fauzi - Panitia PASI" value="<?= old('nama_lengkap', $user['nama_lengkap'] ?? '') ?>" required>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">
                                Password <?= $user ? '<span class="text-muted fw-normal">(Kosongkan jika tidak diubah)</span>' : '<span class="text-danger">*</span>' ?>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="<?= $user ? 'Ketik password baru' : 'Minimal 6 karakter' ?>" <?= $user ? '' : 'required' ?>>
                            </div>
                        </div>

                        <!-- Role Selection -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Role / Hak Akses <span class="text-danger">*</span></label>
                            <select name="role" id="roleSelector" class="form-select" required onchange="handleRoleChange()">
                                <option value="admin_cabor" <?= old('role', $user['role'] ?? 'admin_cabor') === 'admin_cabor' ? 'selected' : '' ?>>Admin Cabor (Cabang Olahraga)</option>
                                <option value="admin" <?= old('role', $user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Super Admin (Akses Penuh)</option>
                                <option value="panitia" <?= old('role', $user['role'] ?? '') === 'panitia' ? 'selected' : '' ?>>Panitia Pelaksana</option>
                                <option value="juri" <?= old('role', $user['role'] ?? '') === 'juri' ? 'selected' : '' ?>>Juri / Wasit</option>
                            </select>
                        </div>

                        <!-- Cabor Assignment (Visible if role is admin_cabor) -->
                        <div class="col-12" id="caborField">
                            <div class="p-3 rounded-2" style="background: rgba(0, 82, 204, 0.04); border: 1px solid rgba(0, 82, 204, 0.15);">
                                <label class="form-label fw-semibold text-primary d-flex align-items-center gap-1" style="font-size: 13px;">
                                    <i class="bi bi-trophy-fill"></i>
                                    <span>Pilih Cabang Olahraga (Cabor)</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="cabor_id" id="caborSelector" class="form-select">
                                    <option value="">-- Pilih Cabang Olahraga --</option>
                                    <?php foreach ($caborList as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= old('cabor_id', $user['cabor_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                            <?= esc($c['nama']) ?> (<?= esc($c['kode']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted d-block mt-1">Admin ini hanya akan mengelola atlet, lomba, dan hasil pada cabang olahraga yang dipilih.</small>
                            </div>
                        </div>

                        <!-- Status Aktif -->
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="isActiveSwitch" name="is_active" value="1" <?= old('is_active', $user['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="isActiveSwitch" style="font-size: 13px;">
                                    Akun Aktif (Dapat Login)
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-12 pt-3 border-top mt-3 d-flex justify-content-end gap-2">
                            <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i> <?= $user ? 'Perbarui Pengguna' : 'Simpan Pengguna' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function handleRoleChange() {
    const role = document.getElementById('roleSelector').value;
    const caborField = document.getElementById('caborField');
    const caborSelector = document.getElementById('caborSelector');

    if (role === 'admin_cabor') {
        caborField.style.display = 'block';
        caborSelector.setAttribute('required', 'required');
    } else {
        caborField.style.display = 'none';
        caborSelector.removeAttribute('required');
    }
}

document.addEventListener('DOMContentLoaded', handleRoleChange);
</script>

<?= $this->endSection() ?>
