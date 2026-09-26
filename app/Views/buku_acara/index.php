<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$currentTab = $_GET['tab'] ?? 'susunan';
$sortDir    = strtolower($sortDir ?? 'asc') === 'desc' ? 'desc' : 'asc';
$sortBy     = $sortBy ?? 'nomor_acara';

$queryBase = http_build_query([
    'tab'      => $currentTab,
    'sort'     => $sortBy,
    'dir'      => $sortDir,
    'kategori' => $filterKategori ?? '',
    'gender'   => $filterGender ?? '',
    'babak'    => $filterBabak ?? '',
]);

// Helper URL untuk klik-urut di judul kolom tabel
$getSortUrl = function(string $col) use ($currentTab, $sortBy, $sortDir, $filterKategori, $filterGender, $filterBabak) {
    $nextDir = ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc';
    $params = [
        'tab'      => $currentTab,
        'sort'     => $col,
        'dir'      => $nextDir,
    ];
    if (!empty($filterKategori)) $params['kategori'] = $filterKategori;
    if (!empty($filterGender))   $params['gender']   = $filterGender;
    if (!empty($filterBabak))    $params['babak']    = $filterBabak;
    return base_url('buku-acara?' . http_build_query($params));
};

$getSortIcon = function(string $col, string $type = 'alpha') use ($sortBy, $sortDir) {
    if ($sortBy !== $col) {
        return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size: 10px; opacity: 0.4;"></i>';
    }
    if ($sortDir === 'desc') {
        return '<i class="bi bi-sort-' . ($type === 'numeric' ? 'numeric' : 'alpha') . '-up-alt text-primary fw-bold ms-1" style="font-size: 14px;"></i>';
    }
    return '<i class="bi bi-sort-' . ($type === 'numeric' ? 'numeric' : 'alpha') . '-down text-primary fw-bold ms-1" style="font-size: 14px;"></i>';
};
?>

<style>
.sortable-th {
    position: relative;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    padding: 0 !important;
}
.sortable-th a {
    color: #172B4D;
    padding: 0.65rem 0.6rem;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.3px;
    display: flex;
    user-select: none;
    text-decoration: none;
    height: 100%;
    min-height: 42px;
}
.sortable-th:hover {
    background-color: #EBECF0 !important;
}
.sortable-th:hover a {
    color: #0052CC !important;
}
.sortable-th.active-sort {
    background-color: #DEEBFF !important;
    border-bottom: 2px solid #0052CC !important;
}
.sortable-th.active-sort a {
    color: #0052CC !important;
}
.bg-blue-light {
    background-color: #F8FAFC !important;
}
</style>

<!-- Header Banner -->
<div class="card-custom mb-4 p-4" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(59, 130, 246, 0.1) 100%); border-color: rgba(245, 158, 11, 0.3);">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge badge-amber fs-6 mb-1">
                <i class="bi bi-book-half"></i> Buku Acara & Susunan Acara Resmi
            </span>
            <h3 class="fw-bold mb-1" style="color: #172B4D;"><?= esc($activeKejuaraan['nama'] ?? 'Kejurwil Atletik 2026') ?></h3>
            <p class="text-muted small mb-0">
                <i class="bi bi-geo-alt me-1"></i> <?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng') ?>
                &bull; Tanggal: <?= esc($activeKejuaraan['tanggal_mulai'] ?? '-') ?> s/d <?= esc($activeKejuaraan['tanggal_selesai'] ?? '-') ?>
                &bull; Total: <strong class="text-primary"><?= count($bukuAcaraData) ?> Nomor Acara</strong>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-warning text-dark fw-bold d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSyncOfficialBook">
                <i class="bi bi-arrow-repeat fs-5"></i>
                <span>Sinkronkan Buku Acara Excel</span>
            </button>
            <a href="<?= base_url('buku-acara/cetak?' . $queryBase) ?>" target="_blank" class="btn btn-secondary-custom d-inline-flex align-items-center gap-2">
                <i class="bi bi-printer-fill fs-5"></i>
                <span>Cetak / PDF Lengkap</span>
            </a>
            <a href="<?= base_url('downloads/BUKU%20ACARA%20ATLETIK%20KARES%202026.xlsx') ?>" download="BUKU ACARA ATLETIK KARES 2026.xlsx" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2 shadow-sm" title="Download file Excel resmi BUKU ACARA ATLETIK KARES 2026.xlsx">
                <i class="bi bi-file-earmark-excel-fill fs-5 text-white"></i>
                <span>Download Excel (.xlsx)</span>
            </a>
        </div>
    </div>
</div>

<!-- Jira SaaS Toolbar: Filter Cepat Per Jenis Lomba & Gender -->
<div class="card-custom mb-4 p-3" style="background: #FFFFFF; border: 1px solid #DFE1E6;">
    <form method="get" action="<?= base_url('buku-acara') ?>" id="formFilterSort" class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
        <input type="hidden" name="tab" id="activeTabInput" value="<?= esc($currentTab) ?>">
        <input type="hidden" name="sort" value="<?= esc($sortBy) ?>">
        <input type="hidden" name="dir" value="<?= esc($sortDir) ?>">

        <!-- Filter Cepat Per Jenis Lomba & Gender -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted small fw-bold text-uppercase me-1" style="letter-spacing: 0.5px; font-size: 11px;">
                <i class="bi bi-funnel-fill text-primary"></i> Filter Jenis:
            </span>
            <div class="btn-group btn-group-sm" role="group">
                <a href="<?= base_url('buku-acara?tab=' . $currentTab . '&sort=' . $sortBy . '&dir=' . $sortDir . '&gender=' . $filterGender . '&babak=' . $filterBabak) ?>" class="btn <?= empty($filterKategori) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Semua (<?= $counts['all'] ?? 0 ?>)
                </a>
                <a href="<?= base_url('buku-acara?tab=' . $currentTab . '&sort=' . $sortBy . '&dir=' . $sortDir . '&kategori=Lari&gender=' . $filterGender . '&babak=' . $filterBabak) ?>" class="btn <?= $filterKategori === 'Lari' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Lari (<?= $counts['lari'] ?? 0 ?>)
                </a>
                <a href="<?= base_url('buku-acara?tab=' . $currentTab . '&sort=' . $sortBy . '&dir=' . $sortDir . '&kategori=Lompat&gender=' . $filterGender . '&babak=' . $filterBabak) ?>" class="btn <?= $filterKategori === 'Lompat' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Lompat (<?= $counts['lompat'] ?? 0 ?>)
                </a>
                <a href="<?= base_url('buku-acara?tab=' . $currentTab . '&sort=' . $sortBy . '&dir=' . $sortDir . '&kategori=Lempar&gender=' . $filterGender . '&babak=' . $filterBabak) ?>" class="btn <?= $filterKategori === 'Lempar' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Lempar (<?= $counts['lempar'] ?? 0 ?>)
                </a>
                <a href="<?= base_url('buku-acara?tab=' . $currentTab . '&sort=' . $sortBy . '&dir=' . $sortDir . '&kategori=Estafet&gender=' . $filterGender . '&babak=' . $filterBabak) ?>" class="btn <?= $filterKategori === 'Estafet' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Estafet (<?= $counts['estafet'] ?? 0 ?>)
                </a>
            </div>

            <!-- Filter Gender -->
            <select name="gender" class="form-select form-select-sm" style="width: auto; min-width: 145px;" onchange="this.form.submit()">
                <option value="">Semua Gender</option>
                <option value="L" <?= $filterGender === 'L' ? 'selected' : '' ?>>Putra / Male (<?= $counts['putra'] ?? 0 ?>)</option>
                <option value="P" <?= $filterGender === 'P' ? 'selected' : '' ?>>Putri / Female (<?= $counts['putri'] ?? 0 ?>)</option>
            </select>

            <!-- Filter Babak -->
            <select name="babak" class="form-select form-select-sm" style="width: auto; min-width: 135px;" onchange="this.form.submit()">
                <option value="">Semua Babak</option>
                <option value="Final" <?= $filterBabak === 'Final' ? 'selected' : '' ?>>Final (<?= $counts['final'] ?? 0 ?>)</option>
                <option value="Penyisihan" <?= $filterBabak === 'Penyisihan' ? 'selected' : '' ?>>Penyisihan (<?= $counts['penyisihan'] ?? 0 ?>)</option>
            </select>
        </div>

        <!-- Info Pengurutan & Reset -->
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-2 py-1 small fw-normal d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                <i class="bi bi-cursor-fill text-primary"></i> Klik judul kolom untuk mengurutkan
            </span>

            <?php if (!empty($filterKategori) || !empty($filterGender) || !empty($filterBabak) || $sortBy !== 'nomor_acara' || $sortDir !== 'asc'): ?>
                <a href="<?= base_url('buku-acara?tab=' . $currentTab) ?>" class="btn btn-sm btn-outline-danger text-nowrap" title="Reset Filter & Urutan">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Nav Tabs -->
