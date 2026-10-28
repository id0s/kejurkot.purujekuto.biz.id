<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--text-main);">Kelola Pengguna & Admin Cabor</h4>
        <p class="text-muted small mb-0">Kelola akun akses sistem, role administrator, dan penugasan per Cabang Olahraga</p>
    </div>
    <a href="<?= base_url('users/create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-person-plus-fill"></i>
        <span>Tambah Pengguna</span>
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="ps-3">#</th>
                        <th>Pengguna</th>
                        <th>Role Akses</th>
                        <th>Cabang Olahraga (Cabor)</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th class="text-center pe-3" width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 34px; height: 34px; font-weight: 700; font-size: 13px; background-color: <?= $u['role'] === 'admin' ? '#0052CC' : ($u['role'] === 'admin_cabor' ? '#00875A' : '#6554C0') ?>;">
                                        <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: var(--text-main); font-size: 13.5px;">
                                            <?= esc($u['nama_lengkap']) ?>
                                        </div>
                                        <small class="text-muted font-monospace">@<?= esc($u['username']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-primary text-white px-2 py-1">
                                        <i class="bi bi-shield-lock-fill me-1"></i> Super Admin
                                    </span>
                                <?php elseif ($u['role'] === 'admin_cabor'): ?>
                                    <span class="badge bg-success text-white px-2 py-1">
                                        <i class="bi bi-person-badge-fill me-1"></i> Admin Cabor
                                    </span>
                                <?php elseif ($u['role'] === 'panitia'): ?>
                                    <span class="badge bg-warning text-dark px-2 py-1">
                                        <i class="bi bi-clipboard2-check-fill me-1"></i> Panitia
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white px-2 py-1">
                                        <i class="bi bi-stopwatch me-1"></i> <?= esc(ucfirst($u['role'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'admin_cabor' && !empty($u['cabor_nama'])): ?>
                                    <span class="badge bg-info bg-opacity-25 text-info-emphasis border border-info border-opacity-25 px-2 py-1">
                                        <i class="bi bi-trophy-fill me-1"></i> <?= esc($u['cabor_nama']) ?>
                                    </span>
                                <?php elseif ($u['role'] === 'admin'): ?>
                                    <span class="text-muted small">
                                        <i class="bi bi-asterisk me-1 text-primary"></i> Semua Cabor (Akses Penuh)
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-x-circle-fill me-1"></i> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= !empty($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '-' ?>
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('users/edit/' . $u['id']) ?>" class="btn btn-outline-secondary" title="Edit Akun">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <?php if ($u['id'] != session()->get('user_id')): ?>
                                        <a href="<?= base_url('users/toggle-status/' . $u['id']) ?>" class="btn btn-outline-<?= $u['is_active'] ? 'warning' : 'success' ?>" title="<?= $u['is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>" onclick="return confirm('Yakin ingin mengubah status akun ini?')">
                                            <i class="bi <?= $u['is_active'] ? 'bi-slash-circle' : 'bi-check-lg' ?>"></i>
                                        </a>
                                        <form action="<?= base_url('users/delete/' . $u['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user <?= esc($u['username']) ?>? Tindakan ini tidak dapat dibatalkan.')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus Akun">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
