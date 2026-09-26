<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Kelompok Umur
        $kuData = [
            [
                'nama'       => 'KU 5-6 (Usia Dini)',
                'usia_min'   => 5,
                'usia_max'   => 6,
                'keterangan' => 'Kategori Usia Dini / PAUD / TK',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama'       => 'KU 8-9',
                'usia_min'   => 8,
                'usia_max'   => 9,
                'keterangan' => 'Kategori SD / MI Kelas Bawah',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama'       => 'KU 10-11',
                'usia_min'   => 10,
                'usia_max'   => 11,
                'keterangan' => 'Kategori SD / MI Kelas Atas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama'       => 'KU 12-13',
                'usia_min'   => 12,
                'usia_max'   => 13,
                'keterangan' => 'Kategori SMP / MTs Kelas 7-8',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama'       => 'KU 14-15',
                'usia_min'   => 14,
                'usia_max'   => 15,
                'keterangan' => 'Kategori SMP Kelas 9 / SMA Kelas 10',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama'       => 'KU 16-18',
                'usia_min'   => 16,
                'usia_max'   => 18,
                'keterangan' => 'Kategori SMA / SMK / MA',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        $this->db->table('kelompok_umur')->ignore(true)->insertBatch($kuData);

        // 2. Nomor Lomba
        $lombaData = [
            ['nama' => 'Lari 30m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Khusus KU Usia Dini', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => '40 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Lari cepat KU 8-9', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Lari 60 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Lari cepat KU 10-11', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => '80 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Lari cepat KU 12-13', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => '100 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Lari cepat sprint', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => '200 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Sprint 200 meter', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => '400 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Lari satu keliling lintasan', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => '800 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Lari jarak menengah', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => '1000 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Lari jarak menengah KU 12-13', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => '1500 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Lari jarak menengah', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => '5000 m', 'kategori' => 'Lari', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Lari jarak jauh', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Estafet 2 x 20 m', 'kategori' => 'Estafet', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Estafet anak KU 8-9', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Estafet 2 x 30 m', 'kategori' => 'Estafet', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Estafet anak KU 10-11', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Estafet 4 x 100 m', 'kategori' => 'Estafet', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Estafet 4 pelari', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Estafet 5 x 80 m', 'kategori' => 'Estafet', 'satuan_hasil' => 'Waktu', 'keterangan' => 'Estafet beregu 5 pelari', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Lompat Jauh', 'kategori' => 'Lompat', 'satuan_hasil' => 'Jarak', 'keterangan' => 'Lompat jauh bak pasir (meter)', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Tolak Peluru', 'kategori' => 'Lempar', 'satuan_hasil' => 'Jarak', 'keterangan' => 'Tolak peluru (meter)', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Lempar Lembing', 'kategori' => 'Lempar', 'satuan_hasil' => 'Jarak', 'keterangan' => 'Lempar lembing (meter)', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Lempar Cakram', 'kategori' => 'Lempar', 'satuan_hasil' => 'Jarak', 'keterangan' => 'Lempar cakram (meter)', 'created_at' => $now, 'updated_at' => $now],
        ];
        $this->db->table('nomor_lomba')->ignore(true)->insertBatch($lombaData);

        // 3. Kejuaraan Default
        $kejuaraan = [
            'nama'            => 'KEJURWIL ATLETIK KOTA PEKALONGAN 2026',
            'tahun'           => 2026,
            'tempat'          => 'Stadion Hoegeng Kota Pekalongan',
            'tanggal_mulai'   => '2026-08-29',
            'tanggal_selesai' => '2026-08-30',
            'penyelenggara'   => 'PASI Kota Pekalongan & Dinparpora',
            'status'          => 'Aktif',
            'deskripsi'       => 'Kejuaraan Wilayah Atletik Antar Pelajar se-Kota Pekalongan Tahun 2026',
            'created_at'      => $now,
            'updated_at'      => $now,
        ];
        $this->db->table('kejuaraan')->ignore(true)->insert($kejuaraan);

        // 4. Default Admin User (admin / admin123)
        $admin = [
            'username'      => 'admin',
            'nama_lengkap'  => 'Administrator Kejurwil',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role'          => 'admin',
            'is_active'     => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ];
        $this->db->table('users')->ignore(true)->insert($admin);
    }
}
