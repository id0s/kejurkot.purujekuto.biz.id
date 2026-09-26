<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Event Detail Header -->
<?php 
$currentHeatsList = array_unique(array_filter(array_column($pesertaList, 'heat')));
$currentHeatsCount = count($currentHeatsList);
$isDirectFinal = ($currentHeatsCount <= 1 || strcasecmp($event['babak'], 'Final') === 0);
$isEstafetEvent = (bool)preg_match('/estafet/i', $event['nama_acara'] ?? '');
$unitPeserta = $isEstafetEvent ? 'Team' : 'Atlet';
?>
<div class="card-custom mb-4 p-4" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(139, 92, 246, 0.1) 100%); border-color: rgba(59, 130, 246, 0.3);">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge badge-amber fs-6">Acara <?= esc($event['nomor_acara']) ?></span>
                
                <?php if ($isDirectFinal): ?>
                    <span class="badge badge-emerald fs-6">
                        <i class="bi bi-trophy-fill me-1"></i> 1 Hit (Langsung FINAL - Default)
                    </span>
                <?php else: ?>
                    <span class="badge badge-primary fs-6">
                        <i class="bi bi-flag-fill me-1"></i> <?= $currentHeatsCount ?> Hit (Penyisihan)
                    </span>
                <?php endif; ?>

                <?php if ($isEstafetEvent): ?>
                    <span class="badge badge-purple fs-6">
                        <i class="bi bi-diagram-3-fill me-1"></i> Beregu Estafet
                    </span>
                <?php endif; ?>

                <span class="badge badge-blue fs-6">
                    <?= esc($event['satuan_hasil'] ?? 'Waktu') ?>
                </span>

                <?php if ($event['gender'] === 'L'): ?>
                    <span class="badge badge-blue fs-6"><i class="bi bi-gender-male me-1"></i> Male (Putra)</span>
                <?php elseif ($event['gender'] === 'P'): ?>
                    <span class="badge badge-rose fs-6" style="background-color: #e11d48;"><i class="bi bi-gender-female me-1"></i> Female (Putri)</span>
                <?php else: ?>
                    <span class="badge badge-purple fs-6"><i class="bi bi-people-fill me-1"></i> MIX (Campuran)</span>
                <?php endif; ?>
            </div>
            <?php 
            $gSuffix = ($event['gender'] === 'L') ? 'Male' : (($event['gender'] === 'P') ? 'Female' : 'MIX');
            $evTitle = esc($event['nama_acara']);
            if (stripos($evTitle, 'male') === false && stripos($evTitle, 'female') === false && stripos($evTitle, 'mix') === false) {
                $evTitle .= ' ' . $gSuffix;
            }
            ?>
            <h3 class="fw-bold text-white mb-1"><?= $evTitle ?></h3>
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar3 me-1"></i> <?= esc($event['jadwal_hari'] ?: '-') ?>
                &bull; <i class="bi bi-clock me-1"></i> <?= esc($event['jadwal_waktu'] ?: '-') ?>
                &bull; Total Peserta: <strong class="text-info"><?= count($pesertaList) ?> <?= $unitPeserta ?></strong>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= base_url('event') ?>" class="btn btn-secondary-custom">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Acara
            </a>
            <button type="button" class="btn btn-primary fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAutoArrange">
                <i class="bi bi-gear-fill"></i> Atur Seri & Lintasan
            </button>
            <button type="button" class="btn btn-info fw-bold text-white px-3 shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalPreviewSusunan" onclick="generatePreviewSusunan()">
                <i class="bi bi-eye-fill"></i> Preview
            </button>
            <?php if ($isEstafetEvent): ?>
                <button type="button" class="btn fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-2 text-white" style="background: linear-gradient(135deg, #7c3aed, #6d28d9); border: none;" onclick="openAddTeamEstafetModal()">
                    <i class="bi bi-diagram-3-fill"></i> Tambah Tim Estafet
                </button>
            <?php else: ?>
                <button class="btn btn-secondary-custom" data-bs-toggle="modal" data-bs-target="#modalAddPeserta">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Peserta
                </button>
            <?php endif; ?>
            <a href="<?= base_url('hasil/form-lapangan/' . $event['id']) ?>" target="_blank" class="btn btn-warning fw-bold text-dark d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-printer-fill"></i> Cetak Form Kertas
            </a>
            <?php if (!empty($pesertaList)): ?>
            <button type="button" class="btn btn-info fw-bold text-white d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCallRoom">
                <i class="bi bi-megaphone-fill"></i> Panggilan Suara
            </button>
            <?php endif; ?>
            <a href="<?= base_url('hasil/input/' . $event['id']) ?>" class="btn btn-success d-inline-flex align-items-center gap-1">
                <i class="bi bi-stopwatch-fill"></i> Input Hasil
            </a>
        </div>
    </div>
</div>

