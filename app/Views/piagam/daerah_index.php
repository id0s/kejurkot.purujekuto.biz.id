<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="card-custom mb-4 p-4" style="background: linear-gradient(135deg, rgba(0, 82, 204, 0.08) 0%, rgba(255, 171, 0, 0.12) 100%); border: 1px solid #DFE1E6; border-radius: 4px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 4px; background: linear-gradient(135deg, #0052CC, #0747A6); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 1px 3px rgba(9,30,66,0.2);">
                <i class="bi bi-geo-alt-fill text-warning"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-amber">
                        <i class="bi bi-map-fill me-1"></i> Cetak Piagam per Daerah
                    </span>
                    <span class="text-muted" style="font-size: 12px;"><?= esc($activeKejuaraan['nama'] ?? 'Kejurwil Atletik 2026') ?></span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #172B4D; font-size: 20px; letter-spacing: -0.3px;">
                    Cetak Piagam Berdasarkan Daerah Asal
                </h3>
                <p class="text-muted mb-0 small mt-1">
                    Pilih daerah/kab/kota untuk mencetak piagam juara dari daerah tersebut. Berguna untuk mengirimkan piagam ke daerah jauh (Tegal, Brebes, Pemalang, dll).
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= base_url('piagam/sekolah') ?>" class="btn btn-primary fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-white">
                <i class="bi bi-building-check"></i>
                <span>Buka Piagam per Sekolah</span>
            </a>
            <a href="<?= base_url('piagam') ?>" class="btn btn-secondary-custom d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Piagam</span>
            </a>
        </div>
    </div>
</div>

<!-- Notifikasi Flashdata -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2"></i>
        <div><?= session()->getFlashdata('success') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
        <div><?= session()->getFlashdata('error') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Daftar Daerah -->
