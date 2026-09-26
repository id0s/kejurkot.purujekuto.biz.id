# 🏆 Sistem Manajemen Kejurwil Atletik Kota Pekalongan

Aplikasi web modern berbasis **CodeIgniter 4** untuk pengelolaan data perlombaan dan kejuaraan atletik tingkat kota, mencakup pendaftaran atlet, manajemen nomor acara, pembagian seri/lintasan (heat & lane), pencatatan hasil lomba, penentuan juara & medali otomatis, serta penerbitan **Buku Acara resmi** (Cetak PDF & Download Excel).

Live Web URL: **[https://kejurkot.diaplikasi.com/](https://kejurkot.diaplikasi.com/)**

---

## 🚀 Fitur Utama

### 1. Dashboard Interaktif
- Ringkasan statistik waktu-nyata: Total Atlet, Sekolah/Kontingen, Nomor Acara, dan Hasil Tercatat.
- Grafik visual interaktif (*Chart.js*):
  - Diagram sebaran atlet berdasarkan **Kelompok Umur (KU)**.
  - Diagram donat rasio gender atlet (**Putra vs Putri**).
- Tabel jadwal acara lomba terkini dan papan peringkat medali teratas.

### 2. Pendaftaran Atlet & Multi-Event
- **Nomor Dada Manual**: Panitia dapat menginput dan menyunting nomor dada fisik atlet secara mandiri.
- **Pencarian & Input Kontingen Cepat (*Select2*)**: Pengguna tidak perlu repot menggulir ratusan sekolah; cukup ketik nama sekolah untuk pencarian instan, atau ketik nama kontingen baru yang akan tersimpan secara otomatis.
- **Kalkulasi KU Dinamis Otomatis**: Pengguna tidak perlu bingung memilih kelompok umur; sistem menghitung usia dan menentukan **Kelompok Umur (KU)** secara waktu-nyata (*real-time*) saat tanggal lahir dipilih.
- **Dropdown Multi-Event Fleksibel**: Pemilihan nomor lomba multi-event menggunakan dropdown ringkas (*Select2 multiple*) yang dapat memilih lebih dari satu acara lomba sekaligus dengan fitur pencarian dan penghapusan tag instan.
- **Penyaringan Cepat**: Filter data atlet instan berdasarkan asal sekolah/kontingen dan pencarian langsung (*DataTables*).

### 3. Import Massal Template Excel
- Dilengkapi pustaka **PhpSpreadsheet** untuk membaca dan mengimpor berkas Excel secara otomatis:
  - **Template Mentahan Siswa**: Membaca nama, sekolah (auto-create jika belum ada), tanggal lahir (auto-KU), nomor lomba, dan gender.
  - **Mapping Nomor Dada (Sheet 0)**: Memperbarui nomor dada fisik atlet secara massal dari lembar rekapitulasi.

### 4. Manajemen Jadwal & Acara Perlombaan
- Pengelolaan nomor urut acara (Acara 1 s/d Acara 79), babak (Penyisihan / Semifinal / Final), dan waktu pelaksanaan (Sesi Pagi / Sore).
- **Pengaturan Peserta & Lintasan**:
  - Modal tambah atlet dengan filter otomatis berdasarkan kategori gender dan KU acara.
  - Fitur **Atur Seri & Lintasan Otomatis** (misal: membagi peserta ke dalam Seri 1, Seri 2 dengan 8 lintasan standar per seri).

### 5. Input Hasil & Auto-Ranking Medali
- Pencatatan prestasi atlet:
  - Satuan **Waktu** (detik / menit.detik) untuk nomor Lari dan Estafet.
  - Satuan **Jarak** (meter) untuk nomor Lompat dan Lempar.
- Status partisipasi atlet: `Valid`, `DNS` (Did Not Start), `DNF` (Did Not Finish), `DQ` (Disqualified), `NM` (No Mark).
- **Tombol Auto Ranking & Medali**: Satu klik untuk menghitung peringkat 1, 2, 3 secara otomatis:
  - Lomba lari: nilai terkecil = peringkat 1 (paling cepat).
  - Lomba lompat/lempar: nilai terbesar = peringkat 1 (paling jauh).
  - Otomatis menetapkan Medali **Emas**, **Perak**, dan **Perunggu**.

### 6. Buku Acara Resmi
- Format pratinjau resmi sesuai template buku acara perlombaan atletik (Kolom: `NO`, `No Dada`, `Nama Atlet`, `Asal Sekolah`, `Prestasi`, `Keterangan / Juara`).
- **Cetak Formal (PDF/Print)**: Tampilan cetak putih bersih dengan kop surat resmi dan kolom tanda tangan panitia pelaksana & wasit.
- **Download Excel (.xlsx)**: Mengekspor seluruh susunan acara dan hasil perlombaan ke dalam format berkas Excel bergaris rapi.

### 7. Klasemen Perolehan Medali
- Rekapitulasi medali per sekolah / kontingen berdasarkan standar olimpiade (prioritas Emas, lalu Perak, lalu Perunggu).

### 8. Autentikasi Admin & Proteksi Penuh (Strict Access Control)
- **Kunci Akses Menyeluruh**: Seluruh halaman, menu, dan endpoint aplikasi terkunci secara global melalui `AuthFilter`.
- Pengguna yang belum login **tidak dapat mengakses halaman atau fitur apapun**, dan otomatis dialihkan ke halaman login dengan pesan peringatan.

---

## 💻 Teknologi yang Digunakan (Tech Stack)

| Komponen | Teknologi | Keterangan |
|---|---|---|
| **Backend Framework** | **CodeIgniter 4** (v4.7.4) | PHP MVC Framework ringan, cepat, dan terstruktur |
| **Bahasa Pemrograman**| **PHP 8.4** | Mendukung fitur modern dan performa tinggi |
| **Web Server** | **Nginx** | Reverse proxy dengan SSL/TLS aktif (HTTPS) |
| **Basis Data** | **MySQL / MariaDB** | Database relasional dengan 9 tabel terindeks |
| **Styling (CSS)** | **Bootstrap 5.3.3 & Atlassian Design System** | Tema SaaS Jira (Canvas `#F4F5F7`, Atlassian Blue `#0052CC`, Kartu Putih Bersih, Jira Lozenges) |
| **Ikonografi** | **Bootstrap Icons 1.11.3** | Paket ikon SVG serasi dan ringan |
| **Javascript / DOM** | **jQuery 3.7.1** | Manipulasi interaksi antarmuka yang mudah dipelajari |
| **Tabel Interaktif** | **DataTables 1.13.7** | Pencarian instan, sorting kolom, dan paginasi |
| **Grafik Statistik** | **Chart.js 4.4.1** | Visualisasi diagram batang dan donat responsif |
| **Pemroses Excel** | **PhpSpreadsheet 5.10** | Import berkas `.xlsx` mentahan dan export Buku Acara |

---

## 🗄️ Struktur Basis Data

Sistem menggunakan database `class_kejurkot` dengan relasi antar tabel sebagai berikut:

```
kejuaraan
  ├── event (mata acara lomba)
  │     ├── nomor_lomba (kategori lomba & satuan penilaian)
  │     ├── kelompok_umur (batasan umur)
  │     └── peserta_event (peserta lomba per acara)
  │           ├── atlet (data diri atlet & nomor dada)
  │           │     └── sekolah (asal kontingen)
  │           └── hasil (prestasi, peringkat, medali)
  │
users (akun panitia & administrator)
```

### Daftar Tabel:
1. `kejuaraan` — Data event kejuaraan kota (tahun, lokasi, tanggal, status).
2. `sekolah` — Data sekolah/kontingen (jenjang PAUD/TK, SD/MI, SMP/MTs, SMA/SMK/MA, Club).
3. `kelompok_umur` — Batasan usia kategori perlombaan (KU 5-6, KU 8-9, KU 10-11, KU 12-13, KU 14-15, KU 16-18).
4. `nomor_lomba` — Cabang nomor atletik (Lari, Lompat, Lempar, Estafet) dan satuan penilaiannya (Waktu / Jarak).
5. `atlet` — Data atlet (nama, asal sekolah, gender L/P, tanggal lahir, nomor dada manual, KU).
6. `event` — Susunan nomor acara lomba (nomor acara, nama acara, babak, jadwal sesi).
7. `peserta_event` — Relasi atlet dengan acara yang diikuti (nomor seri/heat, lintasan/lane, urutan).
8. `hasil` — Catatan prestasi waktu/jarak, status (Valid/DNS/DNF/DQ/NM), peringkat, dan medali.
9. `users` — Data akun login administrator panitia pelaksana.

---

## 📁 Struktur Direktori Aplikasi

```
kejurkot.diaplikasi.com/
├── app/
│   ├── Commands/
│   │   └── ImportTemplates.php      # CLI spark command untuk import Excel massal
│   ├── Config/
│   │   ├── App.php                  # Pengaturan baseURL HTTPS & clean routing
│   │   ├── Routes.php               # Definisi rute seluruh modul aplikasi
│   │   └── Filters.php              # Pengaturan filter autentikasi
│   ├── Controllers/
│   │   ├── Dashboard.php            # Ringkasan data & statistik
│   │   ├── Auth.php                 # Login & logout admin
│   │   ├── Kejuaraan.php            # CRUD kejuaraan
│   │   ├── Sekolah.php              # CRUD sekolah / kontingen
│   │   ├── KelompokUmur.php         # Master Kelompok Umur
│   │   ├── NomorLomba.php           # Master Nomor Lomba
│   │   ├── Atlet.php                # CRUD atlet & import Excel
│   │   ├── Event.php                # Susunan acara, heat & lintasan
│   │   ├── Hasil.php                # Pencatatan hasil & auto-ranking
│   │   ├── BukuAcara.php            # Pratinjau, cetak PDF, & download Excel
│   │   └── Laporan.php              # Klasemen medali
│   ├── Database/
│   │   ├── Migrations/              # 9 berkas migrasi tabel database
│   │   └── Seeds/
│   │       └── InitialSeeder.php    # Seeder data awal KU, Lomba, Kejuaraan, Admin
│   ├── Filters/
│   │   └── AuthFilter.php           # Filter pelindung sesi login
│   ├── Libraries/
│   │   ├── ExcelImporter.php        # Parser data Excel mentahan & no dada
│   │   └── ExcelExporter.php        # Generator buku acara Excel (.xlsx)
│   ├── Models/                      # 9 CodeIgniter Models
│   └── Views/                       # Seluruh template antarmuka pengguna
│       ├── layouts/main.php         # Layout utama master
│       ├── components/              # Sidebar, topbar, modal
│       ├── dashboard/               # Tampilan dashboard
│       ├── atlet/                   # Tampilan atlet (index, form, import)
│       ├── event/                   # Tampilan acara & pembagian lintasan
│       ├── hasil/                   # Tampilan input hasil lomba
│       ├── buku_acara/              # Tampilan preview & cetak formal
│       └── laporan/                 # Tampilan klasemen medali
├── public/
│   ├── assets/
│   │   ├── css/                     # Bootstrap 5, Bootstrap Icons, Custom CSS
│   │   └── js/                      # jQuery, DataTables, Chart.js, App.js
│   └── index.php                    # Front controller CodeIgniter 4
├── template-kejurkot-excel/         # Berkas mentahan template asli
│   ├── mentahan template siswa.xlsx
│   └── hasil template(buku acara).xlsx
├── .env                             # Konfigurasi database & environment HTTPS
├── composer.json                    # Dependensi composer
└── README.md                        # Dokumentasi proyek
```

---

## ⚙️ Panduan Pemasangan & Menjalankan Aplikasi

### 1. Kebutuhan Server
- PHP versi **8.2** atau lebih baru (direkomendasikan PHP 8.3 / 8.4).
- Ekstensi PHP yang aktif: `intl`, `mbstring`, `mysqli`, `pdo_mysql`, `gd`, `zip`, `xml`, `curl`.
- Database MySQL atau MariaDB.
- Composer versi 2.x.

### 2. Konfigurasi Lingkungan (`.env`)
Salin file `.env.example` menjadi `.env` dan sesuaikan koneksi database serta base URL produksi Anda:
```ini
CI_ENVIRONMENT = production

app.baseURL = 'https://kejurkot.diaplikasi.com/'
app.indexPage = ''

database.default.hostname = localhost
database.default.database = kejurkot_db
database.default.username = db_user
database.default.password = db_password_secure
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### 3. Eksekusi Instalasi Database
Impor skema basis data awal yang bersih melalui file `database/schema.sql`:
```bash
mysql -u db_user -p kejurkot_db < database/schema.sql
```
Atau jika menggunakan CodeIgniter Spark:
```bash
php spark migrate --all
php spark db:seed InitialSeeder
```

### 4. Panduan Lengkap Deployment
Detail arsitektur server, konfigurasi Nginx/Apache, manajemen permission, dan alur CI/CD selengkapnya dapat dilihat pada berkas **[DEPLOYMENT.md](DEPLOYMENT.md)**.

---

## 🔑 Informasi Akun Default

Setelah mengimpor `database/schema.sql`:
- **URL Login**: `https://kejurkot.diaplikasi.com/auth/login`
- **Username**: `admin` atau `panitia`
- **Password Default**: `PanitiaKejurkot2026!` *(Segera ganti password setelah login pertama)*

---

## 📄 Lisensi
Hak Cipta © 2026 PASI Kota Pekalongan / Dinparpora. Dikembangkan untuk Kejuaraan Atletik Antar Pelajar se-Kota Pekalongan.

