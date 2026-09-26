<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Jadwal & Acara Perlombaan</h4>
        <p class="text-muted small mb-0">Kelola susunan nomor acara, babak perlombaan, dan penugasan peserta</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form action="<?= base_url('event/auto-arrange-all') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin mengatur ulang pembagian Hit, Lintasan, dan Babak untuk SEMUA acara perlombaan secara otomatis?\n\n- Nomor Lari sprint (<= 400m dan 800m) dibagi maks 8 orang per Hit, diacak dan dipisahkan kota/kabupaten & sekolah.\n- Acara > 8 orang menjadi Babak Penyisihan.\n- Acara <= 8 orang menjadi Babak Final.')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-warning fw-bold text-dark d-inline-flex align-items-center gap-2 shadow-sm" title="Otomatiskan Hit & Lintasan Semua Acara">
                <i class="bi bi-shuffle"></i>
                <span>Atur Seri Semua Acara</span>
            </button>
        </form>
        <a href="<?= base_url('buku-acara') ?>" class="btn btn-secondary-custom d-inline-flex align-items-center gap-2">
            <i class="bi bi-book text-warning"></i>
            <span>Buku Acara</span>
        </a>
        <a href="<?= base_url('event/create') ?>" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Acara Baru</span>
        </a>
    </div>
</div>

<div class="card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark-custom datatable">
                <thead>
                    <tr>
                        <th width="50" class="text-center">No Acara</th>
                        <th>Nama Acara Perlombaan</th>
                        <th>Nomor Lomba</th>
                        <th>Kategori / KU</th>
                        <th class="text-center">Gender</th>
                        <th>Babak</th>
                        <th class="text-center">Peserta</th>
                        <th>Status</th>
                        <th class="text-center" width="160">Aksi</th>
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
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i><?= esc($ev['jadwal_hari'] ?: '-') ?>
                                    &bull; <i class="bi bi-clock me-1"></i><?= esc($ev['jadwal_waktu'] ?: '-') ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-blue"><?= esc($ev['nama_lomba'] ?? '-') ?></span>
                                <small class="text-secondary d-block"><?= esc($ev['satuan_hasil'] ?? 'Waktu') ?></small>
                            </td>
                            <td>
                                <?= esc($ev['nama_ku'] ?: 'Terbuka / Umum') ?>
                            </td>
                            <td class="text-center">
                                <?php if ($ev['gender'] === 'L'): ?>
                                    <span class="badge badge-blue">Putra (Male)</span>
                                <?php elseif ($ev['gender'] === 'P'): ?>
                                    <span class="badge badge-rose">Putri (Female)</span>
                                <?php else: ?>
                                    <span class="badge badge-purple">Campuran (Mixed)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $ev['babak'] === 'Final' ? 'badge-emerald' : 'badge-amber' ?>">
                                    <?= esc($ev['babak']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php 
                                $isEstafet = stripos($ev['nama_acara'] ?? '', 'estafet') !== false || stripos($ev['nama_lomba'] ?? '', 'estafet') !== false;
                                $unitLabel = $isEstafet ? 'Team' : 'Atlet';
                                ?>
                                <a href="<?= base_url('event/peserta/' . $ev['id']) ?>" class="btn btn-sm btn-outline-primary" title="Atur Peserta">
                                    <i class="bi bi-people-fill me-1"></i> <?= $ev['total_peserta'] ?> <?= $unitLabel ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($ev['status'] === 'Selesai'): ?>
                                    <span class="badge badge-purple">Selesai</span>
                                <?php elseif ($ev['status'] === 'Berlangsung'): ?>
                                    <span class="badge badge-amber">Berlangsung</span>
                                <?php else: ?>
                                    <span class="badge badge-blue">Belum Mulai</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('hasil/input/' . $ev['id']) ?>" class="btn btn-primary-gradient" title="Input Hasil Perlombaan">
                                        <i class="bi bi-stopwatch"></i> Hasil
                                    </a>
                                    <a href="<?= base_url('event/edit/' . $ev['id']) ?>" class="btn btn-outline-info" title="Edit Acara">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= base_url('event/delete/' . $ev['id']) ?>" class="btn btn-outline-danger btn-delete" data-title="Acara <?= esc($ev['nomor_acara']) . ': ' . esc($ev['nama_acara']) ?>" title="Hapus">
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
