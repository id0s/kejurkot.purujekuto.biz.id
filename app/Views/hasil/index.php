<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Input & Rekap Hasil Perlombaan</h4>
        <p class="text-muted small mb-0">Pencatatan waktu, jarak, status perlombaan, dan penentuan juara per nomor acara</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= base_url('buku-acara') ?>" class="btn btn-secondary-custom">
            <i class="bi bi-book me-1 text-warning"></i> Buku Acara
        </a>
        <a href="<?= base_url('laporan/klasemen') ?>" class="btn btn-primary-gradient">
            <i class="bi bi-trophy-fill me-1"></i> Klasemen Medali
        </a>
    </div>
</div>

<div class="card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark-custom datatable">
                <thead>
                    <tr>
                        <th width="50" class="text-center">Acara</th>
                        <th>Nama Acara Perlombaan</th>
                        <th>Nomor Lomba</th>
                        <th>Kategori</th>
                        <th class="text-center">Peserta</th>
                        <th class="text-center">Status Hasil</th>
                        <th class="text-center" width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                        <tr>
                            <td class="text-center fw-bold text-info fs-5">
                                <?= esc($ev['nomor_acara']) ?>
                            </td>
                            <td>
                                <div class="fw-bold text-white"><?= esc($ev['nama_acara']) ?></div>
                                <small class="text-muted"><?= esc($ev['babak']) ?> &bull; <?= esc($ev['jadwal_waktu'] ?: '-') ?></small>
                            </td>
                            <td>
                                <span class="badge badge-blue"><?= esc($ev['nama_lomba']) ?></span>
                                <small class="text-secondary d-block"><?= esc($ev['satuan_hasil']) ?></small>
                            </td>
                            <td><?= esc($ev['nama_ku'] ?: 'Umum') ?></td>
                            <td class="text-center">
                                <span class="badge badge-purple"><?= $ev['total_peserta'] ?> Atlet</span>
                            </td>
                            <td class="text-center">
                                <?php if ($ev['total_hasil'] > 0 && $ev['total_hasil'] >= $ev['total_peserta']): ?>
                                    <span class="badge badge-emerald">
                                        <i class="bi bi-check-all"></i> Lengkap (<?= $ev['total_hasil'] ?>/<?= $ev['total_peserta'] ?>)
                                    </span>
                                <?php elseif ($ev['total_hasil'] > 0): ?>
                                    <span class="badge badge-amber">
                                        <i class="bi bi-hourglass-split"></i> Sebagian (<?= $ev['total_hasil'] ?>/<?= $ev['total_peserta'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-blue">Belum Diinput</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="<?= base_url('hasil/input/' . $ev['id']) ?>" class="btn btn-sm btn-primary-gradient flex-grow-1">
                                        <i class="bi bi-stopwatch-fill me-1"></i> Input
                                    </a>
                                    <?php if ($ev['total_hasil'] > 0): ?>
                                        <a href="<?= base_url('hasil/cetak-hasil/' . $ev['id']) ?>" target="_blank" class="btn btn-sm btn-info text-white" title="Cetak Lembar Hasil Perlombaan (Meet Manager)">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('hasil/form-lapangan/' . $ev['id']) ?>" target="_blank" class="btn btn-sm btn-warning text-dark fw-bold" title="Cetak Blangko Form Lapangan / Lembar Juri">
                                        <i class="bi bi-printer-fill"></i>
                                    </a>
                                    <a href="<?= base_url('piagam?event_id=' . $ev['id']) ?>" class="btn btn-sm btn-secondary-custom" title="Lihat & Cetak Piagam Penghargaan">
                                        <i class="bi bi-award-fill text-warning"></i>
                                    </a>
                                    <?php if (strcasecmp($ev['babak'], 'Final') === 0 && $ev['total_peserta'] > 0): ?>
                                        <a href="<?= base_url('hasil/reset-final/' . $ev['id']) ?>" class="btn btn-sm btn-outline-danger" title="Reset Peserta Final (0 Atlet) - Jaga-jaga Salah Klik/Input" onclick="return confirm('Reset peserta Babak Final Acara <?= $ev['nomor_acara'] ?> (<?= esc($ev['nama_acara']) ?>)?\n\nPeserta babak final akan dikosongkan kembali menjadi 0 atlet.')">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </a>
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
