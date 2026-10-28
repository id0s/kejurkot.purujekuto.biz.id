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

<?php 
$countCompleted = 0;
$countPending   = 0;
foreach ($events as $ev) {
    $isSelesai = ($ev['status'] === 'Selesai' || ($ev['total_hasil'] > 0 && $ev['total_hasil'] >= $ev['total_peserta']));
    if ($isSelesai) {
        $countCompleted++;
    } else {
        $countPending++;
    }
}
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="btn-group shadow-sm" role="group" id="filterStatusGroup">
        <button type="button" class="btn btn-sm btn-outline-primary active px-3" data-filter="all">
            <i class="bi bi-grid-fill me-1"></i> Semua Acara (<?= count($events) ?>)
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning px-3" data-filter="pending">
            <i class="bi bi-stopwatch me-1"></i> Perlu Diinput / Berlangsung (<?= $countPending ?>)
        </button>
        <button type="button" class="btn btn-sm btn-outline-success px-3" data-filter="completed">
            <i class="bi bi-check-circle-fill me-1"></i> Sudah Selesai (<?= $countCompleted ?>)
        </button>
    </div>
    <span class="text-muted small">
        <i class="bi bi-info-circle me-1"></i> Acara yang sudah ditandai <strong>Selesai</strong> dapat disembunyikan menggunakan tab filter di samping.
    </span>
</div>

<div class="card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark-custom datatable" id="tableHasilIndex">
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
                    <?php foreach ($events as $ev): 
                        $isSelesai = ($ev['status'] === 'Selesai' || ($ev['total_hasil'] > 0 && $ev['total_hasil'] >= $ev['total_peserta']));
                    ?>
                        <tr class="event-row" data-status="<?= $isSelesai ? 'completed' : 'pending' ?>">
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
                                <?php if ($isSelesai): ?>
                                    <span class="badge badge-emerald">
                                        <i class="bi bi-check-all"></i> Selesai (<?= $ev['total_hasil'] ?>/<?= $ev['total_peserta'] ?>)
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
                                    <?php if ($isSelesai): ?>
                                        <a href="<?= base_url('hasil/input/' . $ev['id']) ?>" class="btn btn-sm btn-outline-info flex-grow-1" title="Lihat Rekap Hasil Acara Ini">
                                            <i class="bi bi-eye-fill me-1"></i> Rekap
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url('hasil/input/' . $ev['id']) ?>" class="btn btn-sm btn-primary-gradient flex-grow-1" title="Input Catatan Prestasi Atlet">
                                            <i class="bi bi-stopwatch-fill me-1"></i> Input
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($ev['total_hasil'] > 0): ?>
                                        <a href="<?= base_url('hasil/cetak-hasil/' . $ev['id']) ?>" target="_blank" class="btn btn-sm btn-info text-white" title="Cetak Lembar Hasil Perlombaan (Meet Manager)">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('hasil/form-lapangan/' . $ev['id']) ?>" target="_blank" class="btn btn-sm btn-warning text-dark fw-bold" title="Cetak Blangko Form Lapangan / Lembar Juri">
                                        <i class="bi bi-printer-fill"></i>
                                    </a>
                                    <?php if (strcasecmp($ev['babak'] ?? '', 'Penyisihan') !== 0): ?>
                                        <a href="<?= base_url('piagam?event_id=' . $ev['id']) ?>" class="btn btn-sm btn-secondary-custom" title="Lihat & Cetak Piagam Penghargaan">
                                            <i class="bi bi-award-fill text-warning"></i>
                                        </a>
                                    <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterButtons = document.querySelectorAll('#filterStatusGroup button');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            const rows = document.querySelectorAll('#tableHasilIndex tbody tr.event-row');

            // Jika DataTables aktif
            if (window.jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable('#tableHasilIndex')) {
                const dt = jQuery('#tableHasilIndex').DataTable();
                if (filter === 'all') {
                    dt.search('').columns().search('').draw();
                } else if (filter === 'pending') {
                    dt.column(5).search('Belum|Sebagian', true, false).draw();
                } else if (filter === 'completed') {
                    dt.column(5).search('Selesai|Lengkap', true, false).draw();
                }
            } else {
                rows.forEach(r => {
                    const st = r.getAttribute('data-status');
                    if (filter === 'all' || st === filter) {
                        r.style.display = '';
                    } else {
                        r.style.display = 'none';
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
