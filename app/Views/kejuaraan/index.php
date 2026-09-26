<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Daftar Kejuaraan</h4>
        <p class="text-muted small mb-0">Kelola daftar event kejuaraan kota dan status aktifnya</p>
    </div>
    <a href="<?= base_url('kejuaraan/create') ?>" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Kejuaraan</span>
    </a>
</div>

<div class="card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark-custom datatable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Kejuaraan</th>
                        <th>Tahun</th>
                        <th>Tempat & Tanggal</th>
                        <th>Penyelenggara</th>
                        <th>Status</th>
                        <th class="text-center" width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($kejuaraans as $k): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold text-white"><?= esc($k['nama']) ?></div>
                                <small class="text-muted"><?= esc($k['deskripsi'] ? word_limiter($k['deskripsi'], 8) : '') ?></small>
                            </td>
                            <td><span class="badge badge-blue"><?= esc($k['tahun']) ?></span></td>
                            <td>
                                <div><i class="bi bi-geo-alt text-danger me-1"></i><?= esc($k['tempat']) ?></div>
                                <small class="text-muted"><?= esc($k['tanggal_mulai']) ?> s/d <?= esc($k['tanggal_selesai']) ?></small>
                            </td>
                            <td><?= esc($k['penyelenggara'] ?? '-') ?></td>
                            <td>
                                <?php if ($k['status'] === 'Aktif'): ?>
                                    <span class="badge badge-emerald"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                                <?php elseif ($k['status'] === 'Selesai'): ?>
                                    <span class="badge badge-purple">Selesai</span>
                                <?php else: ?>
                                    <span class="badge badge-amber">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <?php if ($k['status'] !== 'Aktif'): ?>
                                        <a href="<?= base_url('kejuaraan/activate/' . $k['id']) ?>" class="btn btn-outline-success" title="Set sebagai Aktif">
                                            <i class="bi bi-play-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('kejuaraan/edit/' . $k['id']) ?>" class="btn btn-outline-info" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('kejuaraan/delete/' . $k['id']) ?>" class="btn btn-outline-danger btn-delete" data-title="<?= esc($k['nama']) ?>" title="Hapus">
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

<?= $this->endSection() ?>