<div class="row g-3">
    <?php
    $totalAll = 0;
    foreach ($daerahList as $d) {
        $totalAll += (int)$d['jumlah_pemenang'];
    }

    // Warna ikon per daerah
    $daerahColors = [
        'Kabupaten Tegal'      => ['bg' => '#E3F2FD', 'icon' => '#1565C0', 'border' => '#90CAF9'],
        'Kabupaten Brebes'     => ['bg' => '#FFF3E0', 'icon' => '#E65100', 'border' => '#FFCC80'],
        'Kabupaten Pemalang'   => ['bg' => '#E8F5E9', 'icon' => '#2E7D32', 'border' => '#A5D6A7'],
        'Kota Tegal'           => ['bg' => '#F3E5F5', 'icon' => '#7B1FA2', 'border' => '#CE93D8'],
        'Kabupaten Batang'     => ['bg' => '#E0F7FA', 'icon' => '#00838F', 'border' => '#80DEEA'],
        'Kabupaten Pekalongan' => ['bg' => '#FFF8E1', 'icon' => '#F57F17', 'border' => '#FFE082'],
        'Kota Pekalongan'      => ['bg' => '#EFEBE9', 'icon' => '#4E342E', 'border' => '#BCAAA4'],
    ];
    ?>

    <?php foreach ($daerahList as $d):
        $daerah = $d['daerah'] ?? 'Tidak Diketahui';
        $jumlah = (int)$d['jumlah_pemenang'];
        $slug = strtolower(str_replace(' ', '-', $daerah));
        $colors = $daerahColors[$daerah] ?? ['bg' => '#F5F5F5', 'icon' => '#616161', 'border' => '#E0E0E0'];

        // Tentukan apakah daerah jauh
        $isDaerahJauh = in_array($daerah, ['Kabupaten Tegal', 'Kabupaten Brebes', 'Kabupaten Pemalang', 'Kota Tegal']);
    ?>
        <div class="col-lg-4 col-md-6">
            <div class="card-custom p-0 h-100" style="border: 1px solid <?= $colors['border'] ?>; border-radius: 8px; overflow: hidden; transition: all 0.2s ease;">
                <!-- Header Card -->
                <div class="p-3 d-flex align-items-center gap-3" style="background: <?= $colors['bg'] ?>; border-bottom: 1px solid <?= $colors['border'] ?>;">
                    <div style="width: 44px; height: 44px; border-radius: 8px; background: <?= $colors['icon'] ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bi bi-geo-alt-fill text-white fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-0" style="color: #172B4D; font-size: 15px;">
                            <?= esc($daerah) ?>
                            <?php if ($isDaerahJauh): ?>
                                <span class="badge bg-danger ms-1" style="font-size: 10px; vertical-align: middle;">Jauh</span>
                            <?php endif; ?>
                        </h6>
                        <span class="text-muted" style="font-size: 12px;">
                            <i class="bi bi-trophy-fill text-warning me-1"></i>
                            <?= $jumlah ?> piagam pemenang
                        </span>
                    </div>
                </div>

                <!-- Body Card -->
                <div class="p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border" style="font-size: 12px;">
                                <i class="bi bi-people-fill me-1"></i>
                                <?= $jumlah ?> piagam
                            </span>
                            <span class="text-muted" style="font-size: 11px;">
                                <?= round($jumlah / max($totalAll, 1) * 100, 1) ?>% dari total
                            </span>
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            <?php if ($jumlah > 0): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="collapse" data-bs-target="#collapseDaerah_<?= $slug ?>">
                                    <i class="bi bi-eye"></i>
                                    <span>Lihat Juara</span>
                                </button>
                            <?php endif; ?>
                            <a href="<?= base_url('piagam/cetak-daerah/' . esc($slug, 'url')) ?>" target="_blank" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
                                <i class="bi bi-printer-fill"></i>
                                <span>Cetak</span>
                            </a>
                        </div>
                    </div>

                    <?php if ($jumlah > 0 && !empty($winnersByDaerah[$daerah])): ?>
                        <div class="collapse mt-2" id="collapseDaerah_<?= $slug ?>">
                            <div class="border-top pt-2">
                                <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 11.5px;">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th style="width: 50px;">Juara</th>
                                                <th>Nama Atlet</th>
                                                <th>Acara</th>
                                                <th class="text-end" style="width: 75px;">Akte</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($winnersByDaerah[$daerah] as $w): 
                                                $p = (int)$w['peringkat'];
                                                $badgeP = ($p === 1 ? 'badge-amber' : ($p === 2 ? 'bg-secondary text-white' : 'bg-warning-subtle text-dark border'));
                                            ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge <?= $badgeP ?> px-1 py-0" style="font-size: 10px;">J-<?= $p ?></span>
                                                    </td>
                                                    <td>
                                                        <strong class="text-dark d-block text-truncate" style="max-width: 130px;"><?= esc($w['nama_atlet']) ?></strong>
                                                        <span class="text-muted d-block text-truncate" style="font-size: 10px; max-width: 130px;"><?= esc($w['nama_sekolah'] ?: 'Independen') ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border" style="font-size: 10px;">#<?= esc($w['nomor_acara']) ?></span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-inline-flex align-items-center gap-1">
                                                            <?php if (!empty($w['akta_url'])): ?>
                                                                <a href="<?= esc($w['akta_url']) ?>" target="_blank" rel="noopener noreferrer" 
                                                                   class="btn btn-xs py-0 px-2 btn-sm btn-outline-warning text-dark shadow-sm" 
                                                                   title="Buka Mentahan Google Drive Peserta" style="font-size: 10px;">
                                                                    <i class="fa-brands fa-google-drive text-warning"></i>
                                                                    <span class="d-none d-md-inline">GDrive</span>
                                                                </a>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-xs py-0 px-2 btn-sm <?= !empty($w['akta_file']) ? 'btn-info text-white' : (!empty($w['akta_url']) ? 'btn-outline-info' : 'btn-outline-secondary') ?> btn-preview-akta"
                                                                data-atlet-id="<?= (int)($w['atlet_id'] ?? 0) ?>"
                                                                data-nama="<?= esc($w['nama_atlet']) ?>"
                                                                title="Verifikasi Akta Kelahiran Resmi">
                                                                <i class="bi bi-file-earmark-person-fill"></i>
                                                                <span style="font-size: 10.5px;">Akta</span>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Ringkasan Total -->
