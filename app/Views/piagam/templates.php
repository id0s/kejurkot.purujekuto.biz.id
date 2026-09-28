<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header Gadget -->
<div class="card-custom mb-4 p-4" style="background: linear-gradient(135deg, rgba(0, 82, 204, 0.08) 0%, rgba(59, 130, 246, 0.1) 100%); border: 1px solid #DFE1E6; border-radius: 4px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 4px; background: #0052CC; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 1px 3px rgba(9,30,66,0.2);">
                <i class="bi bi-images text-white"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-blue">
                        <i class="bi bi-gear-fill me-1"></i> Pengaturan Sertifikat
                    </span>
                    <span class="text-muted" style="font-size: 12px;"><?= esc($activeKejuaraan['nama'] ?? 'Kejurwil Atletik 2026') ?></span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #172B4D; font-size: 20px; letter-spacing: -0.3px;">
                    Kelola Template & Tanda Tangan Piagam Penghargaan
                </h3>
                <p class="text-muted mb-0 small mt-1">
                    Atur tanda tangan (unggah file TTD, ketik manual, atau gunakan otomatis), sesuaikan judul, nama pejabat, atau sembunyikan template lain.
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= base_url('piagam') ?>" class="btn btn-secondary-custom d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Piagam
            </a>

            <?php if (!empty($showAll)): ?>
                <a href="<?= base_url('piagam/templates') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-eye-slash"></i> Tampilkan Hanya Template Aktif
                </a>
            <?php else: ?>
                <a href="<?= base_url('piagam/templates?show_all=1') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" title="Lihat template lain yang disembunyikan">
                    <i class="bi bi-archive"></i> Tampilkan Semua (Termasuk Arsip)
                </a>
            <?php endif; ?>

            <button type="button" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUploadTemplate">
                <i class="bi bi-cloud-arrow-up-fill fs-6"></i>
                <span>Upload Template Baru</span>
            </button>
        </div>
    </div>
</div>

<!-- Flashdata Alert -->
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

<?php if (empty($showAll) && isset($totalAll) && $totalAll > count($templates)): ?>
    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 py-2 px-3 small">
        <div>
            <i class="bi bi-info-circle-fill me-1 text-primary"></i>
            Hanya menampilkan template resmi aktif (<strong><?= count($templates) ?></strong> aktif). 
            Sebanyak <strong><?= $totalAll - count($templates) ?></strong> template lama disembunyikan sesuai permintaan.
        </div>
        <a href="<?= base_url('piagam/templates?show_all=1') ?>" class="fw-bold text-decoration-none">
            Tampilkan Semua Template Arsip &raquo;
        </a>
    </div>
<?php endif; ?>

