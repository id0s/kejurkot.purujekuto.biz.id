<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-white mb-1"><?= $atlet ? 'Edit Data Atlet' : 'Pendaftaran Atlet Baru' ?></h4>
                <p class="text-muted small mb-0">Input nomor dada, identitas atlet, kontingen, dan pilih acara lomba melalui dropdown</p>
            </div>
            <a href="<?= base_url('atlet') ?>" class="btn btn-secondary-custom">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <form action="<?= base_url('atlet/save' . ($atlet ? '/' . $atlet['id'] : '')) ?>" method="post">
            <?= csrf_field() ?>

            <!-- Card 1: Identitas Atlet & Kontingen -->
            <div class="card-custom mb-4">
                <div class="card-header">
                    <i class="bi bi-person-badge-fill me-2 text-primary"></i> Identitas Atlet & Kontingen
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                Nomor Dada (Manual) <span class="badge badge-amber ms-1">Manual</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent" style="border-color: var(--border-color); color: var(--accent-amber);">
                                    <i class="bi bi-hash"></i>
                                </span>
                                <input type="text" name="no_dada" class="form-control" value="<?= old('no_dada', $atlet['no_dada'] ?? '') ?>" placeholder="Misal: 001, 692, 1011">
                            </div>
                            <small class="text-muted">Nomor dada fisik atlet yang disematkan di kostum.</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nama Lengkap Atlet <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?= old('nama', $atlet['nama'] ?? '') ?>" placeholder="Nama lengkap atlet sesuai akte/kartu pelajar" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label">
                                Asal Sekolah / Kontingen <span class="text-danger">*</span>
                                <span class="badge badge-blue ms-1">Cari / Ketik Nama</span>
                            </label>
                            <select name="sekolah_id" id="sekolah_id" class="form-select" required>
                                <option value="">-- Cari atau Ketik Asal Sekolah / Kontingen --</option>
                                <?php foreach ($sekolahs as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= old('sekolah_id', $atlet['sekolah_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                        <?= esc($s['nama']) ?><?= !empty($s['jenjang']) ? ' (' . esc($s['jenjang']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted"><i class="bi bi-search me-1"></i>Ketik nama sekolah untuk mencari cepat, atau ketik nama baru jika belum terdaftar.</small>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Jenis Kelamin (Gender) <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-select" required>
                                <option value="L" <?= old('gender', $atlet['gender'] ?? '') === 'L' ? 'selected' : '' ?>>Putra / Male (L)</option>
                                <option value="P" <?= old('gender', $atlet['gender'] ?? '') === 'P' ? 'selected' : '' ?>>Putri / Female (P)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir Atlet</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" value="<?= old('tanggal_lahir', $atlet['tanggal_lahir'] ?? '') ?>">
                            <small class="text-muted">Pilih tanggal lahir, Kelompok Umur (KU) akan terhitung otomatis di samping.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Kelompok Umur (KU) <span class="badge badge-emerald ms-1" id="kuBadge">Dinamis Otomatis</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent" style="border-color: var(--border-color); color: var(--accent-cyan);">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                </span>
                                <input type="text" id="ku_preview" class="form-control" readonly value="Otomatis terisi saat tanggal lahir dipilih..." style="background-color: rgba(255,255,255,0.04); color: #38bdf8; font-weight: 600;">
                                <input type="hidden" name="kelompok_umur_id" id="kelompok_umur_id" value="<?= old('kelompok_umur_id', $atlet['kelompok_umur_id'] ?? '') ?>">
                            </div>
                            <small class="text-muted" id="age_detail_hint">Sistem menentukan KU atlet otomatis berdasarkan tanggal lahir.</small>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan medis, kontak darurat, atau keterangan lain..."><?= old('catatan', $atlet['catatan'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Card 2: Pendaftaran Multi-Event Lomba (Dropdown Multi-Select) -->
            <div class="card-custom mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-trophy-fill me-2 text-warning"></i>
                        <span>Pilihan Nomor Perlombaan (Multi-Event)</span>
                    </div>
                    <span class="badge badge-blue" id="selectedEventCount">0 Acara Dipilih</span>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($events)): ?>
                        <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning-emphasis">
                            <i class="bi bi-exclamation-circle me-1"></i> Belum ada nomor acara lomba yang terdaftar pada kejuaraan ini.
                        </div>
                    <?php else: ?>
                        <label class="form-label fw-semibold">
                            Pilih Nomor Acara / Lomba yang Diikuti
                            <span class="badge badge-secondary ms-1">Bisa Pilih Lebih dari 1</span>
                        </label>
                        <select name="event_ids[]" id="event_ids" class="form-select" multiple="multiple" data-placeholder="-- Klik atau ketik untuk memilih satu atau beberapa acara lomba --">
                            <?php foreach ($events as $ev): ?>
                                <?php $isSelected = in_array($ev['id'], old('event_ids', $registeredEventIds)); ?>
                                <option value="<?= $ev['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    Acara <?= esc($ev['nomor_acara']) ?>: <?= esc($ev['nama_acara']) ?> (<?= esc($ev['babak']) ?> &bull; <?= esc($ev['gender']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle me-1"></i> Klik untuk membuka dropdown atau ketik nama lomba untuk mencari cepat. Anda dapat memilih lebih dari 1 acara lomba, dan menghapus pilihan dengan menekan tanda silang (x).
                        </small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit Button Footer -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="<?= base_url('atlet') ?>" class="btn btn-secondary-custom">Batal</a>
                <button type="submit" class="btn btn-primary-gradient px-4">
                    <i class="bi bi-check-circle-fill me-1"></i> Simpan Data Atlet
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    // 1. Inisialisasi Select2 Kontingen / Sekolah (Bisa search cepat & bisa input nama baru)
    $('#sekolah_id').select2({
        placeholder: '-- Cari atau Ketik Asal Sekolah / Kontingen --',
        allowClear: true,
        tags: true,
        width: '100%'
    });

    // 2. Inisialisasi Select2 Multi-Event (Dropdown biasa dengan multi-select)
    $('#event_ids').select2({
        placeholder: '-- Klik atau ketik untuk memilih acara lomba --',
        allowClear: true,
        closeOnSelect: false,
        width: '100%'
    });

    // Update indikator badge jumlah acara dipilih
    function updateEventCount() {
        const selected = $('#event_ids').val();
        const count = selected ? selected.length : 0;
        $('#selectedEventCount').text(count + ' Acara Dipilih');
    }
    $('#event_ids').on('change', updateEventCount);
    updateEventCount();

    // 3. Kalkulasi Dinamis Kelompok Umur (KU) Otomatis dari Tanggal Lahir
    const kuData = <?= json_encode($kus) ?>;

    function updateDynamicKU() {
        const birthVal = $('#tanggal_lahir').val();
        if (!birthVal) {
            $('#kelompok_umur_id').val('');
            $('#ku_preview').val('Bebas / Terbuka (Tanggal lahir belum diisi)');
            $('#age_detail_hint').text('Pilih tanggal lahir untuk menentukan kelompok umur secara otomatis.');
            $('#kuBadge').text('Menunggu Tgl Lahir').removeClass('badge-emerald').addClass('badge-secondary');
            return;
        }

        const birthDate = new Date(birthVal);
        const refYear = 2026; // Acuan tahun kejuaraan
        let age = refYear - birthDate.getFullYear();
        if (age < 0) age = 0;

        let matchedKU = null;
        if (kuData && kuData.length > 0) {
            for (let i = 0; i < kuData.length; i++) {
                const min = parseInt(kuData[i].usia_min);
                const max = parseInt(kuData[i].usia_max);
                if (age >= min && age <= max) {
                    matchedKU = kuData[i];
                    break;
                }
            }
            // Jika usia lebih muda dari rentang terendah
            if (!matchedKU && age < parseInt(kuData[0].usia_min)) {
                matchedKU = kuData[0];
            }
            // Jika usia lebih tua dari rentang tertinggi
            if (!matchedKU && age > parseInt(kuData[kuData.length - 1].usia_max)) {
                matchedKU = kuData[kuData.length - 1];
            }
        }

        if (matchedKU) {
            $('#kelompok_umur_id').val(matchedKU.id);
            $('#ku_preview').val(matchedKU.nama + ' (Usia: ' + age + ' Tahun)');
            $('#age_detail_hint').html('<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Otomatis masuk <strong>' + matchedKU.nama + '</strong> (Rentang ' + matchedKU.usia_min + ' - ' + matchedKU.usia_max + ' tahun).</span>');
            $('#kuBadge').text('Otomatis: ' + matchedKU.nama).removeClass('badge-secondary').addClass('badge-emerald');
        } else {
            $('#kelompok_umur_id').val('');
            $('#ku_preview').val('Umum / Terbuka (Usia: ' + age + ' Tahun)');
            $('#age_detail_hint').text('Usia atlet terhitung: ' + age + ' tahun.');
            $('#kuBadge').text('Umum').removeClass('badge-emerald').addClass('badge-secondary');
        }
    }

    $('#tanggal_lahir').on('change input', updateDynamicKU);
    if ($('#tanggal_lahir').val()) {
        updateDynamicKU();
    }
});
</script>
<?= $this->endSection() ?>