<ul class="nav nav-pills mb-4 gap-2" id="bukuAcaraTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= ($currentTab === 'susunan') ? 'active' : '' ?>" id="susunan-tab" data-bs-toggle="tab" data-bs-target="#tab-susunan" type="button" role="tab" onclick="setTabParam('susunan')">
            <i class="bi bi-calendar-range me-1"></i> Susunan Acara (Pagi & Sore)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= ($currentTab === 'heats') ? 'active' : '' ?>" id="heats-tab" data-bs-toggle="tab" data-bs-target="#tab-heats" type="button" role="tab" onclick="setTabParam('heats')">
            <i class="bi bi-list-stars me-1"></i> Buku Acara (Seri / Heat & Peserta)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= ($currentTab === 'cover') ? 'active' : '' ?>" id="cover-tab" data-bs-toggle="tab" data-bs-target="#tab-cover" type="button" role="tab" onclick="setTabParam('cover')">
            <i class="bi bi-file-earmark-person me-1"></i> Cover Resmi (Sheet 1)
        </button>
    </li>
</ul>

<div class="tab-content" id="bukuAcaraTabContent">

    <!-- TAB 1: SUSUNAN ACARA (PAGI & SORE) -->
    <div class="tab-pane fade <?= ($currentTab === 'susunan') ? 'show active' : '' ?>" id="tab-susunan" role="tabpanel">
        <div class="card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0" style="color: #172B4D;">
                        <i class="bi bi-table me-2 text-primary"></i> Tabel Susunan Acara Kejurwil
                    </h5>
                    <small class="text-muted">
                        Urutan aktif: <strong class="text-primary"><?= esc(ucwords(str_replace('_', ' ', $sortBy ?? 'nomor_acara'))) ?> (<?= strtoupper($sortDir) ?>)</strong>
                        <span class="badge bg-light text-secondary border ms-1" style="font-size: 11px; font-weight: normal;">
                            <i class="bi bi-cursor-fill me-1 text-primary"></i> Klik judul kolom di bawah untuk mengubah urutan
                        </span>
                        <?php if (!empty($filterKategori)): ?> &bull; Filter Kategori: <span class="badge badge-purple"><?= esc($filterKategori) ?></span><?php endif; ?>
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge badge-blue">Pagi: <?= count($pagiEvents) ?> Acara</span>
                    <span class="badge badge-amber">Sore: <?= count($soreEvents) ?> Acara</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($bukuAcaraData)): ?>
                    <div class="p-5 text-center text-muted">Belum ada acara lomba yang sesuai filter.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle" style="border-color: #DFE1E6; font-size: 13px;">
                            <thead style="background-color: #F4F5F7; color: #172B4D;">
                                <tr>
                                    <th width="130" class="text-center align-middle" style="background-color: #EBECF0; font-size: 11px; letter-spacing: 0.5px; font-weight: 700;">JAM PEMANGGILAN</th>
                                    
                                    <th width="85" class="text-center align-middle sortable-th <?= $sortBy === 'nomor_acara' ? 'active-sort' : '' ?>" title="<?= $sortBy === 'nomor_acara' ? ($sortDir === 'asc' ? 'Urutan: 1-9 (Klik untuk 9-1)' : 'Urutan: 9-1 (Klik untuk 1-9)') : 'Klik untuk urutkan berdasarkan Nomor Acara Perlombaan' ?>">
                                        <a href="<?= $getSortUrl('nomor_acara') ?>" class="d-flex align-items-center justify-content-center text-decoration-none">
                                            <span>NO ACARA</span>
                                            <?= $getSortIcon('nomor_acara', 'numeric') ?>
                                        </a>
                                    </th>

                                    <th class="align-middle sortable-th <?= $sortBy === 'nama' ? 'active-sort' : '' ?>" title="<?= $sortBy === 'nama' ? ($sortDir === 'asc' ? 'Urutan: A-Z (Klik untuk Z-A)' : 'Urutan: Z-A (Klik untuk A-Z)') : 'Klik untuk urutkan berdasarkan Nama Lomba / Kelompok Umur' ?>">
                                        <a href="<?= $getSortUrl('nama') ?>" class="d-flex align-items-center justify-content-between text-decoration-none">
                                            <span>NOMOR LOMBA</span>
                                            <?= $getSortIcon('nama', 'alpha') ?>
                                        </a>
                                    </th>

                                    <th width="110" class="text-center align-middle sortable-th <?= $sortBy === 'kategori' ? 'active-sort' : '' ?>" title="<?= $sortBy === 'kategori' ? ($sortDir === 'asc' ? 'Urutan: A-Z (Klik untuk Z-A)' : 'Urutan: Z-A (Klik untuk A-Z)') : 'Klik untuk urutkan berdasarkan Jenis / Kategori Lomba' ?>">
                                        <a href="<?= $getSortUrl('kategori') ?>" class="d-flex align-items-center justify-content-center text-decoration-none">
                                            <span>JENIS KELAMIN</span>
                                            <?= $getSortIcon('kategori', 'alpha') ?>
                                        </a>
                                    </th>

                                    <th width="100" class="text-center align-middle sortable-th <?= $sortBy === 'babak' ? 'active-sort' : '' ?>" title="<?= $sortBy === 'babak' ? ($sortDir === 'asc' ? 'Urutan: Final-Penyisihan (Klik sebaliknya)' : 'Urutan sebaliknya (Klik untuk Final-Penyisihan)') : 'Klik untuk urutkan berdasarkan Babak' ?>">
                                        <a href="<?= $getSortUrl('babak') ?>" class="d-flex align-items-center justify-content-center text-decoration-none">
                                            <span>BABAK</span>
                                            <?= $getSortIcon('babak', 'alpha') ?>
                                        </a>
                                    </th>

                                    <th width="120" class="text-center align-middle sortable-th <?= $sortBy === 'sesi' ? 'active-sort' : '' ?>" title="<?= $sortBy === 'sesi' ? ($sortDir === 'asc' ? 'Urutan: Pagi-Sore (Klik sebaliknya)' : 'Urutan: Sore-Pagi (Klik sebaliknya)') : 'Klik untuk urutkan berdasarkan Sesi & Waktu' ?>">
                                        <a href="<?= $getSortUrl('sesi') ?>" class="d-flex align-items-center justify-content-center text-decoration-none">
                                            <span>SESI</span>
                                            <?= $getSortIcon('sesi', 'alpha') ?>
                                        </a>
                                    </th>

                                    <th width="85" class="text-center align-middle" style="background-color: #EBECF0; font-size: 11px; letter-spacing: 0.5px; font-weight: 700;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $isSessionGrouped = ($sortBy === 'sesi' || ($sortBy === 'nomor_acara' && $sortDir === 'asc'));
                                ?>

                                <?php if ($isSessionGrouped): ?>
                                    <!-- TAMPILAN RESMI GRUP SESI PAGI & SORE -->
                                    <?php 
                                    $totalRows = count($pagiEvents) + count($soreEvents);
                                    $dateRendered = false;
                                    ?>

                                    <!-- Sesi Pagi Rows -->
                                    <?php if (!empty($pagiEvents)): ?>
                                        <?php $firstPagi = true; ?>
                                        <?php foreach ($pagiEvents as $ev): ?>
                                            <tr>
                                                <?php if (!$dateRendered): ?>
                                                    <td rowspan="<?= $totalRows ?>" class="text-center fw-bold align-top bg-light" style="padding-top: 2rem; letter-spacing: 0.5px; border-right: 2px solid #0052CC; color: #0052CC;">
                                                        <div style="writing-mode: vertical-rl; transform: rotate(180deg); margin: auto; font-size: 14px; font-weight: 700; position: sticky; top: 80px;">
                                                            <?= esc($ev['jadwal_hari'] ?: 'SABTU, 29 AGUSTUS 2026') ?>
                                                        </div>
                                                    </td>
                                                    <?php $dateRendered = true; ?>
                                                <?php endif; ?>

                                                <td class="text-center fw-bold font-monospace <?= $sortBy === 'nomor_acara' ? 'bg-blue-light' : '' ?>"><?= esc($ev['nomor_acara']) ?></td>
                                                <td class="fw-semibold text-uppercase <?= $sortBy === 'nama' ? 'bg-blue-light' : '' ?>" style="color: #172B4D;">
                                                    <?= esc($ev['nama_acara']) ?>
                                                </td>
                                                <td class="text-center <?= $sortBy === 'kategori' ? 'bg-blue-light' : '' ?>">
                                                    <span class="badge badge-purple"><?= esc($ev['kategori_lomba'] ?? 'Lari') ?></span>
                                                    <span class="badge <?= $ev['gender'] === 'L' ? 'badge-blue' : 'badge-amber' ?>"><?= $ev['gender'] === 'L' ? 'Putra (Male)' : 'Putri (Female)' ?></span>
                                                </td>
                                                <td class="text-center <?= $sortBy === 'babak' ? 'bg-blue-light' : '' ?>">
                                                    <span class="badge <?= $ev['babak'] === 'Final' ? 'badge-emerald' : 'badge-amber' ?> fw-bold" style="cursor: pointer;" onclick="openEditSesiModal(<?= $ev['id'] ?>, '<?= esc($ev['nama_acara']) ?>', '<?= esc($ev['jadwal_hari'] ?: 'Sabtu, 29 Agustus 2026') ?>', 'Pagi', '08.00', '<?= esc($ev['babak']) ?>')" title="Klik untuk ubah Babak">
                                                        <?= strtoupper(esc($ev['babak'])) ?>
                                                    </span>
                                                </td>

                                                <?php if ($firstPagi): ?>
                                                    <td rowspan="<?= count($pagiEvents) ?>" class="text-center align-top <?= $sortBy === 'sesi' ? 'bg-blue-light' : '' ?>" style="padding-top: 2rem; background-color: #F8FAFC; border-left: 2px solid #E2E8F0;">
                                                        <div style="position: sticky; top: 80px;">
                                                            <div class="fw-bold fs-6 text-primary">PAGI</div>
                                                            <div class="fw-bold font-monospace text-muted">08.00</div>
                                                        </div>
                                                    </td>
                                                    <?php $firstPagi = false; ?>
                                                <?php endif; ?>

                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="openEditSesiModal(<?= $ev['id'] ?>, '<?= esc($ev['nama_acara']) ?>', '<?= esc($ev['jadwal_hari'] ?: 'Sabtu, 29 Agustus 2026') ?>', 'Pagi', '08.00', '<?= esc($ev['babak']) ?>')" title="Atur Babak & Jadwal">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <!-- Sesi Sore Rows -->
                                    <?php if (!empty($soreEvents)): ?>
                                        <?php $firstSore = true; ?>
                                        <?php foreach ($soreEvents as $ev): ?>
                                            <tr style="border-top: <?= $firstSore && !empty($pagiEvents) ? '2px solid #CBD5E1' : '1px solid #DFE1E6' ?>;">
                                                <?php if (!$dateRendered): ?>
                                                    <td rowspan="<?= $totalRows ?>" class="text-center fw-bold align-top bg-light" style="padding-top: 2rem; letter-spacing: 0.5px; border-right: 2px solid #0052CC; color: #0052CC;">
                                                        <div style="writing-mode: vertical-rl; transform: rotate(180deg); margin: auto; font-size: 14px; font-weight: 700; position: sticky; top: 80px;">
                                                            <?= esc($ev['jadwal_hari'] ?: 'SABTU, 29 AGUSTUS 2026') ?>
                                                        </div>
                                                    </td>
                                                    <?php $dateRendered = true; ?>
                                                <?php endif; ?>

                                                <td class="text-center fw-bold font-monospace <?= $sortBy === 'nomor_acara' ? 'bg-blue-light' : '' ?>"><?= esc($ev['nomor_acara']) ?></td>
                                                <td class="fw-semibold text-uppercase <?= $sortBy === 'nama' ? 'bg-blue-light' : '' ?>" style="color: #172B4D;">
                                                    <?= esc($ev['nama_acara']) ?>
                                                </td>
                                                <td class="text-center <?= $sortBy === 'kategori' ? 'bg-blue-light' : '' ?>">
                                                    <span class="badge badge-purple"><?= esc($ev['kategori_lomba'] ?? 'Lari') ?></span>
                                                    <span class="badge <?= $ev['gender'] === 'L' ? 'badge-blue' : 'badge-amber' ?>"><?= $ev['gender'] === 'L' ? 'Putra (Male)' : 'Putri (Female)' ?></span>
                                                </td>
                                                <td class="text-center <?= $sortBy === 'babak' ? 'bg-blue-light' : '' ?>">
                                                    <span class="badge <?= $ev['babak'] === 'Final' ? 'badge-emerald' : 'badge-amber' ?> fw-bold" style="cursor: pointer;" onclick="openEditSesiModal(<?= $ev['id'] ?>, '<?= esc($ev['nama_acara']) ?>', '<?= esc($ev['jadwal_hari'] ?: 'Sabtu, 29 Agustus 2026') ?>', 'Sore', '13.30', '<?= esc($ev['babak']) ?>')" title="Klik untuk ubah Babak">
                                                        <?= strtoupper(esc($ev['babak'])) ?>
                                                    </span>
                                                </td>

                                                <?php if ($firstSore): ?>
                                                    <td rowspan="<?= count($soreEvents) ?>" class="text-center align-top <?= $sortBy === 'sesi' ? 'bg-blue-light' : '' ?>" style="padding-top: 2rem; background-color: #FEF3C7; border-left: 2px solid #FDE68A;">
                                                        <div style="position: sticky; top: 80px;">
                                                            <div class="fw-bold fs-6 text-amber" style="color: #B45309;">SORE</div>
                                                            <div class="fw-bold font-monospace text-muted">13.30</div>
                                                        </div>
                                                    </td>
                                                    <?php $firstSore = false; ?>
                                                <?php endif; ?>

                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="openEditSesiModal(<?= $ev['id'] ?>, '<?= esc($ev['nama_acara']) ?>', '<?= esc($ev['jadwal_hari'] ?: 'Sabtu, 29 Agustus 2026') ?>', 'Sore', '13.30', '<?= esc($ev['babak']) ?>')" title="Atur Babak & Jadwal">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <!-- TAMPILAN URUT KHUSUS KOLOM (<?= esc(strtoupper($sortBy)) ?> <?= strtoupper($sortDir) ?>) -->
                                    <?php 
                                    $totalRows = count($bukuAcaraData);
                                    $dateRendered = false;
                                    foreach ($bukuAcaraData as $item):
                                        $ev = $item['event'];
                                        $waktu = strtolower($ev['jadwal_waktu'] ?? '');
                                        $isSore = (strpos($waktu, 'sore') !== false || strpos($waktu, '13.') !== false || strpos($waktu, '14.') !== false || strpos($waktu, '15.') !== false);
                                        $sesiText = $isSore ? 'Sore' : 'Pagi';
                                        $jamText  = trim(str_ireplace(['Pagi', 'Sore'], '', $ev['jadwal_waktu'] ?? '')) ?: ($isSore ? '13.30' : '08.00');
                                    ?>
                                        <tr>
                                            <?php if (!$dateRendered): ?>
                                                <td rowspan="<?= $totalRows ?>" class="text-center fw-bold align-top bg-light" style="padding-top: 2rem; letter-spacing: 0.5px; border-right: 2px solid #0052CC; color: #0052CC;">
                                                    <div style="writing-mode: vertical-rl; transform: rotate(180deg); margin: auto; font-size: 14px; font-weight: 700; position: sticky; top: 80px;">
                                                        <?= esc($ev['jadwal_hari'] ?: 'SABTU, 29 AGUSTUS 2026') ?>
                                                    </div>
                                                </td>
                                                <?php $dateRendered = true; ?>
                                            <?php endif; ?>

                                            <td class="text-center fw-bold font-monospace <?= $sortBy === 'nomor_acara' ? 'bg-blue-light' : '' ?>"><?= esc($ev['nomor_acara']) ?></td>
                                            <td class="fw-semibold text-uppercase <?= $sortBy === 'nama' ? 'bg-blue-light' : '' ?>" style="color: #172B4D;">
                                                <?= esc($ev['nama_acara']) ?>
                                            </td>
                                            <td class="text-center <?= $sortBy === 'kategori' ? 'bg-blue-light' : '' ?>">
                                                <span class="badge badge-purple"><?= esc($ev['kategori_lomba'] ?? 'Lari') ?></span>
                                                <span class="badge <?= $ev['gender'] === 'L' ? 'badge-blue' : 'badge-amber' ?>"><?= $ev['gender'] === 'L' ? 'Putra (Male)' : 'Putri (Female)' ?></span>
                                            </td>
                                            <td class="text-center <?= $sortBy === 'babak' ? 'bg-blue-light' : '' ?>">
                                                <span class="badge <?= $ev['babak'] === 'Final' ? 'badge-emerald' : 'badge-amber' ?> fw-bold" style="cursor: pointer;" onclick="openEditSesiModal(<?= $ev['id'] ?>, '<?= esc($ev['nama_acara']) ?>', '<?= esc($ev['jadwal_hari'] ?: 'Sabtu, 29 Agustus 2026') ?>', '<?= $sesiText ?>', '<?= $jamText ?>', '<?= esc($ev['babak']) ?>')" title="Klik untuk ubah Babak">
                                                    <?= strtoupper(esc($ev['babak'])) ?>
                                                </span>
                                            </td>
                                            <td class="text-center <?= $sortBy === 'sesi' ? 'bg-blue-light' : '' ?>">
                                                <span class="badge <?= $isSore ? 'badge-amber' : 'badge-blue' ?> fw-semibold">
                                                    <i class="bi <?= $isSore ? 'bi-sun-fill' : 'bi-sunrise-fill' ?> me-1"></i>
                                                    <?= esc($ev['jadwal_waktu'] ?: ($sesiText . ' ' . $jamText)) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="openEditSesiModal(<?= $ev['id'] ?>, '<?= esc($ev['nama_acara']) ?>', '<?= esc($ev['jadwal_hari'] ?: 'Sabtu, 29 Agustus 2026') ?>', '<?= $sesiText ?>', '<?= $jamText ?>', '<?= esc($ev['babak']) ?>')" title="Atur Babak & Jadwal">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TAB 2: BUKU ACARA (SERI / HEAT & PESERTA) -->
    <div class="tab-pane fade <?= ($currentTab === 'heats') ? 'show active' : '' ?>" id="tab-heats" role="tabpanel">
        <?php if (empty($bukuAcaraData)): ?>
            <div class="card-custom p-5 text-center text-muted">Belum ada acara lomba yang sesuai kriteria filter.</div>
        <?php else: ?>
            <!-- Quick Click-Sort for Heats Tab -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 p-2 px-3 rounded" style="background: #FAFBFC; border: 1px solid #DFE1E6;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="small fw-bold text-muted text-uppercase" style="font-size: 11px;">
                        <i class="bi bi-sort-down text-primary"></i> Klik untuk Urutkan:
                    </span>
                    <a href="<?= $getSortUrl('nomor_acara') ?>" class="badge <?= $sortBy === 'nomor_acara' ? 'bg-primary text-white shadow-sm' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-2" style="cursor: pointer;">
                        Nomor Acara <?= $getSortIcon('nomor_acara', 'numeric') ?>
                    </a>
                    <a href="<?= $getSortUrl('nama') ?>" class="badge <?= $sortBy === 'nama' ? 'bg-primary text-white shadow-sm' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-2" style="cursor: pointer;">
                        Nama Lomba <?= $getSortIcon('nama', 'alpha') ?>
                    </a>
                    <a href="<?= $getSortUrl('kategori') ?>" class="badge <?= $sortBy === 'kategori' ? 'bg-primary text-white shadow-sm' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-2" style="cursor: pointer;">
                        Jenis <?= $getSortIcon('kategori', 'alpha') ?>
                    </a>
                    <a href="<?= $getSortUrl('babak') ?>" class="badge <?= $sortBy === 'babak' ? 'bg-primary text-white shadow-sm' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-2" style="cursor: pointer;">
                        Babak <?= $getSortIcon('babak', 'alpha') ?>
                    </a>
                    <a href="<?= $getSortUrl('sesi') ?>" class="badge <?= $sortBy === 'sesi' ? 'bg-primary text-white shadow-sm' : 'bg-light text-dark border' ?> text-decoration-none py-2 px-2" style="cursor: pointer;">
                        Sesi & Waktu <?= $getSortIcon('sesi', 'alpha') ?>
                    </a>
                </div>
                <small class="text-muted">
                    Total: <strong><?= count($bukuAcaraData) ?> Acara</strong>
                </small>
            </div>

            <?php 
            $lastCategory = null; 
            foreach ($bukuAcaraData as $item): 
                $ev       = $item['event']; 
                $peserta  = $item['peserta']; 
                $isFinal  = $item['is_final']; 
                $heats    = $item['heats']; 

                // Jika diurutkan berdasarkan kategori, beri header grup kategori
                if ($sortBy === 'kategori' && $lastCategory !== ($ev['kategori_lomba'] ?? 'Lari')):
                    $lastCategory = $ev['kategori_lomba'] ?? 'Lari';
            ?>
                    <div class="d-flex align-items-center gap-2 my-3 p-2 px-3 rounded" style="background: #EAE6FF; border-left: 4px solid #6554C0;">
                        <i class="bi bi-diagram-3-fill fs-5 text-purple" style="color: #6554C0;"></i>
                        <span class="fw-bold text-uppercase" style="color: #403294; letter-spacing: 0.5px;">
                            KELOMPOK CABANG / JENIS LOMBA: <?= esc($lastCategory) ?>
                        </span>
                    </div>
            <?php endif; ?>

                <div class="card-custom mb-4">
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge badge-blue font-monospace fs-6">Event : <?= esc($ev['nomor_acara']) ?></span>
                                <?php 
                                $evTitleAccordion = esc($ev['nama_acara']);
                                $gAccordion = ($ev['gender'] === 'L') ? 'Male' : (($ev['gender'] === 'P') ? 'Female' : 'MIX');
                                if (stripos($evTitleAccordion, 'male') === false && stripos($evTitleAccordion, 'female') === false && stripos($evTitleAccordion, 'mix') === false) {
                                    $evTitleAccordion .= ' ' . $gAccordion;
                                }
                                ?>
                                <h5 class="fw-bold mb-0" style="color: #172B4D; font-family: 'Courier New', Courier, monospace;">
                                    <?= esc($ev['nomor_acara']) ?>. <?= $evTitleAccordion ?>
                                </h5>
                                <span class="badge badge-purple"><?= esc($ev['kategori_lomba'] ?? 'Lari') ?></span>
                                <span class="badge <?= $ev['gender'] === 'L' ? 'badge-blue' : 'badge-amber' ?>"><?= $ev['gender'] === 'L' ? 'Putra (Male)' : 'Putri (Female)' ?></span>
                            </div>
                            <small class="text-muted mt-1 d-block">
                                Babak: <span class="badge <?= $isFinal ? 'badge-emerald' : 'badge-amber' ?>"><?= esc($ev['babak']) ?></span>
                                &bull; Jadwal: <?= esc($ev['jadwal_hari'] ?: '-') ?> (<?= esc($ev['jadwal_waktu'] ?: '-') ?>)
                                &bull; Total Peserta: <strong><?= count($peserta) ?> Atlet</strong>
                                <?php if ($isFinal): ?>
                                    &bull; <span class="text-success"><i class="bi bi-check-circle-fill"></i> FINAL</span>
                                <?php else: ?>
                                    &bull; <span class="text-primary"><i class="bi bi-shuffle"></i> <?= count($heats) ?> Hit</span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php 
                        $namaCheck  = strtolower(($ev['nama_acara'] ?? '') . ' ' . ($ev['nama_lomba'] ?? '') . ' ' . ($ev['kategori_lomba'] ?? ''));
                        $isLari     = (bool)preg_match('/(lari|estafet|sprint)/i', $namaCheck);
                        $isField    = !$isLari && (bool)preg_match('/(tolak|lempar|lompat|lontar|cakram|martil|peluru|lembing)/i', $namaCheck);
                        $isSprint   = (bool)preg_match('/\b(30|60|80|100|200|400|estafet)\b/i', $namaCheck);
                        $isDistance = (bool)preg_match('/(800|1\.?000|1\.?500|3\.?000|5\.?000)/i', $namaCheck);
                        $isRelay    = (bool)preg_match('/(estafet|4\s*x|5\s*x|relay)/i', $namaCheck);
                        $firstCol   = ($isSprint && !$isDistance && !$isField) ? 'LINT' : 'NO';
                        $lastCol    = $isField ? 'Mark' : 'Time';
                        ?>
                        <div class="d-flex gap-1 align-items-center">
                            <a href="<?= base_url('hasil/form-lapangan/' . $ev['id']) ?>" target="_blank" class="btn btn-sm btn-outline-warning text-dark fw-semibold" title="Cetak Blangko Form Kertas Penjurian <?= $isField ? '(3x Percobaan)' : '(Catatan Waktu)' ?>">
                                <i class="bi bi-printer-fill me-1"></i> Form Kertas
                            </a>
                            <a href="<?= base_url('event/peserta/' . $ev['id']) ?>" class="btn btn-sm btn-primary fw-semibold shadow-xs">
                                <i class="bi bi-gear-fill me-1"></i> Atur Seri & Lintasan
                            </a>
                            <a href="<?= base_url('hasil/input/' . $ev['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-stopwatch me-1"></i> Hasil
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($peserta)): ?>
                            <div class="p-4 text-center text-muted">Belum ada atlet yang didaftarkan pada acara ini.</div>
                        <?php elseif ($isFinal && empty($heats)): ?>
                            <!-- BABAK FINAL: Cukup ditulis FINAL -->
                            <div class="px-3 py-2 border-bottom fw-bold text-success d-flex align-items-center justify-content-between" style="background-color: #ECFDF5;">
                                <span><i class="bi bi-trophy-fill me-1"></i> FINAL <?= $isField ? '(3x Percobaan)' : '' ?></span>
                                <small class="text-muted"><?= count($peserta) ?> Finalis</small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-dark-custom mb-0 font-monospace">
                                    <thead>
                                        <tr>
                                            <th width="65" class="text-center sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 0)" title="Klik untuk urutkan <?= $firstCol ?>">
                                                <span><?= $firstCol ?></span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                            </th>
                                            <th width="95" class="text-center sortable-peserta-th" style="cursor: pointer; background-color: #FEF3C7; color: #92400E;" onclick="sortPesertaTable(this, 1)" title="Klik untuk mengurutkan berdasarkan BIB (No Dada)">
                                                <span>BIB</span> <i class="bi bi-arrow-down-up text-amber ms-1" style="font-size: 11px;"></i>
                                            </th>
                                            <th class="sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 2)" title="Klik untuk urutkan Nama Atlet">
                                                <span>Nama Atlet</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                            </th>
                                            <?php if (!$isRelay): ?>
                                            <th width="65" class="text-center sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 3)" title="Klik untuk urutkan Tahun Lahir (YR)">
                                                <span>YR</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                            </th>
                                            <?php endif; ?>
                                            <th class="sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 4)" title="Klik untuk urutkan Team">
                                                <span>Team</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                            </th>
                                            <?php if ($isField): ?>
                                                <th width="80" class="text-center">P1</th>
                                                <th width="80" class="text-center">P2</th>
                                                <th width="80" class="text-center">P3</th>
                                                <th width="110" class="text-center sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 8)" title="Klik untuk urutkan Mark">
                                                    <span>Mark</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                                </th>
                                            <?php else: ?>
                                                <th width="130" class="text-center sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 5)" title="Klik untuk urutkan Time">
                                                    <span>Time</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                                </th>
                                            <?php endif; ?>
                                            <th width="150" class="text-center">Keterangan / Juara</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $noUrut = 1; foreach ($peserta as $p): ?>
                                            <?php
                                            $yrVal = !empty($p['tanggal_lahir']) ? date('y', strtotime($p['tanggal_lahir'])) : '';
                                            $lintVal = ($firstCol === 'LINT') ? (!empty($p['lane']) ? $p['lane'] : $noUrut) : $noUrut;
                                            $p1 = ''; $p2 = ''; $p3 = '';
                                            if ($isField && !empty($p['keterangan']) && preg_match('/P1:\s*([^|]+)\s*\|\s*P2:\s*([^|]+)\s*\|\s*P3:\s*(.*)/i', $p['keterangan'], $km)) {
                                                $p1 = trim($km[1]) === '-' ? '' : trim($km[1]);
                                                $p2 = trim($km[2]) === '-' ? '' : trim($km[2]);
                                                $p3 = trim($km[3]) === '-' ? '' : trim($km[3]);
                                            }
                                            ?>
                                            <tr>
                                                <td class="text-center fw-bold"><?= esc($lintVal) ?></td>
                                                <td class="text-center" data-nodada="<?= esc($p['no_dada'] ?: '999999') ?>">
                                                    <span class="badge <?= !empty($p['no_dada']) ? 'badge-amber' : 'badge-secondary' ?> font-monospace fw-bold fs-6 btn-quick-nodada" 
                                                          style="cursor: pointer;" 
                                                          data-atlet-id="<?= $p['atlet_id'] ?>" 
                                                          data-atlet-name="<?= esc($p['nama_atlet']) ?>" 
                                                          data-no-dada="<?= esc($p['no_dada'] ?? '') ?>" 
                                                          title="Klik untuk ubah No Dada atlet ini">
                                                        <?= esc($p['no_dada'] ?: '-') ?> <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>
                                                    </span>
                                                </td>
                                                <td class="fw-bold" style="color: #172B4D;"><?= esc($p['nama_atlet']) ?></td>
                                                <?php if (!$isRelay): ?>
                                                <td class="text-center fw-semibold text-secondary"><?= esc($yrVal ?: '-') ?></td>
                                                <?php endif; ?>
                                                <td><?= esc($p['nama_sekolah'] ?: '-') ?></td>
                                                <?php if ($isField): ?>
                                                    <td class="text-center"><?= esc($p1 ?: '-') ?></td>
                                                    <td class="text-center"><?= esc($p2 ?: '-') ?></td>
                                                    <td class="text-center"><?= esc($p3 ?: '-') ?></td>
                                                    <td class="text-center">
                                                        <?php if (!empty($p['prestasi'])): ?>
                                                            <span class="badge badge-emerald fw-bold fs-6"><?= esc($p['prestasi']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php else: ?>
                                                    <td class="text-center">
                                                        <?php if (!empty($p['prestasi'])): ?>
                                                            <span class="badge badge-emerald fw-bold fs-6"><?= esc($p['prestasi']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
                                                <td class="text-center">
                                                    <?php if (!empty($p['peringkat'])): ?>
                                                        <span class="badge badge-emerald fw-bold">
                                                            Juara <?= $p['peringkat'] ?> <?= !empty($p['medali']) && $p['medali'] !== 'None' ? '(' . $p['medali'] . ')' : '' ?>
                                                        </span>
                                                    <?php elseif (!empty($p['ket_hasil'])): ?>
                                                        <span class="badge badge-amber"><?= esc($p['ket_hasil']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php $noUrut++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <!-- BABAK PENYISIHAN: DIBAGI PER HIT 1, 2, 3... -->
                            <?php foreach ($heats as $heatNum => $pesertaHeat): ?>
                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center" style="background-color: #F8FAFC;">
                                    <span class="fw-bold font-monospace <?= $isFinal ? 'text-success' : 'text-primary' ?>">
                                        <i class="bi <?= $isFinal ? 'bi-trophy-fill' : 'bi-flag-fill' ?> me-1"></i> <?= $isFinal ? 'FINAL &bull; SERI ' . $heatNum : 'HIT ' . $heatNum ?>
                                    </span>
                                    <small class="text-muted"><?= count($pesertaHeat) ?> Peserta di Hit ini</small>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-dark-custom mb-0 font-monospace">
                                        <thead>
                                            <tr>
                                                <th width="65" class="text-center sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 0)" title="Klik untuk urutkan <?= $firstCol ?>">
                                                    <span><?= $firstCol ?></span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                                </th>
                                                <th width="95" class="text-center sortable-peserta-th" style="cursor: pointer; background-color: #FEF3C7; color: #92400E;" onclick="sortPesertaTable(this, 1)" title="Klik untuk mengurutkan berdasarkan BIB (No Dada)">
                                                    <span>BIB</span> <i class="bi bi-arrow-down-up text-amber ms-1" style="font-size: 11px;"></i>
                                                </th>
                                                <th class="sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 2)" title="Klik untuk urutkan Nama Atlet">
                                                    <span>Nama Atlet</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                                </th>
                                                <?php if (!$isRelay): ?>
                                                <th width="65" class="text-center sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 3)" title="Klik untuk urutkan Tahun Lahir (YR)">
                                                    <span>YR</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                                </th>
                                                <?php endif; ?>
                                                <th class="sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 4)" title="Klik untuk urutkan Team">
                                                    <span>Team</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                                </th>
                                                <?php if ($isField): ?>
                                                    <th width="80" class="text-center">P1</th>
                                                    <th width="80" class="text-center">P2</th>
                                                    <th width="80" class="text-center">P3</th>
                                                    <th width="110" class="text-center sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 8)" title="Klik untuk urutkan Mark">
                                                        <span>Mark</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                                    </th>
                                                <?php else: ?>
                                                    <th width="130" class="text-center sortable-peserta-th" style="cursor: pointer;" onclick="sortPesertaTable(this, 5)" title="Klik untuk urutkan Time">
                                                        <span>Time</span> <i class="bi bi-arrow-down-up opacity-50 ms-1" style="font-size: 10px;"></i>
                                                    </th>
                                                <?php endif; ?>
                                                <th width="150" class="text-center">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $noUrut = 1; foreach ($pesertaHeat as $p): ?>
                                                <?php
                                                $yrVal = !empty($p['tanggal_lahir']) ? date('y', strtotime($p['tanggal_lahir'])) : '';
                                                $lintVal = ($firstCol === 'LINT') ? (!empty($p['lane']) ? $p['lane'] : $noUrut) : $noUrut;
                                                $p1 = ''; $p2 = ''; $p3 = '';
                                                if ($isField && !empty($p['keterangan']) && preg_match('/P1:\s*([^|]+)\s*\|\s*P2:\s*([^|]+)\s*\|\s*P3:\s*(.*)/i', $p['keterangan'], $km)) {
                                                    $p1 = trim($km[1]) === '-' ? '' : trim($km[1]);
                                                    $p2 = trim($km[2]) === '-' ? '' : trim($km[2]);
                                                    $p3 = trim($km[3]) === '-' ? '' : trim($km[3]);
                                                }
                                                ?>
                                                <tr>
                                                    <td class="text-center fw-bold"><?= esc($lintVal) ?></td>
                                                    <td class="text-center" data-nodada="<?= esc($p['no_dada'] ?: '999999') ?>">
                                                        <span class="badge <?= !empty($p['no_dada']) ? 'badge-amber' : 'badge-secondary' ?> font-monospace fw-bold fs-6 btn-quick-nodada" 
                                                              style="cursor: pointer;" 
                                                              data-atlet-id="<?= $p['atlet_id'] ?>" 
                                                              data-atlet-name="<?= esc($p['nama_atlet']) ?>" 
                                                              data-no-dada="<?= esc($p['no_dada'] ?? '') ?>" 
                                                              title="Klik untuk ubah No Dada atlet ini">
                                                            <?= esc($p['no_dada'] ?: '-') ?> <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>
                                                        </span>
                                                    </td>
                                                    <td class="fw-bold" style="color: #172B4D;"><?= esc($p['nama_atlet']) ?></td>
                                                    <?php if (!$isRelay): ?>
                                                    <td class="text-center fw-semibold text-secondary"><?= esc($yrVal ?: '-') ?></td>
                                                    <?php endif; ?>
                                                    <td><?= esc($p['nama_sekolah'] ?: '-') ?></td>
                                                    <?php if ($isField): ?>
                                                        <td class="text-center"><?= esc($p1 ?: '-') ?></td>
                                                        <td class="text-center"><?= esc($p2 ?: '-') ?></td>
                                                        <td class="text-center"><?= esc($p3 ?: '-') ?></td>
                                                        <td class="text-center">
                                                            <?php if (!empty($p['prestasi'])): ?>
                                                                <span class="badge badge-emerald fw-bold fs-6"><?= esc($p['prestasi']) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php else: ?>
                                                        <td class="text-center">
                                                            <?php if (!empty($p['prestasi'])): ?>
                                                                <span class="badge badge-emerald fw-bold fs-6"><?= esc($p['prestasi']) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td class="text-center">
                                                        <?php if (!empty($p['ket_hasil'])): ?>
                                                            <span class="badge badge-amber"><?= esc($p['ket_hasil']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php $noUrut++; endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- TAB 3: COVER RESMI -->
    <div class="tab-pane fade <?= ($currentTab === 'cover') ? 'show active' : '' ?>" id="tab-cover" role="tabpanel">
        <div class="d-flex justify-content-center">
            <div class="card-custom p-5 text-center shadow-lg" style="max-width: 650px; width: 100%; border: 2px solid #DFE1E6; background: #FFFFFF;">
                <!-- Header Cover -->
                <h2 class="fw-bold mb-3" style="font-family: 'Times New Roman', serif; letter-spacing: 2px; color: #000;">
                    BUKU ACARA
                </h2>
                <h5 class="fw-bold mb-1" style="font-family: 'Times New Roman', serif; color: #000;">
                    <?= esc($activeKejuaraan['nama'] ?? 'Kejurwil Kelompok Umur Atletik Tingkat Kota Pekalongan') ?>
                </h5>
                <h6 class="fw-bold mb-4" style="font-family: 'Times New Roman', serif; color: #000;">
                    <?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng') ?> 29 Agustus 2026
                </h6>

                <!-- Logo PASI -->
                <div class="my-5">
                    <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="max-height: 240px; width: auto;" class="img-fluid">
                </div>

                <!-- Footer Cover -->
                <div class="mt-4 pt-4" style="font-family: 'Times New Roman', serif; color: #000; line-height: 1.6;">
                    <div class="fw-bold fs-6">PEMERINTAH KOTA PEKALONGAN</div>
                    <div class="fw-bold fs-6">PERSATUAN ATLETIK SELURUH INDONESIA</div>
                    <div class="fw-bold fs-6">KOTA PEKALONGAN</div>
                    <div class="fw-bold fs-6">TAHUN 2026</div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <span class="badge badge-emerald">Tersimpan di Sheet 1 (.xlsx) & Halaman 1 Cetak PDF</span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Edit Babak, Sesi & Jam Acara -->
<div class="modal fade" id="modalEditSesi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('buku-acara/update-jadwal') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" id="editEventId">
                <input type="hidden" name="tab" id="editModalTab" value="<?= esc($currentTab) ?>">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #172B4D;">
                        <i class="bi bi-gear-wide-connected text-primary me-2"></i> Pengaturan Babak & Jadwal Acara
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Nama Acara Perlombaan</label>
                        <div class="fw-bold text-dark fs-6" id="editEventName">-</div>
                    </div>

                    <!-- Pengaturan Babak (Final vs Penyisihan) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Babak Perlombaan <span class="text-danger">*</span></label>
                        <select name="babak" id="editBabak" class="form-select fw-bold" onchange="handleBabakChange(this.value)">
                            <option value="Final">FINAL (Semua peserta digabung jadi 1 Heat bersama)</option>
                            <option value="Penyisihan">PENYISIHAN (Peserta dibagi ke beberapa Seri / Heat)</option>
                            <option value="Semifinal">SEMIFINAL (Peserta dibagi ke beberapa Seri / Heat)</option>
                        </select>
                    </div>

                    <!-- Pengaturan Kapasitas Heat untuk Penyisihan (Isi 2 s/d 8) -->
                    <div id="boxPenyisihanSetting" class="p-3 mb-3 rounded" style="background: #FEF3C7; border: 1px solid #FDE68A;">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="bi bi-sliders me-1 text-warning"></i> Kapasitas Peserta per Seri (Heat):
                        </label>
                        <select name="lanes_per_heat" id="editLanesPerHeat" class="form-select mb-2 fw-semibold">
                            <option value="8" selected>8 Peserta per Seri (Standar Stadion Lintasan Lari - Rekomendasi)</option>
                            <option value="7">7 Peserta per Seri</option>
                            <option value="6">6 Peserta per Seri</option>
                            <option value="5">5 Peserta per Seri</option>
                            <option value="4">4 Peserta per Seri</option>
                            <option value="3">3 Peserta per Seri</option>
                            <option value="2">2 Peserta per Seri</option>
                        </select>
                        <small class="text-muted d-block">
                            <i class="bi bi-info-circle-fill me-1 text-primary"></i> 1 heat dapat disetting berisi 2 hingga 8 peserta. Sistem akan otomatis membagi peserta acara ke dalam beberapa heat sesuai kapasitas yang Anda tentukan.
                        </small>
                    </div>

                    <!-- Notifikasi Khusus Babak Final -->
                    <div id="boxFinalSetting" class="p-3 mb-3 rounded" style="background: #ECFDF5; border: 1px solid #A7F3D0;">
                        <div class="d-flex align-items-center gap-2 text-success fw-bold small">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <span>Babak Final: Seluruh peserta akan didaftarkan ke dalam SATU DAFTAR (1 Seri / Heat bersama).</span>
                        </div>
                    </div>

                    <!-- Otomatis Terapkan ke Peserta -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="auto_rearrange" value="1" id="autoRearrangeCheck" checked>
                        <label class="form-check-label small fw-bold" for="autoRearrangeCheck">
                            Otomatis atur susunan Seri & Lintasan peserta sesuai babak
                        </label>
                    </div>

                    <hr class="my-3 text-muted">

                    <div class="mb-3">
                        <label class="form-label">Hari / Tanggal</label>
                        <input type="text" name="jadwal_hari" id="editJadwalHari" class="form-control" value="Sabtu, 29 Agustus 2026">
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Sesi</label>
                            <select name="sesi" id="editSesi" class="form-select">
                                <option value="Pagi">PAGI</option>
                                <option value="Sore">SORE</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam Mulai</label>
                            <input type="text" name="jam" id="editJam" class="form-control" placeholder="08.00 atau 13.30">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-gradient">
                        <i class="bi bi-save me-1"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Quick Edit No Dada -->
<div class="modal fade" id="modalQuickNoDada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 10px;">
            <div class="modal-header py-2 px-3" style="background-color: #FEF3C7; border-bottom: 1px solid #FDE68A;">
                <h6 class="modal-title fw-bold mb-0" style="color: #92400E;">
                    <i class="bi bi-tag-fill text-warning me-1"></i> Ubah No Dada Atlet
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formQuickNoDada">
                <div class="modal-body p-3">
                    <input type="hidden" id="quickAtletId" name="atlet_id">
                    <div class="mb-2">
                        <label class="form-label text-muted small mb-0" style="font-size: 11px;">NAMA ATLET:</label>
                        <div class="fw-bold fs-6 text-truncate" id="quickAtletName" style="color: #172B4D;">-</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small mb-1" style="color: #172B4D;">Nomor Dada:</label>
                        <input type="text" id="quickNoDadaInput" name="no_dada" class="form-control form-control-lg font-monospace fw-bold text-center" placeholder="Misal: 101" style="letter-spacing: 2px; font-size: 1.3rem; border-color: #F59E0B;" autofocus>
                        <div class="form-text small text-muted" style="font-size: 11px;">Nomor dada untuk identifikasi atlet oleh juri lintasan. Kosongkan jika belum ada.</div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light" style="border-top: 1px solid #DFE1E6;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3" id="btnSaveQuickNoDada">
                        <i class="bi bi-check-lg me-1"></i> Simpan No Dada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function setTabParam(tabName) {
    $('#activeTabInput').val(tabName);
    $('#editModalTab').val(tabName);
    var url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url.toString());
}

function handleBabakChange(babak) {
    if (babak === 'Final') {
        $('#boxFinalSetting').show();
        $('#boxPenyisihanSetting').hide();
    } else {
        $('#boxFinalSetting').hide();
        $('#boxPenyisihanSetting').show();
    }
}

function openEditSesiModal(id, name, hari, sesi, jam, babak, lanes) {
    $('#editEventId').val(id);
    $('#editEventName').text(name);
    $('#editJadwalHari').val(hari);
    $('#editSesi').val(sesi);
    $('#editJam').val(jam);
    
    babak = babak || 'Final';
    $('#editBabak').val(babak);
    if (lanes) {
        $('#editLanesPerHeat').val(lanes);
    } else {
        $('#editLanesPerHeat').val('8');
    }
    handleBabakChange(babak);

    var modal = new bootstrap.Modal(document.getElementById('modalEditSesi'));
    modal.show();
}

// 1. Sort Peserta Table in Tab 2 (Client-Side Sort on click header)
function sortPesertaTable(th, colIdx) {
    var table = $(th).closest('table');
    var tbody = table.find('tbody');
    var rows = tbody.find('tr').toArray();
    var currentDir = $(th).data('dir') || 'asc';
    var nextDir = (currentDir === 'asc') ? 'desc' : 'asc';
    $(th).data('dir', nextDir);

    // Reset icons on sibling headers in this table
    table.find('thead th i').removeClass('bi-sort-down bi-sort-up-alt text-primary text-amber').addClass('bi-arrow-down-up opacity-50');
    var icon = $(th).find('i');
    icon.removeClass('bi-arrow-down-up opacity-50');
    if (nextDir === 'asc') {
        icon.addClass('bi-sort-down ' + (colIdx === 1 ? 'text-amber' : 'text-primary'));
    } else {
        icon.addClass('bi-sort-up-alt ' + (colIdx === 1 ? 'text-amber' : 'text-primary'));
    }

    rows.sort(function(a, b) {
        var cellA = $(a).find('td').eq(colIdx);
        var cellB = $(b).find('td').eq(colIdx);

        // Sorting BIB (Column 1)
        if (colIdx === 1) {
            var rawA = cellA.attr('data-nodada') || cellA.text().trim();
            var rawB = cellB.attr('data-nodada') || cellB.text().trim();
            var numA = parseInt(rawA.replace(/\D/g, ''), 10);
            var numB = parseInt(rawB.replace(/\D/g, ''), 10);
            if (isNaN(numA)) numA = 999999;
            if (isNaN(numB)) numB = 999999;
            return nextDir === 'asc' ? (numA - numB) : (numB - numA);
        }

        // Sorting LINT / NO (Col 0) or YR (Col 3)
        if (colIdx === 0 || colIdx === 3) {
            var numA = parseInt(cellA.text().trim().replace(/\D/g, ''), 10) || 0;
            var numB = parseInt(cellB.text().trim().replace(/\D/g, ''), 10) || 0;
            return nextDir === 'asc' ? (numA - numB) : (numB - numA);
        }

        // Text sorting (Nama, Team, Mark, Time)
        var textA = cellA.text().trim();
        var textB = cellB.text().trim();
        return nextDir === 'asc' ? textA.localeCompare(textB) : textB.localeCompare(textA);
    });

    tbody.empty().append(rows);
}

// 2. Quick Edit No Dada Modal Handler
$(document).on('click', '.btn-quick-nodada', function(e) {
    e.preventDefault();
    var atletId   = $(this).data('atlet-id');
    var atletName = $(this).data('atlet-name');
    var noDada    = $(this).data('no-dada') || '';

    $('#quickAtletId').val(atletId);
    $('#quickAtletName').text(atletName);
    $('#quickNoDadaInput').val(noDada);

    var modal = new bootstrap.Modal(document.getElementById('modalQuickNoDada'));
    modal.show();
    setTimeout(function() {
        $('#quickNoDadaInput').focus().select();
    }, 400);
});

$('#formQuickNoDada').on('submit', function(e) {
    e.preventDefault();
    var btn = $('#btnSaveQuickNoDada');
    var atletId = $('#quickAtletId').val();
    var noDada  = $('#quickNoDadaInput').val().trim();

    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

    $.ajax({
        url: '<?= base_url('atlet/update-no-dada') ?>',
        type: 'POST',
        data: {
            atlet_id: atletId,
            no_dada: noDada
        },
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Simpan No Dada');
            var modalEl = document.getElementById('modalQuickNoDada');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Update badge on all occurrences of this athlete on screen
            var targets = $('.btn-quick-nodada[data-atlet-id="' + atletId + '"]');
            targets.data('no-dada', noDada);
            targets.closest('td').attr('data-nodada', noDada || '999999');
            if (noDada) {
                targets.removeClass('badge-secondary').addClass('badge-amber').html(noDada + ' <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>');
            } else {
                targets.removeClass('badge-amber').addClass('badge-secondary').html('- <i class="bi bi-pencil-fill ms-1 opacity-50" style="font-size: 8px;"></i>');
            }
        },
        error: function(err) {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Simpan No Dada');
            alert('Gagal menyimpan No Dada. Silakan coba lagi.');
        }
    });
});

function toggleUploadSection(show) {
    var el = document.getElementById('sectionUploadFile');
    if (el) {
        if (show) {
            el.classList.remove('d-none');
        } else {
            el.classList.add('d-none');
        }
    }
}
</script>

<!-- Modal Sinkronisasi Resmi dari File Excel -->
<div class="modal fade" id="modalSyncOfficialBook" tabindex="-1" aria-labelledby="modalSyncOfficialBookLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-warning text-dark py-3">
                <h5 class="modal-title fw-bold" id="modalSyncOfficialBookLabel">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Sinkronisasi Buku Acara Resmi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('buku-acara/sync-official') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-3" style="font-size: 13px;">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Proses ini akan membaca dan menyinkronkan <strong>52 Nomor Acara</strong>, <strong>Jadwal & Waktu</strong>, serta <strong>Susunan Seri / Hit & Lintasan Peserta</strong> secara valid sesuai template resmi.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small text-uppercase">Pilihan Sumber File:</label>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="source_type" id="srcDefault" value="default" checked onchange="toggleUploadSection(false)">
                                <label class="form-check-label fw-semibold" for="srcDefault">
                                    Gunakan File Default Server
                                </label>
                                <small class="text-muted d-block ms-4 font-monospace">0FIX BUKU ACARA ATLETIK KARES 2026-1.xlsx (Terbaru)</small>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="source_type" id="srcUpload" value="upload" onchange="toggleUploadSection(true)">
                                <label class="form-check-label fw-semibold" for="srcUpload">
                                    Unggah File Revisi Baru (.xlsx)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 d-none" id="sectionUploadFile">
                        <label for="excel_file" class="form-label fw-bold small text-muted">PILIH FILE EXCEL (.XLSX):</label>
                        <input class="form-control" type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls">
                    </div>

                    <div class="p-2 px-3 rounded bg-light border-start border-4 border-warning text-muted" style="font-size: 12px;">
                        <strong>Catatan:</strong> Nomor estafet (Acara 51 & 52) akan otomatis disatukan menjadi 1 regu per lintasan dengan daftar seluruh pelari tersimpan rapi.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold btn-sm px-4">
                        <i class="bi bi-play-fill me-1"></i> Mulai Sinkronisasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
