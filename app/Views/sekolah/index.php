<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Daftar Sekolah / Kontingen</h4>
        <p class="text-muted small mb-0">Kelola data asal sekolah dan kontingen peserta kejuaraan</p>
    </div>
    <a href="<?= base_url('sekolah/create') ?>" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Sekolah</span>
    </a>
</div>

<div class="card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark-custom datatable">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Sekolah</th>
                        <th>Jenjang</th>
                        <th>Kontak & Telepon</th>
                        <th class="text-center">Total Atlet</th>
                        <th class="text-center" width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($sekolahs as $s): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td class="fw-bold text-white"><?= esc($s['nama']) ?></td>
                            <td>
                                <?php
                                $badgeClass = 'badge-blue';
                                if ($s['jenjang'] === 'PAUD/TK') $badgeClass = 'badge-amber';
                                elseif ($s['jenjang'] === 'SD/MI') $badgeClass = 'badge-emerald';
                                elseif ($s['jenjang'] === 'SMP/MTs') $badgeClass = 'badge-blue';
                                elseif ($s['jenjang'] === 'SMA/SMK/MA') $badgeClass = 'badge-purple';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= esc($s['jenjang']) ?></span>
                            </td>
                            <td>
                                <div><?= esc($s['kontak_person'] ?: '-') ?></div>
                                <small class="text-muted"><?= esc($s['no_telepon'] ?: '') ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-blue"><?= $s['total_atlet'] ?> Atlet</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('sekolah/edit/' . $s['id']) ?>" class="btn btn-outline-info" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('sekolah/delete/' . $s['id']) ?>" class="btn btn-outline-danger btn-delete" data-title="<?= esc($s['nama']) ?>" title="Hapus">
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
