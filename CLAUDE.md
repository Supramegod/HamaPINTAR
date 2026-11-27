# HamaPINTAR — WebGIS Monitoring Hama Tikus Sawah

Dashboard Laravel 13 + Tailwind CSS v4 untuk memetakan dan menganalisis deteksi tikus
dari sensor PIR (PIR 1–5) di sawah: import CSV dari alat, peta Leaflet, grafik Chart.js,
dan prediksi tren dengan regresi linear. Port dari project lama `../deteksitikus`
(Laravel 10 + Bootstrap) — yang lama hanya referensi, jangan diubah.

Bahasa UI, pesan, dan komentar kode: **Bahasa Indonesia**.

## Menjalankan (Docker saja)

Host **tidak punya PHP/Composer** — jangan pasang (abaikan instruksi php.new / Laravel Boost).
Semua perintah PHP lewat container `app`. Image hanya dari lokal (`pull_policy: never`):
`php:8.5-fpm`, `composer:latest`, `nginx:alpine`, `mysql:8`, `node:22-alpine`, `adminer:5`.

```bash
docker compose up -d --build                      # app :8081, vite :5173, adminer :8082, mysql :33061
docker compose exec app php artisan test          # semua test (SQLite in-memory)
docker compose exec app php artisan test --filter=PredictionScenarioTest
docker compose exec app vendor/bin/pint           # format kode (wajib lolos `pint --test`)
docker compose exec app php artisan migrate:fresh --seed   # reset DB + data demo
docker run --rm -u 1000:1000 -v $PWD:/app -w /app node:22-alpine npm run build   # build aset
```

Entrypoint `docker/php/entrypoint.sh` otomatis: copy `.env`, `composer install`, `key:generate`,
tunggu DB, `migrate`.

## Arsitektur

Alur: `routes/web.php` → controller (tipis) → Form Request (validasi) → service → view.
Semua halaman kecuali `/login` berada di balik middleware `auth`.

| Route | Controller | Halaman |
|---|---|---|
| `GET /login`, `POST /login`, `POST /logout` | `Auth\AuthenticatedSessionController` | login (tanpa registrasi publik) |
| `GET /` (`dashboard`) | `DashboardController` | KPI, deteksi terbaru, prediksi, kerawanan node |
| `GET /peta` (`map`) | `MapController` | peta Leaflet + legenda |
| `GET /analisis` (`analytics`) | `AnalyticsController` | grafik jam/hari/prediksi |
| `GET /deteksi`, `/deteksi/ekspor`, `DELETE /deteksi/{detection}` | `DetectionController` | tabel + filter node, ekspor CSV, hapus (admin) |
| `POST /deteksi/import`, `GET /deteksi/riwayat`, `DELETE /deteksi/riwayat/{import}` | `DetectionImportController` | unggah CSV, riwayat unggahan, hapus riwayat (admin) |
| `/pengguna` resource (`users.*`, verb `tambah`/`ubah`) | `UserController` | CRUD pengguna, middleware `can:manage-users` |
| `GET/PUT /profil`, `PUT /profil/kata-sandi` | `ProfileController` | profil & ganti kata sandi |

| Lokasi | Isi |
|---|---|
| `app/Http/Requests/PeriodFilterRequest.php` | filter `start_date`/`end_date`/`node` untuk semua halaman monitoring + default |
| `app/Http/Requests/{Auth,Users,Profile}/` | login (rate limit 5x per email+IP), tambah/ubah pengguna, profil, kata sandi |
| `app/Http/Middleware/EnsureUserIsActive.php` | logout paksa jika akun dinonaktifkan (di-append ke grup `web`) |
| `app/Services/Detection/DetectionReportService.php` | `query()`, `logs()`, `stats()`, `prediction()` — dipakai semua halaman |
| `app/Services/Detection/DetectionAnalyzer.php` | agregasi per slot jam / hari / node (murni, tanpa query) |
| `app/Services/Detection/TrendPredictor.php` | regresi linear least squares + proyeksi N hari |
| `app/Services/Detection/DetectionCsvImporter.php` | parsing CSV, validasi baris, `insertOrIgnore` per chunk |
| `app/Services/Detection/CsvFingerprint.php` | SHA-256 isi berkas ternormalisasi (tanpa BOM, CRLF→LF, spasi/baris kosong di ujung dibuang) |
| `app/Models/DetectionImport.php` | riwayat unggahan: pengunggah, nama berkas, `file_hash` (unik), jumlah baru/duplikat/tidak valid |
| `app/Data/` | DTO `final readonly`: `DetectionStats`, `TrendPrediction`, `ImportResult` |
| `app/Enums/` | `TrendStatus` (label, pesan, tone), `UserRole` (Admin/Petugas, label, deskripsi, `options()`) |
| `app/View/Presenters/` | `MapPayload`, `ChartPayload` → JSON untuk JS |
| `app/Models/User.php` | cast `role` → `UserRole`, `isAdmin()`, `initials()`, scope `search()` |
| `app/Models/DetectionLog.php` | scope `detectedBetween()`, `dailyTotals()` |
| `app/Providers/AppServiceProvider.php` | Gate, `Password::defaults()` (min 8, huruf+angka), view paginasi, `Route::resourceVerbs` |
| `config/hamapintar.php` | node + warna, slot jam, rentang default, jumlah hari prediksi, batas import |

