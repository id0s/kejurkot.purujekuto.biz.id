<header id="topbar">
    <div class="d-flex align-items-center">
        <button id="sidebarToggle" class="btn btn-secondary-custom me-2 d-lg-none" type="button">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted d-none d-sm-inline" style="font-size: 12px; font-weight: 500;">Projects /</span>
            <span class="badge badge-blue">
                <i class="bi bi-trophy me-1"></i>
                <?= esc($activeKejuaraan['nama'] ?? 'Kejurwil Atletik 2026') ?>
            </span>
            <span class="d-none d-md-inline ms-1 text-muted" style="font-size: 12px;">
                &bull; <i class="bi bi-geo-alt"></i> <?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng') ?>
            </span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <!-- Theme Toggle Button (Dark / Light Mode) -->
        <button id="themeToggleBtn" class="btn btn-secondary-custom btn-sm d-inline-flex align-items-center justify-content-center p-0 rounded-circle" type="button" title="Ganti Mode Tampilan (Dark / Light)" onclick="toggleKejurwilTheme(event)" style="width: 34px; height: 34px;">
            <i class="bi bi-moon-fill theme-icon-light" style="font-size: 14px; color: #8e8e93;"></i>
            <i class="bi bi-sun-fill theme-icon-dark" style="display: none; color: #ffcc00; font-size: 14px;"></i>
        </button>

        <a href="<?= base_url('atlet/import') ?>" class="btn btn-secondary-custom btn-sm d-none d-sm-inline-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-arrow-up"></i>
            <span>Import Excel</span>
        </a>
        <a href="<?= base_url('buku-acara') ?>" class="btn btn-secondary-custom btn-sm d-none d-sm-inline-flex align-items-center gap-1">
            <i class="bi bi-printer"></i>
            <span>Buku Acara</span>
        </a>
        <a href="<?= base_url('atlet/create') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i>
            <span>Daftar Atlet</span>
        </a>
    </div>
</header>
