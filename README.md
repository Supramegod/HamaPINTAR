# HamaPINTAR — WebGIS Monitoring Hama Tikus Sawah

Versi Laravel 13 + Tailwind CSS 4 dari project `deteksitikus` (tanpa Bootstrap).
Peta memakai Leaflet, grafik memakai Chart.js — keduanya di-bundle lewat Vite.

## Fitur

- Login dengan peran **Administrator** dan **Petugas** (tanpa registrasi publik)
- Manajemen pengguna (khusus admin): tambah, ubah, nonaktifkan, hapus
- Halaman terpisah: Dashboard, Peta Sebaran, Analisis Tren, Data Deteksi, Pengguna, Profil
- Upload CSV data alat dengan deteksi ganda dua lapis: berkas yang sama persis ditolak (dikenali dari isinya,
  bukan nama), dan baris duplikat (node + waktu sama) dilewati
- Riwayat unggahan: siapa mengunggah berkas apa, kapan, dan hasilnya
- Filter rentang tanggal
- Peta sebaran titik deteksi (Leaflet + OpenStreetMap)
- Grafik deret waktu: per jam, per hari, per node PIR
- Prediksi tren 3 hari ke depan (regresi linear)

Format CSV (baris pertama = header):

```
waktu,node,latitude,longitude
2026/11/20 18:15:00,PIR 1,-7.25200000,112.79440000
```

Contoh file: `tests/Fixtures/csv/00-contoh-data.csv` (skenario uji metode lain ada di folder yang sama, lihat `KETERANGAN.md`). Data demo: `php artisan db:seed`.

## Struktur kode

```
app/
├── Http/Controllers/                    # tipis: Dashboard, Map, Analytics, Detection(+Import), User, Profile, Auth
├── Http/Requests/                       # validasi: filter periode, CSV, login, pengguna, profil
├── Http/Middleware/EnsureUserIsActive   # akun yang dinonaktifkan langsung keluar
├── Services/Detection/
│   ├── DetectionReportService.php       # query + statistik + prediksi untuk semua halaman
│   ├── DetectionAnalyzer.php            # agregasi per slot jam, hari, node
│   ├── TrendPredictor.php               # regresi linear + proyeksi N hari
│   └── DetectionCsvImporter.php         # parsing CSV, validasi baris, insert batch
├── Data/                                # DTO readonly: DetectionStats, TrendPrediction, ImportResult
├── Enums/                              # TrendStatus, UserRole
├── View/Presenters/                     # MapPayload, ChartPayload (JSON untuk JS)
└── Models/DetectionLog.php              # scope detectedBetween, dailyTotals()
config/hamapintar.php                    # daftar node + warna, slot jam, rentang default, dll.
resources/views/
├── components/                          # layouts.app, card, stat, button, badge, alert, icon, ...
├── auth/ dashboard/ map/ analytics/     # satu folder per halaman
└── detections/ users/ profile/
resources/js/modules/                    # ui.js, map.js, charts.js
tests/                                   # Unit (analyzer, predictor) + Feature (dashboard, import)
```

UI memakai Tailwind CSS v4 murni (utility class lewat Blade component), tanpa Bootstrap.

## Menjalankan dengan Docker (lokal)

Semua image memakai yang sudah ada di lokal (`pull_policy: never`):
`php:8.5-fpm`, `composer:latest`, `nginx:alpine`, `mysql:8`, `node:22-alpine`, `adminer:5`.

```bash
docker compose up -d --build
```

| Service | URL |
|---|---|
| Aplikasi | http://localhost:8081 |
| Vite dev server (HMR) | http://localhost:5173 |
| Adminer (db: `db`, user `deteksitikus` / `secret`) | http://localhost:8082 |
| MySQL dari host | `127.0.0.1:33061` |

Port bisa diubah lewat env: `APP_PORT`, `VITE_PORT`, `ADMINER_PORT`, `FORWARD_DB_PORT`.

Akun awal (dibuat oleh `php artisan db:seed`, **ganti kata sandinya setelah login**):

| Peran | Email | Kata sandi |
|---|---|---|
| Administrator | `admin@hamapintar.test` | `password` |
| Petugas | `petugas@hamapintar.test` | `password` |

Saat container `app` start, entrypoint otomatis menjalankan `composer install` (jika `vendor/` belum ada),
`key:generate`, menunggu MySQL siap, lalu `migrate`.

Perintah yang sering dipakai:

```bash
docker compose exec app php artisan test        # jalankan test (SQLite in-memory, DB dev aman)
docker compose exec app php artisan db:seed     # isi data demo
docker compose exec app php artisan migrate:fresh
docker compose run --rm vite npm run build      # build aset produksi
docker compose down                             # stop (data MySQL tetap di volume dbdata)
```