<!-- Grid Kartu Template -->
<div class="row g-4">
    <?php foreach ($templates as $tpl): ?>
        <?php $isActive = !empty($tpl['is_active']); ?>
        <div class="col-md-6 col-xl-4">
            <div class="card-custom h-100 shadow-sm border <?= $tpl['is_default'] ? 'border-primary' : ($isActive ? '' : 'border-dashed opacity-75') ?>" style="background: #FFFFFF;">
                <!-- Header Card -->
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center <?= $isActive ? 'bg-light' : 'bg-secondary bg-opacity-10' ?>">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge <?= $tpl['orientasi'] === 'landscape' ? 'badge-blue' : 'badge-purple' ?>">
                            <i class="bi <?= $tpl['orientasi'] === 'landscape' ? 'bi-aspect-ratio' : 'bi-file-earmark-text' ?> me-1"></i>
                            <?= strtoupper($tpl['orientasi']) ?>
                        </span>
                        <?php if ($isActive): ?>
                            <span class="badge bg-success text-white small">
                                <i class="bi bi-check-circle me-1"></i> AKTIF
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary text-white small">
                                <i class="bi bi-eye-slash me-1"></i> DISEMBUNYIKAN
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($tpl['is_default']): ?>
                        <span class="badge badge-amber d-inline-flex align-items-center gap-1">
                            <i class="bi bi-star-fill text-warning"></i> AKTIF DEFAULT
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Preview Gambar / Thumbnail Desain -->
                <div class="p-3 text-center" style="min-height: 220px; display: flex; align-items: center; justify-content: center; background-color: #f8fafc;">
                    <?php if ($tpl['tipe'] === 'official_federation'): ?>
                        <!-- Mini Preview Portrait Resmi Federasi PASI Jateng -->
                        <div class="p-2 border rounded text-center w-100 position-relative shadow-sm" style="background: #FFFDF8; border: 2.5px solid #0B2545 !important; max-width: 190px;">
                            <div class="d-flex justify-content-center align-items-center gap-1 mb-1">
                                <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="PASI" style="height: 18px; width: auto;">
                                <div style="font-size: 6px; font-weight: 800; color: #0B2545; line-height: 1.1; text-align: left;">
                                    PENGPROV PASI JATENG
                                </div>
                            </div>
                            <div class="py-1 px-1 mb-1 rounded-1" style="background: #0B2545; color: #E5C378; font-size: 7.5px; font-weight: 900; letter-spacing: 0.8px; font-family: serif;">
                                SERTIFIKAT PENGHARGAAN
                            </div>
                            <div class="text-center mb-1" style="font-size: 7px; color: #0B2545;">
                                <div style="font-weight: 800;">[NAMA ATLET SISTEM]</div>
                                <div style="font-size: 5.5px; color: #78350F; font-weight: 700;">JUARA 1 &bull; ATLETIK RESMI</div>
                            </div>
                            <div class="pt-1 border-top d-flex justify-content-between" style="font-size: 5.5px; color: #0B2545;">
                                <div><i class="bi bi-pen text-primary"></i> Jumanto</div>
                                <div><i class="bi bi-patch-check-fill text-danger"></i> Dr. Agus Widodo</div>
                                <div><i class="bi bi-pen text-primary"></i> Zaini</div>
                            </div>
                        </div>
                    <?php elseif ($tpl['tipe'] === 'portrait_prestige'): ?>
                        <!-- Mini Preview Portrait Prestige (Royal Navy & Gold) -->
                        <div class="p-3 border rounded text-center w-100 position-relative shadow-sm" style="background: #FFFFFF; border: 2.5px solid #0A2540 !important; max-width: 210px;">
                            <div class="text-start mb-1" style="font-size: 8px; font-weight: 900; line-height: 1.1; color: #071E3D;">
                                PENGPROV PASI JATENG<br>EKS-KARESIDENAN
                            </div>
                            <div class="py-1 px-2 mb-2 text-start rounded-1" style="background: linear-gradient(135deg, #0A2540, #16213E); color: #F8E7B9; font-size: 9px; font-weight: 900; letter-spacing: 0.5px; border-bottom: 2px solid #D4AF37;">
                                PIAGAM PENGHARGAAN
                                <div style="font-size: 6px; font-weight: 700; color: #FDE68A;">NOMOR : 001/SERT/PASIJATENG/2026</div>
                            </div>
                            <div class="text-start mb-2" style="font-size: 7px; line-height: 1.4; color: #334155;">
                                <div><strong>Nama:</strong> [NAMA ATLET SISTEM]</div>
                                <div><strong>Nomor:</strong> [NOMOR LOMBA SISTEM]</div>
                                <div><span class="badge bg-dark text-warning p-1" style="font-size: 6px;">★ JUARA 1 (SATU) ★</span></div>
                            </div>
                            <div class="pt-1 border-top d-flex justify-content-between align-items-center" style="font-size: 6px; color: #0F172A;">
                                <div><i class="bi bi-pen text-primary"></i> Jumanto</div>
                                <div><i class="bi bi-patch-check-fill text-danger"></i> Dr. Agus Widodo</div>
                                <div><i class="bi bi-pen text-primary"></i> Zaini</div>
                            </div>
                        </div>
                    <?php elseif ($tpl['id'] == 2 || $tpl['tipe'] === 'portrait_ekskari'): ?>
                        <!-- Mini Preview Portrait Format Resmi Eks-Karesidenan -->
                        <div class="p-3 border rounded text-center w-100 position-relative shadow-sm" style="background: #FFFFFF; border: 2px solid #0A2540 !important; max-width: 210px;">
                            <div class="text-start mb-1" style="font-size: 8px; font-weight: 800; line-height: 1.1; color: #0F172A;">
                                KEJUARAAN ATLETIK<br>TINGKAT EKS KARESIDENAN
                            </div>
                            <div class="py-1 px-2 mb-2 text-start rounded-1" style="background: linear-gradient(90deg, #F59E0B, #FBBF24); color: #0A2540; font-size: 9px; font-weight: 900; letter-spacing: 0.5px;">
                                PIAGAM PENGHARGAAN
                                <div style="font-size: 6px; font-weight: 700; color: #111;">NOMOR : 001/EKS/PASI/2026</div>
                            </div>
                            <div class="text-start mb-2" style="font-size: 7px; line-height: 1.4; color: #334155;">
                                <div><strong>Nama:</strong> [NAMA ATLET SISTEM]</div>
                                <div><strong>Nomor:</strong> [NOMOR LOMBA SISTEM]</div>
                                <div><strong>Juara:</strong> [1 (SATU)]</div>
                            </div>
                            <div class="pt-1 border-top d-flex justify-content-between align-items-center" style="font-size: 6px; color: #0F172A;">
                                <div><i class="bi bi-pen text-primary"></i> Jumanto</div>
                                <div><i class="bi bi-patch-check-fill text-danger"></i> Dr. Agus Widodo</div>
                                <div><i class="bi bi-pen text-primary"></i> Zaini</div>
                            </div>
                        </div>
                    <?php elseif (!empty($tpl['file_background']) && file_exists(FCPATH . 'uploads/piagam/templates/' . $tpl['file_background'])): ?>
                        <img src="<?= base_url('uploads/piagam/templates/' . esc($tpl['file_background'])) ?>" 
                             alt="<?= esc($tpl['nama']) ?>" 
                             class="img-fluid rounded border shadow-sm" 
                             style="max-height: 180px; width: auto; object-fit: contain;">
                    <?php else: ?>
                        <!-- Vector Certificate Graphic Placeholder -->
                        <div class="p-3 border rounded text-center w-100 shadow-sm" style="border: 2px solid #CBD5E1 !important; background: #FFF; max-width: 250px;">
                            <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="PASI" style="height: 20px; width: auto;">
                                <strong class="small text-dark"><?= esc($tpl['judul'] ?: 'PIAGAM PENGHARGAAN') ?></strong>
                            </div>
                            <div class="badge bg-primary-subtle text-primary border small mb-2"><?= esc($tpl['nama']) ?></div>
                            <div class="small text-muted" style="font-size: 10px;">Format: <?= strtoupper($tpl['orientasi']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info Template -->
                <div class="p-3 flex-grow-1">
                    <h5 class="fw-bold text-dark fs-6 mb-1"><?= esc($tpl['nama']) ?></h5>
                    <p class="text-muted small mb-2 text-truncate" title="<?= esc($tpl['instansi_header']) ?>">
                        <i class="bi bi-building me-1"></i> <?= esc($tpl['instansi_header'] ?: 'PASI Jawa Tengah') ?>
                    </p>
                    <div class="small text-muted mb-2 font-monospace">
                        <i class="bi bi-hash me-1"></i> Format: <strong><?= esc($tpl['format_nomor']) ?></strong>
                    </div>

                    <!-- Penandatangan Summary -->
                    <div class="p-2 bg-light rounded small text-secondary" style="font-size: 11px;">
                        <div><strong>Pejabat Penandatangan:</strong></div>
                        <div class="text-truncate">
                            &bull; <strong><?= esc($tpl['ttd_1_nama']) ?></strong> (<?= esc($tpl['ttd_1_jabatan']) ?>)
                            <?= !empty($tpl['ttd_1_image']) ? '<i class="bi bi-file-earmark-image text-success ms-1" title="TTD Image Aktif"></i>' : '' ?>
                        </div>
                        <div class="text-truncate">
                            &bull; <strong><?= esc($tpl['ttd_2_nama']) ?></strong> (<?= esc($tpl['ttd_2_jabatan']) ?>)
                            <?= !empty($tpl['ttd_2_image']) ? '<i class="bi bi-file-earmark-image text-success ms-1" title="TTD Image Aktif"></i>' : '' ?>
                        </div>
                        <div class="text-truncate">
                            &bull; <strong><?= esc($tpl['ttd_3_nama']) ?></strong> (<?= str_replace("\n", " ", esc($tpl['ttd_3_jabatan'])) ?>)
                            <?= !empty($tpl['ttd_3_image']) ? '<i class="bi bi-patch-check-fill text-danger ms-1" title="TTD & Stempel Image Aktif"></i>' : '' ?>
                        </div>
                    </div>
                </div>

                <!-- Footer Card Action -->
                <div class="p-3 border-top bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <?php if (!$tpl['is_default']): ?>
                            <a href="<?= base_url('piagam/set-default/' . $tpl['id']) ?>" class="btn btn-sm btn-outline-primary" title="Jadikan template bawaan cetak">
                                <i class="bi bi-star"></i> Set Default
                            </a>
                        <?php else: ?>
                            <span class="text-success small fw-semibold"><i class="bi bi-check2-circle me-1"></i> Default Utama</span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-1">
                        <!-- Toggle Sembunyikan / Aktifkan -->
                        <a href="<?= base_url('piagam/toggle-active/' . $tpl['id']) ?>" class="btn btn-sm <?= $isActive ? 'btn-outline-secondary' : 'btn-outline-success' ?>" title="<?= $isActive ? 'Sembunyikan template ini dari daftar pilihan' : 'Aktifkan template ini' ?>">
                            <i class="bi <?= $isActive ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                        </a>

                        <!-- Edit Button -->
                        <button type="button" class="btn btn-sm btn-warning fw-semibold shadow-sm" onclick='editTemplate(<?= json_encode($tpl) ?>)' title="Ubah Nama, TTD & Desain">
                            <i class="bi bi-pencil-square me-1"></i> Edit TTD & Desain
                        </button>

                        <!-- Cetak & Isi Data -->
                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="openQuickPrintModal(<?= (int)$tpl['id'] ?>, '<?= esc(addslashes($tpl['nama'])) ?>')" title="Cetak dengan Nama / Data Peserta">
                            <i class="bi bi-printer me-1"></i> Cetak & Isi Data
                        </button>

                        <!-- Preview Cetak Blank -->
                        <a href="<?= base_url('piagam/cetak-custom?template_id=' . $tpl['id']) ?>" target="_blank" class="btn btn-sm btn-secondary-custom" title="Preview Tampilan Cetak">
                            <i class="bi bi-eye"></i>
                        </a>

                        <?php if ($tpl['id'] > 3): // Hanya template buatan user yang bisa dihapus ?>
                            <a href="<?= base_url('piagam/delete-template/' . $tpl['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus template ini? File background akan dihapus.')" title="Hapus Template">
                                <i class="bi bi-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
#modalUploadTemplate .modal-dialog-scrollable {
    max-height: calc(100vh - 2rem);
}
#modalUploadTemplate .modal-dialog-scrollable .modal-content {
    max-height: calc(100vh - 3.5rem);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
