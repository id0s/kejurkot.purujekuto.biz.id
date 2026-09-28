<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-white mb-1"><?= $sekolah ? 'Edit Sekolah' : 'Tambah Sekolah Baru' ?></h4>
                <p class="text-muted small mb-0">Lengkapi data asal sekolah atau kontingen perlombaan</p>
            </div>
            <a href="<?= base_url('sekolah') ?>" class="btn btn-secondary-custom">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card-custom">
            <div class="card-body p-4">
                <form action="<?= base_url('sekolah/save' . ($sekolah ? '/' . $sekolah['id'] : '')) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Sekolah / Lembaga / Club <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="<?= old('nama', $sekolah['nama'] ?? '') ?>" placeholder="Misal: SMP NEGERI 01 PEKALONGAN" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jenjang Pendidikan <span class="text-danger">*</span></label>
                            <select name="jenjang" class="form-select" required>
                                <option value="PAUD/TK" <?= old('jenjang', $sekolah['jenjang'] ?? '') === 'PAUD/TK' ? 'selected' : '' ?>>PAUD / TK / KB / RA</option>
                                <option value="SD/MI" <?= old('jenjang', $sekolah['jenjang'] ?? '') === 'SD/MI' ? 'selected' : '' ?>>SD / MI</option>
                                <option value="SMP/MTs" <?= old('jenjang', $sekolah['jenjang'] ?? '') === 'SMP/MTs' ? 'selected' : '' ?>>SMP / MTs</option>
                                <option value="SMA/SMK/MA" <?= old('jenjang', $sekolah['jenjang'] ?? '') === 'SMA/SMK/MA' ? 'selected' : '' ?>>SMA / SMK / MA</option>
                                <option value="Umum/Club" <?= old('jenjang', $sekolah['jenjang'] ?? '') === 'Umum/Club' ? 'selected' : '' ?>>Umum / Klub Atletik</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Kontak / Guru Pembimbing</label>
                            <input type="text" name="kontak_person" class="form-control" value="<?= old('kontak_person', $sekolah['kontak_person'] ?? '') ?>" placeholder="Nama penanggung jawab">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="no_telepon" class="form-control" value="<?= old('no_telepon', $sekolah['no_telepon'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Asal Kabupaten / Kota <span class="text-danger">*</span></label>
                        <select name="alamat" class="form-select" required>
                            <?php 
                            $currentKota = old('alamat', $sekolah['alamat'] ?? 'Kota Pekalongan');
                            $kotaList = [
                                'Kota Pekalongan',
                                'Kabupaten Pekalongan',
                                'Kabupaten Batang',
                                'Kabupaten Pemalang',
                                'Kabupaten Tegal',
                                'Kota Tegal',
                                'Kabupaten Brebes',
                            ];
                            ?>
                            <?php foreach ($kotaList as $k): ?>
                                <option value="<?= esc($k) ?>" <?= $currentKota === $k ? 'selected' : '' ?>><?= esc($k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('sekolah') ?>" class="btn btn-secondary-custom">Batal</a>
                        <button type="submit" class="btn btn-primary-gradient px-4">
                            <i class="bi bi-save me-1"></i> Simpan Sekolah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
