<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-white mb-1">Import Data Atlet dari Excel</h4>
                <p class="text-muted small mb-0">Migrasi dan unggah data pendaftaran atlet dalam jumlah banyak sekaligus</p>
            </div>
            <a href="<?= base_url('atlet') ?>" class="btn btn-secondary-custom">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Atlet
            </a>
        </div>

        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="bi bi-file-earmark-spreadsheet-fill me-2 text-success"></i> Formulir Unggah Berkas Excel (.xlsx)
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('atlet/import') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label class="form-label">Tipe Format Berkas Excel</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="card-custom p-3 d-flex align-items-start gap-3 cursor-pointer h-100" style="cursor: pointer;">
                                    <input class="form-check-input mt-1" type="radio" name="import_type" value="mentahan" checked>
                                    <div>
                                        <strong class="text-white d-block mb-1">1. Mentahan Template Atlet</strong>
                                        <small class="text-muted">
                                            Sheet ACARA: Kolom Nama, Asal Sekolah, Tgl Lahir, Nomor Lomba, Gender. Otomatis membuat sekolah baru dan nomor acara.
                                        </small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="card-custom p-3 d-flex align-items-start gap-3 cursor-pointer h-100" style="cursor: pointer;">
                                    <input class="form-check-input mt-1" type="radio" name="import_type" value="no_dada">
                                    <div>
                                        <strong class="text-white d-block mb-1">2. Mapping Nomor Dada</strong>
                                        <small class="text-muted">
                                            Sheet 0 / No Dada: Kolom No Dada, Asal Sekolah, Nama Atlet. Memperbarui nomor dada fisik atlet yang sudah terdaftar.
                                        </small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Pilih Berkas Excel (.xlsx)</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted mt-1 d-block">
                            <i class="bi bi-info-circle me-1"></i> Mendukung format Excel dari template resmi (misal: <code>mentahan template siswa.xlsx</code> atau <code>hasil template(buku acara).xlsx</code>).
                        </small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('atlet') ?>" class="btn btn-secondary-custom">Batal</a>
                        <button type="submit" class="btn btn-primary-gradient px-4">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Mulai Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Format Guide Card -->
        <div class="card-custom p-4">
            <h6 class="fw-bold text-white mb-3">
                <i class="bi bi-lightbulb-fill text-warning me-2"></i> Panduan Kolom Template Excel Mentahan
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-dark-custom mb-0">
                    <thead>
                        <tr>
                            <th>Kolom</th>
                            <th>Field</th>
                            <th>Contoh Data</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge badge-blue">C</span></td>
                            <td>NAMA</td>
                            <td>Satrio Pinandhito</td>
                            <td>Nama atlet</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-blue">D</span></td>
                            <td>ASAL SEKOLAH</td>
                            <td>TK ABA BINAGRIYA</td>
                            <td>Dibuat otomatis bila belum ada</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-blue">E</span></td>
                            <td>TGL LAHIR</td>
                            <td>2020-11-23</td>
                            <td>Untuk auto-hitung Kelompok Umur (KU)</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-blue">F</span></td>
                            <td>NOMOR LOMBA</td>
                            <td>Lari 30m / 100 m</td>
                            <td>Otomatis didaftarkan ke acara terkait</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-blue">H</span></td>
                            <td>L/P</td>
                            <td>L atau P</td>
                            <td>Putra / Putri</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