#modalUploadTemplate .modal-header {
    flex-shrink: 0;
}
#modalUploadTemplate .modal-body {
    flex: 1 1 auto;
    max-height: calc(100vh - 13rem);
    overflow-y: auto !important;
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
}
#modalUploadTemplate .modal-body::-webkit-scrollbar {
    width: 8px;
}
#modalUploadTemplate .modal-body::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
#modalUploadTemplate .modal-body::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 4px;
}
#modalUploadTemplate .modal-body::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
#modalUploadTemplate .modal-footer {
    flex-shrink: 0;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    z-index: 10;
}
</style>

<!-- =============================================================== -->
<!-- MODAL UPLOAD / EDIT TEMPLATE & PENGATURAN TTD                   -->
<!-- =============================================================== -->
<div class="modal fade" id="modalUploadTemplate" tabindex="-1" aria-labelledby="modalUploadLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content border-0 shadow-lg" action="<?= base_url('piagam/save-template') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="tpl_id" value="">

            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fs-6 fw-bold d-flex align-items-center gap-2" id="modalUploadLabel">
                    <i class="bi bi-pencil-square text-warning"></i> Pengaturan Template & Pejabat Penandatangan Piagam
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                    <!-- Tombol Cepat Isi Otomatis Default -->
                    <div class="alert alert-warning border-warning d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 p-3 rounded">
                        <div>
                            <strong class="text-dark"><i class="bi bi-magic me-1"></i> Fleksibilitas Pengisian Data:</strong>
                            <div class="small text-secondary">
                                Anda bisa mengunggah file tanda tangan sendiri (PNG/JPG transparan), mengetik nama sendiri, atau klik tombol di sebelah kanan untuk mengisi otomatis pejabat resmi bawaan.
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-warning text-dark fw-bold text-nowrap shadow-sm" onclick="isiDefaultEksKaresidenan()">
                            <i class="bi bi-arrow-repeat me-1"></i> Gunakan Nilai Default Otomatis
                        </button>
                    </div>

                    <div class="row g-3">
                        <!-- Nama Template -->
                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-dark">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Misal: Template Portrait Eks-Karesidenan (Format Resmi)" required>
                        </div>

                        <!-- Orientasi -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-dark">Orientasi Kertas <span class="text-danger">*</span></label>
                            <select name="orientasi" class="form-select">
                                <option value="portrait" selected>Portrait (A4 Tegak - Format Resmi)</option>
                                <option value="landscape">Landscape (A4 Tidur)</option>
                            </select>
                        </div>

                        <!-- Tipe Tampilan -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-dark">Tipe Tampilan</label>
                            <select name="tipe" id="tpl_tipe" class="form-select">
                                <option value="overlay" selected>Overlay Background</option>
                                <option value="portrait_ekskari">Portrait Eks-Kari</option>
                                <option value="full_css">Full Vector</option>
                            </select>
                        </div>

                        <!-- UPLOAD FILE BACKGROUND / GRID PIAGAM KOSONGAN -->
                        <div class="col-12">
                            <div class="p-3 border rounded-3" style="background: #F0FDF4; border: 1.5px solid #22C55E !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold text-success mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-image-fill fs-5"></i> File Background / Grid Piagam (Kosongan)
                                    </label>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                        Bisa Portrait / Landscape (JPG, PNG, PDF Canva)
                                    </span>
                                </div>
                                <p class="text-secondary small mb-3" style="font-size: 12px;">
                                    Unggah file gambar atau <strong>PDF (misal unduhan dari Canva)</strong>. 
                                    Jika berupa PDF, sistem akan <em>otomatis mengonversi halaman sertifikat ke format gambar beresolusi tinggi 300 DPI</em>. Semua teks data juara dan tanda tangan akan dicetak rapi di atasnya.
                                </p>

                                <div class="row g-3 align-items-center">
                                    <div class="col-md-7">
                                        <input type="file" name="file_background" id="file_background" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        <div id="remove_bg_container" class="form-check mt-2" style="display: none;">
                                            <input class="form-check-input" type="checkbox" name="remove_file_background" value="1" id="remove_file_background">
                                            <label class="form-check-label small text-danger fw-semibold" for="remove_file_background">
                                                <i class="bi bi-trash"></i> Hapus Background Saat Ini (Kembali ke Polos/Vector)
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Preview Thumbnail Background -->
                                    <div class="col-md-5">
                                        <div id="preview_box_bg" class="p-2 border rounded text-center bg-white" style="display: none; min-height: 70px;">
                                            <small class="text-muted d-block mb-1" style="font-size: 11px;">Preview Background:</small>
                                            <img src="" id="img_preview_bg" alt="Preview Background" style="max-height: 85px; max-width: 100%; object-fit: contain; border-radius: 4px;" class="border shadow-xs">
                                        </div>
                                    </div>
                                </div>

                                <!-- Penyesuaian Grid & Tata Letak Konten di atas Background -->
                                <div class="row g-2 mt-2 pt-2 border-top">
                                    <div class="col-md-3">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="hide_header" value="1" id="hide_header">
                                            <label class="form-check-label fw-bold text-dark small" for="hide_header">
                                                Sembunyikan Kop
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="hide_title" value="1" id="hide_title">
                                            <label class="form-check-label fw-bold text-dark small" for="hide_title">
                                                Sembunyikan Judul
                                            </label>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 10px;">Aktifkan jika background Canva sudah ada tulisan Judul / Kop.</small>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-dark mb-1">Offset Teks Atas (mm):</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="offset_top" id="offset_top" class="form-control" value="0" min="-50" max="150" step="1">
                                            <span class="input-group-text">mm</span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 10px;">Geser teks (nama, juara) naik (-) atau turun (+).</small>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-dark mb-1">Offset Letak TTD (mm):</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="offset_ttd" id="offset_ttd" class="form-control" value="0" min="-50" max="150" step="1">
                                            <span class="input-group-text">mm</span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 10px;">Geser baris TTD & stempel naik (+) atau turun (-).</small>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-dark mb-1">Ukuran Font Nama:</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="font_size_nama" id="font_size_nama" class="form-control" value="26" min="16" max="48" step="1">
                                            <span class="input-group-text">px</span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 10px;">Ukuran nama agar proporsional di kotak background.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Header & Judul -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Kop Header Atas</label>
                            <textarea name="instansi_header" class="form-control" rows="2">KEJUARAAN ATLETIK&#10;TINGKAT EKS KARESIDENAN</textarea>
                            <small class="text-muted" style="font-size: 11px;">Teks judul kejuaraan di sudut kiri atas piagam.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Format Nomor Piagam</label>
                            <input type="text" name="format_nomor" class="form-control font-monospace" value="{nomor}/SERT/PASIJATENG/IX/{tahun}">
                            <small class="text-muted" style="font-size: 11px;">Gunakan <code>{nomor}</code> untuk nomor urut dan <code>{tahun}</code> untuk tahun.</small>
                        </div>

                        <!-- Kalimat Pembuka & Kalimat Pernyataan -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-dark">Kalimat Pembuka Piagam</label>
                            <input type="text" name="kalimat_pembuka" class="form-control" value="Pengurus Provinsi Persatuan Atletik Seluruh Indonesia (PASI) Jawa Tengah dengan ini memberikan Piagam Penghargaan kepada :">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-dark">Format Kalimat Lokasi & Tanggal (Bawah)</label>
                            <textarea name="kalimat_penghargaan" class="form-control" rows="2">dalam Kejuaraan Atletik Tingkat Eks Karesidenan Pekalongan yang diselenggarakan pada tanggal {tanggal} di {tempat}.</textarea>
                            <small class="text-muted" style="font-size: 11px;">Variabel dinamis: <code>{tanggal}</code> dan <code>{tempat}</code> akan diisi otomatis oleh sistem.</small>
                        </div>

                        <!-- SECTION TANDA TANGAN (FLEKSIBEL: UPLOAD / KETIK / OTOMATIS) -->
                        <div class="col-12 mt-3">
                            <div class="p-3 border rounded" style="background: #FAFCFF; border-color: #0052CC !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-primary mb-0">
                                        <i class="bi bi-pen-fill me-1"></i> Data & File Tanda Tangan (3 Pejabat Penandatangan)
                                    </h6>
                                    <span class="badge bg-primary-subtle text-primary border small">Fleksibel: Upload Sendiri / Otomatis</span>
                                </div>
                                <p class="text-muted small mb-3">
                                    Unggah file gambar scan tanda tangan (format PNG transparan disarankan), atau ketik manual nama & jabatan. Jika tidak ingin menampilkan gambar tanda tangan, centang kosongkan.
                                </p>

                                <div class="row g-3">
                                    <!-- Penandatangan 1 (Kiri Atas: Koordinator Wilayah) -->
                                    <div class="col-md-4">
                                        <div class="card p-3 h-100 border bg-white shadow-sm">
                                            <div class="fw-bold small text-dark mb-2 pb-1 border-bottom d-flex align-items-center justify-content-between">
                                                <span><i class="bi bi-person-badge text-primary me-1"></i> TTD 1 (Kiri Atas)</span>
                                                <span class="badge bg-light text-dark">Korwil</span>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Jabatan:</label>
                                                <input type="text" name="ttd_1_jabatan" class="form-control form-control-sm" value="Koordinator Wilayah" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Nama Lengkap & Gelar:</label>
                                                <input type="text" name="ttd_1_nama" class="form-control form-control-sm fw-bold" value="Jumanto, S.Pd., M.Si." required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Upload File TTD (PNG/JPG):</label>
                                                <input type="file" name="ttd_1_image" class="form-control form-control-sm" accept=".png,.jpg,.jpeg,.webp">
                                            </div>
                                            <!-- Preview Saat ini -->
                                            <div class="p-2 border rounded text-center bg-light mb-2" id="preview_box_ttd_1" style="min-height: 60px;">
                                                <small class="text-muted d-block" style="font-size: 10px;">Preview TTD 1:</small>
                                                <img src="<?= base_url('public/uploads/piagam/ttd/ttd_jumanto_clean.png') ?>" id="img_preview_ttd_1" alt="Preview TTD 1" style="max-height: 50px; max-width: 140px; object-fit: contain;">
                                            </div>
                                            <div class="d-flex flex-column gap-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="use_default_ttd_1_image" value="1" id="use_default_ttd_1_image">
                                                    <label class="form-check-label small text-secondary" for="use_default_ttd_1_image" style="font-size: 11px;">
                                                        Gunakan TTD Bawaan (Jumanto)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remove_ttd_1_image" value="1" id="remove_ttd_1_image">
                                                    <label class="form-check-label small text-danger" for="remove_ttd_1_image" style="font-size: 11px;">
                                                        Kosongkan Gambar TTD
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Penandatangan 2 (Kanan Atas: Ketua Panitia) -->
                                    <div class="col-md-4">
                                        <div class="card p-3 h-100 border bg-white shadow-sm">
                                            <div class="fw-bold small text-dark mb-2 pb-1 border-bottom d-flex align-items-center justify-content-between">
                                                <span><i class="bi bi-person-badge text-primary me-1"></i> TTD 2 (Kanan Atas)</span>
                                                <span class="badge bg-light text-dark">Panitia</span>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Jabatan:</label>
                                                <input type="text" name="ttd_2_jabatan" class="form-control form-control-sm" value="Ketua Panitia Pelaksana" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Nama Lengkap & Gelar:</label>
                                                <input type="text" name="ttd_2_nama" class="form-control form-control-sm fw-bold" value="Zaini, S.Pd." required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Upload File TTD (PNG/JPG):</label>
                                                <input type="file" name="ttd_2_image" class="form-control form-control-sm" accept=".png,.jpg,.jpeg,.webp">
                                            </div>
                                            <!-- Preview Saat ini -->
                                            <div class="p-2 border rounded text-center bg-light mb-2" id="preview_box_ttd_2" style="min-height: 60px;">
                                                <small class="text-muted d-block" style="font-size: 10px;">Preview TTD 2:</small>
                                                <img src="<?= base_url('uploads/piagam/ttd/ttd_zaini_pasi_pekalongan.png') ?>" id="img_preview_ttd_2" alt="Preview TTD 2" style="max-height: 50px; max-width: 140px; object-fit: contain;">
                                            </div>
                                            <div class="d-flex flex-column gap-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="use_default_ttd_2_image" value="1" id="use_default_ttd_2_image">
                                                    <label class="form-check-label small text-secondary" for="use_default_ttd_2_image" style="font-size: 11px;">
                                                        Gunakan TTD Bawaan (Zaini)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remove_ttd_2_image" value="1" id="remove_ttd_2_image">
                                                    <label class="form-check-label small text-danger" for="remove_ttd_2_image" style="font-size: 11px;">
                                                        Kosongkan Gambar TTD
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Penandatangan 3 (Bawah Tengah: Mengetahui, Ketua Umum PASI) -->
                                    <div class="col-md-4">
                                        <div class="card p-3 h-100 border bg-white shadow-sm">
                                            <div class="fw-bold small text-dark mb-2 pb-1 border-bottom d-flex align-items-center justify-content-between">
                                                <span><i class="bi bi-person-badge text-danger me-1"></i> TTD 3 (Tengah Bawah)</span>
                                                <span class="badge bg-danger-subtle text-danger">PASI Jateng</span>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Jabatan:</label>
                                                <textarea name="ttd_3_jabatan" class="form-control form-control-sm" rows="2" required>Mengetahui,&#10;Ketua Umum PASI Jateng</textarea>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Nama Lengkap & Gelar:</label>
                                                <input type="text" name="ttd_3_nama" class="form-control form-control-sm fw-bold" value="Dr. Rumini, M.Pd." required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Upload File TTD & Stempel:</label>
                                                <input type="file" name="ttd_3_image" class="form-control form-control-sm" accept=".png,.jpg,.jpeg,.webp">
                                            </div>
                                            <!-- Preview Saat ini -->
                                            <div class="p-2 border rounded text-center bg-light mb-2" id="preview_box_ttd_3" style="min-height: 60px;">
                                                <small class="text-muted d-block" style="font-size: 10px;">Preview TTD 3 & Stempel:</small>
                                                <img src="<?= base_url('public/uploads/piagam/ttd/ttd_rumini_clean.png') ?>" id="img_preview_ttd_3" alt="Preview TTD 3" style="max-height: 50px; max-width: 140px; object-fit: contain;">
                                            </div>
                                            <div class="d-flex flex-column gap-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="use_default_ttd_3_image" value="1" id="use_default_ttd_3_image">
                                                    <label class="form-check-label small text-secondary" for="use_default_ttd_3_image" style="font-size: 11px;">
                                                        Gunakan TTD Bawaan (Rumini & Stempel)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remove_ttd_3_image" value="1" id="remove_ttd_3_image">
                                                    <label class="form-check-label small text-danger" for="remove_ttd_3_image" style="font-size: 11px;">
                                                        Kosongkan Gambar TTD
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opsi Tambahan Status & Default -->
                        <div class="col-md-6 mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="switchActive" checked>
                                <label class="form-check-label fw-bold text-dark small" for="switchActive">
                                    Aktifkan & Tampilkan Template Ini di Sistem
                                </label>
                            </div>
                            <small class="text-muted d-block" style="font-size: 11px;">Jika tidak dicentang, template akan disembunyikan dari pilihan cetak.</small>
                        </div>

                        <div class="col-md-6 mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="switchDefault">
                                <label class="form-check-label fw-bold text-dark small" for="switchDefault">
                                    Jadikan Template Default Utama
                                </label>
                            </div>
                            <small class="text-muted d-block" style="font-size: 11px;">Template default otomatis dipilih saat tombol Cetak Piagam ditekan.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-3 border-top">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-save-fill"></i> Simpan Pengaturan & TTD
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cetak & Isi Data Peserta Langsung -->
<div class="modal fade" id="modalQuickPrint" tabindex="-1" aria-labelledby="modalQuickPrintLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%) !important;">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">🖨️</span>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalQuickPrintLabel">
                            Cetak Piagam dengan Data Peserta
                        </h5>
                        <small class="text-white-50" id="modalQuickPrintSub">Pilih template dan isi nama pemenang.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= base_url('piagam/cetak-custom') ?>" method="get" target="_blank" id="formQuickPrint">
                <input type="hidden" name="template_id" id="qp_template_id" value="">

                <div class="modal-body p-4 bg-light">
                    <!-- Opsi Mode -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <label class="form-label fw-bold small text-dark mb-2">Pilih Cara Mengisi Data Piagam:</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="qp_mode" id="qp_mode_manual" value="manual" checked onchange="toggleQpMode('manual')">
                                    <label class="form-check-label fw-semibold text-dark small" for="qp_mode_manual">
                                        <i class="bi bi-pencil-square text-primary me-1"></i> Ketik Nama Atlet / Peserta Manual
                                    </label>
                                </div>
                                <?php if (!empty($events)): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="qp_mode" id="qp_mode_event" value="event" onchange="toggleQpMode('event')">
                                    <label class="form-check-label fw-semibold text-dark small" for="qp_mode_event">
                                        <i class="bi bi-trophy text-warning me-1"></i> Cetak Juara dari Nomor Acara Lomba
                                    </label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Mode 1: Manual Input Atlet -->
                    <div id="qp_panel_manual">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-dark">
                                    1. Nama Lengkap Atlet / Pemenang <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama_pemenang" id="qp_nama_pemenang" class="form-control fw-bold" placeholder="Contoh: DONNA ARTANTI MAHADANA" required>
                                <small class="text-muted" style="font-size: 11px;">*Nama yang akan dicetak pada lembar piagam.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">Sebagai Juara <span class="text-danger">*</span></label>
                                <select name="peringkat" id="qp_peringkat" class="form-select fw-bold">
                                    <option value="1">Juara 1 (Satu) - Emas</option>
                                    <option value="2">Juara 2 (Dua) - Perak</option>
                                    <option value="3">Juara 3 (Tiga) - Perunggu</option>
                                    <option value="4">Juara Harapan 1</option>
                                    <option value="5">Juara Harapan 2</option>
                                </select>
                            </div>

                            <input type="hidden" name="asal_sekolah" id="qp_asal_sekolah" value="">
                            <input type="hidden" name="asal_kota" id="qp_asal_kota" value="">

                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-dark">Nomor & Kategori Lomba</label>
                                <input type="text" name="nomor_lomba" id="qp_nomor_lomba" class="form-control" placeholder="Contoh: LARI 100 M KU 16-18 PUTRA">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">Catatan Prestasi</label>
                                <input type="text" name="prestasi" id="qp_prestasi" class="form-control font-monospace" placeholder="Contoh: 11.24 dtk">
                            </div>
                        </div>
                    </div>

                    <!-- Panel Mode 2: Dari Event Acara Lomba -->
                    <?php if (!empty($events)): ?>
                    <div id="qp_panel_event" style="display: none;">
                        <div class="alert alert-info py-2 px-3 small rounded-2 mb-3">
                            <i class="bi bi-info-circle me-1"></i> Seluruh juara 1, 2, dan 3 dari nomor acara yang dipilih akan dicetak otomatis menggunakan template ini.
                        </div>
                        <label class="form-label fw-bold small text-dark">Pilih Nomor Acara Lomba:</label>
                        <select id="qp_event_id" class="form-select fw-semibold">
                            <?php foreach ($events as $ev): ?>
                                <option value="<?= $ev['id'] ?>">
                                    #<?= esc($ev['nomor_acara']) ?> - <?= esc($ev['nama_acara']) ?> (<?= ($ev['gender'] ?? '') === 'L' ? 'Putra' : 'Putri' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer bg-white border-top py-2 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold shadow-sm" id="btnSubmitQuickPrint">
                        <i class="bi bi-printer-fill me-1"></i> Buka Tampilan Cetak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateTtdPreview(fieldPrefix, imgFile) {
    const imgEl = document.getElementById('img_preview_' + fieldPrefix);
    const boxEl = document.getElementById('preview_box_' + fieldPrefix);
    if (!imgEl || !boxEl) return;

    if (imgFile) {
        imgEl.src = '<?= base_url('uploads/piagam/ttd/') ?>' + imgFile;
        imgEl.style.display = 'inline-block';
        imgEl.onerror = function() {
            this.src = '<?= base_url('assets/images/') ?>' + imgFile;
        };
    } else {
        imgEl.style.display = 'none';
    }
}

