<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Daftar Nomor Lomba</h4>
        <p class="text-muted small mb-0">Kelola nomor cabang olahraga atletik (Lari, Lompat, Lempar, Estafet)</p>
    </div>
    <button class="btn btn-primary-gradient d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalLomba" onclick="resetFormLomba()">
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Nomor Lomba</span>
    </button>
</div>

<div class="card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark-custom datatable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Nomor Lomba</th>
                        <th>Kategori</th>
                        <th>Penilaian Hasil</th>
                        <th>Keterangan</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($lombas as $l): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td class="fw-bold text-white"><?= esc($l['nama']) ?></td>
                            <td>
                                <?php
                                $badgeClass = 'badge-blue';
                                if ($l['kategori'] === 'Lari') $badgeClass = 'badge-blue';
                                elseif ($l['kategori'] === 'Lompat') $badgeClass = 'badge-emerald';
                                elseif ($l['kategori'] === 'Lempar') $badgeClass = 'badge-amber';
                                elseif ($l['kategori'] === 'Estafet') $badgeClass = 'badge-purple';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= esc($l['kategori']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-blue">
                                    <i class="bi <?= $l['satuan_hasil'] === 'Waktu' ? 'bi-stopwatch' : 'bi-rulers' ?>"></i>
                                    <?= esc($l['satuan_hasil']) ?> <?= $l['satuan_hasil'] === 'Waktu' ? '(Detik / Menit)' : '(Meter)' ?>
                                </span>
                            </td>
                            <td class="text-muted"><small><?= esc($l['keterangan'] ?: '-') ?></small></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" title="Edit" onclick='editLomba(<?= json_encode($l) ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="<?= base_url('nomor-lomba/delete/' . $l['id']) ?>" class="btn btn-outline-danger btn-delete" data-title="<?= esc($l['nama']) ?>" title="Hapus">
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

<!-- Modal Form Nomor Lomba -->
<div class="modal fade" id="modalLomba" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('nomor-lomba/save') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="lomba_id">

                <div class="modal-header">
                    <h5 class="modal-title text-white" id="modalLombaTitle">Tambah Nomor Lomba</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Nomor Lomba <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="lomba_nama" class="form-control" placeholder="Misal: 100 m atau Lompat Jauh" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" id="lomba_kategori" class="form-select" required>
                                <option value="Lari">Lari</option>
                                <option value="Lompat">Lompat</option>
                                <option value="Lempar">Lempar</option>
                                <option value="Estafet">Estafet</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Satuan Penilaian <span class="text-danger">*</span></label>
                            <select name="satuan_hasil" id="lomba_satuan" class="form-select" required>
                                <option value="Waktu">Waktu (Lari/Estafet)</option>
                                <option value="Jarak">Jarak (Lompat/Lempar)</option>
                                <option value="Poin">Poin</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea name="keterangan" id="lomba_keterangan" class="form-control" rows="2" placeholder="Catatan aturan perlombaan..."></textarea>
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
function resetFormLomba() {
    $('#modalLombaTitle').text('Tambah Nomor Lomba');
    $('#lomba_id').val('');
    $('#lomba_nama').val('');
    $('#lomba_kategori').val('Lari');
    $('#lomba_satuan').val('Waktu');
    $('#lomba_keterangan').val('');
}

function editLomba(data) {
    $('#modalLombaTitle').text('Edit Nomor Lomba');
    $('#lomba_id').val(data.id);
    $('#lomba_nama').val(data.nama);
    $('#lomba_kategori').val(data.kategori);
    $('#lomba_satuan').val(data.satuan_hasil);
    $('#lomba_keterangan').val(data.keterangan);
    const m = new bootstrap.Modal(document.getElementById('modalLomba'));
    m.show();
}
</script>
<?= $this->endSection() ?>
