<?php
$uri = service('uri');
$segments = $uri->getSegments();
$seg1 = $segments[0] ?? 'dashboard';
$seg2 = $segments[1] ?? '';
?>
<aside id="sidebar">
    <a href="<?= base_url('dashboard') ?>" class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-trophy-fill"></i>
        </div>
        <div>
            <span class="brand-text">KEJURWIL ATLETIK</span>
            <span class="brand-sub">SaaS Championship System</span>
        </div>
    </a>

    <div class="sidebar-menu">
        <div class="menu-header">Menu Utama</div>
        <a href="<?= base_url('dashboard') ?>" class="nav-link <?= ($seg1 === 'dashboard' || $seg1 === '') ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?= base_url('kejuaraan') ?>" class="nav-link <?= ($seg1 === 'kejuaraan') ? 'active' : '' ?>">
            <i class="bi bi-award-fill"></i>
            <span>Kejuaraan</span>
        </a>

        <div class="menu-header">Data Master</div>
        <a href="<?= base_url('sekolah') ?>" class="nav-link <?= ($seg1 === 'sekolah') ? 'active' : '' ?>">
            <i class="bi bi-building"></i>
            <span>Sekolah / Kontingen</span>
        </a>
        <a href="<?= base_url('kelompok-umur') ?>" class="nav-link <?= ($seg1 === 'kelompok-umur') ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i>
            <span>Kelompok Umur (KU)</span>
        </a>
        <a href="<?= base_url('nomor-lomba') ?>" class="nav-link <?= ($seg1 === 'nomor-lomba') ? 'active' : '' ?>">
            <i class="bi bi-flag-fill"></i>
            <span>Nomor Lomba</span>
        </a>

        <div class="menu-header">Pendaftaran & Atlet</div>
        <a href="<?= base_url('atlet') ?>" class="nav-link <?= ($seg1 === 'atlet' && $seg2 !== 'import' && $seg2 !== 'cetak-nomor-lomba') ? 'active' : '' ?>">
            <i class="bi bi-person-lines-fill"></i>
            <span>Data Atlet</span>
        </a>
        <a href="<?= base_url('atlet/cetak-nomor-lomba') ?>" target="_blank" class="nav-link <?= ($seg1 === 'atlet' && $seg2 === 'cetak-nomor-lomba') ? 'active' : '' ?>">
            <i class="bi bi-123"></i>
            <span>Cetak Nomor Lomba</span>
        </a>
        <a href="<?= base_url('atlet/import') ?>" class="nav-link <?= ($seg1 === 'atlet' && $seg2 === 'import') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-excel-fill"></i>
            <span>Import Data Excel</span>
        </a>

        <div class="menu-header">Perlombaan</div>
        <a href="<?= base_url('event') ?>" class="nav-link <?= ($seg1 === 'event') ? 'active' : '' ?>">
            <i class="bi bi-calendar2-event-fill"></i>
            <span>Jadwal & Acara Lomba</span>
        </a>
        <a href="<?= base_url('hasil') ?>" class="nav-link <?= ($seg1 === 'hasil') ? 'active' : '' ?>">
            <i class="bi bi-stopwatch-fill"></i>
            <span>Input & Rekap Hasil</span>
        </a>

        <div class="menu-header">Laporan & Publikasi</div>
        <a href="<?= base_url('buku-acara') ?>" class="nav-link <?= ($seg1 === 'buku-acara') ? 'active' : '' ?>">
            <i class="bi bi-book-half"></i>
            <span>Buku Acara (PDF/Excel)</span>
        </a>
        <a href="<?= base_url('laporan/klasemen') ?>" class="nav-link <?= ($seg1 === 'laporan' && $seg2 === 'klasemen') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span>Klasemen Medali</span>
        </a>
        <a href="<?= base_url('piagam') ?>" class="nav-link <?= ($seg1 === 'piagam') ? 'active' : '' ?>">
            <i class="bi bi-award-fill"></i>
            <span>Piagam Penghargaan</span>
        </a>
    </div>

    <!-- User Profile Footer -->
    <div class="p-3 border-top" style="border-color: var(--border-color) !important; background: #FAFBFC;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="avatar text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 600; font-size: 12px; background-color: #0052CC;">
                    <?= strtoupper(substr(session()->get('username') ?? 'A', 0, 1)) ?>
                </div>
                <div class="ms-2">
                    <div class="fw-semibold text-truncate" style="max-width: 130px; font-size: 13px; color: var(--text-main);">
                        <?= esc(session()->get('nama_lengkap') ?? 'Admin Panitia') ?>
                    </div>
                    <small class="text-muted text-capitalize" style="font-size: 11px;">
                        <?= esc(session()->get('role') ?? 'Administrator') ?>
                    </small>
                </div>
            </div>
            <a href="<?= base_url('auth/logout') ?>" class="btn btn-sm btn-outline-secondary" title="Logout" style="padding: 0.2rem 0.45rem; font-size: 12px;">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</aside>
