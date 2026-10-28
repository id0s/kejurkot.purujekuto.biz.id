<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="card-custom mb-4 p-4" style="background: linear-gradient(135deg, rgba(0, 82, 204, 0.08) 0%, rgba(255, 171, 0, 0.14) 100%); border: 1px solid #DFE1E6; border-radius: 6px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 52px; height: 52px; border-radius: 8px; background: linear-gradient(135deg, #0052CC, #0747A6); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.6rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,82,204,0.25);">
                <i class="bi bi-building-check text-warning"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span class="badge badge-amber px-2 py-1">
                        <i class="bi bi-link-45deg me-1"></i> E-Piagam Online Resmi
                    </span>
                    <span class="text-muted small">&bull; <?= esc($activeKejuaraan['nama'] ?? 'Kejurwil Atletik 2026') ?></span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #172B4D; font-size: 21px; letter-spacing: -0.3px;">
                    Link E-Piagam Juara per Sekolah / Kontingen
                </h3>
                <p class="text-muted mb-0 small mt-1">
                    Setiap sekolah memiliki link mandiri yang dapat dibuka langsung tanpa login oleh pembina, guru, atau wali atlet untuk melihat dan mencetak e-piagam resmi ber-QR Code verifikasi.
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= base_url('piagam/export-sekolah-excel') ?>" class="btn btn-success fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-white">
                <i class="bi bi-file-earmark-excel-fill fs-6"></i>
                <span>Unduh Rekap Excel (.xlsx)</span>
            </a>
            <a href="<?= base_url('piagam') ?>" class="btn btn-secondary-custom d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Piagam</span>
            </a>
        </div>
    </div>
</div>

<!-- Statistik Singkat -->
<?php
$totalSchools = count($sekolahList);
$totalPiagamAll = 0;
$totalGold = 0;
$totalSilver = 0;
$totalBronze = 0;
foreach ($sekolahList as $s) {
    $totalPiagamAll += (int)$s['total_juara'];
    $totalGold += (int)$s['emas'];
    $totalSilver += (int)$s['perak'];
    $totalBronze += (int)$s['perunggu'];
}
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 bg-white border d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 44px; height: 44px; font-size: 1.3rem;">
                <i class="bi bi-buildings"></i>
            </div>
            <div>
                <div class="fs-4 fw-bold text-dark"><?= $totalSchools ?></div>
                <div class="text-muted small">Sekolah Peraih Medali</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 bg-white border d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-25 text-warning" style="width: 44px; height: 44px; font-size: 1.3rem;">
                <i class="bi bi-award-fill"></i>
            </div>
            <div>
                <div class="fs-4 fw-bold text-dark"><?= $totalPiagamAll ?></div>
                <div class="text-muted small">Total Piagam Final</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 bg-white border d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 44px; height: 44px; font-size: 1.3rem;">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div>
                <div class="fs-4 fw-bold text-dark"><?= $totalGold ?> Emas &bull; <?= $totalSilver ?> Perak</div>
                <div class="text-muted small">Peringkat 1 & 2</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-custom p-3 bg-white border d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width: 44px; height: 44px; font-size: 1.3rem;">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div class="fs-4 fw-bold text-dark"><?= $totalBronze ?> Perunggu</div>
                <div class="text-muted small">Peringkat 3 Final</div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card-custom mb-4 p-3 bg-white border">
    <div class="row align-items-center g-3">
        <div class="col-md-7 col-lg-8">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="searchSekolahInput" class="form-control border-start-0" placeholder="Ketik nama sekolah, kontingen, atau kota/kabupaten..." onkeyup="filterSekolahCards()">
            </div>
        </div>
        <div class="col-md-5 col-lg-4 text-md-end">
            <select id="filterDaerahSelect" class="form-select form-select-sm fw-semibold" onchange="filterSekolahCards()">
                <option value="">Semua Daerah / Kabupaten / Kota</option>
                <?php 
                $uniqueDaerah = array_unique(array_filter(array_column($sekolahList, 'asal_daerah')));
                sort($uniqueDaerah);
                foreach ($uniqueDaerah as $d): 
                ?>
                    <option value="<?= esc($d) ?>"><?= esc($d) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- Grid Kartu Sekolah -->