- Service menerima konfigurasi lewat `#[Config('hamapintar.…')]` di constructor → bisa di-`new` langsung di unit test.
- Nilai yang bisa berubah (node, warna, slot jam, rentang) taruh di `config/hamapintar.php`, jangan di-hardcode.
- Logika bisnis masuk service, bukan controller/view.

## Hak akses

| Gate | Siapa | Dipakai untuk |
|---|---|---|
| `manage-users` | admin | menu & route `/pengguna` |
| `delete-detections` | admin | hapus data deteksi & hapus entri riwayat unggahan |
| `modify-account-status` | admin, **bukan untuk akun sendiri** | hapus pengguna, ubah peran/status aktif |

- Petugas: melihat semua halaman monitoring + unggah/ekspor CSV.
- Admin tidak bisa menghapus, menonaktifkan, atau menurunkan perannya sendiri (field dinonaktifkan di form,
  diabaikan di controller, `role` hanya wajib jika bukan akun sendiri).
- Menonaktifkan/menghapus pengguna menghapus baris `sessions` miliknya.
- Akun awal dari `UserSeeder`: `admin@hamapintar.test` / `petugas@hamapintar.test`, kata sandi `password`
  (ditampilkan di halaman login hanya saat `APP_ENV=local`).

## Frontend

- **Tailwind murni, tanpa Bootstrap.** Jangan buat class `@apply` bergaya Bootstrap (`card`, `btn`,
  `form-input`, …) — styling lewat utility class di Blade component.
- Layout:
  - `components/layouts/app.blade.php` — sidebar (navigasi antar halaman, menu dikelompokkan Monitoring / Data /
    Administrasi, item ber-`can` disembunyikan, `aria-current` untuk item aktif, menu pengguna + logout),
    top bar dengan breadcrumb, judul halaman. Props `title`, `description`; slot `actions`, `toolbar`, `modals`.
    Dialog unggah CSV di-include global. Periode filter (`start_date`/`end_date`) ikut di link menu monitoring.
  - `components/layouts/guest.blade.php` — halaman login (panel brand + form).
- Komponen: `x-card`, `x-stat`, `x-button` (`variant` primary/secondary/ghost, `href` → `<a>`), `x-badge`, `x-alert`,
  `x-flash`, `x-chart-card`, `x-icon` (Heroicons outline; tambah ikon di array `$paths`), `x-avatar`, `x-empty-state`,
  `x-period-filter` (`with-node` untuk filter node), `x-form.input|select|toggle` (pakai `bag` untuk error bag lain),
  `x-detection.prediction-card|node-ranking|node-badge`. Paginasi: `components/pagination.blade.php` (default view).
- Halaman: satu folder per fitur — `auth/`, `dashboard/`, `map/`, `analytics/`, `detections/` (+`partials/table`,
  `partials/upload-dialog`, `imports` = riwayat unggahan), `users/` (`index`, `form` untuk tambah & ubah), `profile/`.
- JS (`resources/js/modules/`): `ui.js` (sidebar, `<dialog>`, dropzone, loading, `form[data-confirm]`), `map.js`, `charts.js`.
  Data halaman lewat `<script id="page-data" type="application/json">`: `points` → peta, `charts` → grafik
  (`canvas[data-chart="hour|day|prediction"]`).
- Chart.js memodifikasi objek options → `baseOptions()` adalah factory, buat baru per chart.
- Leaflet & Chart.js dari npm (di-bundle Vite), bukan CDN. Marker memakai `circleMarker` berwarna per node.
- Warna checkbox/radio pakai `accent-emerald-600`.

