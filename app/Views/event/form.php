<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-white mb-1"><?= $event ? 'Edit Acara Perlombaan' : 'Tambah Acara Perlombaan Baru' ?></h4>
                <p class="text-muted small mb-0">Atur nomor urut acara, kategori perlombaan, babak, dan waktu pelaksanaan</p>
            </div>
            <a href="<?= base_url('event') ?>" class="btn btn-secondary-custom">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Acara
            </a>
        </div>

        <div class="card-custom">
            <div class="card-body p-4">
                <form action="<?= base_url('event/save' . ($event ? '/' . $event['id'] : '')) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">No. Urut Acara <span class="text-danger">*</span></label>
                            <input type="number" name="nomor_acara" id="nomor_acara" class="form-control" value="<?= old('nomor_acara', $event['nomor_acara'] ?? $nextNoAcara) ?>" required>
                            <small class="text-muted">Urutan di buku acara</small>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Nama Acara Perlombaan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_acara" id="nama_acara" class="form-control" value="<?= old('nama_acara', $event['nama_acara'] ?? '') ?>" placeholder="Misal: 100M KU (14-15) Putri" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nomor Lomba Cabang Atletik <span class="text-danger">*</span></label>
                            <select name="nomor_lomba_id" id="nomor_lomba_id" class="form-select" required onchange="suggestEventName()">
                                <option value="">-- Pilih Nomor Lomba --</option>
                                <?php foreach ($lombas as $l): ?>
                                    <option value="<?= $l['id'] ?>" data-nama="<?= esc($l['nama']) ?>" <?= old('nomor_lomba_id', $event['nomor_lomba_id'] ?? '') == $l['id'] ? 'selected' : '' ?>>
                                        <?= esc($l['nama']) ?> (<?= esc($l['kategori']) ?> &bull; <?= esc($l['satuan_hasil']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelompok Umur (KU)</label>
                            <select name="kelompok_umur_id" id="kelompok_umur_id" class="form-select" onchange="suggestEventName()">
                                <option value="">-- Terbuka / Tanpa Batasan KU --</option>
                                <?php foreach ($kus as $ku): ?>
                                    <option value="<?= $ku['id'] ?>" data-nama="<?= esc($ku['nama']) ?>" <?= old('kelompok_umur_id', $event['kelompok_umur_id'] ?? '') == $ku['id'] ? 'selected' : '' ?>>
                                        <?= esc($ku['nama']) ?> (<?= $ku['usia_min'] ?>-<?= $ku['usia_max'] ?> Thn)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kategori Peserta (Gender) <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-select" required onchange="suggestEventName()">
                                <option value="L" <?= old('gender', $event['gender'] ?? '') === 'L' ? 'selected' : '' ?>>Putra / Male (L)</option>
                                <option value="P" <?= old('gender', $event['gender'] ?? '') === 'P' ? 'selected' : '' ?>>Putri / Female (P)</option>
                                <option value="Campuran" <?= old('gender', $event['gender'] ?? '') === 'Campuran' ? 'selected' : '' ?>>Campuran / Mixed (Pa & Pi)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Babak Perlombaan <span class="text-danger">*</span></label>
                            <select name="babak" class="form-select" required>
                                <option value="Final" <?= old('babak', $event['babak'] ?? '') === 'Final' ? 'selected' : '' ?>>Final</option>
                                <option value="Penyisihan" <?= old('babak', $event['babak'] ?? '') === 'Penyisihan' ? 'selected' : '' ?>>Penyisihan</option>
                                <option value="Semifinal" <?= old('babak', $event['babak'] ?? '') === 'Semifinal' ? 'selected' : '' ?>>Semifinal</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jadwal Hari / Tanggal</label>
                            <input type="text" name="jadwal_hari" class="form-control" value="<?= old('jadwal_hari', $event['jadwal_hari'] ?? 'Sabtu, 29 Agustus 2026') ?>" placeholder="Misal: Sabtu, 29 Agustus 2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jadwal Waktu / Sesi</label>
                            <input type="text" name="jadwal_waktu" class="form-control" value="<?= old('jadwal_waktu', $event['jadwal_waktu'] ?? 'Pagi 06.00') ?>" placeholder="Misal: Pagi 06.00 atau Sore 13.30">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Status Perlombaan</label>
                            <select name="status" class="form-select">
                                <option value="Belum Dimulai" <?= old('status', $event['status'] ?? '') === 'Belum Dimulai' ? 'selected' : '' ?>>Belum Dimulai</option>
                                <option value="Berlangsung" <?= old('status', $event['status'] ?? '') === 'Berlangsung' ? 'selected' : '' ?>>Sedang Berlangsung</option>
                                <option value="Selesai" <?= old('status', $event['status'] ?? '') === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Catatan Teknis Acara</label>
                            <input type="text" name="catatan" class="form-control" value="<?= old('catatan', $event['catatan'] ?? '') ?>" placeholder="Misal: 8 pelari terbaik lolos ke final">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('event') ?>" class="btn btn-secondary-custom">Batal</a>
                        <button type="submit" class="btn btn-primary-gradient px-4">
                            <i class="bi bi-save me-1"></i> Simpan Acara
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function suggestEventName() {
    // Hanya auto-suggest jika form tambah baru dan belum diketik manual
    const curName = $('#nama_acara').val();
    const lombaOpt = $('#nomor_lomba_id option:selected');
    const kuOpt = $('#kelompok_umur_id option:selected');
    const gender = $('#gender').val();

    const lombaNama = lombaOpt.data('nama') || '';
    const kuNama = kuOpt.data('nama') || '';
    let genderText = 'Putra';
    if (gender === 'P') genderText = 'Putri';
    else if (gender === 'Campuran') genderText = 'Pa & Pi';

    if (lombaNama) {
        let suggested = lombaNama;
        if (kuNama) suggested += ' ' + kuNama;
        suggested += ' ' + genderText;
        $('#nama_acara').val(suggested);
    }
}
</script>
<?= $this->endSection() ?>