<div class="card-custom mt-4 p-3" style="border: 1px solid #DFE1E6;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="fw-semibold text-muted small">Total: </span>
            <span class="fw-bold"><?= count($daerahList) ?> daerah</span>
            <span class="text-muted mx-1">&bull;</span>
            <span class="fw-bold text-primary"><?= $totalAll ?> piagam pemenang</span>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <!-- Cetak Daerah Jauh Saja (Dropdown) -->
            <?php
            $daerahJauhCount = 0;
            foreach ($daerahList as $d) {
                $nm = $d['daerah'] ?? '';
                if (in_array($nm, ['Kabupaten Tegal', 'Kabupaten Brebes', 'Kabupaten Pemalang', 'Kota Tegal'])) {
                    $daerahJauhCount += (int)$d['jumlah_pemenang'];
                }
            }
            ?>
            <?php if ($daerahJauhCount > 0): ?>
                <div class="dropdown">
                    <button class="btn btn-warning fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-geo-fill"></i>
                        Cetak Daerah Jauh (<?= $daerahJauhCount ?> piagam)
                    </button>
                    <ul class="dropdown-menu shadow">
                        <?php foreach ($daerahList as $d):
                            $nm = $d['daerah'] ?? '';
                            if (in_array($nm, ['Kabupaten Tegal', 'Kabupaten Brebes', 'Kabupaten Pemalang', 'Kota Tegal'])):
                                $slug = strtolower(str_replace(' ', '-', $nm));
                        ?>
                            <li>
                                <a class="dropdown-item d-flex justify-content-between align-items-center" href="<?= base_url('piagam/cetak-daerah/' . esc($slug, 'url')) ?>" target="_blank">
                                    <?= esc($nm) ?>
                                    <span class="badge bg-primary ms-2"><?= $d['jumlah_pemenang'] ?></span>
                                </a>
                            </li>
                        <?php
                            endif;
                        endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- MODAL PREVIEW AKTA KELAHIRAN -->
