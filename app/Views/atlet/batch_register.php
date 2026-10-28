<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--text-main);">
            <i class="bi bi-people-fill text-primary me-2"></i>Pendaftaran Massal Atlet & Multi-Nomor Lomba
        </h4>
        <p class="text-muted small mb-0">
            Daftarkan banyak atlet sekaligus dalam satu kontingen/sekolah dengan multi-pilihan nomor lomba dan unggah akta kelahiran.
        </p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-success d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalPasteExcel">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            <span>Paste dari Excel</span>
        </button>
        <a href="<?= base_url('atlet') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Data Atlet</span>
        </a>
    </div>
</div>

<form action="<?= base_url('atlet/batch-register-save') ?>" method="post" enctype="multipart/form-data" id="batchRegisterForm">
    <?= csrf_field() ?>

    <!-- Header Card: Kontingen & Cabang Olahraga -->
    <div class="card-custom mb-4">
        <div class="card-header bg-transparent py-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary px-2 py-1">Langkah 1</span>
                <h6 class="mb-0 fw-bold" style="color: var(--text-main);">Pilih Kontingen / Sekolah & Cabang Olahraga</h6>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <!-- Sekolah / Kontingen -->
                <div class="col-md-7">
                    <label class="form-label fw-semibold" style="font-size: 13px;">
                        Sekolah / Kontingen Asal <span class="text-danger">*</span>
                    </label>
                    <select name="sekolah_id" id="sekolahSelect" class="form-select select2" required onchange="handleSekolahChange()">
                        <option value="">-- Pilih Sekolah / Kontingen yang Ada --</option>
                        <option value="new" class="fw-bold text-primary">+ Tambah Sekolah / Kontingen Baru</option>
                        <?php foreach ($sekolahs as $s): ?>
                            <option value="<?= $s['id'] ?>" data-jenjang="<?= esc($s['jenjang']) ?>">
                                <?= esc($s['nama']) ?> (<?= esc($s['jenjang']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Input Sekolah Baru (jika opsi baru dipilih) -->
                    <div id="newSekolahContainer" class="mt-3 p-3 rounded-2" style="display: none; background: rgba(0, 82, 204, 0.04); border: 1px dashed rgba(0, 82, 204, 0.3);">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Nama Sekolah / Klub Baru</label>
                                <input type="text" name="nama_sekolah_baru" id="namaSekolahBaru" class="form-control form-control-sm" placeholder="contoh: SDN 01 Pekalongan">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Jenjang</label>
                                <select name="jenjang_sekolah_baru" class="form-select form-select-sm">
                                    <option value="SD/MI">SD / MI</option>
                                    <option value="SMP/MTs">SMP / MTs</option>
                                    <option value="SMA/SMK/MA">SMA / SMK / MA</option>
                                    <option value="PAUD/TK">PAUD / TK</option>
                                    <option value="Umum/Club">Umum / Club</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cabang Olahraga (Cabor) -->
                <div class="col-md-5">
                    <label class="form-label fw-semibold" style="font-size: 13px;">
                        Cabang Olahraga (Cabor) <span class="text-danger">*</span>
                    </label>
                    <?php if ($sessionRole === 'admin_cabor'): ?>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white"><i class="bi bi-trophy-fill"></i></span>
                            <input type="text" class="form-control fw-bold" value="Cabor <?= esc(session()->get('cabor_nama')) ?>" readonly>
                            <input type="hidden" name="cabor_id" value="<?= esc($selectedCaborId) ?>">
                        </div>
                        <small class="text-muted">Akses terkunci sesuai role admin cabor Anda.</small>
                    <?php else: ?>
                        <select name="cabor_id" id="caborSelect" class="form-select" onchange="filterEventsByCabor()">
                            <?php foreach ($caborList as $cb): ?>
                                <option value="<?= $cb['id'] ?>" <?= $cb['id'] == $selectedCaborId ? 'selected' : '' ?>>
                                    <?= esc($cb['nama']) ?> (<?= esc($cb['kode']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Registration Card: Dynamic Athlete Rows -->
    <div class="card-custom mb-4">
        <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success px-2 py-1">Langkah 2</span>
                <h6 class="mb-0 fw-bold" style="color: var(--text-main);">Daftar Atlet & Pemilihan Nomor Lomba</h6>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 small">
                    <span class="text-muted">Total Atlet:</span>
                    <span class="badge bg-primary fs-6 px-2.5" id="totalAtletBadge">1</span>
                    <span class="text-muted ms-2">Total Keikutsertaan Lomba:</span>
                    <span class="badge bg-info text-dark fs-6 px-2.5" id="totalLombaBadge">0</span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="athleteTable" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">#</th>
                            <th style="min-width: 220px;">Nama Atlet <span class="text-danger">*</span></th>
                            <th width="110">Gender <span class="text-danger">*</span></th>
                            <th width="145">Tgl Lahir</th>
                            <th width="150">Kelompok Umur (KU)</th>
                            <th style="min-width: 260px;">Nomor Lomba yang Diikuti <span class="text-danger">*</span></th>
                            <th style="min-width: 190px;">Akta Kelahiran</th>
                            <th width="85">No Dada</th>
                            <th width="50" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="athleteRowsContainer">
                        <!-- Dynamic Row 0 (initial row) -->
                        <tr class="athlete-row" data-index="0" id="row_0">
                            <td class="text-center fw-bold text-muted row-number">1</td>
                            <td>
                                <input type="text" name="atlets[0][nama]" class="form-control form-control-sm athlete-name" placeholder="Nama lengkap atlet" required oninput="updateSummary()">
                            </td>
                            <td>
                                <select name="atlets[0][gender]" class="form-select form-select-sm athlete-gender" onchange="handleGenderChange(0)">
                                    <option value="L">L (Putra)</option>
                                    <option value="P">P (Putri)</option>
                                </select>
                            </td>
                            <td>
                                <input type="date" name="atlets[0][tanggal_lahir]" class="form-control form-control-sm athlete-dob" onchange="handleDobChange(0)">
                            </td>
                            <td>
                                <select name="atlets[0][kelompok_umur_id]" class="form-select form-select-sm athlete-ku" id="ku_select_0" onchange="handleKuChange(0)">
                                    <option value="">(Auto dari Tgl)</option>
                                    <?php foreach ($kelompokUmurs as $ku): ?>
                                        <option value="<?= $ku['id'] ?>" data-min="<?= $ku['usia_min'] ?>" data-max="<?= $ku['usia_max'] ?>">
                                            <?= esc($ku['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted d-block mt-0.5 ku-badge" id="ku_label_0" style="font-size: 10.5px;"></small>
                            </td>
                            <td>
                                <div class="event-picker-box p-1.5 rounded border bg-light" style="max-height: 120px; overflow-y: auto;">
                                    <div class="event-checkboxes" id="event_checkboxes_0">
                                        <?php if (empty($events)): ?>
                                            <span class="text-muted small">Belum ada acara lomba.</span>
                                        <?php else: ?>
                                            <?php foreach ($events as $ev): ?>
                                                <div class="form-check form-check-inline me-2 mb-1 event-item" 
                                                     data-gender="<?= esc($ev['gender']) ?>" 
                                                     data-ku="<?= esc($ev['kelompok_umur_id'] ?? '') ?>"
                                                     style="font-size: 11.5px;">
                                                    <input class="form-check-input event-checkbox" type="checkbox" 
                                                           name="atlets[0][events][]" 
                                                           value="<?= $ev['id'] ?>" 
                                                           id="ev_0_<?= $ev['id'] ?>"
                                                           onchange="updateSummary()">
                                                    <label class="form-check-label" for="ev_0_<?= $ev['id'] ?>" title="<?= esc($ev['nama_acara']) ?>">
                                                        <span class="badge bg-secondary-subtle text-dark border me-1" style="font-size: 9.5px;"><?= esc($ev['gender']) ?></span>
                                                        <?= esc($ev['nama_lomba']) ?>
                                                        <?php if (!empty($ev['nama_ku'])): ?>
                                                            <span class="text-muted">(<?= esc($ev['nama_ku']) ?>)</span>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted selected-event-count" id="event_count_0" style="font-size: 11px;">0 lomba dipilih</small>
                                    <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" style="font-size: 11px;" onclick="toggleAllEvents(0)">Pilih Semua yang Cocok</button>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <input type="file" name="akta_files[0]" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" title="Unggah Akta (JPG/PNG/PDF)">
                                    <input type="url" name="atlets[0][akta_url]" class="form-control form-control-sm" placeholder="atau Link GDrive" style="font-size: 11px;">
                                </div>
                            </td>
                            <td>
                                <input type="text" name="atlets[0][no_dada]" class="form-control form-control-sm text-center font-monospace" placeholder="Auto" maxlength="5">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" onclick="removeAthleteRow(0)" title="Hapus Baris Ini">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Row Action Bar -->
            <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1" onclick="addAthleteRow()">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Tambah Baris Atlet (+1)</span>
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addMultipleRows(5)">
                        + 5 Baris
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addMultipleRows(10)">
                        + 10 Baris
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Submit Bar -->
    <div class="card-custom p-3 d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            <i class="bi bi-info-circle text-primary me-1"></i>
            Pastikan nama dan tanggal lahir atlet terisi dengan benar. Akta kelahiran dapat diunggah langsung atau menyusul.
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('atlet') ?>" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="submitBatchBtn">
                <i class="bi bi-check2-circle me-1"></i> Simpan Pendaftaran Massal
            </button>
        </div>
    </div>
</form>

<!-- Modal: Paste Data dari Excel -->
<div class="modal fade" id="modalPasteExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clipboard-data text-success me-2"></i>Salin & Tempel Data dari Spreadsheet (Excel / Google Sheets)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">
                    Copy baris tabel dari Excel Anda (kolom: <strong>Nama Atlet | Gender (L/P) | Tanggal Lahir (YYYY-MM-DD atau DD/MM/YYYY) | No Dada (opsional)</strong>), lalu paste di kotak di bawah ini:
                </p>
                <textarea id="pasteExcelTextarea" class="form-control font-monospace" rows="8" placeholder="Ahmad Dani	L	2012-05-15	001
Siti Aminah	P	2013-08-20	002"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success fw-semibold" onclick="parseExcelData()">
                    <i class="bi bi-plus-lg me-1"></i> Masukkan ke Formulir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Master Kelompok Umur Data
const kelompokUmurList = <?= json_encode($kelompokUmurs) ?>;
const eventList = <?= json_encode($events) ?>;

let rowCount = 1;

function handleSekolahChange() {
    const val = document.getElementById('sekolahSelect').value;
    const container = document.getElementById('newSekolahContainer');
    if (val === 'new') {
        container.style.display = 'block';
        document.getElementById('namaSekolahBaru').focus();
    } else {
        container.style.display = 'none';
    }
}

function calculateAge(birthDateString) {
    if (!birthDateString) return null;
    const birthDate = new Date(birthDateString);
    if (isNaN(birthDate.getTime())) return null;
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function findMatchingKu(birthDateString) {
    const age = calculateAge(birthDateString);
    if (age === null) return null;
    for (let ku of kelompokUmurList) {
        if (age >= parseInt(ku.usia_min) && age <= parseInt(ku.usia_max)) {
            return ku;
        }
    }
    return null;
}

function handleDobChange(index) {
    const dobInput = document.querySelector(`input[name="atlets[${index}][tanggal_lahir]"]`);
    const kuSelect = document.getElementById(`ku_select_${index}`);
    const kuLabel = document.getElementById(`ku_label_${index}`);
    
    if (dobInput && dobInput.value) {
        const age = calculateAge(dobInput.value);
        const matched = findMatchingKu(dobInput.value);
        if (matched) {
            kuSelect.value = matched.id;
            kuLabel.innerHTML = `<span class="badge bg-success-subtle text-success">Usia: ${age} th &bull; ${matched.nama}</span>`;
        } else {
            kuLabel.innerHTML = `<span class="badge bg-warning-subtle text-warning">Usia: ${age} th (Di luar KU baku)</span>`;
        }
    } else {
        kuLabel.innerHTML = '';
    }
    filterEventsForRow(index);
    updateSummary();
}

function handleGenderChange(index) {
    filterEventsForRow(index);
    updateSummary();
}

function handleKuChange(index) {
    filterEventsForRow(index);
    updateSummary();
}

function filterEventsForRow(index) {
    const genderSelect = document.querySelector(`select[name="atlets[${index}][gender]"]`);
    const kuSelect = document.getElementById(`ku_select_${index}`);
    const selectedGender = genderSelect ? genderSelect.value : 'L';
    const selectedKu = kuSelect ? kuSelect.value : '';

    const container = document.getElementById(`event_checkboxes_${index}`);
    if (!container) return;

    const items = container.querySelectorAll('.event-item');
    items.forEach(item => {
        const evGender = item.getAttribute('data-gender');
        const evKu = item.getAttribute('data-ku');

        const genderMatch = (evGender === 'Campuran' || evGender === selectedGender);
        const kuMatch = (!selectedKu || !evKu || evKu === selectedKu);

        if (genderMatch && kuMatch) {
            item.style.display = 'inline-block';
            item.classList.remove('opacity-50');
        } else {
            // Jika tidak cocok, redupkan atau sembunyikan
            item.style.display = 'none';
            const chk = item.querySelector('.event-checkbox');
            if (chk) chk.checked = false; // uncheck jika tidak cocok
        }
    });
    updateEventCount(index);
}

function updateEventCount(index) {
    const container = document.getElementById(`event_checkboxes_${index}`);
    if (!container) return;
    const checked = container.querySelectorAll('.event-checkbox:checked').length;
    const countLabel = document.getElementById(`event_count_${index}`);
    if (countLabel) {
        countLabel.textContent = `${checked} lomba dipilih`;
    }
}

function toggleAllEvents(index) {
    const container = document.getElementById(`event_checkboxes_${index}`);
    if (!container) return;
    const visibleCheckboxes = container.querySelectorAll('.event-item[style*="display: inline-block"] .event-checkbox');
    const allChecked = Array.from(visibleCheckboxes).every(c => c.checked);
    visibleCheckboxes.forEach(c => {
        c.checked = !allChecked;
    });
    updateEventCount(index);
    updateSummary();
}

function addAthleteRow(data = {}) {
    const index = rowCount++;
    const container = document.getElementById('athleteRowsContainer');

    const tr = document.createElement('tr');
    tr.className = 'athlete-row';
    tr.id = `row_${index}`;
    tr.setAttribute('data-index', index);

    // Build event checkboxes HTML
    let eventsHtml = '';
    if (eventList.length === 0) {
        eventsHtml = '<span class="text-muted small">Belum ada acara lomba.</span>';
    } else {
        eventList.forEach(ev => {
            eventsHtml += `
                <div class="form-check form-check-inline me-2 mb-1 event-item" 
                     data-gender="${ev.gender}" 
                     data-ku="${ev.kelompok_umur_id || ''}"
                     style="font-size: 11.5px;">
                    <input class="form-check-input event-checkbox" type="checkbox" 
                           name="atlets[${index}][events][]" 
                           value="${ev.id}" 
                           id="ev_${index}_${ev.id}"
                           onchange="updateSummary()">
                    <label class="form-check-label" for="ev_${index}_${ev.id}" title="${ev.nama_acara}">
                        <span class="badge bg-secondary-subtle text-dark border me-1" style="font-size: 9.5px;">${ev.gender}</span>
                        ${ev.nama_lomba}
                        ${ev.nama_ku ? `<span class="text-muted">(${ev.nama_ku})</span>` : ''}
                    </label>
                </div>
            `;
        });
    }

    // Build KU options HTML
    let kuOptionsHtml = '<option value="">(Auto dari Tgl)</option>';
    kelompokUmurList.forEach(ku => {
        kuOptionsHtml += `<option value="${ku.id}" data-min="${ku.usia_min}" data-max="${ku.usia_max}">${ku.nama}</option>`;
    });

    tr.innerHTML = `
        <td class="text-center fw-bold text-muted row-number">${container.children.length + 1}</td>
        <td>
            <input type="text" name="atlets[${index}][nama]" class="form-control form-control-sm athlete-name" placeholder="Nama lengkap atlet" value="${data.nama || ''}" required oninput="updateSummary()">
        </td>
        <td>
            <select name="atlets[${index}][gender]" class="form-select form-select-sm athlete-gender" onchange="handleGenderChange(${index})">
                <option value="L" ${data.gender === 'L' ? 'selected' : ''}>L (Putra)</option>
                <option value="P" ${data.gender === 'P' ? 'selected' : ''}>P (Putri)</option>
            </select>
        </td>
        <td>
            <input type="date" name="atlets[${index}][tanggal_lahir]" class="form-control form-control-sm athlete-dob" value="${data.dob || ''}" onchange="handleDobChange(${index})">
        </td>
        <td>
            <select name="atlets[${index}][kelompok_umur_id]" class="form-select form-select-sm athlete-ku" id="ku_select_${index}" onchange="handleKuChange(${index})">
                ${kuOptionsHtml}
            </select>
            <small class="text-muted d-block mt-0.5 ku-badge" id="ku_label_${index}" style="font-size: 10.5px;"></small>
        </td>
        <td>
            <div class="event-picker-box p-1.5 rounded border bg-light" style="max-height: 120px; overflow-y: auto;">
                <div class="event-checkboxes" id="event_checkboxes_${index}">
                    ${eventsHtml}
                </div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <small class="text-muted selected-event-count" id="event_count_${index}" style="font-size: 11px;">0 lomba dipilih</small>
                <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" style="font-size: 11px;" onclick="toggleAllEvents(${index})">Pilih Semua yang Cocok</button>
            </div>
        </td>
        <td>
            <div class="d-flex flex-column gap-1">
                <input type="file" name="akta_files[${index}]" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" title="Unggah Akta (JPG/PNG/PDF)">
                <input type="url" name="atlets[${index}][akta_url]" class="form-control form-control-sm" placeholder="atau Link GDrive" style="font-size: 11px;">
            </div>
        </td>
        <td>
            <input type="text" name="atlets[${index}][no_dada]" class="form-control form-control-sm text-center font-monospace" placeholder="Auto" value="${data.no_dada || ''}" maxlength="5">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" onclick="removeAthleteRow(${index})" title="Hapus Baris Ini">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    container.appendChild(tr);
    reindexRows();
    handleDobChange(index);
    updateSummary();
}

function addMultipleRows(count) {
    for (let i = 0; i < count; i++) {
        addAthleteRow();
    }
}

function removeAthleteRow(index) {
    const row = document.getElementById(`row_${index}`);
    const container = document.getElementById('athleteRowsContainer');
    if (container.children.length <= 1) {
        alert('Minimal harus ada 1 baris atlet.');
        return;
    }
    if (row) {
        row.remove();
        reindexRows();
        updateSummary();
    }
}

function reindexRows() {
    const rows = document.querySelectorAll('#athleteRowsContainer .athlete-row');
    rows.forEach((r, idx) => {
        const num = r.querySelector('.row-number');
        if (num) num.textContent = idx + 1;
    });
}

function updateSummary() {
    const rows = document.querySelectorAll('#athleteRowsContainer .athlete-row');
    let filledAtlet = 0;
    rows.forEach(r => {
        const nameInput = r.querySelector('.athlete-name');
        if (nameInput && nameInput.value.trim() !== '') filledAtlet++;
        const idx = r.getAttribute('data-index');
        updateEventCount(idx);
    });

    const totalEventsChecked = document.querySelectorAll('#athleteRowsContainer .event-checkbox:checked').length;
    document.getElementById('totalAtletBadge').textContent = rows.length;
    document.getElementById('totalLombaBadge').textContent = totalEventsChecked;
}

// Paste Excel Parser
function parseExcelData() {
    const text = document.getElementById('pasteExcelTextarea').value.trim();
    if (!text) return;

    const lines = text.split('\n');
    let count = 0;

    lines.forEach(line => {
        const parts = line.split('\t');
        if (parts.length >= 1 && parts[0].trim()) {
            const nama = parts[0].trim();
            const rawGender = (parts[1] || '').trim().toUpperCase();
            const gender = (rawGender === 'P' || rawGender === 'PUTRI' || rawGender === 'PEREMPUAN') ? 'P' : 'L';
            
            // Format tanggal: handle YYYY-MM-DD or DD/MM/YYYY
            let dob = (parts[2] || '').trim();
            if (dob.includes('/')) {
                const segs = dob.split('/');
                if (segs.length === 3) {
                    dob = `${segs[2]}-${segs[1].padStart(2, '0')}-${segs[0].padStart(2, '0')}`;
                }
            }

            const noDada = (parts[3] || '').trim();

            addAthleteRow({
                nama: nama,
                gender: gender,
                dob: dob,
                no_dada: noDada
            });
            count++;
        }
    });

    bootstrap.Modal.getInstance(document.getElementById('modalPasteExcel')).hide();
    document.getElementById('pasteExcelTextarea').value = '';
    alert(`Berhasil menambahkan ${count} atlet dari data Excel!`);
}

document.addEventListener('DOMContentLoaded', function() {
    handleDobChange(0);
    updateSummary();
});
</script>

<?= $this->endSection() ?>