function editTemplate(tpl) {
    document.getElementById('modalUploadLabel').innerHTML = '<i class="bi bi-pencil-square text-warning"></i> Ubah Pengaturan Template & Tanda Tangan';
    document.getElementById('tpl_id').value = tpl.id;
    document.querySelector('input[name="nama"]').value = tpl.nama || '';
    document.querySelector('select[name="orientasi"]').value = tpl.orientasi || 'portrait';
    document.querySelector('select[name="tipe"]').value = tpl.tipe || 'overlay';
    document.querySelector('input[name="format_nomor"]').value = tpl.format_nomor || '{nomor}/SERT/PASIJATENG/IX/{tahun}';
    document.querySelector('textarea[name="instansi_header"]').value = tpl.instansi_header || 'KEJUARAAN ATLETIK\nTINGKAT EKS KARESIDENAN';
    document.querySelector('input[name="kalimat_pembuka"]').value = tpl.kalimat_pembuka || 'Pengurus Provinsi Persatuan Atletik Seluruh Indonesia (PASI) Jawa Tengah dengan ini memberikan Piagam Penghargaan kepada :';
    document.querySelector('textarea[name="kalimat_penghargaan"]').value = tpl.kalimat_penghargaan || 'dalam Kejuaraan Atletik Tingkat Eks Karesidenan Pekalongan yang diselenggarakan pada tanggal {tanggal} di {tempat}.';
    
    // Background handling
    const bgPreviewBox = document.getElementById('preview_box_bg');
    const bgPreviewImg = document.getElementById('img_preview_bg');
    const removeBgContainer = document.getElementById('remove_bg_container');
    const fileBgInput = document.getElementById('file_background');
    if (fileBgInput) fileBgInput.value = '';

    if (tpl.file_background) {
        bgPreviewImg.src = '<?= base_url('uploads/piagam/templates/') ?>' + tpl.file_background;
        bgPreviewBox.style.display = 'block';
        removeBgContainer.style.display = 'block';
    } else {
        bgPreviewBox.style.display = 'none';
        removeBgContainer.style.display = 'none';
    }
    document.getElementById('remove_file_background').checked = false;
    document.getElementById('hide_header').checked = (tpl.hide_header == 1);
    if (document.getElementById('hide_title')) document.getElementById('hide_title').checked = (tpl.hide_title == 1);
    document.getElementById('offset_top').value = tpl.offset_top || 0;
    if (document.getElementById('offset_ttd')) document.getElementById('offset_ttd').value = tpl.offset_ttd || 0;
    document.getElementById('font_size_nama').value = tpl.font_size_nama || 26;

    // TTD 1
    document.querySelector('input[name="ttd_1_jabatan"]').value = tpl.ttd_1_jabatan || 'Koordinator Wilayah';
    document.querySelector('input[name="ttd_1_nama"]').value = tpl.ttd_1_nama || 'Jumanto, S.Pd., M.Si.';
    updateTtdPreview('ttd_1', tpl.ttd_1_image || 'ttd_jumanto_pasi_pekalongan.png');
    document.getElementById('use_default_ttd_1_image').checked = false;
    document.getElementById('remove_ttd_1_image').checked = false;

    // TTD 2
    document.querySelector('input[name="ttd_2_jabatan"]').value = tpl.ttd_2_jabatan || 'Ketua Panitia Pelaksana';
    document.querySelector('input[name="ttd_2_nama"]').value = tpl.ttd_2_nama || 'Zaini, S.Pd.';
    updateTtdPreview('ttd_2', tpl.ttd_2_image || 'ttd_zaini_pasi_pekalongan.png');
    document.getElementById('use_default_ttd_2_image').checked = false;
    document.getElementById('remove_ttd_2_image').checked = false;

    // TTD 3
    document.querySelector('textarea[name="ttd_3_jabatan"]').value = tpl.ttd_3_jabatan || "Mengetahui,\nPLT PASI Jateng";
    document.querySelector('input[name="ttd_3_nama"]').value = tpl.ttd_3_nama || 'Dr. Agus Widodo Suripto, MPd';
    updateTtdPreview('ttd_3', tpl.ttd_3_image || 'ttd_agus_widodo_pasi_jateng.png');
    document.getElementById('use_default_ttd_3_image').checked = false;
    document.getElementById('remove_ttd_3_image').checked = false;

    document.getElementById('switchActive').checked = (tpl.is_active == 1);
    document.getElementById('switchDefault').checked = (tpl.is_default == 1);

    var modal = new bootstrap.Modal(document.getElementById('modalUploadTemplate'));
    modal.show();
}