<div class="modal fade" id="modalPreviewAkta" tabindex="-1" aria-labelledby="modalPreviewAktaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border: 1px solid #0052CC; border-radius: 8px; overflow: hidden;">
            <div class="modal-header text-white py-3 px-4" style="background: linear-gradient(135deg, #0747A6 0%, #0052CC 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-lock-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalPreviewAktaLabel" style="font-size: 17px; letter-spacing: -0.2px;">
                            Verifikasi Dokumen Akta Kelahiran Atlet
                        </h5>
                        <div class="d-flex align-items-center gap-2 mt-1" style="font-size: 11.5px;">
                            <span class="badge bg-warning text-dark"><i class="bi bi-shield-fill-check me-1"></i>UU PDP No. 27/2022</span>
                            <span class="text-white-50">Kerahasiaan data pribadi dilindungi &bull; Akses tercatat panitia</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <div class="py-2 px-3 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 12px;">
                    <div class="text-muted d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill text-primary fs-6"></i>
                        <span>Pastikan ejaan nama di piagam <strong>sesuai 100%</strong> dengan nama di kutipan Akta Kelahiran resmi.</span>
                    </div>
                    <span id="aktaAuditStamp" class="badge bg-dark font-monospace" style="font-size: 11px;">SECURE-AUDIT-ACTIVE</span>
                </div>

                <div class="row g-0">
                    <div class="col-lg-4 p-3 border-end bg-light" style="font-size: 13px;">
                        <div class="card mb-3 shadow-sm border-0" style="border: 1px solid #DFE1E6 !important;">
                            <div class="card-header py-2 px-3 bg-white fw-bold d-flex justify-content-between align-items-center border-bottom">
                                <span><i class="bi bi-person-badge-fill text-primary me-1"></i> Data Atlet di Sistem</span>
                                <span id="badgeDocStatus" class="badge bg-secondary">Memuat...</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Nama di Database:</label>
                                    <strong id="dispNamaAtlet" class="fs-6 text-dark text-break">-</strong>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="text-muted small d-block mb-0" style="font-size: 11px;">No. Dada:</label>
                                        <span id="dispNoDada" class="badge bg-dark font-monospace">-</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block mb-0" style="font-size: 11px;">Jenis Kelamin:</label>
                                        <span id="dispGender" class="text-dark fw-semibold">-</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Tanggal Lahir:</label>
                                    <span id="dispTglLahir" class="text-dark fw-bold">-</span>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Kelompok Umur:</label>
                                    <span id="dispKU" class="badge bg-primary text-white">-</span>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Asal Sekolah / Klub:</label>
                                    <span id="dispSekolah" class="text-dark fw-semibold text-break">-</span>
                                </div>
                                <div>
                                    <label class="text-muted small d-block mb-0" style="font-size: 11px;">Asal Daerah (Kab/Kota):</label>
                                    <span id="dispDaerah" class="badge bg-info text-dark border">-</span>
                                </div>
                                <div class="mt-3 pt-2 border-top">
                                    <label class="text-muted small d-block mb-1" style="font-size: 11px; font-weight: 600;">
                                        <i class="bi bi-google text-danger me-1"></i> Mentahan Link Google Drive:
                                    </label>
                                    <a id="btnModalRawDrive" href="#" target="_blank" rel="noopener noreferrer" 
                                       class="btn btn-sm btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" 
                                       style="font-size: 11.5px; display: none;">
                                        <i class="fa-brands fa-google-drive text-warning"></i>
                                        <span>Buka Mentahan Google Drive</span>
                                        <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                    <div id="boxNoRawDrive" class="badge bg-light text-secondary border w-100 py-2 text-start" style="font-size: 11px; display: none;">
                                        <i class="bi bi-info-circle me-1"></i> Tidak ada link Google Drive di formulir
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0" style="border: 1px solid #FFE082 !important; background: #FFFDE7;">
                            <div class="card-header py-2 px-3 fw-bold text-dark border-bottom" style="background: #FFF8E1;">
                                <i class="bi bi-pencil-square text-warning me-1"></i> Koreksi Nama Sesuai Akte
                            </div>
                            <div class="card-body p-3">
                                <p class="text-muted mb-2" style="line-height: 1.35; font-size: 11.5px;">
                                    Jika terdapat perbedaan ejaan (misal <code>RIZQI</code> &rarr; <code>RISQI</code>), simpan nama yang benar di bawah ini agar piagam otomatis tersinkronisasi.
                                </p>
                                <form id="formKoreksiNama">
                                    <input type="hidden" id="editAtletId" name="atlet_id">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1" style="font-size: 11.5px;">Nama Baru (Huruf Kapital):</label>
                                        <input type="text" id="editNamaBaru" name="nama" class="form-control form-control-sm text-uppercase fw-bold" placeholder="NAMA LENGKAP ATLET" required>
                                    </div>
                                    <button type="submit" id="btnSimpanNama" class="btn btn-warning btn-sm fw-bold w-100 shadow-sm d-flex align-items-center justify-content-center gap-1 text-dark">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Perbarui Nama di Database</span>
                                    </button>
                                </form>
                                <div id="koreksiAlert" class="mt-2" style="display: none;"></div>
                            </div>
                        </div>
                        <!-- Card Upload / Ganti Berkas Akta Baru -->
                        <div class="card shadow-sm border-0 mt-3" style="border: 1px solid #BBDEFB !important; background: #F0F7FF;">
                            <div class="card-header py-2 px-3 fw-bold text-dark border-bottom d-flex justify-content-between align-items-center" style="background: #E3F2FD;">
                                <span><i class="bi bi-cloud-arrow-up-fill text-primary me-1"></i> Unggah / Ganti Berkas</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">Event Baru / Update</span>
                            </div>
                            <div class="card-body p-3">
                                <p class="text-muted mb-2" style="line-height: 1.35; font-size: 11px;">
                                    Upload scan foto akte (JPG/PNG/PDF maks 15MB) atau masukkan link Google Drive untuk memperbarui berkas atlet ini.
                                </p>
                                <form id="formUploadAkta" enctype="multipart/form-data">
                                    <input type="hidden" id="uploadAtletId" name="atlet_id">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1" style="font-size: 11px;">Pilih File Fisik (JPG / PNG / PDF):</label>
                                        <input type="file" id="fileInputAkta" name="file_akta" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1" style="font-size: 11px;">Atau Tautan Google Drive:</label>
                                        <input type="url" id="urlInputAkta" name="akta_url" class="form-control form-control-sm" placeholder="https://drive.google.com/file/d/...">
                                    </div>
                                    <button type="submit" id="btnSubmitUpload" class="btn btn-primary btn-sm fw-bold w-100 shadow-sm d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-upload"></i>
                                        <span>Simpan & Tampilkan Dokumen</span>
                                    </button>
                                </form>
                                <div id="uploadAlert" class="mt-2" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 p-3" style="background: #1e1e1e;">
                        <div class="d-flex justify-content-between align-items-center mb-2 px-3 py-2 bg-dark rounded text-white" style="font-size: 12px; border: 1px solid #333;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary font-monospace" id="badgeViewerType">VIEWER</span>
                                <span class="text-white-50" id="viewerInfoText">Memuat dokumen...</span>
                            </div>
                            <div class="d-flex align-items-center gap-1" id="viewerControls" style="display: none;">
                                <a id="btnViewerDriveTop" href="#" target="_blank" rel="noopener noreferrer" 
                                   class="btn btn-sm btn-warning text-dark py-0 px-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm me-1" 
                                   style="display: none; font-size: 11px;" title="Buka Link Mentahan Google Drive Form Peserta">
                                    <i class="fa-brands fa-google-drive text-warning"></i>
                                    <span>Mentahan GDrive</span>
                                    <i class="bi bi-box-arrow-up-right" style="font-size: 9px;"></i>
                                </a>
                                <span class="badge bg-secondary font-monospace me-1" id="zoomLevelBadge" title="Tingkat Pembesaran">100%</span>
                                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnZoomIn" title="Perbesar (+ / Scroll Up)"><i class="bi bi-zoom-in"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnZoomOut" title="Perkecil (- / Scroll Down)"><i class="bi bi-zoom-out"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnRotate" title="Putar 90 Derajat"><i class="bi bi-arrow-clockwise"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnResetZoom" title="Reset Posisi & Zoom"><i class="bi bi-arrows-fullscreen"></i></button>
                            </div>
                        </div>

                        <div id="aktaViewerContainer" class="position-relative d-flex align-items-center justify-content-center bg-black rounded" style="min-height: 520px; max-height: 640px; overflow: hidden; user-select: none; cursor: grab;" oncontextmenu="return false;">
                            <div id="aktaWatermarkOverlay" class="position-absolute w-100 h-100 pointer-events-none d-flex flex-column align-items-center justify-content-around py-4" style="z-index: 10; opacity: 0.15; pointer-events: none; overflow: hidden; transform: rotate(-25deg); color: #fff; font-size: 13px; font-weight: bold; letter-spacing: 2px;">
                                <span>DOKUMEN RAHASIA &bull; VERIFIKASI KEJURWIL ATLETIK</span>
                                <span>PERLINDUNGAN DATA PRIBADI &bull; HANYA AKSES PANITIA RESMI</span>
                                <span>DOKUMEN RAHASIA &bull; VERIFIKASI KEJURWIL ATLETIK</span>
                            </div>

                            <div id="aktaLoadingSpinner" class="text-center text-white py-5">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div class="small">Mengambil berkas secara aman dari server terlindung...</div>
                            </div>

                            <img id="aktaViewerImg" src="" alt="Akta Kelahiran" class="img-fluid rounded transition" style="display: none; max-width: 95%; max-height: 600px; transition: transform 0.2s ease; cursor: grab;" draggable="false">

                            <iframe id="aktaViewerIframe" src="" style="width: 100%; height: 580px; border: none; display: none;"></iframe>

                            <div id="aktaDriveFallback" class="text-center text-white p-4" style="display: none; max-width: 480px;">
                                <div style="width: 60px; height: 60px; border-radius: 12px; background: rgba(0, 82, 204, 0.25); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; border: 1px solid rgba(0, 82, 204, 0.5);">
                                    <i class="bi bi-google text-warning fs-2"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Dokumen Google Drive Formulir</h6>
                                <p class="text-white-50 small mb-3">
                                    Berkas diunggah peserta melalui formulir pendaftaran online resmi. Klik tombol di bawah untuk membuka dokumen di Google Drive:
                                </p>
                                <a id="btnOpenDrive" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow fw-semibold">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    <span>Buka Dokumen di Google Drive</span>
                                </a>
                                <div class="mt-3 text-white-50" style="font-size: 11.5px;">
                                    <i class="bi bi-lock-fill text-warning me-1"></i> Diperlukan login akun Google panitia jika berkas memiliki izin akses terbatas.
                                </div>
                            </div>

                            <div id="aktaNotFound" class="text-center text-white p-4" style="display: none; max-width: 420px;">
                                <i class="bi bi-file-earmark-x text-secondary fs-1 mb-2 d-block"></i>
                                <h6 class="fw-bold mb-1 text-white-50">Dokumen Belum Tersedia</h6>
                                <p class="text-secondary small mb-0">
                                    Dokumen Akta Kelahiran atlet ini belum diunggah atau tidak ditemukan di rekapan formulir pendaftaran.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer py-2 px-3 bg-light justify-content-between">
                <span class="text-muted small" style="font-size: 11.5px;">
                    <i class="bi bi-shield-check text-success me-1"></i> Dokumen dilindungi sistem isolasi storage server (tidak dapat diakses publik secara langsung).
                </span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Viewer State Variables
    let currentZoom = 1;
    let currentRotation = 0;
    let panX = 0;
    let panY = 0;
    let isDragging = false;
    let startX = 0;
    let startY = 0;

    const imgEl = document.getElementById('aktaViewerImg');
    const iframeEl = document.getElementById('aktaViewerIframe');
    const driveFallbackEl = document.getElementById('aktaDriveFallback');
    const notFoundEl = document.getElementById('aktaNotFound');
    const spinnerEl = document.getElementById('aktaLoadingSpinner');
    const controlsEl = document.getElementById('viewerControls');
    const badgeTypeEl = document.getElementById('badgeViewerType');
    const infoTextEl = document.getElementById('viewerInfoText');
    const containerEl = document.getElementById('aktaViewerContainer');
    const zoomLevelBadge = document.getElementById('zoomLevelBadge');

    function applyTransform(withTransition = true) {
        if (imgEl) {
            imgEl.style.transition = withTransition ? 'transform 0.15s ease-out' : 'none';
            imgEl.style.transform = `translate(${panX}px, ${panY}px) scale(${currentZoom}) rotate(${currentRotation}deg)`;
            if (zoomLevelBadge) {
                zoomLevelBadge.textContent = Math.round(currentZoom * 100) + '%';
            }
        }
    }

    // Zoom Controls
    document.getElementById('btnZoomIn')?.addEventListener('click', function() {
        if (currentZoom < 5) {
            currentZoom = Math.min(5, Math.round((currentZoom + 0.25) * 100) / 100);
            applyTransform(true);
        }
    });

    document.getElementById('btnZoomOut')?.addEventListener('click', function() {
        if (currentZoom > 0.4) {
            currentZoom = Math.max(0.4, Math.round((currentZoom - 0.25) * 100) / 100);
            applyTransform(true);
        }
    });

    document.getElementById('btnRotate')?.addEventListener('click', function() {
        currentRotation = (currentRotation + 90) % 360;
        applyTransform(true);
    });

    document.getElementById('btnResetZoom')?.addEventListener('click', function() {
        currentZoom = 1;
        currentRotation = 0;
        panX = 0;
        panY = 0;
        applyTransform(true);
    });

    // Drag / Pan Mouse Events (Klik & Geser)
    if (containerEl) {
        containerEl.addEventListener('mousedown', function(e) {
            if (e.button !== 0 || !imgEl || imgEl.style.display === 'none') return;
            isDragging = true;
            startX = e.clientX - panX;
            startY = e.clientY - panY;
            containerEl.style.cursor = 'grabbing';
            imgEl.style.cursor = 'grabbing';
            e.preventDefault();
        });

        window.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            panX = e.clientX - startX;
            panY = e.clientY - startY;
            applyTransform(false); // Smooth direct tracking tanpa lag
        });

        window.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                if (containerEl) containerEl.style.cursor = 'grab';
                if (imgEl) imgEl.style.cursor = 'grab';
            }
        });

        // Mouse Wheel Zoom (Scroll untuk Zoom In/Out)
        containerEl.addEventListener('wheel', function(e) {
            if (!imgEl || imgEl.style.display === 'none') return;
            e.preventDefault();
            const step = 0.15;
            if (e.deltaY < 0) {
                currentZoom = Math.min(5, Math.round((currentZoom + step) * 100) / 100);
            } else {
                currentZoom = Math.max(0.4, Math.round((currentZoom - step) * 100) / 100);
            }
            applyTransform(true);
        }, { passive: false });

        // Touch Drag Events untuk Touchscreen / HP / Tablet
        let touchX = 0, touchY = 0;
        containerEl.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1 && imgEl && imgEl.style.display !== 'none') {
                isDragging = true;
                touchX = e.touches[0].clientX - panX;
                touchY = e.touches[0].clientY - panY;
            }
        }, { passive: true });

        window.addEventListener('touchmove', function(e) {
            if (!isDragging || e.touches.length !== 1) return;
            panX = e.touches[0].clientX - touchX;
            panY = e.touches[0].clientY - touchY;
            applyTransform(false);
        }, { passive: true });

        window.addEventListener('touchend', function() {
            isDragging = false;
        });
    }

    const modalPreviewAkta = new bootstrap.Modal(document.getElementById('modalPreviewAkta'));
    let activeRowNamaEl = null;

    document.querySelectorAll('.btn-preview-akta').forEach(btn => {
        btn.addEventListener('click', function() {
            const atletId = this.dataset.atletId;
            const nama = this.dataset.nama;
            activeRowNamaEl = this.closest('tr')?.querySelector('td strong.text-dark');

            document.getElementById('editAtletId').value = atletId;
            document.getElementById('editNamaBaru').value = nama;
            document.getElementById('dispNamaAtlet').textContent = nama;
            document.getElementById('dispNoDada').textContent = '-';
            document.getElementById('dispGender').textContent = '-';
            document.getElementById('dispTglLahir').textContent = '-';
            document.getElementById('dispKU').textContent = '-';
            document.getElementById('dispSekolah').textContent = '-';
            document.getElementById('dispDaerah').textContent = '-';
            document.getElementById('koreksiAlert').style.display = 'none';
            const btnRawDrive = document.getElementById('btnModalRawDrive');
            const boxNoDrive = document.getElementById('boxNoRawDrive');
            const btnViewerTop = document.getElementById('btnViewerDriveTop');
            if (btnRawDrive) btnRawDrive.style.display = 'none';
            if (boxNoDrive) boxNoDrive.style.display = 'none';
            if (btnViewerTop) btnViewerTop.style.display = 'none';

            currentZoom = 1;
            currentRotation = 0;
            panX = 0;
            panY = 0;
            applyTransform(true);
            controlsEl.style.display = 'none';
            imgEl.style.display = 'none';
            imgEl.src = '';
            iframeEl.style.display = 'none';
            iframeEl.src = '';
            driveFallbackEl.style.display = 'none';
            notFoundEl.style.display = 'none';
            spinnerEl.style.display = 'block';
            badgeTypeEl.textContent = 'MEMUAT';
            badgeTypeEl.className = 'badge bg-secondary font-monospace';
            infoTextEl.textContent = 'Mengambil data atlet...';

            modalPreviewAkta.show();

            fetch(`<?= base_url('piagam/preview-akta') ?>/${atletId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                spinnerEl.style.display = 'none';
                if (res.status !== 'success') {
                    notFoundEl.style.display = 'block';
                    infoTextEl.textContent = res.message || 'Gagal memuat dokumen.';
                    return;
                }

                const a = res.atlet;
                document.getElementById('dispNamaAtlet').textContent = a.nama;
                document.getElementById('editNamaBaru').value = a.nama;
                document.getElementById('dispNoDada').textContent = a.no_dada;
                document.getElementById('dispGender').textContent = a.gender;
                document.getElementById('dispTglLahir').textContent = a.tanggal_lahir;
                document.getElementById('dispKU').textContent = a.nama_ku;
                document.getElementById('dispSekolah').textContent = a.nama_sekolah;
                document.getElementById('dispDaerah').textContent = a.asal_daerah;

                if (res.watermark) {
                    document.getElementById('aktaAuditStamp').textContent = res.watermark.split('•')[2]?.trim() || 'SECURE-AUDIT';
                }

                // Tampilkan tombol mentahan Google Drive jika tersedia
                if (a.akta_url) {
                    if (btnRawDrive) {
                        btnRawDrive.href = a.akta_url;
                        btnRawDrive.style.display = 'inline-flex';
                    }
                    if (boxNoDrive) boxNoDrive.style.display = 'none';
                    if (btnViewerTop) {
                        btnViewerTop.href = a.akta_url;
                        btnViewerTop.style.display = 'inline-flex';
                    }
                } else {
                    if (btnRawDrive) btnRawDrive.style.display = 'none';
                    if (boxNoDrive) boxNoDrive.style.display = 'block';
                    if (btnViewerTop) btnViewerTop.style.display = 'none';
                }

                if (a.has_local && a.view_url) {
                    document.getElementById('badgeDocStatus').className = 'badge bg-success';
                    document.getElementById('badgeDocStatus').textContent = 'Server Terlindung';

                    if (a.file_type === 'pdf') {
                        iframeEl.src = a.view_url;
                        iframeEl.style.display = 'block';
                        badgeTypeEl.textContent = 'DOKUMEN PDF';
                        badgeTypeEl.className = 'badge bg-danger font-monospace';
                        infoTextEl.textContent = 'Kutipan Akta Format PDF';
                    } else {
                        imgEl.src = a.view_url;
                        imgEl.style.display = 'block';
                        controlsEl.style.display = 'flex';
                        badgeTypeEl.textContent = 'FOTO / SCAN ASLI';
                        badgeTypeEl.className = 'badge bg-success font-monospace';
                        infoTextEl.textContent = 'Format Gambar: Geser (drag) untuk melihat bagian lain & Scroll mouse untuk zoom';
                    }
                } else if (a.akta_url) {
                    document.getElementById('badgeDocStatus').className = 'badge bg-warning text-dark';
                    document.getElementById('badgeDocStatus').textContent = 'Google Drive';

                    driveFallbackEl.style.display = 'block';
                    document.getElementById('btnOpenDrive').href = a.akta_url;
                    badgeTypeEl.textContent = 'GOOGLE DRIVE';
                    badgeTypeEl.className = 'badge bg-primary font-monospace';
                    infoTextEl.textContent = 'Tersedia tautan Google Drive';
                } else {
                    document.getElementById('badgeDocStatus').className = 'badge bg-secondary';
                    document.getElementById('badgeDocStatus').textContent = 'Tidak Ada';
                    notFoundEl.style.display = 'block';
                    badgeTypeEl.textContent = 'KOSONG';
                    infoTextEl.textContent = 'Berkas belum diunggah.';
                }
            })
            .catch(err => {
                spinnerEl.style.display = 'none';
                notFoundEl.style.display = 'block';
                infoTextEl.textContent = 'Terjadi kesalahan jaringan.';
            });
        });
    });

    const formKoreksi = document.getElementById('formKoreksiNama');
    formKoreksi?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanNama');
        const alertBox = document.getElementById('koreksiAlert');
        const atletId = document.getElementById('editAtletId').value;
        const namaBaru = document.getElementById('editNamaBaru').value.trim();

        if (!namaBaru) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        alertBox.style.display = 'none';

        const formData = new FormData();
        formData.append('atlet_id', atletId);
        formData.append('nama', namaBaru);

        fetch('<?= base_url('piagam/update-nama-atlet') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Perbarui Nama di Database</span>';
            if (res.status === 'success') {
                alertBox.className = 'alert alert-success py-2 px-3 mb-0 small';
                alertBox.textContent = res.message;
                alertBox.style.display = 'block';

                document.getElementById('dispNamaAtlet').textContent = res.nama;

                if (activeRowNamaEl) {
                    activeRowNamaEl.textContent = res.nama;
                }

                document.querySelectorAll(`.btn-preview-akta[data-atlet-id="${atletId}"]`).forEach(b => {
                    b.dataset.nama = res.nama;
                });
            } else {
                alertBox.className = 'alert alert-danger py-2 px-3 mb-0 small';
                alertBox.textContent = res.message || 'Gagal menyimpan.';
                alertBox.style.display = 'block';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Perbarui Nama di Database</span>';
            alertBox.className = 'alert alert-danger py-2 px-3 mb-0 small';
            alertBox.textContent = 'Terjadi kesalahan jaringan saat menyimpan.';
            alertBox.style.display = 'block';
        });
    });

    // Form Upload Berkas Akta Baru
    const formUpload = document.getElementById('formUploadAkta');
    formUpload?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitUpload');
        const alertBox = document.getElementById('uploadAlert');
        const atletId = document.getElementById('editAtletId').value;
        document.getElementById('uploadAtletId').value = atletId;

        const fileInput = document.getElementById('fileInputAkta');
        const urlInput = document.getElementById('urlInputAkta');

        if (!fileInput.files.length && !urlInput.value.trim()) {
            alertBox.className = 'alert alert-warning py-2 px-3 mb-0 small';
            alertBox.textContent = 'Pilih berkas dokumen atau masukkan tautan Google Drive.';
            alertBox.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah...';
        alertBox.style.display = 'none';

        const formData = new FormData(this);
        formData.set('atlet_id', atletId);

        fetch('<?= base_url('piagam/upload-akta') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> <span>Simpan & Tampilkan Dokumen</span>';
            if (res.status === 'success') {
                alertBox.className = 'alert alert-success py-2 px-3 mb-0 small';
                alertBox.textContent = res.message;
                alertBox.style.display = 'block';

                fileInput.value = '';
                urlInput.value = '';

                // Update tombol baris di tabel agar aktif
                document.querySelectorAll(`.btn-preview-akta[data-atlet-id="${atletId}"]`).forEach(b => {
                    b.className = 'btn btn-sm btn-info text-white d-inline-flex align-items-center gap-1 btn-preview-akta';
                });

                // Reload preview dokumen di modal secara otomatis
                const btnActive = document.querySelector(`.btn-preview-akta[data-atlet-id="${atletId}"]`);
                if (btnActive) {
                    setTimeout(() => btnActive.click(), 600);
                }
            } else {
                alertBox.className = 'alert alert-danger py-2 px-3 mb-0 small';
                alertBox.textContent = res.message || 'Gagal mengunggah berkas.';
                alertBox.style.display = 'block';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> <span>Simpan & Tampilkan Dokumen</span>';
            alertBox.className = 'alert alert-danger py-2 px-3 mb-0 small';
            alertBox.textContent = 'Terjadi kesalahan jaringan saat mengunggah berkas.';
            alertBox.style.display = 'block';
        });
    });

});
</script>

<?= $this->endSection() ?>
