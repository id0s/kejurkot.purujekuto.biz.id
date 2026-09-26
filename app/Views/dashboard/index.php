<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Jira Project Header Gadget -->
<div class="card-custom mb-4 p-4" style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: 4px;">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 4px; background: #0052CC; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 1px 3px rgba(9,30,66,0.2);">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-emerald">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i> Active Project
                    </span>
                    <span class="text-muted" style="font-size: 12px;">Kejurwil Atletik Kota Pekalongan &bull; Tahun <?= date('Y') ?></span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #172B4D; font-size: 20px; letter-spacing: -0.3px;">
                    <?= esc($activeKejuaraan['nama'] ?? 'Kejuaraan Wilayah Atletik') ?>
                </h3>
                <p class="text-muted mb-0 small mt-1">
                    <i class="bi bi-geo-alt me-1 text-danger"></i> <?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan') ?>
                    &nbsp;&bull;&nbsp;
                    <i class="bi bi-calendar3 me-1 text-primary"></i> <?= esc($activeKejuaraan['tanggal_mulai'] ?? '-') ?> s/d <?= esc($activeKejuaraan['tanggal_selesai'] ?? '-') ?>
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= base_url('atlet/import') ?>" class="btn btn-secondary-custom d-inline-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-excel text-success"></i>
                <span>Import Atlet</span>
            </a>
            <a href="<?= base_url('buku-acara') ?>" class="btn btn-secondary-custom d-inline-flex align-items-center gap-1">
                <i class="bi bi-printer"></i>
                <span>Buku Acara</span>
            </a>
            <a href="<?= base_url('atlet/create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-1">
                <i class="bi bi-plus-lg"></i>
                <span>Daftar Atlet</span>
            </a>
        </div>
    </div>
</div>

<!-- 4 Top Metric Gadgets -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-label">Total Atlet Terdaftar</span>
                    <div class="stat-val"><?= number_format($totalAtlet) ?></div>
                    <small class="text-muted d-block mt-1">
                        <span class="fw-semibold text-primary"><?= $atletPutra ?> Putra</span> &bull; <span class="fw-semibold" style="color: #FF5630;"><?= $atletPutri ?> Putri</span>
                    </small>
                </div>
                <div class="stat-icon" style="background: #DEEBFF; color: #0052CC;">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-label">Sekolah / Kontingen</span>
                    <div class="stat-val"><?= number_format($totalSekolah) ?></div>
                    <small class="text-muted d-block mt-1">SD, SMP, SMA & Klub</small>
                </div>
                <div class="stat-icon" style="background: #E3FCEF; color: #006644;">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-label">Nomor Acara Lomba</span>
                    <div class="stat-val"><?= number_format($totalEvent) ?></div>
                    <small class="text-muted d-block mt-1">Penyisihan & Final</small>
                </div>
                <div class="stat-icon" style="background: #FFF0B3; color: #172B4D;">
                    <i class="bi bi-calendar2-event-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-label">Hasil Tercatat</span>
                    <div class="stat-val"><?= number_format($totalHasilCount) ?></div>
                    <small class="text-muted d-block mt-1">Waktu / Jarak Tersimpan</small>
                </div>
                <div class="stat-icon" style="background: #EAE6FF; color: #403294;">
                    <i class="bi bi-stopwatch-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Jira Dashboard Charts Row -->
<div class="row g-3 mb-4">
    <!-- Chart: Kelompok Umur -->
    <div class="col-lg-8">
        <div class="card-custom h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-bar-chart-fill me-2 text-primary"></i> Distribusi Atlet per Kelompok Umur (KU)
                </span>
                <span class="badge badge-blue">Kategori Usia</span>
            </div>
            <div class="card-body">
                <div style="height: 260px;">
                    <canvas id="kuChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart: Gender Breakdown -->
    <div class="col-lg-4">
        <div class="card-custom h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-pie-chart-fill me-2 text-primary"></i> Rasio Gender Atlet
                </span>
                <span class="badge badge-secondary">Gender</span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="height: 190px; width: 100%;">
                    <canvas id="genderChart"></canvas>
                </div>
                <div class="d-flex justify-content-center gap-4 mt-3 w-100">
                    <div class="text-center">
                        <div class="d-flex align-items-center gap-1 justify-content-center">
                            <span style="width: 10px; height: 10px; border-radius: 2px; background: #0052CC; display: inline-block;"></span>
                            <span class="text-muted" style="font-size: 12px;">Putra</span>
                        </div>
                        <strong class="d-block mt-1" style="font-size: 16px; color: #172B4D;"><?= $atletPutra ?></strong>
                    </div>
                    <div class="text-center">
                        <div class="d-flex align-items-center gap-1 justify-content-center">
                            <span style="width: 10px; height: 10px; border-radius: 2px; background: #FF5630; display: inline-block;"></span>
                            <span class="text-muted" style="font-size: 12px;">Putri</span>
                        </div>
                        <strong class="d-block mt-1" style="font-size: 16px; color: #172B4D;"><?= $atletPutri ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row: Jira Issue Filter Tables -->
