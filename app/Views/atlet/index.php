<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #172B4D;">Daftar Atlet Terdaftar</h4>
        <p class="text-muted small mb-0">Total <?= count($atlets) ?> atlet terdaftar pada kejuaraan aktif</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= base_url('atlet/cetak-nomor-lomba') ?>" target="_blank" class="btn btn-warning fw-bold text-dark d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-printer-fill"></i>
            <span>Desain & Cetak Nomor Lomba</span>
        </a>
        <button type="button" class="btn btn-outline-warning fw-bold d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGenerateNoDada">
            <i class="bi bi-123 fs-5"></i>
            <span>Generate No Dada (Per Sekolah)</span>
        </button>
        <a href="<?= base_url('atlet/import') ?>" class="btn btn-secondary-custom d-inline-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-arrow-up text-success"></i>
            <span>Import Excel</span>
        </a>
        <a href="<?= base_url('atlet/create') ?>" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2">
            <i class="bi bi-person-plus-fill"></i>
            <span>Daftar Atlet Baru</span>
        </a>
    </div>
</div>

<!-- Filter Sekolah Card -->
<div class="card-custom mb-4 p-3">
    <form method="get" action="<?= base_url('atlet') ?>" class="row g-3 align-items-center">
        <div class="col-md-6 col-lg-5">
            <label class="form-label small text-muted mb-1">Filter Asal Sekolah / Kontingen</label>
            <select name="sekolah_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Semua Sekolah (<?= count($sekolahs) ?> Kontingen) --</option>
                <?php foreach ($sekolahs as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $selectedSekolah == $s['id'] ? 'selected' : '' ?>>
                        <?= esc($s['nama']) ?> (<?= esc($s['jenjang']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($selectedSekolah): ?>
            <div class="col-auto align-self-end">
                <a href="<?= base_url('atlet') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Reset Filter
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

<div class="card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark-custom datatable">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th width="90" class="text-center">No Dada</th>
                        <th>Nama Atlet</th>
                        <th>Asal Sekolah</th>
                        <th class="text-center">Gender</th>
                        <th>Tgl Lahir</th>
                        <th>Nomor Lomba Diikuti</th>
                        <th class="text-center" width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($atlets as $a): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td class="text-center" data-nodada="<?= esc($a['no_dada'] ?: '999999') ?>">
                                <?php if (!empty($a['no_dada'])): ?>
                                    <span class="badge badge-amber fw-bold fs-6 font-monospace btn-quick-nodada" style="cursor: pointer;" data-atlet-id="<?= $a['id'] ?>" data-atlet-name="<?= esc($a['nama']) ?>" data-no-dada="<?= esc($a['no_dada']) ?>" title="Klik untuk ubah No Dada">
                                        <?= esc($a['no_dada']) ?> <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary fw-bold fs-6 font-monospace btn-quick-nodada" style="cursor: pointer;" data-atlet-id="<?= $a['id'] ?>" data-atlet-name="<?= esc($a['nama']) ?>" data-no-dada="" title="Klik untuk isi No Dada">
                                        - <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold" style="color: #172B4D;"><?= esc($a['nama']) ?></div>
                                <?php if (!empty($a['catatan'])): ?>
                                    <small class="text-secondary"><?= esc($a['catatan']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= esc($a['nama_sekolah'] ?: 'Independen / Club') ?></div>
                                <small class="text-muted"><?= esc($a['jenjang'] ?: '') ?></small>
                            </td>
                            <td class="text-center">
                                <?php if ($a['gender'] === 'L'): ?>
                                    <span class="badge badge-blue">Putra (Male)</span>
                                <?php else: ?>
                                    <span class="badge badge-rose">Putri (Female)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= $a['tanggal_lahir'] ? esc($a['tanggal_lahir']) : '<span class="text-muted">-</span>' ?></div>
                                <?php if (!empty($a['nama_ku'])): ?>
                                    <span class="badge badge-purple small"><?= esc($a['nama_ku']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($atletEvents[$a['id']]) && !empty($atletEvents[$a['id']])): ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($atletEvents[$a['id']] as $ev): ?>
                                            <span class="badge badge-emerald small" title="Acara <?= $ev['nomor_acara'] ?>">
                                                <?= esc($ev['nama_acara']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small italic">Belum terdaftar di acara</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('atlet/edit/' . $a['id']) ?>" class="btn btn-outline-info" title="Edit Data & Nomor Lomba">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('atlet/delete/' . $a['id']) ?>" class="btn btn-outline-danger btn-delete" data-title="<?= esc($a['nama']) ?>" title="Hapus Atlet">
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

<!-- Modal Generate No Dada Per Sekolah -->
<div class="modal fade" id="modalGenerateNoDada" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #1e293b; color: #f8fafc; border: 1px solid rgba(255,255,255,0.15);">
            <form action="<?= base_url('atlet/generate-no-dada') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold text-warning d-flex align-items-center gap-2">
                        <i class="bi bi-123 fs-4"></i> Generate Nomor Dada Berkesinambungan (Per Sekolah)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-4 text-info"></i>
                        <div>
                            Nomor dada atlet akan dibuat berurutan dan berkesinambungan per sekolah.<br>
                            <strong>Contoh:</strong> SMP 8 dapat nomor 1 - 20, lalu SMP 9 lanjut nomor 21 - 30, dst.
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-white">Nomor Awal (Mulai Dari):</label>
                            <input type="number" name="start_number" class="form-control fw-bold text-warning font-monospace fs-5" value="1" min="1" required>
                            <small class="text-muted">Nomor dada pertama untuk kontingen urutan pertama.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-white">Format Angka Nomor Dada:</label>
                            <select name="number_format" class="form-select fw-semibold">
                                <option value="pad3" selected>3 Digit (001, 002, ... 020, 021 ...) [Standar Lomba]</option>
                                <option value="standard">Angka Biasa (1, 2, 3, ... 20, 21 ...)</option>
                                <option value="pad4">4 Digit (0001, 0002, ... 0020 ...)</option>
                            </select>
                            <small class="text-muted">Format tampilan angka nomor dada atlet.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-white">Urutan Sekolah / Kontingen:</label>
                            <select name="sort_sekolah" class="form-select fw-semibold">
                                <option value="nama_asc" selected>Nama Sekolah (A - Z)</option>
                                <option value="jenjang_nama">Berdasarkan Jenjang (TK -> SD -> SMP -> SMA -> Umum) lalu Nama</option>
                            </select>
                            <small class="text-muted">Urutan sekolah yang mendapatkan blok nomor dada.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-white">Urutan Atlet di dalam Tiap Sekolah:</label>
                            <select name="sort_atlet" class="form-select fw-semibold">
                                <option value="nama_asc" selected>Nama Atlet (A - Z)</option>
                                <option value="gender_nama">Gender (Putra lalu Putri) lalu Nama</option>
                                <option value="ku_nama">Kelompok Umur (Usia Muda ke Tua) lalu Nama</option>
                            </select>
                            <small class="text-muted">Urutan pembagian nomor dada antar atlet di sekolah yang sama.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-white">Cakupan Sekolah:</label>
                            <select name="sekolah_scope" class="form-select fw-semibold">
                                <option value="all" selected>Semua Sekolah / Kontingen (<?= count($sekolahs) ?> Kontingen)</option>
                                <?php foreach ($sekolahs as $s): ?>
                                    <option value="<?= $s['id'] ?>">Khusus: <?= esc($s['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Pilih apakah seluruh sekolah atau hanya satu sekolah tertentu.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-white">Mode Timpa Nomor Dada:</label>
                            <select name="overwrite_mode" class="form-select fw-semibold">
                                <option value="all" selected>Timpa Semua Atlet (Rekomendasi agar Berkesinambungan)</option>
                                <option value="empty_only">Hanya Atlet yang Belum Punya Nomor Dada</option>
                            </select>
                            <small class="text-muted">Pilih apakah memperbarui seluruh nomor dada atau hanya yang kosong.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark px-4 shadow-sm">
                        <i class="bi bi-play-fill me-1"></i> Mulai Generate Nomor Dada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Cepat No Dada -->
<div class="modal fade" id="modalQuickNoDada" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background: #1e293b; color: #f8fafc; border: 1px solid rgba(255,255,255,0.15);">
            <div class="modal-header border-secondary py-2">
                <h6 class="modal-title fw-bold text-warning">
                    <i class="bi bi-pencil-square me-1"></i> Ubah No Dada
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="small text-muted mb-2" id="quickNoDadaAtletName">-</p>
                <input type="hidden" id="quickNoDadaAtletId">
                <div class="mb-2">
                    <input type="text" id="quickNoDadaInput" class="form-control form-control-lg text-center fw-bold font-monospace text-warning" placeholder="Contoh: 25">
                </div>
                <small class="text-muted d-block text-center">Kosongkan jika ingin menghapus nomor dada.</small>
            </div>
            <div class="modal-footer border-secondary py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-success fw-bold" id="btnSaveQuickNoDada">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $(document).on('click', '.btn-quick-nodada', function(e) {
        e.preventDefault();
        var atletId = $(this).data('atlet-id');
        var atletName = $(this).data('atlet-name');
        var noDada = $(this).data('no-dada');

        $('#quickNoDadaAtletId').val(atletId);
        $('#quickNoDadaAtletName').text(atletName);
        $('#quickNoDadaInput').val(noDada);

        var modal = new bootstrap.Modal(document.getElementById('modalQuickNoDada'));
        modal.show();
        setTimeout(function() { $('#quickNoDadaInput').focus().select(); }, 400);
    });

    $('#btnSaveQuickNoDada').on('click', function() {
        var atletId = $('#quickNoDadaAtletId').val();
        var noDada  = $('#quickNoDadaInput').val().trim();
        var btn = $(this);
        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: '<?= base_url('atlet/update-no-dada') ?>',
            type: 'POST',
            data: {
                atlet_id: atletId,
                no_dada: noDada,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).text('Simpan');
                if (res.status === 'success') {
                    var target = $('.btn-quick-nodada[data-atlet-id="' + atletId + '"]');
                    target.data('no-dada', res.no_dada || '');
                    if (res.no_dada) {
                        target.removeClass('badge-secondary').addClass('badge-amber').html(res.no_dada + ' <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>');
                    } else {
                        target.removeClass('badge-amber').addClass('badge-secondary').html('- <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>');
                    }
                    bootstrap.Modal.getInstance(document.getElementById('modalQuickNoDada')).hide();
                } else {
                    alert(res.message || 'Gagal menyimpan.');
                }
            },
            error: function() {
                btn.prop('disabled', false).text('Simpan');
                alert('Terjadi kesalahan jaringan.');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