> Jika service `vite` berjalan, file `public/hot` dibuat dan halaman memuat aset dari dev server.
> Tanpa `vite`, aset diambil dari `public/build` (hasil `npm run build`).

## Menjalankan tanpa Docker (Windows)

Panduan ini memakai **PowerShell**. Buka lewat Start → ketik *PowerShell*.
Untuk Linux/macOS lihat [bagian paling bawah](#linux--macos).

### Kebutuhan

| Perangkat | Versi | Catatan |
|---|---|---|
| PHP | 8.3 atau lebih baru | ekstensi: `pdo_mysql` (atau `pdo_sqlite`), `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`. `intl` dan `gd` **tidak** diperlukan. |
| Composer | 2.x | |
| Node.js + npm | 20.19+ atau 22.12+ (LTS) | syarat Vite 8 |
| Database | MySQL 8 | atau SQLite untuk mencoba cepat tanpa server database |

### Langkah 1 — Pasang PHP & Composer

**Cara cepat (resmi Laravel, disarankan).** Jalankan di PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Perintah ini memasang PHP 8.5 beserta ekstensi yang dibutuhkan Laravel dan Composer.
**Tutup lalu buka ulang PowerShell** sesudahnya.

<details>
<summary>Cara manual (jika php.new tidak bisa dipakai)</summary>

1. Pasang **Microsoft Visual C++ Redistributable x64** (2015–2022) dari
   <https://learn.microsoft.com/cpp/windows/latest-supported-vc-redist> — tanpa ini PHP gagal jalan dengan pesan
   `VCRUNTIME140.dll` tidak ditemukan.
2. Unduh PHP **8.3+ VS16/VS17 x64 Thread Safe** (zip) dari <https://windows.php.net/download>, ekstrak ke `C:\php`.
3. Tambahkan `C:\php` ke PATH pengguna:
   ```powershell
   [Environment]::SetEnvironmentVariable('Path', [Environment]::GetEnvironmentVariable('Path', 'User') + ';C:\php', 'User')
   ```
4. Buat `php.ini` dan aktifkan ekstensi:
   ```powershell
   Copy-Item C:\php\php.ini-development C:\php\php.ini
   notepad C:\php\php.ini
   ```
   Hapus tanda `;` di depan baris-baris ini, lalu simpan:
   ```ini
   extension_dir = "ext"
   extension=curl
   extension=fileinfo
   extension=mbstring
   extension=openssl
   extension=pdo_mysql
   extension=pdo_sqlite
   extension=zip
   ```
5. Pasang Composer dengan **Composer-Setup.exe** dari <https://getcomposer.org/download/>
   (otomatis mendeteksi `C:\php\php.exe`).
6. Buka PowerShell baru.

> Paket seperti Laragon/XAMPP juga bisa, **asal PHP-nya 8.3 atau lebih baru** — beberapa versi XAMPP masih
> membawa PHP 8.2 sehingga `composer install` gagal.

</details>

### Langkah 2 — Pasang Node.js

Unduh installer **LTS** dari <https://nodejs.org>, atau lewat PowerShell:

```powershell
winget install OpenJS.NodeJS.LTS
```

Lalu buka PowerShell baru.

### Langkah 3 — Pasang MySQL (lewati jika memakai SQLite)

Unduh **MySQL Installer** dari <https://dev.mysql.com/downloads/installer/>, pilih **Server only**,
dan catat kata sandi `root` yang dibuat saat instalasi. MySQL berjalan otomatis sebagai Windows Service.

### Langkah 4 — Cek instalasi

```powershell
php -v                                              # PHP 8.3.x atau lebih baru
php -m | Select-String "pdo_mysql|pdo_sqlite|mbstring|curl"
composer -V                                         # Composer 2.x
node -v; npm -v                                     # v20.19+ / v22.12+
```

Jika `npm` ditolak dengan pesan *running scripts is disabled on this system*, jalankan sekali:

```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```

### Langkah 5 — Pasang dependensi project

```powershell
cd C:\path\ke\deteksitikus-laravel
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
```

### Langkah 6 — Siapkan database

`.env.example` diatur untuk Docker (`DB_HOST=db`), jadi **ubah bagian database di `.env`** (`notepad .env`).
Pilih salah satu:

**A. MySQL.** Buka **MySQL 8.0 Command Line Client** dari Start menu (masukkan kata sandi root), lalu jalankan:

```sql
CREATE DATABASE deteksitikus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'deteksitikus'@'localhost' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON deteksitikus.* TO 'deteksitikus'@'localhost';
```

Di `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=deteksitikus
DB_USERNAME=deteksitikus
DB_PASSWORD=secret
```

**B. SQLite** — tanpa server database, cocok untuk mencoba/demo. Di `.env`:

```dotenv
DB_CONNECTION=sqlite
# beri tanda # di depan DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

lalu buat berkas database-nya:

```powershell
New-Item database\database.sqlite -ItemType File
```

> Jika `DB_DATABASE=deteksitikus` masih aktif saat memakai SQLite, Laravel mencari berkas bernama
> `deteksitikus`, bukan `database\database.sqlite`.

Ubah juga `APP_URL=http://localhost:8000`, lalu buat tabel + akun awal + data demo:

```powershell
php artisan migrate --seed
```

### Langkah 7 — Jalankan

Buka **dua jendela PowerShell** di folder project:

```powershell
php artisan serve        # jendela 1 → http://localhost:8000
```

```powershell
npm run dev              # jendela 2 → Vite (hot reload) di :5173
```

Buka <http://localhost:8000> dan masuk dengan akun awal (lihat tabel **Akun awal** di bagian Docker di atas).

Tanpa Vite dev server (misalnya untuk demo), build aset sekali lalu cukup satu jendela:

```powershell
npm run build
php artisan serve
```

> `composer run dev` (server + queue + Vite dalam satu perintah) juga tersedia; jika tidak berjalan di Windows,
> pakai cara dua jendela di atas.

### Perintah lain

```powershell
php artisan test                  # test memakai SQLite in-memory, database utama tidak tersentuh
vendor\bin\pint                   # rapikan format kode
php artisan migrate:fresh --seed  # reset database + data demo
```

### Masalah umum di Windows

| Gejala | Penyebab & solusi |
|---|---|
| `'php' is not recognized` / `'composer' is not recognized` | PATH belum terbaca → tutup dan buka ulang PowerShell. Jika masih, periksa PATH (lihat langkah 1). |
| `VCRUNTIME140.dll was not found` | Pasang Visual C++ Redistributable x64 (langkah 1, cara manual). |
| `npm.ps1 cannot be loaded because running scripts is disabled` | `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned` |
| `could not find driver` | Ekstensi `pdo_mysql`/`pdo_sqlite` belum aktif. Cek berkas yang dipakai dengan `php --ini`, lalu aktifkan di `php.ini` tersebut. |
| `SQLSTATE[HY000] [2002] No connection could be made` | `DB_HOST` masih `db` (setelan Docker), atau service MySQL belum berjalan (cek di *Services* → `MySQL80`). |
| `'mysql' is not recognized` | Pakai *MySQL 8.0 Command Line Client* dari Start menu, atau tambahkan `C:\Program Files\MySQL\MySQL Server 8.0\bin` ke PATH. |
| Halaman tampil tanpa gaya/peta/grafik setelah Vite ditutup | Berkas `public\hot` tertinggal → hapus berkas itu, atau jalankan `npm run build`. |
| `Vite manifest not found` | Belum `npm run build` dan `npm run dev` tidak berjalan. |
| Port 8000 / 5173 sudah dipakai | `php artisan serve --port=8080`; untuk Vite ubah `server.port` di `vite.config.js`. |
| Waktu "hari ini" terasa meleset | Atur `APP_TIMEZONE` di `.env` (default `Asia/Jakarta`). |

> CSV yang disimpan dari Excel di Windows (akhir baris CRLF, BOM) tetap terbaca, dan dikenali sebagai berkas yang
> sama dengan versi aslinya saat pengecekan unggahan ganda.

## Linux / macOS

<details>
<summary>Instalasi & menjalankan di Linux / macOS</summary>

**PHP + Composer** — cara cepat (resmi Laravel):

```bash
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"   # Linux
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"     # macOS
```

atau lewat paket sistem:

```bash
# Ubuntu 24.04 (Ubuntu 22.04: tambahkan dulu ppa:ondrej/php)
sudo apt install -y php8.3-cli php8.3-mysql php8.3-sqlite3 php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip unzip composer

# macOS (Homebrew)
brew install php composer
```

**Node.js 22** — `nvm install 22` (via <https://github.com/nvm-sh/nvm>) atau `brew install node`.
**MySQL** — `sudo apt install -y mysql-server` atau `brew install mysql && brew services start mysql` (lewati jika SQLite).

**Project** — sama dengan langkah 5–7 Windows, dengan perintah bash:

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
# ubah DB_* di .env (lihat langkah 6); untuk SQLite: touch database/database.sqlite
php artisan migrate --seed
composer run dev          # atau: php artisan serve  +  npm run dev  (dua terminal)
```

</details>