<div class="row g-3" id="sekolahGrid">
    <?php foreach ($sekolahList as $s): 
        $schId   = (int)$s['sekolah_id'];
        $schName = $s['nama_sekolah'];
        $schDaerah = $s['asal_daerah'] ?: 'Tidak Diketahui';
        $schJenjang = $s['jenjang'] ?: 'Umum';
        $publicUrl = $s['public_url'];
        $waMessage = "Yth. Pembina / Pelatih " . $schName . ",\n\nBerikut tautan E-Piagam Penghargaan resmi dari Pengprov PASI Jawa Tengah untuk para atlet Anda yang meraih juara:\n" . $publicUrl . "\n\nPiagam ini diterbitkan secara elektronik dan dapat langsung diunduh / dicetak (terverifikasi melalui kode QR resmi). Terima kasih atas partisipasinya!";
        $waLink = "https://api.whatsapp.com/send?text=" . rawurlencode($waMessage);
    ?>
    <div class="col-md-6 col-xl-4 sekolah-card-item" data-nama="<?= strtolower(esc($schName)) ?>" data-daerah="<?= esc($schDaerah) ?>">
        <div class="card h-100 shadow-sm border" style="border-radius: 8px; transition: transform 0.2s, box-shadow 0.2s;">
            <!-- Header Card -->
            <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="badge bg-secondary font-monospace" style="font-size: 11px;">
                    <?= esc($schJenjang) ?>
                </span>
                <span class="badge bg-primary-subtle text-primary fw-semibold" style="font-size: 11px;">
                    <i class="bi bi-geo-alt me-1"></i><?= esc($schDaerah) ?>
                </span>
            </div>

            <!-- Body Card -->
            <div class="card-body p-3">
                <h5 class="fw-bold text-dark mb-2" style="font-size: 16px; line-height: 1.3;">
                    <a href="<?= base_url('piagam/sekolah/' . $schId) ?>" target="_blank" class="text-dark text-decoration-none" title="Buka E-Piagam <?= esc($schName) ?>">
                        <?= esc($schName) ?> <i class="bi bi-box-arrow-up-right text-primary ms-1" style="font-size: 12px;"></i>
                    </a>
                </h5>

                <!-- Medals Badge Row -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if ((int)$s['emas'] > 0): ?>
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1 shadow-xs" title="Juara 1 (Emas)">
                            🥇 <?= (int)$s['emas'] ?> Emas
                        </span>
                    <?php endif; ?>
                    <?php if ((int)$s['perak'] > 0): ?>
                        <span class="badge bg-secondary text-white fw-bold px-2 py-1 shadow-xs" title="Juara 2 (Perak)">
                            🥈 <?= (int)$s['perak'] ?> Perak
                        </span>
                    <?php endif; ?>
                    <?php if ((int)$s['perunggu'] > 0): ?>
                        <span class="badge text-white fw-bold px-2 py-1 shadow-xs" style="background-color: #CD7F32;" title="Juara 3 (Perunggu)">
                            🥉 <?= (int)$s['perunggu'] ?> Prg
                        </span>
                    <?php endif; ?>
                    <span class="badge bg-light text-muted border ms-auto small">
                        <?= (int)$s['total_juara'] ?> Piagam
                    </span>
                </div>

                <!-- Preview Daftar Juara -->
                <div class="bg-light p-2 rounded border small mb-3" style="max-height: 130px; overflow-y: auto;">
                    <div class="text-muted fw-bold mb-1" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px;">Daftar Atlet Pemenang:</div>
                    <?php foreach ($s['winners'] as $w): ?>
                        <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-light-subtle">
                            <span class="fw-semibold text-truncate me-2" style="max-width: 65%; color: #172B4D;" title="<?= esc($w['nama_atlet']) ?>">
                                &bull; <?= esc($w['nama_atlet']) ?>
                            </span>
                            <span class="badge <?= (int)$w['peringkat'] === 1 ? 'bg-warning text-dark' : ((int)$w['peringkat'] === 2 ? 'bg-secondary text-white' : 'text-white') ?>" style="<?= (int)$w['peringkat'] === 3 ? 'background-color:#CD7F32;' : '' ?> font-size: 10px;">
                                Juara <?= (int)$w['peringkat'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Public Link Box -->
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-link-45deg"></i></span>
                    <input type="text" class="form-control bg-light font-monospace" style="font-size: 11px;" value="<?= esc($publicUrl) ?>" id="linkInput-<?= $schId ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyLink('linkInput-<?= $schId ?>', this)" title="Salin tautan ke clipboard">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <!-- Card Footer Action Buttons: Print, Download PDF, Share WA -->
            <div class="card-footer bg-white border-top p-2 d-flex align-items-center gap-1.5 flex-wrap">
                <a href="<?= base_url('piagam/sekolah/' . $schId) ?>?print=1" target="_blank" class="btn btn-sm btn-outline-primary fw-bold flex-fill d-inline-flex align-items-center justify-content-center gap-1 shadow-xs" title="Cetak piagam ke printer">
                    <i class="bi bi-printer-fill"></i>
                    <span>Print</span>
                </a>
                <a href="<?= base_url('piagam/download-sekolah-pdf/' . $schId) ?>" class="btn btn-sm btn-warning text-dark fw-bold flex-fill d-inline-flex align-items-center justify-content-center gap-1 shadow-xs" title="Download langsung berkas PDF (Pure Download untuk HP & Laptop)">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                    <span>Download PDF</span>
                </a>
                <a href="<?= $waLink ?>" target="_blank" class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center justify-content-center gap-1 shadow-xs px-2.5" title="Bagikan Tautan Resmi ke WhatsApp Guru / Pelatih">
                    <i class="bi bi-whatsapp"></i>
                    <span>WA</span>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Toast Notifikasi Copy -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="copyToast" class="toast align-items-center text-bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <span>Tautan E-Piagam Sekolah berhasil disalin!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
function filterSekolahCards() {
    const searchVal = document.getElementById('searchSekolahInput').value.toLowerCase().trim();
    const daerahVal = document.getElementById('filterDaerahSelect').value.trim();
    const cards = document.querySelectorAll('.sekolah-card-item');

    cards.forEach(card => {
        const nama = card.getAttribute('data-nama') || '';
        const daerah = card.getAttribute('data-daerah') || '';

        const matchSearch = (nama.includes(searchVal) || daerah.toLowerCase().includes(searchVal));
        const matchDaerah = (daerahVal === '' || daerah === daerahVal);

        if (matchSearch && matchDaerah) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function copyLink(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-secondary');
        
        const toastEl = document.getElementById('copyToast');
        const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
        toast.show();

        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 1500);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}
</script>

<?= $this->endSection() ?>
