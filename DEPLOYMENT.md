# 🚀 Panduan Deployment Kejurkot Atletik (Production Guide)

Dokumen ini menjelaskan tata cara deployment aplikasi **Sistem Informasi Manajemen Kejurkot Atletik** dari repositori GitHub `id0s/kejurkot.diaplikasi.com` ke server produksi (VPS / Dedicated Server / Cloud Server / cPanel).

---

## 📋 1. Kebutuhan Sistem (System Requirements)

Pastikan server tujuan memenuhi persyaratan minimal berikut:

- **Sistem Operasi**: Linux (Ubuntu 22.04/24.04 LTS, Debian 11/12, AlmaLinux/RockyLinux) atau Cloud Hosting.
- **Web Server**: Nginx (disarankan) atau Apache 2.4+.
- **PHP**: Versi **8.2** atau **8.3 / 8.4** (CodeIgniter 4.7.4 kompatibel penuh).
  - Ekstensi PHP wajib diaktifkan:
    ```bash
    sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-intl php8.3-mbstring \
                        php8.3-xml php8.3-curl php8.3-gd php8.3-zip php8.3-bcmath
    ```
- **Database Server**: MySQL 8.0+ atau MariaDB 10.6+.
- **Composer**: Composer 2.x.
- **SSL Certificate**: Let's Encrypt Certbot (HTTPS).

---

## 🛠️ 2. Langkah-Langkah Deployment (Step-by-Step)

### Langkah 1: Clone Repositori dari GitHub

Masuk ke direktori web server (misalnya `/var/www/` atau `/home/username/htdocs/`):

```bash
cd /var/www/
git clone https://github.com/id0s/kejurkot.diaplikasi.com.git kejurkot
cd kejurkot
```

---

### Langkah 2: Instalasi Dependensi PHP via Composer

Jalankan composer dengan flag produksi tanpa dependensi development:

```bash
composer install --no-dev --optimize-autoloader
```

---

### Langkah 3: Konfigurasi File Lingkungan (`.env`)

Salin template `.env.example` menjadi `.env`:

```bash
cp .env.example .env
nano .env
```

Sesuaikan nilai-nilai berikut di dalam `.env`:

```ini
CI_ENVIRONMENT = production

# URL domain resmi produksi Anda
app.baseURL = 'https://kejurkot.diaplikasi.com/'
app.forceGlobalSecureRequests = true

# Pengaturan Database Server Anda
database.default.hostname = localhost
database.default.database = kejurkot_db
database.default.username = kejurkot_user
database.default.password = PasswordDatabaseKuat123!
database.default.DBDriver = MySQLi
database.default.port = 3306
```

---

### Langkah 4: Setup Database & Impor Skema Awal

Buat database di MySQL/MariaDB:

```sql
CREATE DATABASE kejurkot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kejurkot_user'@'localhost' IDENTIFIED BY 'PasswordDatabaseKuat123!';
GRANT ALL PRIVILEGES ON kejurkot_db.* TO 'kejurkot_user'@'localhost';
FLUSH PRIVILEGES;
```

Impor skema dan master data bersih (bebas data pribadi mentah):

```bash
mysql -u kejurkot_user -p kejurkot_db < database/schema.sql
```

> **Catatan Akun Default:**
> Setelah impor, akun panitia pelaksana default siap digunakan:
> - **Username**: `admin` atau `panitia`
> - **Password**: `PanitiaKejurkot2026!`
> - *Segera ganti password melalui database atau menu profil setelah login.*

---

### Langkah 5: Pengaturan Hak Akses (Permissions)

CodeIgniter 4 memerlukan izin tulis pada direktori `writable/`:

```bash
sudo chown -R www-data:www-data /var/www/kejurkot/writable/
sudo chmod -R 775 /var/www/kejurkot/writable/

# Pastikan folder downloads di public juga dapat ditulis untuk cetak buku acara excel
sudo chown -R www-data:www-data /var/www/kejurkot/public/downloads/
sudo chmod -R 775 /var/www/kejurkot/public/downloads/
```

---

## 🌐 3. Konfigurasi Web Server

> ⚠️ **SANGAT PENTING (SECURITY):**
> `DocumentRoot` web server **HARUS diarahkan ke subdirektori `/public`**, BUKAN ke root project! Ini mencegah file sistem seperti `.env`, `app/`, dan `composer.json` diakses langsung dari peramban.

### A. Contoh Konfigurasi Nginx (`/etc/nginx/sites-available/kejurkot`)

```nginx
server {
    listen 80;
    server_name kejurkot.diaplikasi.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name kejurkot.diaplikasi.com;

    # SSL Certificate
    ssl_certificate /etc/letsencrypt/live/kejurkot.diaplikasi.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/kejurkot.diaplikasi.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Root diarahkan ke folder /public
    root /var/www/kejurkot/public;
    index index.php index.html;

    access_log /var/log/nginx/kejurkot_access.log;
    error_log /var/log/nginx/kejurkot_error.log;

    # Ukuran upload berkas (misal file Excel buku acara / template piagam)
    client_max_body_size 64M;

    # Routing CodeIgniter 4
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # Blok eksekusi PHP
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # Proteksi berkas tersembunyi
    location ~ /\. {
        deny all;
    }

    # Cache untuk aset statis
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg|eot)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```

Aktifkan konfigurasi Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/kejurkot /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

### B. Contoh Konfigurasi Apache 2.4 (VirtualHost)

```apache
<VirtualHost *:80>
    ServerName kejurkot.diaplikasi.com
    ServerAdmin webmaster@diaplikasi.com
    
    # Root ke folder public
    DocumentRoot /var/www/kejurkot/public

    <Directory /var/www/kejurkot/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/kejurkot_error.log
    CustomLog ${APACHE_LOG_DIR}/kejurkot_access.log combined
</VirtualHost>
```

Pastikan modul rewrite aktif:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 🔒 4. Checklist Keamanan & Produksi

1. [x] **Environment**: Pastikan `CI_ENVIRONMENT = production` pada `.env` (debug toolbar non-aktif).
2. [x] **File Sensitif**: Pastikan `.env` tidak dapat diunduh langsung dari browser (`https://domain.com/.env` harus menghasilkan 403 atau 404).
3. [x] **Data Pribadi**: Database awal menggunakan `database/schema.sql` yang bersih dari data pribadi atlet/kontak sensitif.
4. [x] **Password Admin**: Ganti password akun `admin` dan `panitia` segera setelah aplikasi siap digunakan di lapangan.
5. [x] **HTTPS Active**: Seluruh lalu lintas HTTP dialihkan (*redirect 301*) ke HTTPS.
6. [x] **Backup Rutin**: Jalankan backup database harian via cron job:
   ```bash
   0 2 * * * mysqldump -u kejurkot_user -p'PasswordDatabaseKuat123!' kejurkot_db > /backup/kejurkot_$(date +\%Y\%m\%d).sql
   ```