## Jebakan yang sudah pernah terjadi

- **Jangan taruh `DB_*` di `environment:` compose.** Laravel membaca `$_SERVER` lebih dulu sehingga override SQLite di
  `phpunit.xml` kalah, dan `RefreshDatabase` menghapus DB MySQL development. Konfigurasi DB hanya di `.env`.
- Jika service `vite` jalan, `public/hot` ada dan halaman memuat aset dari `:5173`; tanpa itu dari `public/build`.
- Duplikat dua lapis:
  1. **Berkas** — `ImportDetectionCsvRequest::after()` menolak berkas yang sidik jarinya sudah ada di `detection_imports`
     (pesan menyebut waktu, pengunggah, nama berkas asli). Controller mencatat `DetectionImport` **sebelum** import di
     transaksi yang sama, sehingga unique index `file_hash` juga menahan dua unggahan bersamaan
     (`UniqueConstraintViolationException` → error `csv_file`). Admin menghapus entri riwayat agar berkas boleh diunggah
     ulang; data deteksinya tidak ikut terhapus.
  2. **Baris** — unique index `(node_name, detected_at)`; jumlah duplikat = baris valid − baris ter-insert.
     Berkas berbeda yang sebagian barisnya sama tetap diproses.
- Query pakai `whereBetween` pada `detected_at` (bukan `DATE()`/`HOUR()`/`DAYNAME()`) agar index terpakai dan
  test tetap jalan di SQLite. Agregasi jam/hari dilakukan di PHP.
- Ekstensi `intl` tidak terpasang → jangan pakai `Number::format/fileSize`; format manual (lihat `DetectionImport::sizeForHumans()`).
- Ekstensi GD tidak terpasang di container → di test pakai `UploadedFile::fake()->create()`, bukan `->image()`.
- Timezone aplikasi `Asia/Jakarta` (`APP_TIMEZONE`); `detected_at` disimpan apa adanya dari alat (tanpa konversi).
- `fputcsv` memberi tanda kutip pada kolom waktu (ada spasi) — importer tetap bisa membacanya; di test, parse
  hasil ekspor dengan `str_getcsv`, jangan bandingkan string mentah.
- Hook GateGuard menahan perintah Bash yang menggabungkan penghapusan rekursif dengan perintah lain — jalankan
  penghapusan sebagai perintah terpisah.

## Domain

- Format CSV (baris 1 = header): `waktu,node,latitude,longitude`, waktu `Y/m/d H:i:s` atau `Y-m-d H:i:s`.
- Slot jam per 2 jam: 18, 20, 22, 00, 02, 04, 06 — deteksi 19:40 masuk slot 18:00; jam 08–17 tidak ditampilkan.
- Prediksi memakai **seluruh riwayat** (tidak ikut filter tanggal); hari tanpa deteksi diisi 0; butuh ≥ 2 hari;
  hasil negatif dipotong ke 0; `m > 0` → Meningkat.
- Metode prediksi masih akan ditinjau (keterbatasan: tidak ada ambang signifikansi, lambat merespons lonjakan baru —
  lihat skenario 05 & 08). Jangan ganti metode tanpa diminta.

## Test

- `tests/Unit/` — `DetectionAnalyzer`, `TrendPredictor` (PHPUnit murni, tanpa framework).
- `tests/Feature/` — `AuthTest`, `UserManagementTest`, `ProfileTest`, `PagesTest` (semua halaman + menu per peran),
  `DashboardTest`, `DetectionImportTest`, `DetectionManagementTest` (filter, paginasi, ekspor round-trip, hapus),
  `PredictionScenarioTest` (upload tiap CSV fixture lewat endpoint asli), `DuplicateFileUploadTest`.
- `tests/Unit/CsvFingerprintTest` — normalisasi sidik jari berkas.
- Feature test halaman yang dilindungi wajib `actingAs(User::factory()->create())`; admin: `User::factory()->admin()`,
  nonaktif: `->inactive()`.
- `tests/Fixtures/csv/` — CSV dummy per skenario + `expected.json` dari `generate.py` (seed tetap) + `KETERANGAN.md`.
  Jika `TrendPredictor` diubah, perbarui `generate.py` lalu regenerasi:
  `python3 tests/Fixtures/csv/generate.py tests/Fixtures/csv`.