<div class="row g-3">
    <!-- Recent Events List -->
    <div class="col-lg-7">
        <div class="card-custom h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-calendar-check me-2 text-primary"></i> Acara & Jadwal Perlombaan Terkini
                </span>
                <a href="<?= base_url('event') ?>" class="btn btn-secondary-custom btn-sm" style="font-size: 11px;">
                    Lihat Semua (<?= $totalEvent ?>) &rarr;
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="70" class="text-center">Acara</th>
                                <th>Nama Perlombaan</th>
                                <th>Kategori / KU</th>
                                <th class="text-center">Babak</th>
                                <th class="text-center" width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentEvents)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada acara lomba terdaftar</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentEvents as $ev): ?>
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge badge-blue font-monospace">
                                                #<?= esc($ev['nomor_acara']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold" style="color: #172B4D;"><?= esc($ev['nama_acara']) ?></div>
                                            <small class="text-muted"><?= esc($ev['nama_lomba']) ?></small>
                                        </td>
                                        <td>
                                            <div>
                                                <?php if ($ev['gender'] === 'L'): ?>
                                                    <span class="badge badge-blue">Putra</span>
                                                <?php elseif ($ev['gender'] === 'P'): ?>
                                                    <span class="badge badge-rose">Putri</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Campuran</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?= esc($ev['nama_ku'] ?? 'Umum') ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $ev['babak'] === 'Final' ? 'badge-emerald' : 'badge-amber' ?>">
                                                <?= esc($ev['babak']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('hasil/input/' . $ev['id']) ?>" class="btn btn-action" title="Input Hasil">
                                                <i class="bi bi-stopwatch"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Klasemen Medali -->
    <div class="col-lg-5">
        <div class="card-custom h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-award-fill me-2 text-warning"></i> Papan Medali Teratas
                </span>
                <a href="<?= base_url('laporan/klasemen') ?>" class="btn btn-secondary-custom btn-sm" style="font-size: 11px;">
                    Klasemen Lengkap &rarr;
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40" class="text-center">#</th>
                            <th>Sekolah / Kontingen</th>
                            <th class="text-center" width="45" title="Emas"><span class="medal-gold">E</span></th>
                            <th class="text-center" width="45" title="Perak"><span class="medal-silver">P</span></th>
                            <th class="text-center" width="45" title="Perunggu"><span class="medal-bronze">B</span></th>
                            <th class="text-center" width="45">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topKlasemen)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada medali tercatat</td>
                            </tr>
                        <?php else: ?>
                            <?php $r = 1; foreach ($topKlasemen as $k): ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold"><?= $r++ ?></td>
                                    <td>
                                        <div class="fw-semibold" style="color: #172B4D;"><?= esc($k['nama_sekolah']) ?></div>
                                    </td>
                                    <td class="text-center fw-bold" style="color: #B78103;"><?= $k['emas'] ?></td>
                                    <td class="text-center fw-bold text-secondary"><?= $k['perak'] ?></td>
                                    <td class="text-center fw-bold" style="color: #BF2600;"><?= $k['perunggu'] ?></td>
                                    <td class="text-center fw-bold text-primary"><?= $k['total_medali'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    // 1. Chart Kelompok Umur (Atlassian Blue Theme)
    const kuLabels = <?= json_encode(array_column($kuStats, 'nama')) ?>;
    const kuData = <?= json_encode(array_column($kuStats, 'count')) ?>;

    const ctxKU = document.getElementById('kuChart');
    if (ctxKU) {
        new Chart(ctxKU, {
            type: 'bar',
            data: {
                labels: kuLabels,
                datasets: [{
                    label: 'Jumlah Atlet',
                    data: kuData,
                    backgroundColor: '#0052CC',
                    borderRadius: 3,
                    barThickness: 24,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#172B4D',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        cornerRadius: 3,
                        padding: 10
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#5E6C84', font: { size: 11, weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#EBECF0', borderDash: [3, 3] },
                        ticks: { color: '#5E6C84', precision: 0, font: { size: 11 } }
                    }
                }
            }
        });
    }

    // 2. Chart Gender Ratio (Jira Blue & Coral)
    const ctxGender = document.getElementById('genderChart');
    if (ctxGender) {
        new Chart(ctxGender, {
            type: 'doughnut',
            data: {
                labels: ['Putra (L)', 'Putri (P)'],
                datasets: [{
                    data: [<?= $atletPutra ?>, <?= $atletPutri ?>],
                    backgroundColor: ['#0052CC', '#FF5630'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#172B4D',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        cornerRadius: 3,
                        padding: 8
                    }
                },
                cutout: '72%'
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