function isiDefaultEksKaresidenan() {
    document.querySelector('input[name="nama"]').value = 'Template Piagam Baru (Custom Background)';
    document.querySelector('select[name="orientasi"]').value = 'portrait';
    document.querySelector('select[name="tipe"]').value = 'overlay';
    document.querySelector('input[name="format_nomor"]').value = '{nomor}/SERT/PASIJATENG/IX/{tahun}';
    document.querySelector('textarea[name="instansi_header"]').value = 'KEJUARAAN ATLETIK\nTINGKAT EKS KARESIDENAN';
    document.querySelector('input[name="kalimat_pembuka"]').value = 'Pengurus Provinsi Persatuan Atletik Seluruh Indonesia (PASI) Jawa Tengah dengan ini memberikan Piagam Penghargaan kepada :';
    document.querySelector('textarea[name="kalimat_penghargaan"]').value = 'dalam Kejuaraan Atletik Tingkat Eks Karesidenan Pekalongan yang diselenggarakan pada tanggal {tanggal} di {tempat}.';

    // Reset background preview
    document.getElementById('preview_box_bg').style.display = 'none';
    document.getElementById('remove_bg_container').style.display = 'none';
    document.getElementById('remove_file_background').checked = false;
    document.getElementById('hide_header').checked = false;
    document.getElementById('offset_top').value = 0;
    document.getElementById('font_size_nama').value = 26;
    const fileBgInput = document.getElementById('file_background');
    if (fileBgInput) fileBgInput.value = '';

    // TTD 1
    document.querySelector('input[name="ttd_1_jabatan"]').value = 'Koordinator Wilayah';
    document.querySelector('input[name="ttd_1_nama"]').value = 'Jumanto, S.Pd., M.Si.';
    document.getElementById('use_default_ttd_1_image').checked = true;
    document.getElementById('remove_ttd_1_image').checked = false;
    updateTtdPreview('ttd_1', 'ttd_jumanto_pasi_pekalongan.png');

    // TTD 2
    document.querySelector('input[name="ttd_2_jabatan"]').value = 'Ketua Panitia Pelaksana';
    document.querySelector('input[name="ttd_2_nama"]').value = 'Zaini, S.Pd.';
    document.getElementById('use_default_ttd_2_image').checked = true;
    document.getElementById('remove_ttd_2_image').checked = false;
    updateTtdPreview('ttd_2', 'ttd_zaini_pasi_pekalongan.png');

    // TTD 3
    document.querySelector('textarea[name="ttd_3_jabatan"]').value = "Mengetahui,\nPLT PASI Jateng";
    document.querySelector('input[name="ttd_3_nama"]').value = 'Dr. Agus Widodo Suripto, MPd';
    document.getElementById('use_default_ttd_3_image').checked = true;
    document.getElementById('remove_ttd_3_image').checked = false;
    updateTtdPreview('ttd_3', 'ttd_agus_widodo_pasi_jateng.png');

    document.getElementById('switchActive').checked = true;
    document.getElementById('switchDefault').checked = false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Listener untuk file background preview realtime
    const fileBgInput = document.getElementById('file_background');
    if (fileBgInput) {
        fileBgInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const previewBox = document.getElementById('preview_box_bg');
                const imgPreview = document.getElementById('img_preview_bg');
                if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
                    previewBox.style.display = 'block';
                    imgPreview.style.display = 'none';
                    let pdfNote = document.getElementById('pdf_preview_note');
                    if (!pdfNote) {
                        pdfNote = document.createElement('div');
                        pdfNote.id = 'pdf_preview_note';
                        pdfNote.className = 'py-2 px-1 text-danger';
                        previewBox.appendChild(pdfNote);
                    }
                    pdfNote.innerHTML = '<i class="bi bi-file-earmark-pdf fs-3 d-block text-danger"></i><strong class="d-block small text-dark">' + file.name + '</strong><span class="badge bg-danger-subtle text-danger" style="font-size:10px;">PDF Auto-Convert ke 300 DPI</span>';
                    pdfNote.style.display = 'block';
                } else {
                    const pdfNote = document.getElementById('pdf_preview_note');
                    if (pdfNote) pdfNote.style.display = 'none';
                    imgPreview.style.display = 'inline-block';
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        imgPreview.src = evt.target.result;
                        previewBox.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
                // Otomatis pilih tipe overlay
                const tipeSelect = document.getElementById('tpl_tipe');
                if (tipeSelect) tipeSelect.value = 'overlay';
            }
        });
    }

    const uploadBtn = document.querySelector('[data-bs-target="#modalUploadTemplate"]');
    if (uploadBtn) {
        uploadBtn.addEventListener('click', function() {
            document.getElementById('modalUploadLabel').innerHTML = '<i class="bi bi-cloud-arrow-up-fill text-warning"></i> Unggah / Tambah Template Piagam Baru';
            document.getElementById('tpl_id').value = '';
            isiDefaultEksKaresidenan();
        });
    }

    const formQp = document.getElementById('formQuickPrint');
    if (formQp) {
        formQp.addEventListener('submit', function(e) {
            const isEvent = document.getElementById('qp_mode_event')?.checked;
            if (isEvent) {
                e.preventDefault();
                const evId = document.getElementById('qp_event_id')?.value;
                const tplId = document.getElementById('qp_template_id')?.value;
                if (evId) {
                    window.open('<?= base_url('piagam/cetak-event') ?>/' + evId + '?template_id=' + tplId, '_blank');
                    const modalEl = document.getElementById('modalQuickPrint');
                    const m = bootstrap.Modal.getInstance(modalEl);
                    if (m) m.hide();
                }
            }
        });
    }
});

function openQuickPrintModal(tplId, tplNama) {
    document.getElementById('qp_template_id').value = tplId;
    document.getElementById('modalQuickPrintSub').innerText = 'Template: ' + tplNama;
    toggleQpMode('manual');
    const modalEl = document.getElementById('modalQuickPrint');
    const m = new bootstrap.Modal(modalEl);
    m.show();
    setTimeout(() => {
        const inp = document.getElementById('qp_nama_pemenang');
        if (inp) inp.focus();
    }, 400);
}

function toggleQpMode(mode) {
    const pManual = document.getElementById('qp_panel_manual');
    const pEvent = document.getElementById('qp_panel_event');
    const inpNama = document.getElementById('qp_nama_pemenang');

    if (mode === 'event') {
        if (pManual) pManual.style.display = 'none';
        if (pEvent) pEvent.style.display = 'block';
        if (inpNama) inpNama.removeAttribute('required');
    } else {
        if (pManual) pManual.style.display = 'block';
        if (pEvent) pEvent.style.display = 'none';
        if (inpNama) inpNama.setAttribute('required', 'required');
    }
}
</script>

<?= $this->endSection() ?>
