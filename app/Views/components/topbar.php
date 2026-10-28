<header id="topbar">
    <div class="d-flex align-items-center">
        <button id="sidebarToggle" class="btn btn-secondary-custom me-2 d-lg-none" type="button">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted d-none d-sm-inline" style="font-size: 12px; font-weight: 500;">Event:</span>
            <div class="dropdown">
                <button class="btn btn-sm d-flex align-items-center gap-2 dropdown-toggle py-1 px-2" 
                        type="button" id="kejuaraanSwitcher" data-bs-toggle="dropdown" aria-expanded="false"
                        style="background: rgba(0, 82, 204, 0.12); border: 1px solid rgba(0, 82, 204, 0.25); color: var(--text-main, #172b4d); border-radius: 8px; font-weight: 600; font-size: 12.5px;">
                    <i class="bi bi-trophy-fill text-warning"></i>
                    <span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= esc($activeKejuaraan['nama'] ?? 'Pilih Kejuaraan') ?>">
                        <?= esc($activeKejuaraan['nama'] ?? 'Pilih Kejuaraan') ?>
                    </span>
                </button>
                <ul class="dropdown-menu shadow border-0 py-1" aria-labelledby="kejuaraanSwitcher" style="min-width: 320px; font-size: 13px; z-index: 1050; border-radius: 10px;">
                    <li class="dropdown-header text-uppercase fw-bold text-muted" style="font-size: 10.5px; letter-spacing: 0.5px;">
                        <i class="bi bi-arrow-left-right me-1"></i> Ganti Fokus Kejuaraan
                    </li>
                    <?php if (!empty($allKejuaraans)): ?>
                        <?php foreach ($allKejuaraans as $kj): 
                            $isSelected = ($kj['id'] == ($activeKejuaraan['id'] ?? 0));
                        ?>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 <?= $isSelected ? 'active fw-semibold' : '' ?>" 
                               href="<?= base_url('kejuaraan/switch/' . $kj['id']) ?>"
                               style="gap: 8px;">
                                <div class="d-flex align-items-center text-truncate">
                                    <i class="bi <?= $isSelected ? 'bi-check-circle-fill text-success' : 'bi-trophy text-muted' ?> me-2"></i>
                                    <span class="text-truncate" style="max-width: 210px;" title="<?= esc($kj['nama']) ?>">
                                        <?= esc($kj['nama']) ?>
                                    </span>
                                </div>
                                <span class="badge <?= $kj['status'] === 'Aktif' ? 'bg-success' : ($kj['status'] === 'Selesai' ? 'bg-secondary' : 'bg-warning text-dark') ?>" style="font-size: 10px;">
                                    <?= esc($kj['status'] ?? 'Draft') ?>
                                </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item py-1 px-3 text-muted small d-flex align-items-center" href="<?= base_url('kejuaraan') ?>">
                            <i class="bi bi-gear me-2"></i> Kelola Semua Kejuaraan...
                        </a>
                    </li>
                </ul>
            </div>
            <span class="d-none d-xl-inline ms-1 text-muted" style="font-size: 12px;">
                &bull; <i class="bi bi-geo-alt"></i> <?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng') ?>
            </span>
            <?php if (session()->get('cabor_nama')): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 ms-2 d-none d-md-inline-flex align-items-center gap-1" style="font-size: 11px;">
                    <i class="bi bi-trophy-fill"></i> Cabor <?= esc(session()->get('cabor_nama')) ?>
                </span>
            <?php endif; ?>
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
        <?php if ((session()->get('role') ?? 'admin') === 'admin'): ?>
            <a href="<?= base_url('buku-acara') ?>" class="btn btn-secondary-custom btn-sm d-none d-sm-inline-flex align-items-center gap-1">
                <i class="bi bi-printer"></i>
                <span>Buku Acara</span>
            </a>
        <?php endif; ?>
        <a href="<?= base_url('atlet/create') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i>
            <span>Daftar Atlet</span>
        </a>
    </div>
</header>
