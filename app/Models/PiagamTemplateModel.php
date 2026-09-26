<?php

namespace App\Models;

use CodeIgniter\Model;

class PiagamTemplateModel extends Model
{
    protected $table            = 'piagam_template';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama',
        'orientasi',
        'tipe',
        'file_background',
        'hide_header',
        'hide_title',
        'offset_top',
        'offset_ttd',
        'font_size_nama',
        'font_size_detail',
        'custom_offsets',
        'instansi_header',
        'sub_header',
        'judul',
        'format_nomor',
        'kalimat_pembuka',
        'kalimat_penghargaan',
        'ttd_1_jabatan',
        'ttd_1_nama',
        'ttd_1_image',
        'ttd_2_jabatan',
        'ttd_2_nama',
        'ttd_2_image',
        'ttd_3_jabatan',
        'ttd_3_nama',
        'ttd_3_image',
        'is_default',
        'is_active',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Pastikan tabel dan data template bawaan tersedia
     */
    public function initializeTable(): void
    {
        $db = \Config\Database::connect();

        $sql = "CREATE TABLE IF NOT EXISTS `piagam_template` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nama` VARCHAR(150) NOT NULL,
            `orientasi` ENUM('landscape', 'portrait') DEFAULT 'landscape',
            `tipe` VARCHAR(50) DEFAULT 'overlay',
            `file_background` VARCHAR(255) NULL,
            `hide_header` TINYINT(1) DEFAULT 0,
            `offset_top` INT DEFAULT 0,
            `font_size_nama` INT DEFAULT 32,
            `font_size_detail` INT DEFAULT 14,
            `instansi_header` VARCHAR(255) NULL DEFAULT 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
            `sub_header` VARCHAR(255) NULL DEFAULT 'PENGURUS PROVINSI JAWA TENGAH',
            `judul` VARCHAR(100) DEFAULT 'PIAGAM PENGHARGAAN',
            `format_nomor` VARCHAR(150) DEFAULT '{nomor}/SERT/PASIJATENG/IX/{tahun}',
            `kalimat_pembuka` TEXT NULL,
            `kalimat_penghargaan` TEXT NULL,
            `ttd_1_jabatan` VARCHAR(150) DEFAULT 'A.n Ketua Umum PASI Jawa Tengah\\nSekretaris Umum',
            `ttd_1_nama` VARCHAR(150) DEFAULT 'Dr. Hermahayu, M.Si',
            `ttd_1_image` VARCHAR(255) NULL,
            `ttd_2_jabatan` VARCHAR(150) DEFAULT 'Koordinator Wilayah',
            `ttd_2_nama` VARCHAR(150) DEFAULT 'Drs. Sukono, M.Or.',
            `ttd_2_image` VARCHAR(255) NULL,
            `ttd_3_jabatan` VARCHAR(150) DEFAULT 'Ketua Penyelenggara',
            `ttd_3_nama` VARCHAR(150) DEFAULT 'Waluyo, S.Pd',
            `ttd_3_image` VARCHAR(255) NULL,
            `is_default` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $db->query($sql);

        // Auto-migration jika tabel sudah ada sebelumnya
        try {
            $fields = $db->getFieldNames('piagam_template');
            if (!in_array('hide_header', $fields)) {
                $db->query("ALTER TABLE `piagam_template` ADD COLUMN `hide_header` TINYINT(1) DEFAULT 0 AFTER `file_background`");
            }
            if (!in_array('offset_top', $fields)) {
                $db->query("ALTER TABLE `piagam_template` ADD COLUMN `offset_top` INT DEFAULT 0 AFTER `hide_header`");
            }
            if (!in_array('font_size_nama', $fields)) {
                $db->query("ALTER TABLE `piagam_template` ADD COLUMN `font_size_nama` INT DEFAULT 32 AFTER `offset_top`");
            }
            if (!in_array('font_size_detail', $fields)) {
                $db->query("ALTER TABLE `piagam_template` ADD COLUMN `font_size_detail` INT DEFAULT 14 AFTER `font_size_nama`");
            }
        } catch (\Throwable $e) {
            log_message('error', 'Migration piagam_template fields error: ' . $e->getMessage());
        }

        // Jika tabel masih kosong, seed 3 template default
        $count = $db->table('piagam_template')->countAllResults();
        if ($count === 0) {
            $now = date('Y-m-d H:i:s');
            $defaultTemplates = [
                [
                    'nama'                => 'Template Landscape PASI (Background Resmi)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'overlay',
                    'file_background'     => 'template_pasi_clean.jpg',
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'PENGURUS PROVINSI JAWA TENGAH',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/SERT/PASIJATENG/IX/{tahun}',
                    'kalimat_pembuka'     => 'Dengan ini Persatuan Atletik Seluruh Indonesia ( PASI ) Provinsi Jawa Tengah memberikan Piagam Penghargaan kepada :',
                    'kalimat_penghargaan' => 'Atas Partisipasinya sebagai Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "A.n Ketua Umum\nPASI Jawa Tengah\nSekretaris Umum",
                    'ttd_1_nama'          => 'Dr. Hermahayu, M.Si',
                    'ttd_1_image'         => null,
                    'ttd_2_jabatan'       => 'Koordinator Wilayah',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_2_image'         => null,
                    'ttd_3_jabatan'       => 'Ketua Penyelenggara',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'ttd_3_image'         => null,
                    'is_default'          => 1,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'nama'                => 'Template Portrait Eks-Karesidenan (Format Resmi)',
                    'orientasi'           => 'portrait',
                    'tipe'                => 'full_css',
                    'file_background'     => null,
                    'instansi_header'     => 'KEJUARAAN ATLETIK TINGKAT EKS KARESIDENAN',
                    'sub_header'          => 'PENGURUS PROVINSI PERSATUAN ATLETIK SELURUH INDONESIA (PASI) JAWA TENGAH',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/SERT/PASIJATENG/IX/{tahun}',
                    'kalimat_pembuka'     => 'Pengurus Provinsi Persatuan Atletik Seluruh Indonesia (PASI) Jawa Tengah dengan ini memberikan Piagam Penghargaan kepada :',
                    'kalimat_penghargaan' => 'dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => 'Koordinator Wilayah',
                    'ttd_1_nama'          => 'Jumanto, S.Pd., M.Si.',
                    'ttd_1_image'         => null,
                    'ttd_2_jabatan'       => 'Ketua Panitia Pelaksana',
                    'ttd_2_nama'          => 'Zaini, S.Pd.',
                    'ttd_2_image'         => 'ttd_zaini_pasi_pekalongan.png',
                    'ttd_3_jabatan'       => "Mengetahui,\nKetua Umum PASI Jateng",
                    'ttd_3_nama'          => 'Dr. Rumini, M.Pd.',
                    'ttd_3_image'         => null,
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'nama'                => 'Template Modern Navy & Gold (A4 Landscape)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'full_css',
                    'file_background'     => null,
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'PENGURUS KOTA PEKALONGAN',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/PIAGAM/PASI-JATENG/{tahun}',
                    'kalimat_pembuka'     => 'Dengan ini memberikan penghargaan dan apresiasi setinggi-tingginya kepada :',
                    'kalimat_penghargaan' => 'Atas prestasi gemilang sebagai Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} pada ajang {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "Ketua Umum\nPASI Jawa Tengah",
                    'ttd_1_nama'          => 'Dr. Rumini, M.Pd.',
                    'ttd_1_image'         => null,
                    'ttd_2_jabatan'       => 'Koordinator Wilayah Pekalongan',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_2_image'         => null,
                    'ttd_3_jabatan'       => 'Ketua Panitia Pelaksana',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'ttd_3_image'         => null,
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'nama'                => 'Template Modern Cyber Grid (A4 Landscape)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'cyber_grid',
                    'file_background'     => null,
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'PENGURUS PROVINSI JAWA TENGAH',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/GRID/PASI-JATENG/{tahun}',
                    'kalimat_pembuka'     => 'Dengan ini Persatuan Atletik Seluruh Indonesia ( PASI ) Provinsi Jawa Tengah memberikan Piagam Penghargaan kepada :',
                    'kalimat_penghargaan' => 'Atas Partisipasinya sebagai Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "A.n Ketua Umum\nPASI Jawa Tengah\nSekretaris Umum",
                    'ttd_1_nama'          => 'Dr. Hermahayu, M.Si',
                    'ttd_1_image'         => null,
                    'ttd_2_jabatan'       => 'Koordinator Wilayah',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_2_image'         => null,
                    'ttd_3_jabatan'       => 'Ketua Penyelenggara',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'ttd_3_image'         => null,
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'nama'                => 'Template Royal Guilloche Gold (A4 Landscape)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'royal_guilloche',
                    'file_background'     => null,
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'PENGURUS PROVINSI JAWA TENGAH',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/ROYAL/PASI-JATENG/{tahun}',
                    'kalimat_pembuka'     => 'Dengan ini memberikan anugerah Piagam Kehormatan & Prestasi kepada :',
                    'kalimat_penghargaan' => 'Atas dedikasi dan keberhasilannya meraih Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "A.n Ketua Umum\nPASI Jawa Tengah\nSekretaris Umum",
                    'ttd_1_nama'          => 'Dr. Hermahayu, M.Si',
                    'ttd_1_image'         => null,
                    'ttd_2_jabatan'       => 'Koordinator Wilayah',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_2_image'         => null,
                    'ttd_3_jabatan'       => 'Ketua Penyelenggara',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'ttd_3_image'         => null,
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'nama'                => 'Template Athletic Track Stripes & Emerald (A4 Landscape)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'athletic_stripes',
                    'file_background'     => null,
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'KEJURWIL ATLETIK 2026',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/SPORT/PASI-JATENG/{tahun}',
                    'kalimat_pembuka'     => 'Dengan ini Persatuan Atletik Seluruh Indonesia ( PASI ) Provinsi Jawa Tengah memberikan Piagam Penghargaan kepada :',
                    'kalimat_penghargaan' => 'Atas Partisipasinya sebagai Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "Ketua Umum\nPASI Jawa Tengah",
                    'ttd_1_nama'          => 'Dr. Rumini, M.Pd.',
                    'ttd_1_image'         => null,
                    'ttd_2_jabatan'       => 'Koordinator Wilayah',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_2_image'         => null,
                    'ttd_3_jabatan'       => 'Ketua Penyelenggara',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'ttd_3_image'         => null,
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
            ];

            $db->table('piagam_template')->insertBatch($defaultTemplates);
        } else {
            // Pastikan jika template 4, 5, 6 belum ada di database, kita tambahkan
            $existingNames = $db->table('piagam_template')->select('nama')->get()->getResultArray();
            $namesList = array_column($existingNames, 'nama');
            $now = date('Y-m-d H:i:s');

            if (!in_array('Template Modern Cyber Grid (A4 Landscape)', $namesList)) {
                $db->table('piagam_template')->insert([
                    'nama'                => 'Template Modern Cyber Grid (A4 Landscape)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'cyber_grid',
                    'file_background'     => null,
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'PENGURUS PROVINSI JAWA TENGAH',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/GRID/PASI-JATENG/{tahun}',
                    'kalimat_pembuka'     => 'Dengan ini Persatuan Atletik Seluruh Indonesia ( PASI ) Provinsi Jawa Tengah memberikan Piagam Penghargaan kepada :',
                    'kalimat_penghargaan' => 'Atas Partisipasinya sebagai Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "A.n Ketua Umum\nPASI Jawa Tengah\nSekretaris Umum",
                    'ttd_1_nama'          => 'Dr. Hermahayu, M.Si',
                    'ttd_2_jabatan'       => 'Koordinator Wilayah',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_3_jabatan'       => 'Ketua Penyelenggara',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }

            if (!in_array('Template Royal Guilloche Gold (A4 Landscape)', $namesList)) {
                $db->table('piagam_template')->insert([
                    'nama'                => 'Template Royal Guilloche Gold (A4 Landscape)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'royal_guilloche',
                    'file_background'     => null,
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'PENGURUS PROVINSI JAWA TENGAH',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/ROYAL/PASI-JATENG/{tahun}',
                    'kalimat_pembuka'     => 'Dengan ini memberikan anugerah Piagam Kehormatan & Prestasi kepada :',
                    'kalimat_penghargaan' => 'Atas dedikasi dan keberhasilannya meraih Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "A.n Ketua Umum\nPASI Jawa Tengah\nSekretaris Umum",
                    'ttd_1_nama'          => 'Dr. Hermahayu, M.Si',
                    'ttd_2_jabatan'       => 'Koordinator Wilayah',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_3_jabatan'       => 'Ketua Penyelenggara',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }

            if (!in_array('Template Athletic Track Stripes & Emerald (A4 Landscape)', $namesList)) {
                $db->table('piagam_template')->insert([
                    'nama'                => 'Template Athletic Track Stripes & Emerald (A4 Landscape)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'athletic_stripes',
                    'file_background'     => null,
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'KEJURWIL ATLETIK 2026',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/SPORT/PASI-JATENG/{tahun}',
                    'kalimat_pembuka'     => 'Dengan ini Persatuan Atletik Seluruh Indonesia ( PASI ) Provinsi Jawa Tengah memberikan Piagam Penghargaan kepada :',
                    'kalimat_penghargaan' => 'Atas Partisipasinya sebagai Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "Ketua Umum\nPASI Jawa Tengah",
                    'ttd_1_nama'          => 'Dr. Rumini, M.Pd.',
                    'ttd_2_jabatan'       => 'Koordinator Wilayah',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_3_jabatan'       => 'Ketua Penyelenggara',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }

            if (!in_array('Template Geometric Gold Hex Grid (A4 Landscape)', $namesList)) {
                $db->table('piagam_template')->insert([
                    'nama'                => 'Template Geometric Gold Hex Grid (A4 Landscape)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'gold_grid',
                    'file_background'     => null,
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'PENGURUS PROVINSI JAWA TENGAH',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/HEX/PASI-JATENG/{tahun}',
                    'kalimat_pembuka'     => 'Dengan ini memberikan anugerah apresiasi dan kehormatan setinggi-tingginya kepada :',
                    'kalimat_penghargaan' => 'Atas dedikasi dan prestasi gemilang meraih Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "A.n Ketua Umum\nPASI Jawa Tengah\nSekretaris Umum",
                    'ttd_1_nama'          => 'Dr. Hermahayu, M.Si',
                    'ttd_2_jabatan'       => 'Koordinator Wilayah',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_3_jabatan'       => 'Ketua Penyelenggara',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }

            if (!in_array('Template Minimalist Swiss Grid & Badge (A4 Landscape)', $namesList)) {
                $db->table('piagam_template')->insert([
                    'nama'                => 'Template Minimalist Swiss Grid & Badge (A4 Landscape)',
                    'orientasi'           => 'landscape',
                    'tipe'                => 'swiss_grid',
                    'file_background'     => null,
                    'instansi_header'     => 'PERSATUAN ATLETIK SELURUH INDONESIA PROVINSI JAWA TENGAH',
                    'sub_header'          => 'KOMISI KOMPETISI & PERLOMBAAN',
                    'judul'               => 'PIAGAM PENGHARGAAN',
                    'format_nomor'        => '{nomor}/SWISS/PASI-JATENG/{tahun}',
                    'kalimat_pembuka'     => 'PERSATUAN ATLETIK SELURUH INDONESIA (PASI) DENGAN INI MENGANUGERAHKAN KEPADA :',
                    'kalimat_penghargaan' => 'Atas pencapaian peringkat Juara ( {juara} ) {juara_teks} Nomor {nomor_lomba} dalam {nama_kejuaraan} yang diselenggarakan pada tanggal {tanggal} di {tempat}.',
                    'ttd_1_jabatan'       => "Ketua Umum\nPASI Jawa Tengah",
                    'ttd_1_nama'          => 'Dr. Rumini, M.Pd.',
                    'ttd_2_jabatan'       => 'Koordinator Wilayah',
                    'ttd_2_nama'          => 'Drs. Sukono, M.Or.',
                    'ttd_3_jabatan'       => 'Ketua Penyelenggara',
                    'ttd_3_nama'          => 'Waluyo, S.Pd',
                    'is_default'          => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }
    }

    /**
     * Dapatkan template default aktif
     */
    public function getDefaultTemplate(): ?array
    {
        $this->initializeTable();
        $def = $this->where('is_active', 1)->where('is_default', 1)->first();
        if (!$def) {
            $def = $this->where('is_active', 1)->first();
        }
        if (!$def) {
            $def = $this->where('is_default', 1)->first();
        }
        if (!$def) {
            $def = $this->first();
        }
        return $def;
    }
}