<!-- Peserta Management Card -->
<div class="card-custom">
    <form id="formSusunanPeserta" action="<?= base_url('event/update-peserta/' . $event['id']) ?>" method="post">
        <?= csrf_field() ?>
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 pb-3 border-bottom border-secondary border-opacity-25">
            <div>
                <span class="fw-bold text-white fs-6">
                    <i class="bi bi-people-fill me-2 text-primary"></i> Daftar Peserta Lomba (Hit & Lintasan)
                </span>
                <div class="text-muted small mt-1">
                    Default 1 Hit adalah <strong>Langsung FINAL</strong>. Jika memiliki beberapa Hit, peserta terbagi dalam kelompok (Hit 1, Hit 2, dst).
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Tombol Atur Seri & Lintasan -->
                <button type="button" class="btn btn-primary fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAutoArrange">
                    <i class="bi bi-gear-fill"></i> Atur Seri & Lintasan
                </button>
                <button type="button" class="btn btn-info fw-bold text-white px-3 shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalPreviewSusunan" onclick="generatePreviewSusunan()">
                    <i class="bi bi-eye-fill"></i> Preview
                </button>
                <?php if (!empty($pesertaList)): ?>
                    <button type="submit" id="btnSubmitSusunan" class="btn btn-success fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> Simpan Susunan Hit & Lintasan
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Jarak Tambahan & Toolbar Pengatur Tampilan (Sesuai Permintaan User: Diberi Jarak dari Judul) -->
            <div class="p-3 mb-4 rounded d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 shadow-sm" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(255, 255, 255, 0.12); margin-top: 0.5rem;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-white-50 small fw-bold text-uppercase me-1">
                        <i class="bi bi-sliders me-1 text-info"></i> Mode Tampilan:
                    </span>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-primary active fw-bold px-3 d-inline-flex align-items-center gap-1" id="tabBtnDnd" onclick="switchSusunanView('dnd')">
                            <i class="bi bi-arrows-move"></i>
                            <span>Papan Drag & Drop (Pindah Heat Langsung Tarik)</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary text-white fw-bold px-3 d-inline-flex align-items-center gap-1" id="tabBtnTable" onclick="switchSusunanView('table')">
                            <i class="bi bi-table"></i>
                            <span>Tabel Input Manual</span>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1" onclick="addNewHeatColumn()">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>+ Tambah Kolom Hit</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1" onclick="autoFixAllLanes()" title="Rapikan nomor lintasan 1 s/d 8 di setiap Hit secara berurutan">
                        <i class="bi bi-sort-numeric-down"></i>
                        <span>Rapikan Lintasan 1-8</span>
                    </button>
                    <span id="toastAjaxStatus" class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small d-none animate__animated animate__fadeIn">
                        <i class="bi bi-check-circle-fill me-1"></i> Tersimpan Otomatis
                    </span>
                </div>
            </div>

            <?php if (empty($pesertaList)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    Belum ada atlet yang terdaftar di acara ini. Klik tombol <strong>+ Tambah Peserta</strong> untuk mendaftarkan atlet.
                </div>
            <?php else: ?>

                <!-- VIEW 1: PAPAN DRAG & DROP (PINDAH HEAT LANGSUNG TARIK / KLIK PINDAH) -->
                <div id="viewDndBoard" class="mb-4">
                    <div class="alert alert-info py-2 px-3 mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 border-0 shadow-sm" style="background: rgba(13, 110, 253, 0.12); border-left: 4px solid #0d6efd !important;">
                        <div class="small text-white">
                            <i class="bi bi-info-circle-fill text-info me-1"></i>
                            <strong>Cara Mengatur Hit:</strong> 
                            1. <strong>Tarik (Drag)</strong> ikon grip <i class="bi bi-grip-vertical"></i> kartu atlet ke Hit lain. 
                            2. <strong>Atau Klik "Pindah"</strong> pada kartu atlet untuk pindah tanpa menarik. 
                            3. Gunakan panah <i class="bi bi-chevron-up"></i><i class="bi bi-chevron-down"></i> untuk mengubah urutan lintasan. Tiap Hit max <strong>8 lintasan</strong>.
                        </div>
                        <span class="badge badge-emerald small px-2 py-1 align-self-start align-self-md-center">Auto-Save Aktif</span>
                    </div>

                    <?php
                    // Kelompokkan atlet per heat untuk board
                    $boardHeats = [];
                    foreach ($pesertaList as $p) {
                        $h = (int)($p['heat'] ?: 1);
                        $boardHeats[$h][] = $p;
                    }
                    ksort($boardHeats);
                    if (empty($boardHeats)) {
                        $boardHeats = [1 => []];
                    }
                    // Urutkan berdasarkan lintasan
                    foreach ($boardHeats as $hNum => &$runners) {
                        usort($runners, function($a, $b) {
                            $lA = is_numeric($a['lane']) ? (int)$a['lane'] : 999;
                            $lB = is_numeric($b['lane']) ? (int)$b['lane'] : 999;
                            return $lA <=> $lB;
                        });
                    }
                    unset($runners);
                    ?>

                    <div class="dnd-board-container" id="dndBoardContainer" style="display: flex; gap: 1.25rem; overflow-x: auto; padding-bottom: 1.25rem; align-items: flex-start;">
                        <?php foreach ($boardHeats as $hNum => $hPeserta): ?>
                            <div class="dnd-heat-column flex-shrink-0" data-heat="<?= $hNum ?>" style="width: 320px; background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.35);">
                                <!-- Heat Column Header -->
                                <div class="dnd-heat-header p-3 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center" style="background: rgba(30, 41, 59, 0.85); border-top-left-radius: 8px; border-top-right-radius: 8px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary fs-6 fw-bold px-2 py-1">Hit <?= $hNum ?></span>
                                        <span class="badge bg-secondary dnd-heat-count" data-heat="<?= $hNum ?>" style="font-size: 11px;">
                                            <?= count($hPeserta) ?>/8 <?= $unitPeserta ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-xs btn-outline-info py-0 px-2" onclick="renumberSingleHeat(<?= $hNum ?>)" title="Rapikan nomor lintasan 1 s/d 8 di Hit ini" style="font-size: 11px;">
                                            <i class="bi bi-sort-numeric-down"></i> 1-8
                                        </button>
                                    </div>
                                </div>

                                <!-- Heat Dropzone -->
                                <div class="dnd-dropzone p-2" data-heat="<?= $hNum ?>" style="min-height: 220px; display: flex; flex-direction: column; gap: 0.65rem;">
                                    <?php if (empty($hPeserta)): ?>
                                        <div class="dnd-empty-placeholder text-center py-4 text-muted small" style="border: 2px dashed rgba(255,255,255,0.1); border-radius: 6px;">
                                            <i class="bi bi-box-arrow-in-down fs-4 d-block mb-1 text-secondary"></i>
                                            Tarik atlet ke Hit ini
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($hPeserta as $p): ?>
                                            <div class="dnd-athlete-card p-2 rounded shadow-sm position-relative"
                                                 draggable="true"
                                                 data-id="<?= $p['id'] ?>"
                                                 data-heat="<?= $p['heat'] ?: 1 ?>"
                                                 data-lane="<?= esc($p['lane'] ?? '') ?>"
                                                 style="background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.12); user-select: none;">
                                                
                                                <!-- Top Row: Drag Handle, Lane Badge, No Dada, & Quick Actions -->
                                                <div class="d-flex justify-content-between align-items-center gap-1 mb-1">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <div class="dnd-drag-handle px-1 py-1 text-white-50" title="Tahan & tarik untuk memindahkan" style="cursor: grab; touch-action: none;">
                                                            <i class="bi bi-grip-vertical fs-5"></i>
                                                        </div>
                                                        <span class="badge bg-info text-dark font-monospace fw-bold dnd-lane-badge" style="min-width: 30px; font-size: 11px;">
                                                            L<?= esc($p['lane'] ?: '-') ?>
                                                        </span>
                                                        <span class="badge bg-warning text-dark font-monospace fw-bold" style="font-size: 11px;">
                                                            #<?= esc(ltrim($p['no_dada'], "'") ?: '-') ?>
                                                        </span>
                                                    </div>

                                                    <div class="d-flex align-items-center gap-1">
                                                        <!-- Order Up / Down buttons -->
                                                        <div class="btn-group btn-group-xs" role="group">
                                                            <button type="button" class="btn btn-xs btn-outline-secondary text-white-50 px-1 py-0" onclick="moveAthleteOrder(<?= $p['id'] ?>, 'up')" title="Geser ke atas" style="font-size: 10px; line-height: 1.2;">
                                                                <i class="bi bi-chevron-up"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-xs btn-outline-secondary text-white-50 px-1 py-0" onclick="moveAthleteOrder(<?= $p['id'] ?>, 'down')" title="Geser ke bawah" style="font-size: 10px; line-height: 1.2;">
                                                                <i class="bi bi-chevron-down"></i>
                                                            </button>
                                                        </div>

                                                        <!-- 1-Click Move Heat Dropdown -->
                                                        <div class="dropdown">
                                                            <button class="btn btn-xs btn-outline-info dropdown-toggle py-0 px-2 fw-semibold btn-move-heat" 
                                                                    type="button" 
                                                                    data-bs-toggle="dropdown" 
                                                                    data-id="<?= $p['id'] ?>"
                                                                    aria-expanded="false" 
                                                                    style="font-size: 10px; border-radius: 4px; line-height: 1.4;">
                                                                Pindah
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow py-1 menu-move-heat" data-id="<?= $p['id'] ?>" style="min-width: 140px; font-size: 12px; z-index: 1050;">
                                                                <!-- Diisi otomatis oleh updateMoveHeatDropdowns() -->
                                                            </ul>
                                                        </div>

                                                        <?php if ($isEstafetEvent): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-warning py-0 px-2 fw-semibold btn-edit-estafet-trigger" 
                                                                    data-peserta-id="<?= $p['id'] ?>"
                                                                    data-atlet-id="<?= $p['atlet_id'] ?>"
                                                                    data-nama-tim="<?= esc($p['nama_atlet']) ?>"
                                                                    data-no-dada="<?= esc(ltrim($p['no_dada'] ?? '', "'")) ?>"
                                                                    data-sekolah-id="<?= $p['sekolah_id'] ?? '' ?>"
                                                                    data-heat="<?= $p['heat'] ?? 1 ?>"
                                                                    data-lane="<?= esc($p['lane'] ?? '') ?>"
                                                                    data-catatan="<?= esc($p['catatan_atlet'] ?? '') ?>"
                                                                    style="font-size: 10px; border-radius: 4px; line-height: 1.4;"
                                                                    title="Edit Tim & Pelari Estafet">
                                                                <i class="bi bi-pencil-square"></i> Edit
                                                            </button>
                                                        <?php endif; ?>

                                                        <!-- Remove Button -->
                                                        <a href="<?= base_url("event/remove-peserta/{$event['id']}/{$p['id']}") ?>" 
                                                           class="text-danger small opacity-75 hover-opacity-100 ms-1" 
                                                           onclick="return confirm('Keluarkan atlet ini dari nomor acara?')" 
                                                           title="Keluarkan Atlet">
                                                            <i class="bi bi-x-circle-fill"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- Bottom Row: Athlete Name, School, Region, & Team Runners -->
                                                <div class="ps-1 pt-1 border-top border-secondary border-opacity-10">
                                                    <div class="fw-bold text-white small text-truncate" title="<?= esc($p['nama_atlet']) ?>">
                                                        <?= esc($p['nama_atlet']) ?>
                                                    </div>
                                                    <div class="text-muted text-truncate" style="font-size: 11px;" title="<?= esc($p['nama_sekolah']) ?>">
                                                        <?= esc($p['nama_sekolah'] ?: '-') ?>
                                                    </div>
                                                    <?php if (!empty($p['catatan_atlet'])): ?>
                                                        <div class="text-info text-truncate mt-1" style="font-size: 12px; line-height: 1.35;" title="<?= esc($p['catatan_atlet']) ?>">
                                                            <i class="bi bi-people-fill me-1"></i><?= esc($p['catatan_atlet']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- VIEW 2: TABEL INPUT MANUAL (Diberi Jarak Nyaman dari Judul) -->
                <div id="viewManualTable" class="d-none">
                    <div class="table-responsive rounded border border-secondary border-opacity-25" style="margin-top: 0.75rem;">
                        <table class="table table-dark-custom mb-0">
                            <thead>
                                <tr>
                                    <th width="40" class="text-center">#</th>
                                    <th width="115" class="text-center" style="background: rgba(13, 110, 253, 0.15);">
                                        <span class="text-primary fw-bold">Hit (Heat)</span>
                                    </th>
                                    <th width="105" class="text-center" style="background: rgba(13, 202, 240, 0.15);">
                                        <span class="text-info fw-bold">Lintasan (1-8)</span>
                                    </th>
                                    <th width="90" class="text-center">No Dada</th>
                                    <th>Nama Atlet</th>
                                    <th>Asal Sekolah</th>
                                    <th class="text-center">Prestasi</th>
                                    <th class="text-center">Peringkat</th>
                                    <th class="text-center" width="70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($pesertaList as $p): ?>
                                    <tr class="table-peserta-row" data-id="<?= $p['id'] ?>">
                                        <td class="text-muted text-center align-middle"><?= $no++ ?></td>
                                        
                                        <!-- Input Hit (Heat) -->
                                        <td class="text-center align-middle" style="background: rgba(13, 110, 253, 0.05);">
                                            <div class="input-group input-group-sm justify-content-center">
                                                <span class="input-group-text bg-primary text-white border-0 fw-bold px-2" style="font-size: 11px;">Hit</span>
                                                <input type="number" 
                                                       name="peserta[<?= $p['id'] ?>][heat]" 
                                                       id="input_heat_<?= $p['id'] ?>"
                                                       value="<?= $p['heat'] ?: 1 ?>" 
                                                       min="1" max="99" 
                                                       class="form-control form-control-sm text-center fw-bold text-primary input-sync-heat" 
                                                       data-id="<?= $p['id'] ?>"
                                                       style="max-width: 60px;">
                                            </div>
                                        </td>

                                        <!-- Input Lintasan (Lane 1-8) -->
                                        <td class="text-center align-middle" style="background: rgba(13, 202, 240, 0.05);">
                                            <input type="text" 
                                                   name="peserta[<?= $p['id'] ?>][lane]" 
                                                   id="input_lane_<?= $p['id'] ?>"
                                                   value="<?= esc($p['lane'] ?? '') ?>" 
                                                   class="form-control form-control-sm text-center fw-bold text-info input-sync-lane" 
                                                   data-id="<?= $p['id'] ?>"
                                                   style="max-width: 65px; margin: 0 auto;" 
                                                   placeholder="Lane">
                                        </td>

                                        <!-- No Dada -->
                                        <td class="text-center font-monospace fw-bold text-warning align-middle">
                                            <?= esc(ltrim($p['no_dada'], "'") ?: '-') ?>
                                        </td>

                                        <!-- Nama Atlet / Team -->
                                        <td class="align-middle">
                                            <div class="fw-bold text-white"><?= esc($p['nama_atlet']) ?></div>
                                            <?php if (!empty($p['catatan_atlet'])): ?>
                                                <div class="text-info mt-1" style="font-size: 12px; line-height: 1.35;">
                                                    <i class="bi bi-people-fill me-1"></i><?= esc($p['catatan_atlet']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <small class="text-muted"><?= esc(($p['gender'] ?? '') === 'L' ? 'Putra (Male)' : (($p['gender'] ?? '') === 'Campuran' ? 'Campuran (MIX)' : 'Putri (Female)')) ?></small>
                                        </td>

                                        <!-- Asal Sekolah & Asal Daerah -->
                                        <td class="align-middle">
                                            <div class="text-white fw-semibold"><?= esc($p['nama_sekolah'] ?: '-') ?></div>
                                            <?php 
                                            $regInfo = get_label_kota_kabupaten($p['nama_sekolah'] ?? '', $p['alamat_sekolah'] ?? '');
                                            ?>
                                            <div class="mt-1">
                                                <span class="badge <?= esc($regInfo['badge']) ?>" style="font-size: 11px; padding: 3px 7px;">
                                                    <i class="bi <?= esc($regInfo['icon']) ?> me-1"></i><?= esc($regInfo['label']) ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Prestasi / Hasil -->
                                        <td class="text-center align-middle">
                                            <?php if (!empty($p['prestasi'])): ?>
                                                <span class="badge badge-emerald fs-6 font-monospace">
                                                    <?= esc($p['prestasi']) ?> <?= esc($event['satuan_hasil'] ?? '') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small fst-italic">Belum ada</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Peringkat / Juara -->
                                        <td class="text-center align-middle">
                                            <?php if (!empty($p['peringkat'])): ?>
                                                <span class="badge badge-amber fs-6">
                                                    Juara <?= $p['peringkat'] ?>
                                                </span>
                                            <?php elseif (!empty($p['ket_hasil'])): ?>
                                                <span class="badge badge-secondary small">
                                                    <?= esc($p['ket_hasil']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="text-center align-middle">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?php if ($isEstafetEvent): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning btn-edit-estafet-trigger" 
                                                            data-peserta-id="<?= $p['id'] ?>"
                                                            data-atlet-id="<?= $p['atlet_id'] ?>"
                                                            data-nama-tim="<?= esc($p['nama_atlet']) ?>"
                                                            data-no-dada="<?= esc(ltrim($p['no_dada'] ?? '', "'")) ?>"
                                                            data-sekolah-id="<?= $p['sekolah_id'] ?? '' ?>"
                                                            data-heat="<?= $p['heat'] ?? 1 ?>"
                                                            data-lane="<?= esc($p['lane'] ?? '') ?>"
                                                            data-catatan="<?= esc($p['catatan_atlet'] ?? '') ?>"
                                                            title="Edit Tim & Susunan Pelari Estafet">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-outline-info btn-call-single" title="Panggil Atlet Ini"
                                                        data-nama="<?= esc($p['nama_atlet']) ?>"
                                                        data-nodada="<?= esc($p['no_dada']) ?>"
                                                        data-sekolah="<?= esc($p['nama_sekolah']) ?>"
                                                        data-lane="<?= esc($p['lane']) ?>">
                                                    <i class="bi bi-megaphone"></i>
                                                </button>
                                                <a href="<?= base_url("event/remove-peserta/{$event['id']}/{$p['id']}") ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Keluarkan atlet ini dari nomor acara?')" title="Keluarkan Atlet">
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

                <!-- Footer Simpan & Status -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i> Setelah mengatur perpindahan Hit & Lintasan, Anda dapat langsung melihat hasil di <strong>Preview</strong> atau klik <strong>Simpan Susunan Hit & Lintasan</strong>.
                    </small>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAutoArrange">
                            <i class="bi bi-gear-fill"></i> Atur Seri & Lintasan
                        </button>
                        <button type="button" class="btn btn-info fw-bold text-white px-3 shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalPreviewSusunan" onclick="generatePreviewSusunan()">
                            <i class="bi bi-eye-fill"></i> Preview
                        </button>
                        <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i> Simpan Susunan Hit & Lintasan
                        </button>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Modal Tambah / Edit Tim Estafet -->
<div class="modal fade" id="modalEditTeamEstafet" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15);">
            <form id="formTeamEstafet" action="<?= base_url('event/save-team-estafet/' . $event['id']) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="peserta_event_id" id="estafet_peserta_id" value="">
                <input type="hidden" name="atlet_id" id="estafet_atlet_id" value="">

                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2" id="modalTeamEstafetTitle">
                        <i class="bi bi-diagram-3-fill text-warning"></i>
                        <span>Edit Tim & Susunan Pelari Estafet</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Nama Tim / Regu -->
                        <div class="col-md-8">
                            <label class="form-label text-white small fw-bold">Nama Tim / Regu Estafet <span class="text-danger">*</span></label>
                            <input type="text" name="nama_tim" id="estafet_nama_tim" class="form-control bg-dark text-white border-secondary" placeholder="Contoh: Team SMP N 02 atau Team PASI Kota Pekalongan C" required>
                        </div>

                        <!-- No Dada / BIB Tim -->
                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">No Dada / BIB Tim</label>
                            <input type="text" name="no_dada" id="estafet_no_dada" class="form-control bg-dark text-white border-secondary font-monospace" placeholder="Misal: 426">
                        </div>

                        <!-- Asal Sekolah / Kontingen -->
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">Asal Sekolah / Kontingen</label>
                            <select name="sekolah_id" id="estafet_sekolah_id" class="form-select bg-dark text-white border-secondary">
                                <option value="">-- Pilih Asal Sekolah / Kontingen --</option>
                                <?php if (!empty($sekolahList)): ?>
                                    <?php foreach ($sekolahList as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= esc($s['nama']) ?> (<?= esc($s['jenjang'] ?? '-') ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Hit & Lintasan -->
                        <div class="col-md-3">
                            <label class="form-label text-white small fw-bold">Hit (Heat)</label>
                            <input type="number" name="heat" id="estafet_heat" class="form-control bg-dark text-white border-secondary text-center" value="1" min="1" max="99" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white small fw-bold">Lintasan (Lane)</label>
                            <input type="number" name="lane" id="estafet_lane" class="form-control bg-dark text-white border-secondary text-center font-monospace" placeholder="1-8" min="1" max="8">
                        </div>

                        <!-- Susunan Pelari Estafet -->
                        <div class="col-12">
                            <label class="form-label text-white small fw-bold d-flex justify-content-between">
                                <span><i class="bi bi-people-fill text-info me-1"></i> Susunan Nama Pelari (4 - 5 Pelari)</span>
                                <small class="text-muted">Format: Pelari: [NoDada] [Nama], ...</small>
                            </label>
                            <textarea name="catatan_pelari" id="estafet_catatan_pelari" rows="4" class="form-control bg-dark text-white border-secondary font-monospace" placeholder="Contoh: Pelari: 426 AYU AHZA SALSABILA, 797 FACHRY CAESAR SAPUTRA, 481 SAFITRI AZA PUTRI, 924 FAVIAN FIKRI ZUFAR" style="font-size: 13px;"></textarea>
                            <div class="form-text text-muted small mt-1">
                                <i class="bi bi-info-circle me-1 text-info"></i>
                                Tuliskan seluruh nomor dada dan nama pelari berurutan. Format ini langsung tersimpan ke database, buku acara, dan form blangko lapangan.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-check-lg"></i> Simpan Data Tim Estafet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Peserta -->
<div class="modal fade" id="modalAddPeserta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('event/add-peserta/' . $event['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-person-plus-fill me-2 text-primary"></i> Tambah Atlet ke Acara Ini
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <?php if (empty($availableAtlets)): ?>
                        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info mb-0">
                            <i class="bi bi-info-circle me-1"></i> Semua atlet yang sesuai dengan kategori gender/KU acara ini sudah terdaftar.
                        </div>
                    <?php else: ?>
                        <!-- Tentukan Hit Tujuan -->
                        <div class="card-custom mb-3 p-3" style="background: rgba(13, 110, 253, 0.1); border-color: rgba(13, 110, 253, 0.25);">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-white mb-0">
                                        <i class="bi bi-flag-fill text-primary me-1"></i> Tentukan Hit Tujuan:
                                    </label>
                                    <div class="text-muted small">Pilih atlet yang dicentang akan masuk ke Hit berapa.</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white fw-bold">Hit</span>
                                        <input type="number" name="target_heat" class="form-control fw-bold text-center" value="1" min="1" max="99" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mb-2">
                            Pilih atlet yang ingin dimasukkan ke nomor acara ini (hanya menampilkan atlet yang sesuai kategori):
                        </p>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <input type="text" id="filterAtletModal" class="form-control form-control-sm w-50" placeholder="Cari nama atau sekolah...">
                            <button type="button" class="btn btn-sm btn-secondary-custom" onclick="$('.atlet-checkbox').prop('checked', true)">
                                Pilih Semua
                            </button>
                        </div>
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-dark-custom" id="tableAvailableAtlets">
                                <thead>
                                    <tr>
                                        <th width="40"><input type="checkbox" id="selectAllAtlets"></th>
                                        <th width="80">No Dada</th>
                                        <th>Nama Atlet</th>
                                        <th>Asal Sekolah</th>
                                        <th>Kategori</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($availableAtlets as $a): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="atlet_ids[]" value="<?= $a['id'] ?>" class="atlet-checkbox form-check-input">
                                            </td>
                                            <td class="font-monospace fw-bold text-warning"><?= esc($a['no_dada'] ?: '-') ?></td>
                                            <td class="fw-semibold text-white"><?= esc($a['nama']) ?></td>
                                            <td class="text-muted small"><?= esc($a['nama_sekolah']) ?></td>
                                            <td>
                                                <span class="badge badge-purple small"><?= esc($a['nama_ku'] ?: '-') ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                    <?php if (!empty($availableAtlets)): ?>
                        <button type="submit" class="btn btn-primary-gradient px-4">
                            <i class="bi bi-plus-circle me-1"></i> Daftarkan Peserta Terpilih
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Atur Hit & Lintasan -->
<div class="modal fade" id="modalAutoArrange" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('event/auto-arrange/' . $event['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-shuffle me-2 text-info"></i> Atur Pembagian Hit (Kelompok Acara)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <?php 
                    $totalP = count($pesertaList);
                    $isSingleHeat = ($currentHeatsCount <= 1 || $event['babak'] === 'Final');

                    // Hitung sebaran hit peserta yang ada saat ini
                    $existingHeatCounts = [];
                    foreach ($pesertaList as $p) {
                        $h = (int)($p['heat'] ?: 1);
                        $existingHeatCounts[$h] = ($existingHeatCounts[$h] ?? 0) + 1;
                    }
                    ksort($existingHeatCounts);

                    // Jika saat ini belum ada beberapa heat, buat template 2 heat (misal Hit 1: 9, Hit 2: 7 jika total >= 16)
                    if (count($existingHeatCounts) <= 1) {
                        if ($totalP >= 16) {
                            $existingHeatCounts = [1 => 9, 2 => 7];
                        } elseif ($totalP > 8) {
                            $existingHeatCounts = [1 => 8, 2 => max(1, $totalP - 8)];
                        } else {
                            $existingHeatCounts = [1 => max(1, (int)ceil($totalP / 2)), 2 => max(1, (int)floor($totalP / 2))];
                        }
                    }
                    ?>

                    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shuffle fs-4 text-info"></i>
                        <div>
                            Total Peserta: <strong><?= $totalP ?> atlet</strong>. Pengaturan Hit dibuat <strong>acak (random)</strong> namun sistem otomatis <strong>memisahkan atlet dari kota/kabupaten serta sekolah yang sama</strong> agar tidak berada di satu Hit.
                        </div>
                    </div>

                    <!-- Pilihan Metode Pengaturan -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-white small text-uppercase">Pilih Metode Pengelompokan Hit:</label>
                        <div class="d-flex flex-column gap-2">
                            <!-- Opsi 1: Acak Otomatis (Bagi Rata & Pisah Kota/Kabupaten) -->
                            <div class="form-check p-2 rounded" style="background: rgba(13, 110, 253, 0.12); border: 1px solid rgba(13, 110, 253, 0.35);">
                                <input class="form-check-input ms-1" type="radio" name="arrange_type" id="arrangeRandomRegion" value="random_region" <?= ($totalP > 8 || !$isSingleHeat) ? 'checked' : '' ?> onchange="toggleArrangeType()">
                                <label class="form-check-label fw-bold text-primary ms-2" for="arrangeRandomRegion">
                                    <i class="bi bi-shuffle me-1"></i> Acak Otomatis & Pisahkan Kota/Kabupaten <span class="badge bg-primary ms-1">Rekomendasi PASI</span>
                                </label>
                                <div class="text-white-50 small ms-2 ps-4">
                                    Peserta diacak secara otomatis dan disebar seimbang. Atlet dari <strong>Kota/Kabupaten</strong> dan <strong>Sekolah yang sama</strong> TIDAK disatukan dalam satu Hit. Nomor lintasan di tiap Hit juga diacak.
                                </div>
                            </div>

                            <!-- Opsi 2: Kustom Kapasitas Beberapa Hit -->
                            <div class="form-check p-2 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15);">
                                <input class="form-check-input ms-1" type="radio" name="arrange_type" id="arrangeCustom" value="custom" onchange="toggleArrangeType()">
                                <label class="form-check-label fw-bold text-info ms-2" for="arrangeCustom">
                                    <i class="bi bi-sliders me-1"></i> Kustom Kapasitas Beberapa Hit (Tetap Pisah Kota)
                                </label>
                                <div class="text-muted small ms-2 ps-4">Tentukan kapasitas orang yang berbeda per Hit (misal Hit 1: 9 orang, Hit 2: 7 orang). Peserta tetap diacak dan dipisahkan per kota/kabupaten & sekolah.</div>
                            </div>

                            <!-- Opsi 3: 1 Hit (Langsung FINAL) -->
                            <div class="form-check p-2 rounded" style="background: rgba(25, 135, 84, 0.08); border: 1px solid rgba(25, 135, 84, 0.25);">
                                <input class="form-check-input ms-1" type="radio" name="arrange_type" id="arrangeSingle" value="single" <?= ($totalP <= 8 && $isSingleHeat) ? 'checked' : '' ?> onchange="toggleArrangeType()">
                                <label class="form-check-label fw-bold text-success ms-2" for="arrangeSingle">
                                    <i class="bi bi-trophy-fill me-1"></i> 1 Hit (Langsung FINAL)
                                </label>
                                <div class="text-muted small ms-2 ps-4">Semua peserta berada dalam 1 kelompok bersama (Langsung FINAL tanpa babak penyisihan), nomor lintasan diacak.</div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 1: ACAK OTOMATIS & PISAH KOTA/KABUPATEN -->
                    <div id="sectionRandomHeats" class="card-custom p-3 mb-3 <?= ($totalP > 8 || !$isSingleHeat) ? '' : 'd-none' ?>" style="background: rgba(13, 110, 253, 0.08); border-color: rgba(13, 110, 253, 0.3);">
                        <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
                            <i class="bi bi-shuffle fs-5"></i> Acak Otomatis Berdasarkan Asal Daerah
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold text-white small">Target Maksimal Peserta per Hit:</label>
                            <select name="lanes_per_heat" id="selLanesPerHeat" class="form-select fw-semibold" onchange="updateEstimatedHeats()">
                                <option value="8" selected>8 Orang per Hit (Standar Lintasan Stadion)</option>
                                <option value="9">9 Orang per Hit</option>
                                <option value="7">7 Orang per Hit</option>
                                <option value="6">6 Orang per Hit</option>
                                <option value="5">5 Orang per Hit</option>
                                <option value="4">4 Orang per Hit</option>
                            </select>
                        </div>
                        <div id="boxEstimatedHeats" class="p-2 rounded mt-2" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <div class="small text-white">
                                Estimasi Pembagian: <strong id="lblEstimatedHeats" class="text-warning">-</strong>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-shield-check text-success me-1"></i>
                                Peserta dari satu kota/kabupaten dan sekolah yang sama akan disebar ke Hit berbeda secara otomatis.
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: KUSTOM BEBERAPA HIT -->
                    <div id="sectionCustomHeats" class="card-custom p-3 mb-3 d-none" style="background: rgba(15, 23, 42, 0.6); border-color: rgba(13, 110, 253, 0.3);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-white small">
                                <i class="bi bi-list-ol text-primary me-1"></i> Tentukan Jumlah Orang yang Mengikuti Tiap Hit:
                            </span>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-xs btn-outline-info" onclick="addHeatRow()">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Hit
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeLastHeatRow()">
                                    <i class="bi bi-dash-lg me-1"></i> Hapus Hit Terakhir
                                </button>
                            </div>
                        </div>

                        <div id="heatRowsContainer" class="row g-2 mb-3">
                            <?php foreach ($existingHeatCounts as $hNum => $cap): ?>
                                <div class="col-md-6 heat-row-item" data-heat="<?= $hNum ?>">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-primary text-white fw-bold px-3">Hit <?= $hNum ?></span>
                                        <input type="number" name="custom_heats[<?= $hNum ?>]" class="form-control heat-capacity-input fw-bold text-center" value="<?= $cap ?>" min="1" max="99" oninput="calculateAllocated()">
                                        <span class="input-group-text bg-dark text-muted">Orang</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Live Counter Ringkasan Alokasi -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center p-2 rounded" style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="small">
                                Total Atlet: <strong class="text-white"><?= $totalP ?></strong> &bull;
                                Dialokasikan: <strong id="lblTotalAllocated" class="text-info">0</strong> orang
                            </div>
                            <div id="lblAllocationBadge">
                                <!-- Badge status via JS -->
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: 1 HIT (LANGSUNG FINAL) -->
                    <div id="sectionSingleHeat" class="card-custom p-3 mb-3 <?= ($totalP <= 8 && $isSingleHeat) ? '' : 'd-none' ?>" style="background: rgba(25, 135, 84, 0.08); border-color: rgba(25, 135, 84, 0.3);">
                        <div class="d-flex align-items-center gap-2 text-success fw-bold mb-1">
                            <i class="bi bi-check2-circle fs-5"></i> Mode 1 Hit (Langsung FINAL)
                        </div>
                        <p class="text-muted small mb-0">
                            Seluruh <?= $totalP ?> atlet akan otomatis dialokasikan ke <strong>Hit 1</strong> dengan nomor lintasan (Lane 1 s/d <?= $totalP ?>) diacak secara adil. Status babak otomatis menjadi <strong>FINAL</strong>.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-gradient px-4">
                        <i class="bi bi-magic me-1"></i> Terapkan Pengaturan Hit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Prepare heats grouped data for Call Room
$heatsGrouped = [];
foreach ($pesertaList as $p) {
    $h = (int)($p['heat'] ?: 1);
    $heatsGrouped[$h][] = $p;
}
ksort($heatsGrouped);
?>

<!-- Modal Call Room / Pemanggilan Suara -->
<div class="modal fade" id="modalCallRoom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #0891b2, #0e7490); border: 0;">
                <h5 class="modal-title text-white">
                    <i class="bi bi-megaphone-fill me-2"></i> Call Room — Pemanggilan Peserta dengan Suara
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Browser Support Check -->
                <div id="voiceNotSupported" class="alert alert-warning d-none">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Browser Anda tidak mendukung Web Speech API. Gunakan <strong>Chrome</strong> atau <strong>Edge</strong> terbaru.
                </div>

                <!-- Pilih Heat -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-white small text-uppercase">Pilih Heat / Seri:</label>
                    <select id="callHeatSelect" class="form-select">
                        <?php foreach ($heatsGrouped as $hNum => $hPeserta): ?>
                            <option value="<?= $hNum ?>">Heat <?= $hNum ?> — <?= count($hPeserta) ?> peserta</option>
                        <?php endforeach; ?>
                        <option value="all">🔊 Semua Heat (Berurutan)</option>
                    </select>
                </div>

                <!-- Tingkat Panggilan -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-white small text-uppercase">Tingkat Panggilan:</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="callLevel" id="callLevel1" value="1" checked>
                        <label class="btn btn-outline-info" for="callLevel1">
                            <i class="bi bi-1-circle me-1"></i> Pertama
                        </label>
                        <input type="radio" class="btn-check" name="callLevel" id="callLevel2" value="2">
                        <label class="btn btn-outline-warning" for="callLevel2">
                            <i class="bi bi-2-circle me-1"></i> Kedua
                        </label>
                        <input type="radio" class="btn-check" name="callLevel" id="callLevel3" value="3">
                        <label class="btn btn-outline-danger" for="callLevel3">
                            <i class="bi bi-3-circle me-1"></i> Terakhir
                        </label>
                    </div>
                </div>

                <!-- Pengaturan Suara -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Kecepatan: <span id="lblRate">0.85</span>x</label>
                        <input type="range" class="form-range" id="voiceRate" min="0.5" max="1.5" step="0.05" value="0.85">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Volume: <span id="lblVolume">100</span>%</label>
                        <input type="range" class="form-range" id="voiceVolume" min="0" max="1" step="0.1" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Jeda Antar Atlet (detik)</label>
                        <input type="number" class="form-control form-control-sm" id="voiceDelay" value="2.5" min="1" max="10" step="0.5">
                    </div>
                </div>

                <!-- Status Box -->
                <div id="voiceStatusBox" class="p-3 rounded mb-3" style="min-height: 60px; background: rgba(15,23,42,0.7); border: 1px solid rgba(255,255,255,0.1); font-family: monospace; font-size: 13px;">
                    <span class="text-muted"><i class="bi bi-mic me-1"></i> Siap untuk pemanggilan...</span>
                </div>

                <!-- Preview Peserta -->
                <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto; background: rgba(15,23,42,0.4); border-color: rgba(255,255,255,0.1) !important; font-size: 12px;">
                    <?php foreach ($heatsGrouped as $hNum => $hPeserta): ?>
                        <div class="mb-1">
                            <span class="text-info fw-bold small">Heat <?= $hNum ?></span>
                            <?php foreach ($hPeserta as $hp): ?>
                                <div class="text-white-50 ps-2">Lint. <?= esc($hp['lane'] ?: '-') ?> • <span class="text-warning"><?= esc($hp['no_dada'] ?: '-') ?></span> • <?= esc($hp['nama_atlet']) ?> — <em><?= esc($hp['nama_sekolah'] ?: '-') ?></em></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" id="btnStopVoice" disabled>
                    <i class="bi bi-stop-fill me-1"></i> Stop
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnPauseVoice" disabled>
                    <i class="bi bi-pause-fill me-1"></i> Jeda
                </button>
                <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-info text-white fw-bold px-4 shadow-sm" id="btnStartCallRoom">
                    <i class="bi bi-megaphone-fill me-1"></i> Mulai Panggilan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Susunan Seri & Lintasan (Start List) -->
<div class="modal fade" id="modalPreviewSusunan" tabindex="-1" aria-labelledby="modalPreviewSusunanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #0052cc 0%, #0747a6 100%); border: 0;">
                <div>
                    <h5 class="modal-title text-white fw-bold" id="modalPreviewSusunanLabel">
                        <i class="bi bi-eye-fill me-2 text-info"></i> Preview Susunan Seri & Lintasan (Start List)
                    </h5>
                    <div class="text-white-50 small mt-1">
                        Acara #<?= esc($event['nomor_acara']) ?>: <strong><?= esc($event['nama_acara']) ?></strong> &bull; Babak: <strong><?= esc($event['babak']) ?></strong>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: var(--bg-body, #f4f5f7);">
                <!-- Ringkasan Info Acara -->
                <div class="card-custom mb-3 p-3" style="background: #ffffff; border: 1px solid rgba(0, 82, 204, 0.2);">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="fw-bold text-dark fs-6 mb-1">
                                <i class="bi bi-calendar-event text-primary me-1"></i> Jadwal: <?= esc($event['jadwal_hari'] ?: '-') ?> &bull; <?= esc($event['jadwal_waktu'] ?: '-') ?>
                            </div>
                            <div class="text-muted small">
                                Pastikan susunan atlet, nomor seri (hit), dan nomor lintasan telah sesuai sebelum disimpan.
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span id="previewTotalPesertaBadge" class="badge badge-blue fs-6 px-3 py-2 shadow-sm">- Peserta</span>
                            <span id="previewTotalHitBadge" class="badge badge-purple fs-6 px-3 py-2 shadow-sm">- Hit</span>
                            <span id="previewBabakBadge" class="badge badge-emerald fs-6 px-3 py-2 shadow-sm"><?= esc($event['babak']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Kontainer Heats yang Digenerate Secara Dinamis -->
                <div id="previewSusunanContainer">
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat data susunan atlet...
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between bg-white border-top">
                <button type="button" class="btn btn-warning fw-bold text-dark shadow-sm d-inline-flex align-items-center gap-2" onclick="printPreviewStartList()">
                    <i class="bi bi-printer-fill"></i> Cetak Lembar Start List
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/voice-announcer.js') ?>"></script>
<script>
const TOTAL_PESERTA = <?= count($pesertaList) ?>;

function updateEstimatedHeats() {
    const lanes = parseInt($('#selLanesPerHeat').val()) || 8;
    if (TOTAL_PESERTA === 0) {
        $('#lblEstimatedHeats').text('0 Hit');
        return;
    }
    if (TOTAL_PESERTA <= lanes) {
        $('#lblEstimatedHeats').text('1 Hit (Langsung FINAL - ' + TOTAL_PESERTA + ' orang)');
        return;
    }
    const numHeats = Math.ceil(TOTAL_PESERTA / lanes);
    const base = Math.floor(TOTAL_PESERTA / numHeats);
    const rem = TOTAL_PESERTA % numHeats;
    
    let parts = [];
    for (let h = 1; h <= numHeats; h++) {
        const count = base + (h <= rem ? 1 : 0);
        parts.push('Hit ' + h + ': ' + count + ' orang');
    }
    $('#lblEstimatedHeats').text(numHeats + ' Hit (' + parts.join(', ') + ') - Babak Penyisihan');
}

function toggleArrangeType() {
    const isSingle = $('#arrangeSingle').is(':checked');
    const isCustom = $('#arrangeCustom').is(':checked');
    const isRandom = $('#arrangeRandomRegion').is(':checked');

    if (isSingle) {
        $('#sectionSingleHeat').removeClass('d-none');
        $('#sectionCustomHeats').addClass('d-none');
        $('#sectionRandomHeats').addClass('d-none');
    } else if (isCustom) {
        $('#sectionSingleHeat').addClass('d-none');
        $('#sectionCustomHeats').removeClass('d-none');
        $('#sectionRandomHeats').addClass('d-none');
        calculateAllocated();
    } else if (isRandom) {
        $('#sectionSingleHeat').addClass('d-none');
        $('#sectionCustomHeats').addClass('d-none');
        $('#sectionRandomHeats').removeClass('d-none');
        updateEstimatedHeats();
    }
}

function calculateAllocated() {
    let sum = 0;
    $('.heat-capacity-input').each(function () {
        const val = parseInt($(this).val()) || 0;
        sum += val;
    });
    $('#lblTotalAllocated').text(sum);

    const badge = $('#lblAllocationBadge');
    if (sum === TOTAL_PESERTA) {
        badge.html('<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Pas (Semua ' + sum + ' atlet teralokasi)</span>');
    } else if (sum < TOTAL_PESERTA) {
        const diff = TOTAL_PESERTA - sum;
        badge.html('<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i> Sisa ' + diff + ' atlet akan otomatis masuk ke Hit berikutnya</span>');
    } else {
        const diff = sum - TOTAL_PESERTA;
        badge.html('<span class="badge bg-info"><i class="bi bi-info-circle me-1"></i> Kapasitas lebih ' + diff + ' kursi dari jumlah atlet</span>');
    }
}

function addHeatRow() {
    const count = $('.heat-row-item').length + 1;
    const html = `
        <div class="col-md-6 heat-row-item" data-heat="${count}">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-primary text-white fw-bold px-3">Hit ${count}</span>
                <input type="number" name="custom_heats[${count}]" class="form-control heat-capacity-input fw-bold text-center" value="8" min="1" max="99" oninput="calculateAllocated()">
                <span class="input-group-text bg-dark text-muted">Orang</span>
            </div>
        </div>
    `;
    $('#heatRowsContainer').append(html);
    calculateAllocated();
}

function removeLastHeatRow() {
    const items = $('.heat-row-item');
    if (items.length > 1) {
        items.last().remove();
        calculateAllocated();
    }
}

// ==========================================
// DRAG AND DROP & 1-KLIK PINDAH HIT ATLET
// ==========================================
function switchSusunanView(mode) {
    if (mode === 'dnd') {
        $('#viewDndBoard').removeClass('d-none');
        $('#viewManualTable').addClass('d-none');
        $('#tabBtnDnd').addClass('btn-primary active').removeClass('btn-outline-secondary text-white');
        $('#tabBtnTable').addClass('btn-outline-secondary text-white').removeClass('btn-primary active');
        try { localStorage.setItem('kejurwil_susunan_view', 'dnd'); } catch(e) {}
    } else {
        $('#viewDndBoard').addClass('d-none');
        $('#viewManualTable').removeClass('d-none');
        $('#tabBtnTable').addClass('btn-primary active').removeClass('btn-outline-secondary text-white');
        $('#tabBtnDnd').addClass('btn-outline-secondary text-white').removeClass('btn-primary active');
        try { localStorage.setItem('kejurwil_susunan_view', 'table'); } catch(e) {}
    }
}

let draggedCard = null;
const dropPlaceholder = $('<div class="dnd-drop-placeholder" style="height: 52px; border: 2px dashed #0052cc; background: rgba(0, 82, 204, 0.15); border-radius: 6px; margin: 4px 0; pointer-events: none; display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 11px; font-weight: 600;"><i class="bi bi-box-arrow-in-down me-1"></i> Lepas di sini</div>');

function initDragAndDrop() {
    $('.dnd-athlete-card').off('dragstart dragend').on('dragstart', function(e) {
        draggedCard = this;
        $(this).addClass('dragging opacity-50');
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/plain', $(this).data('id'));
    }).on('dragend', function() {
        $(this).removeClass('dragging opacity-50');
        $('.dnd-dropzone').removeClass('drag-over bg-primary-subtle');
        dropPlaceholder.detach();
        draggedCard = null;
    });

    $('.dnd-dropzone').off('dragover dragleave drop').on('dragover', function(e) {
        e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = 'move';
        $(this).addClass('drag-over');

        if (!draggedCard) return;

        // Jangan pindahkan draggedCard saat dragover agar pointer tidak lompat (jitter free)
        $(this).find('.dnd-empty-placeholder').addClass('d-none');
        const afterElement = getDragAfterElement(this, e.originalEvent.clientY);
        if (afterElement == null) {
            $(this).append(dropPlaceholder);
        } else {
            dropPlaceholder.insertBefore(afterElement);
        }
    }).on('dragleave', function(e) {
        if (!$(this).has(e.relatedTarget).length && e.relatedTarget !== dropPlaceholder[0]) {
            $(this).removeClass('drag-over');
        }
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');

        if (!draggedCard) return;

        const targetHeat = parseInt($(this).data('heat')) || 1;
        const oldHeat = $(draggedCard).data('heat');

        // Letakkan draggedCard di posisi placeholder
        if (dropPlaceholder.parent().length) {
            dropPlaceholder.after(draggedCard);
            dropPlaceholder.detach();
        } else {
            $(this).append(draggedCard);
        }

        $(draggedCard).data('heat', targetHeat);

        // Rapikan nomor lintasan pada kedua heat (target dan lama)
        renumberHeatLanes($(this));
        if (oldHeat && oldHeat !== targetHeat) {
            const oldDropzone = $(`.dnd-dropzone[data-heat="${oldHeat}"]`);
            if (oldDropzone.length) {
                renumberHeatLanes(oldDropzone);
            }
        }

        updateMoveHeatDropdowns();
        autoSaveSusunanAjax();
    });

    updateMoveHeatDropdowns();
}

// Touch Drag Support untuk Layar Sentuh / Tablet / Smartphone
let touchItem = null;
let touchClone = null;
let touchOffsetY = 0;

$(document).off('touchstart.dnd touchmove.dnd touchend.dnd touchcancel.dnd')
.on('touchstart.dnd', '.dnd-drag-handle', function(e) {
    const touch = e.originalEvent.touches[0];
    touchItem = $(this).closest('.dnd-athlete-card')[0];
    if (!touchItem) return;

    const rect = touchItem.getBoundingClientRect();
    touchOffsetY = touch.clientY - rect.top;

    touchClone = $(touchItem).clone().css({
        position: 'fixed',
        top: (touch.clientY - touchOffsetY) + 'px',
        left: rect.left + 'px',
        width: rect.width + 'px',
        zIndex: 9999,
        opacity: 0.9,
        pointerEvents: 'none',
        boxShadow: '0 8px 24px rgba(0,0,0,0.6)',
        transform: 'scale(1.02)'
    }).appendTo('body');

    $(touchItem).addClass('opacity-50');
})
.on('touchmove.dnd', function(e) {
    if (!touchClone || !touchItem) return;
    const touch = e.originalEvent.touches[0];
    e.preventDefault(); // Mencegah scrolling saat drag kartu

    touchClone.css({
        top: (touch.clientY - touchOffsetY) + 'px'
    });

    const elUnder = document.elementFromPoint(touch.clientX, touch.clientY);
    const dropzone = $(elUnder).closest('.dnd-dropzone');

    if (dropzone.length) {
        $('.dnd-dropzone').removeClass('drag-over');
        dropzone.addClass('drag-over');
        const afterElement = getDragAfterElement(dropzone[0], touch.clientY);
        if (afterElement == null) {
            dropzone.append(dropPlaceholder);
        } else {
            dropPlaceholder.insertBefore(afterElement);
        }
    }
})
.on('touchend.dnd touchcancel.dnd', function(e) {
    if (!touchClone || !touchItem) return;

    if (dropPlaceholder.parent().length) {
        const targetDropzone = dropPlaceholder.closest('.dnd-dropzone');
        const targetHeat = parseInt(targetDropzone.data('heat')) || 1;
        const oldHeat = $(touchItem).data('heat');

        dropPlaceholder.after(touchItem);
        dropPlaceholder.detach();

        $(touchItem).data('heat', targetHeat);

        renumberHeatLanes(targetDropzone);
        if (oldHeat && oldHeat !== targetHeat) {
            const oldDropzone = $(`.dnd-dropzone[data-heat="${oldHeat}"]`);
            if (oldDropzone.length) {
                renumberHeatLanes(oldDropzone);
            }
        }
        updateMoveHeatDropdowns();
        autoSaveSusunanAjax();
    }

    $(touchItem).removeClass('opacity-50');
    if (touchClone) touchClone.remove();
    touchClone = null;
    touchItem = null;
    $('.dnd-dropzone').removeClass('drag-over');
});

// Geser urutan atlet naik / turun di dalam Hit
function moveAthleteOrder(pId, direction) {
    const card = $(`.dnd-athlete-card[data-id="${pId}"]`);
    if (!card.length) return;
    const dropzone = card.closest('.dnd-dropzone');

    if (direction === 'up') {
        const prev = card.prev('.dnd-athlete-card');
        if (prev.length) {
            card.insertBefore(prev);
            renumberHeatLanes(dropzone);
            autoSaveSusunanAjax();
        }
    } else if (direction === 'down') {
        const next = card.next('.dnd-athlete-card');
        if (next.length) {
            card.insertAfter(next);
            renumberHeatLanes(dropzone);
            autoSaveSusunanAjax();
        }
    }
}

// 1-Klik Pindah Hit dari Dropdown
function moveAthleteToHeat(pId, targetHeat) {
    const card = $(`.dnd-athlete-card[data-id="${pId}"]`);
    if (!card.length) return;

    let targetDropzone = $(`.dnd-dropzone[data-heat="${targetHeat}"]`);
    if (!targetDropzone.length) {
        addNewHeatColumn();
        targetDropzone = $(`.dnd-dropzone[data-heat="${targetHeat}"]`);
    }

    const oldHeat = card.data('heat');
    targetDropzone.find('.dnd-empty-placeholder').remove();
    targetDropzone.append(card);
    card.data('heat', targetHeat);

    renumberHeatLanes(targetDropzone);
    if (oldHeat && oldHeat !== targetHeat) {
        const oldDropzone = $(`.dnd-dropzone[data-heat="${oldHeat}"]`);
        if (oldDropzone.length) {
            renumberHeatLanes(oldDropzone);
        }
    }

    updateMoveHeatDropdowns();
    autoSaveSusunanAjax();
}

function moveAthleteToNewHeat(pId) {
    let maxH = 0;
    $('.dnd-heat-column').each(function() {
        const h = parseInt($(this).data('heat')) || 0;
        if (h > maxH) maxH = h;
    });
    const newH = maxH + 1;
    addNewHeatColumn();
    moveAthleteToHeat(pId, newH);
}

// Update daftar pilihan Heat pada setiap tombol "Pindah"
function updateMoveHeatDropdowns() {
    const heatNumbers = [];
    $('.dnd-heat-column').each(function() {
        const h = parseInt($(this).data('heat'));
        if (h && !heatNumbers.includes(h)) {
            heatNumbers.push(h);
        }
    });
    heatNumbers.sort((a, b) => a - b);

    $('.menu-move-heat').each(function() {
        const menu = $(this);
        const pId = menu.data('id');
        const card = $(`.dnd-athlete-card[data-id="${pId}"]`);
        const currentHeat = parseInt(card.data('heat')) || 1;

        let itemsHtml = '<li class="dropdown-header text-muted text-uppercase" style="font-size: 10px; padding: 4px 12px;">Pindah ke Hit:</li>';
        heatNumbers.forEach(function(h) {
            const isCurrent = (h === currentHeat);
            itemsHtml += `
                <li>
                    <button type="button" class="dropdown-item ${isCurrent ? 'active disabled fw-bold' : ''}" onclick="moveAthleteToHeat(${pId}, ${h})" style="font-size: 11px; padding: 5px 12px;">
                        <i class="bi ${isCurrent ? 'bi-check2' : 'bi-arrow-right-short'} me-1"></i> Hit ${h} ${isCurrent ? '(Saat ini)' : ''}
                    </button>
                </li>
            `;
        });

        itemsHtml += `
            <li><hr class="dropdown-divider my-1"></li>
            <li>
                <button type="button" class="dropdown-item text-info fw-semibold" onclick="moveAthleteToNewHeat(${pId})" style="font-size: 11px; padding: 5px 12px;">
                    <i class="bi bi-plus-circle me-1"></i> + Buat Hit Baru
                </button>
            </li>
        `;

        menu.html(itemsHtml);
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.dnd-athlete-card:not(.dragging)')];
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function renumberHeatLanes(dropzoneEl) {
    const heatNum = parseInt(dropzoneEl.data('heat')) || 1;
    const cards = dropzoneEl.find('.dnd-athlete-card');
    
    // Perbarui counter
    const countBadge = $(`.dnd-heat-count[data-heat="${heatNum}"]`);
    if (countBadge.length) {
        countBadge.text(cards.length + '/8 Atlet');
        if (cards.length > 8) {
            countBadge.removeClass('bg-secondary bg-primary').addClass('bg-danger').attr('title', 'Perhatian: Melebihi 8 lintasan stadion!');
        } else {
            countBadge.removeClass('bg-danger').addClass('bg-secondary').removeAttr('title');
        }
    }

    if (cards.length === 0) {
        if (dropzoneEl.find('.dnd-empty-placeholder').length === 0) {
            dropzoneEl.html(`
                <div class="dnd-empty-placeholder text-center py-4 text-muted small" style="border: 2px dashed rgba(255,255,255,0.1); border-radius: 6px;">
                    <i class="bi bi-box-arrow-in-down fs-4 d-block mb-1 text-secondary"></i>
                    Tarik atlet ke Hit ini
                </div>
            `);
        }
        return;
    } else {
        dropzoneEl.find('.dnd-empty-placeholder').remove();
    }

    cards.each(function(index) {
        const laneNum = index + 1; // Lintasan 1 s/d 8
        const card = $(this);
        const pId = card.data('id');

        card.data('heat', heatNum);
        card.data('lane', laneNum);
        card.find('.dnd-lane-badge').text('L' + laneNum);

        // Sinkronkan ke input tabel manual
        $(`#input_heat_${pId}`).val(heatNum);
        $(`#input_lane_${pId}`).val(laneNum);
    });
}

function renumberSingleHeat(heatNum) {
    const dropzone = $(`.dnd-dropzone[data-heat="${heatNum}"]`);
    if (dropzone.length) {
        renumberHeatLanes(dropzone);
        autoSaveSusunanAjax();
    }
}

function autoFixAllLanes() {
    $('.dnd-dropzone').each(function() {
        renumberHeatLanes($(this));
    });
    autoSaveSusunanAjax();
}

function addNewHeatColumn() {
    let maxH = 0;
    $('.dnd-heat-column').each(function() {
        const h = parseInt($(this).data('heat')) || 0;
        if (h > maxH) maxH = h;
    });
    const newH = maxH + 1;

    const html = `
        <div class="dnd-heat-column flex-shrink-0" data-heat="${newH}" style="width: 320px; background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.35);">
            <div class="dnd-heat-header p-3 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center" style="background: rgba(30, 41, 59, 0.85); border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary fs-6 fw-bold px-2 py-1">Hit ${newH}</span>
                    <span class="badge bg-secondary dnd-heat-count" data-heat="${newH}" style="font-size: 11px;">0/8 Atlet</span>
                </div>
                <button type="button" class="btn btn-xs btn-outline-info py-0 px-2" onclick="renumberSingleHeat(${newH})" style="font-size: 11px;">
                    <i class="bi bi-sort-numeric-down"></i> 1-8
                </button>
            </div>
            <div class="dnd-dropzone p-2" data-heat="${newH}" style="min-height: 220px; display: flex; flex-direction: column; gap: 0.65rem;">
                <div class="dnd-empty-placeholder text-center py-4 text-muted small" style="border: 2px dashed rgba(255,255,255,0.1); border-radius: 6px;">
                    <i class="bi bi-box-arrow-in-down fs-4 d-block mb-1 text-secondary"></i>
                    Tarik atlet ke Hit ini
                </div>
            </div>
        </div>
    `;

    $('#dndBoardContainer').append(html);
    initDragAndDrop();

    // Scroll papan ke kanan
    const container = document.getElementById('dndBoardContainer');
    if (container) {
        container.scrollTo({ left: container.scrollWidth, behavior: 'smooth' });
    }
}

function autoSaveSusunanAjax() {
    const form = $('#formSusunanPeserta');
    const statusBadge = $('#toastAjaxStatus');

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(resp) {
            statusBadge.removeClass('d-none text-warning border-warning-subtle bg-warning-subtle')
                       .addClass('text-success border-success-subtle bg-success-subtle')
                       .html('<i class="bi bi-check-circle-fill me-1"></i> Hit & Lintasan Tersimpan Otomatis');
            setTimeout(function() {
                statusBadge.addClass('d-none');
            }, 3000);
        },
        error: function() {
            statusBadge.removeClass('d-none text-success border-success-subtle bg-success-subtle')
                       .addClass('text-warning border-warning-subtle bg-warning-subtle')
                       .html('<i class="bi bi-exclamation-triangle-fill me-1"></i> Perubahan lokal (klik Simpan)');
        }
    });
}

$(document).ready(function () {
    calculateAllocated();
    updateEstimatedHeats();
    initDragAndDrop();

    // Pulihkan tab view yang disimpan
    const savedView = localStorage.getItem('kejurwil_susunan_view');
    if (savedView === 'table') {
        switchSusunanView('table');
    }

    // Sinkronisasi saat ketik manual di tabel
    $('.input-sync-heat, .input-sync-lane').on('change', function() {
        const id = $(this).data('id');
        const heat = parseInt($(`#input_heat_${id}`).val()) || 1;
        const lane = $(`#input_lane_${id}`).val().trim();

        // Cari kartu di board DnD
        const card = $(`.dnd-athlete-card[data-id="${id}"]`);
        if (card.length) {
            const currentHeatCol = card.closest('.dnd-dropzone').data('heat');
            if (currentHeatCol !== heat) {
                let targetDropzone = $(`.dnd-dropzone[data-heat="${heat}"]`);
                if (!targetDropzone.length) {
                    addNewHeatColumn();
                    targetDropzone = $(`.dnd-dropzone[data-heat="${heat}"]`);
                }
                targetDropzone.find('.dnd-empty-placeholder').remove();
                targetDropzone.append(card);
            }
            card.data('heat', heat);
            card.data('lane', lane);
            card.find('.dnd-lane-badge').text('L' + (lane || '-'));
            updateMoveHeatDropdowns();
        }
    });

    // Select all checkboxes in modal
    $('#selectAllAtlets').on('change', function () {
        $('.atlet-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Simple search filter in modal table
    $('#filterAtletModal').on('keyup', function () {
        const val = $(this).val().toLowerCase();
        $('#tableAvailableAtlets tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    // ========================================
    // Call Room / Voice Announcer Integration
    // ========================================
    const eventInfo = {
        nomor_acara: '<?= esc($event['nomor_acara'] ?? '') ?>',
        nama_acara: '<?= esc($event['nama_acara'] ?? '') ?>',
        babak: '<?= esc($event['babak'] ?? 'Final') ?>',
        kategori_lomba: '<?= esc($event['kategori_lomba'] ?? '') ?>'
    };

    const pesertaByHeat = <?= json_encode($heatsGrouped ?? [], JSON_UNESCAPED_UNICODE) ?>;

    let announcer = null;

    if (typeof VoiceAnnouncer !== 'undefined' && VoiceAnnouncer.isSupported()) {
        announcer = new VoiceAnnouncer({
            rate: 0.85,
            volume: 1.0,
            delay: 2500,
            onStatusChange: function(text, type) {
                const colors = { info: '#94a3b8', speaking: '#38bdf8', done: '#4ade80', stopped: '#f87171', error: '#ef4444', paused: '#fbbf24' };
                $('#voiceStatusBox').html('<span style="color:' + (colors[type] || '#94a3b8') + '"><i class="bi bi-soundwave me-1"></i> ' + text + '</span>');
            }
        });
    } else {
        $('#voiceNotSupported').removeClass('d-none');
        $('#btnStartCallRoom').prop('disabled', true);
    }

    $('#voiceRate').on('input', function() {
        $('#lblRate').text(parseFloat($(this).val()).toFixed(2));
        if (announcer) announcer.rate = parseFloat($(this).val());
    });
    $('#voiceVolume').on('input', function() {
        $('#lblVolume').text(Math.round(parseFloat($(this).val()) * 100));
        if (announcer) announcer.volume = parseFloat($(this).val());
    });

    $('#btnStartCallRoom').on('click', async function() {
        if (!announcer) return;
        const selectedHeat = $('#callHeatSelect').val();
        const callLevel = parseInt($('input[name=callLevel]:checked').val()) || 1;
        announcer.rate = parseFloat($('#voiceRate').val()) || 0.85;
        announcer.volume = parseFloat($('#voiceVolume').val()) || 1;
        announcer.delayBetweenAthletes = (parseFloat($('#voiceDelay').val()) || 2.5) * 1000;

        $(this).prop('disabled', true);
        $('#btnStopVoice, #btnPauseVoice').prop('disabled', false);

        if (selectedHeat === 'all') {
            const heatNumbers = Object.keys(pesertaByHeat).sort((a, b) => a - b);
            for (const hNum of heatNumbers) {
                if (announcer.aborted) break;
                await announcer.announceHeat(eventInfo, parseInt(hNum), pesertaByHeat[hNum], callLevel);
                if (!announcer.aborted && hNum !== heatNumbers[heatNumbers.length - 1]) {
                    await announcer.delay(3000);
                }
            }
        } else {
            const hNum = parseInt(selectedHeat);
            await announcer.announceHeat(eventInfo, hNum, pesertaByHeat[hNum] || [], callLevel);
        }

        $(this).prop('disabled', false);
        $('#btnStopVoice, #btnPauseVoice').prop('disabled', true);
    });

    $('#btnStopVoice').on('click', function() {
        if (announcer) announcer.stop();
        $('#btnStartCallRoom').prop('disabled', false);
        $(this).prop('disabled', true);
        $('#btnPauseVoice').prop('disabled', true).html('<i class="bi bi-pause-fill me-1"></i> Jeda');
    });

    $('#btnPauseVoice').on('click', function() {
        if (!announcer) return;
        if (announcer.isPaused) {
            announcer.resume();
            $(this).html('<i class="bi bi-pause-fill me-1"></i> Jeda');
        } else {
            announcer.pause();
            $(this).html('<i class="bi bi-play-fill me-1"></i> Lanjut');
        }
    });

    $(document).on('click', '.btn-call-single', function() {
        if (!announcer) { alert('Web Speech API tidak tersedia di browser ini.'); return; }
        announcer.announceSingle({
            nama: $(this).data('nama'),
            no_dada: String($(this).data('nodada')),
            sekolah: $(this).data('sekolah'),
            lane: String($(this).data('lane'))
        }, eventInfo);
    });

    // Otomatis refresh preview saat modal dibuka
    $('#modalPreviewSusunan').on('show.bs.modal', function() {
        generatePreviewSusunan();
    });
});

/**
 * Generate preview susunan Hit & Lintasan secara live dari tabel peserta
 */
function generatePreviewSusunan() {
    const rows = $('table.table-dark-custom tbody tr');
    let pesertaList = [];
    
    rows.each(function() {
        const tr = $(this);
        if (tr.find('td[colspan]').length > 0) return;
        
        const heatInput = tr.find('input[name$="[heat]"]');
        if (heatInput.length === 0) return;
        
        const heatVal = parseInt(heatInput.val()) || 1;
        const laneVal = tr.find('input[name$="[lane]"]').val() ? tr.find('input[name$="[lane]"]').val().trim() : '';
        const noDada = tr.find('td:nth-child(4)').text().trim() || '-';
        const namaAtlet = tr.find('td:nth-child(5) .fw-bold').text().trim() || tr.find('td:nth-child(5)').text().trim();
        const gender = tr.find('td:nth-child(5) small').text().trim();
        const sekolah = tr.find('td:nth-child(6) > div:first-child').text().trim() || '-';
        const daerahBadge = tr.find('td:nth-child(6) .badge').prop('outerHTML') || '';
        
        pesertaList.push({
            heat: heatVal,
            lane: laneVal,
            no_dada: noDada,
            nama: namaAtlet,
            gender: gender,
            sekolah: sekolah,
            daerahBadge: daerahBadge
        });
    });

    const totalP = pesertaList.length;
    $('#previewTotalPesertaBadge').text(totalP + ' Peserta');

    if (totalP === 0) {
        $('#previewSusunanContainer').html(`
            <div class="alert alert-info text-center py-4 mb-0 bg-info bg-opacity-10 border-0">
                <i class="bi bi-info-circle fs-3 text-info d-block mb-2"></i>
                <div class="fw-bold text-dark">Belum ada atlet yang terdaftar di nomor acara ini.</div>
                <div class="small text-muted">Klik tombol "+ Tambah Peserta" untuk mendaftarkan atlet terlebih dahulu.</div>
            </div>
        `);
        $('#previewTotalHitBadge').text('0 Hit');
        return;
    }

    // Kelompokkan atlet per Heat (Hit)
    let heats = {};
    pesertaList.forEach(p => {
        if (!heats[p.heat]) heats[p.heat] = [];
        heats[p.heat].push(p);
    });

    const heatKeys = Object.keys(heats).map(Number).sort((a, b) => a - b);
    const countHeats = heatKeys.length;
    const isSingleHeat = (countHeats <= 1 || <?= json_encode(strcasecmp($event['babak'], 'Final') === 0) ?>);

    $('#previewTotalHitBadge').text(countHeats + ' Hit');
    $('#previewBabakBadge').text(isSingleHeat ? 'FINAL (Langsung Final)' : countHeats + ' Hit (Penyisihan)');

    let html = '';

    const isFieldEvent = <?= json_encode((bool)preg_match('/(tolak|lempar|lompat|lontar|cakram|martil|peluru|lembing)/i', ($event['nama_acara'] ?? '') . ' ' . ($nomorLomba['kategori'] ?? ''))) ?>;
    const colHeader = isFieldEvent ? 'Giliran' : 'Lintasan';

    heatKeys.forEach(hNum => {
        let list = heats[hNum];
        // Urutkan berdasarkan lintasan (numerik ascending)
        list.sort((a, b) => {
            let lA = parseInt(a.lane) || 999;
            let lB = parseInt(b.lane) || 999;
            if (lA === lB) return a.nama.localeCompare(b.nama);
            return lA - lB;
        });

        const titleBadge = (isSingleHeat && countHeats === 1)
            ? `<span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-trophy-fill me-1"></i> FINAL (Langsung Final)</span>`
            : `<span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-flag-fill me-1"></i> Hit ${hNum}</span>`;

        html += `
        <div class="card mb-3 shadow-sm border-0" style="background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-light border-bottom">
                <div class="d-flex align-items-center gap-2">
                    ${titleBadge}
                    <span class="text-muted small">${isFieldEvent ? 'Urutan Giliran Resmi' : 'Susunan Lintasan Resmi'}</span>
                </div>
                <span class="badge bg-secondary text-white">${list.length} Atlet</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr class="text-secondary small text-uppercase">
                            <th width="110" class="text-center">${colHeader}</th>
                            <th width="90" class="text-center">No Dada</th>
                            <th>Nama Atlet</th>
                            <th>Asal Sekolah</th>
                            <th width="160">Daerah</th>
                        </tr>
                    </thead>
                    <tbody>`;

        list.forEach(p => {
            const laneBadge = p.lane 
                ? `<span class="badge bg-primary text-white fw-bold px-3 py-1 font-monospace" style="font-size: 12px; letter-spacing: 0.5px;">${isFieldEvent ? 'Giliran ' : 'Lintasan '}${p.lane}</span>`
                : `<span class="badge bg-secondary text-white px-2 py-1">-</span>`;

            html += `
                        <tr>
                            <td class="text-center">${laneBadge}</td>
                            <td class="text-center font-monospace fw-bold text-dark fs-6" style="background: rgba(255, 171, 0, 0.08);">
                                ${p.no_dada}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${p.nama}</div>
                                ${p.gender ? `<small class="text-muted">${p.gender}</small>` : ''}
                            </td>
                            <td class="text-dark">${p.sekolah}</td>
                            <td>${p.daerahBadge || '-'}</td>
                        </tr>`;
        });

        html += `
                    </tbody>
                </table>
            </div>
        </div>`;
    });

    $('#previewSusunanContainer').html(html);
}

/**
 * Cetak lembar resmi Susunan Seri & Lintasan (Start List)
 */
function printPreviewStartList() {
    const eventNomor = <?= json_encode($event['nomor_acara']) ?>;
    const eventNama = <?= json_encode($event['nama_acara']) ?>;
    const eventBabak = <?= json_encode($event['babak']) ?>;
    const eventJadwal = <?= json_encode(($event['jadwal_hari'] ?: '-') . ' • ' . ($event['jadwal_waktu'] ?: '-')) ?>;
    const kejuaraanNama = <?= json_encode($activeKejuaraan['nama'] ?? 'KEJURWIL ATLETIK') ?>;
    const kejuaraanTempat = <?= json_encode($activeKejuaraan['tempat'] ?? 'Kota Pekalongan') ?>;

    const rows = $('table.table-dark-custom tbody tr');
    let heats = {};
    rows.each(function() {
        const tr = $(this);
        if (tr.find('td[colspan]').length > 0) return;
        const heatInput = tr.find('input[name$="[heat]"]');
        if (heatInput.length === 0) return;
        const heatVal = parseInt(heatInput.val()) || 1;
        const laneVal = tr.find('input[name$="[lane]"]').val() ? tr.find('input[name$="[lane]"]').val().trim() : '';
        const noDada = tr.find('td:nth-child(4)').text().trim() || '-';
        const namaAtlet = tr.find('td:nth-child(5) .fw-bold').text().trim() || tr.find('td:nth-child(5)').text().trim();
        const sekolah = tr.find('td:nth-child(6) > div:first-child').text().trim() || '-';
        if (!heats[heatVal]) heats[heatVal] = [];
        heats[heatVal].push({ lane: laneVal, no_dada: noDada, nama: namaAtlet, sekolah: sekolah });
    });

    const heatKeys = Object.keys(heats).map(Number).sort((a, b) => a - b);
    if (heatKeys.length === 0) {
        alert('Tidak ada data peserta untuk dicetak.');
        return;
    }

    const printWin = window.open('', '_blank', 'width=900,height=700');
    if (!printWin) {
        alert('Izinkan pop-up browser untuk membuka jendela cetak start list.');
        return;
    }

    let contentHtml = `
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Start List - Acara ${eventNomor}: ${eventNama}</title>
        <style>
            body { font-family: 'Times New Roman', serif; font-size: 13px; color: #000; padding: 25px; margin: 0; background: #fff; }
            .header-box { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
            .header-box h2 { margin: 0 0 4px 0; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px; }
            .header-box h3 { margin: 0 0 4px 0; font-size: 13px; font-weight: normal; }
            .meta-box { margin-bottom: 12px; padding: 6px 10px; border: 1px solid #000; background: #f5f5f5; font-weight: bold; display: flex; justify-content: space-between; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12px; }
            th, td { border: 1px solid #000; padding: 5px 8px; }
            th { background: #e0e0e0; text-align: center; font-weight: bold; }
            .text-center { text-align: center; }
            .heat-title { font-weight: bold; font-size: 13px; margin: 10px 0 5px 0; text-transform: uppercase; }
            @media print {
                body { padding: 0; }
                .no-print { display: none !important; }
            }
        </style>
    </head>
    <body>
        <div class="no-print" style="margin-bottom: 15px; text-align: right;">
            <button onclick="window.print()" style="padding: 8px 16px; background: #0052cc; color: #fff; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; font-family: sans-serif;">🖨 Cetak Dokumen</button>
            <button onclick="window.close()" style="padding: 8px 16px; background: #eee; border: 1px solid #ccc; cursor: pointer; border-radius: 4px; margin-left: 6px; font-family: sans-serif;">Tutup</button>
        </div>
        <div class="header-box">
            <h2>${kejuaraanNama}</h2>
            <h3>Lokasi: ${kejuaraanTempat}</h3>
            <div><strong>LEMBAR SUSUNAN SERI & LINTASAN (START LIST)</strong></div>
        </div>
        <div class="meta-box">
            <div>Acara ${eventNomor}: ${eventNama.toUpperCase()}</div>
            <div>[Babak: ${eventBabak.toUpperCase()}] &bull; Jadwal: ${eventJadwal}</div>
        </div>
    `;

    heatKeys.forEach(hNum => {
        let list = heats[hNum];
        list.sort((a, b) => {
            let lA = parseInt(a.lane) || 999;
            let lB = parseInt(b.lane) || 999;
            return lA - lB;
        });

        const titleText = (heatKeys.length === 1 || eventBabak.toLowerCase() === 'final') ? 'FINAL' : 'HIT ' + hNum;
        contentHtml += `
        <div class="heat-title">${titleText} (${list.length} Peserta)</div>
        <table>
            <thead>
                <tr>
                    <th width="40">NO</th>
                    <th width="80">LINTASAN</th>
                    <th width="90">NO DADA</th>
                    <th>NAMA ATLET</th>
                    <th width="240">ASAL SEKOLAH / KONTINGEN</th>
                    <th width="90">CATATAN</th>
                </tr>
            </thead>
            <tbody>`;

        list.forEach((p, idx) => {
            contentHtml += `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td class="text-center" style="font-weight: bold;">${p.lane || '-'}</td>
                    <td class="text-center" style="font-weight: bold;">${p.no_dada}</td>
                    <td style="text-transform: uppercase;">${p.nama}</td>
                    <td style="text-transform: uppercase;">${p.sekolah}</td>
                    <td></td>
                </tr>`;
        });

        contentHtml += `</tbody></table>`;
    });

    contentHtml += `
        <div style="margin-top: 35px; display: flex; justify-content: space-between; text-align: center;">
            <div style="width: 200px;">
                <div>Mengetahui,</div>
                <div style="margin-top: 55px; font-weight: bold; border-top: 1px solid #000;">Ketua Pertandingan</div>
            </div>
            <div style="width: 200px;">
                <div>${kejuaraanTempat}, ${new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}</div>
                <div style="margin-top: 55px; font-weight: bold; border-top: 1px solid #000;">Wasit / Juri Lintasan</div>
            </div>
        </div>
    </body>
    </html>`;

    printWin.document.open();
    printWin.document.write(contentHtml);
    printWin.document.close();
}

// Estafet Team Modal Handlers
function openAddTeamEstafetModal() {
    $('#estafet_peserta_id').val('');
    $('#estafet_atlet_id').val('');
    $('#estafet_nama_tim').val('');
    $('#estafet_no_dada').val('');
    $('#estafet_sekolah_id').val('');
    $('#estafet_heat').val('1');
    $('#estafet_lane').val('');
    $('#estafet_catatan_pelari').val('Pelari: ');
    $('#modalTeamEstafetTitle span').text('Tambah Tim Estafet Baru');
    $('#modalEditTeamEstafet').modal('show');
}

$(document).on('click', '.btn-edit-estafet-trigger', function() {
    const $btn = $(this);
    $('#estafet_peserta_id').val($btn.data('peserta-id'));
    $('#estafet_atlet_id').val($btn.data('atlet-id'));
    $('#estafet_nama_tim').val($btn.data('nama-tim'));
    $('#estafet_no_dada').val($btn.data('no-dada'));
    $('#estafet_sekolah_id').val($btn.data('sekolah-id'));
    $('#estafet_heat').val($btn.data('heat') || 1);
    $('#estafet_lane').val($btn.data('lane') || '');
    $('#estafet_catatan_pelari').val($btn.data('catatan') || 'Pelari: ');
    $('#modalTeamEstafetTitle span').text('Edit Tim & Susunan Pelari Estafet');
    $('#modalEditTeamEstafet').modal('show');
});
</script>
<?= $this->endSection() ?>
