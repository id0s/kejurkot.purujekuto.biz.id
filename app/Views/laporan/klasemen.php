<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #172B4D;">Klasemen Perolehan Medali</h4>
        <p class="text-muted small mb-0">Peringkat kontingen sekolah berdasarkan perolehan Medali Emas, Perak, dan Perunggu</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-secondary-custom" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Klasemen
        </button>
        <a href="<?= base_url('buku-acara') ?>" class="btn btn-primary-gradient">
            <i class="bi bi-book me-1"></i> Buku Acara
        </a>
    </div>
</div>

<div class="card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark-custom datatable">
                <thead>
                    <tr>
                        <th width="50" class="text-center">Rank</th>
                        <th>Sekolah / Kontingen</th>
                        <th>Jenjang</th>
                        <th class="text-center" width="100">
                            <span class="medal-gold d-inline-block px-3 py-1">Emas (1)</span>
                        </th>
                        <th class="text-center" width="100">
                            <span class="medal-silver d-inline-block px-3 py-1">Perak (2)</span>
                        </th>
                        <th class="text-center" width="100">
                            <span class="medal-bronze d-inline-block px-3 py-1">Perunggu (3)</span>
                        </th>
                        <th class="text-center" width="110">Total Medali</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($klasemen)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Belum ada medali yang tercatat. Silakan lakukan pencatatan hasil di menu <strong>Input Hasil</strong>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $rank = 1; foreach ($klasemen as $k): ?>
                            <tr>
                                <td class="text-center">
                                    <?php if ($rank === 1 && $k['total_medali'] > 0): ?>
                                        <span class="badge badge-amber fs-6"><i class="bi bi-trophy-fill"></i> 1</span>
                                    <?php elseif ($rank === 2 && $k['total_medali'] > 0): ?>
                                        <span class="badge badge-blue fs-6">2</span>
                                    <?php elseif ($rank === 3 && $k['total_medali'] > 0): ?>
                                        <span class="badge badge-purple fs-6">3</span>
                                    <?php else: ?>
                                        <span class="text-muted fw-bold"><?= $rank ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold" style="color: #172B4D;"><?= esc($k['nama_sekolah']) ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-blue"><?= esc($k['jenjang']) ?></span>
                                </td>
                                <td class="text-center fw-bold fs-5 text-warning"><?= $k['emas'] ?></td>
                                <td class="text-center fw-bold fs-5 text-secondary"><?= $k['perak'] ?></td>
                                <td class="text-center fw-bold fs-5 text-danger"><?= $k['perunggu'] ?></td>
                                <td class="text-center fw-bold fs-5 text-info"><?= $k['total_medali'] ?></td>
                            </tr>
                            <?php $rank++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
