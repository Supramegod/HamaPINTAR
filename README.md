# HamaPINTAR — WebGIS Monitoring Hama Tikus Sawah

Versi Laravel 13 + Tailwind CSS 4 dari project `deteksitikus` (tanpa Bootstrap).
Peta memakai Leaflet, grafik memakai Chart.js — keduanya di-bundle lewat Vite.

## Fitur
- Upload CSV data alat (deteksi otomatis data duplikat)
- Filter rentang tanggal
- Peta sebaran titik deteksi (Leaflet + OpenStreetMap)
- Grafik deret waktu: per jam, per hari, per node PIR
- Prediksi tren 3 hari ke depan (regresi linear)

Format CSV (baris pertama = header):

```
waktu,node,latitude,longitude
2026/11/20 18:15:00,PIR 1,-7.25200000,112.79440000
```

Contoh file: `storage/app/sample/contoh-data.csv`.

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

Saat container `app` start, entrypoint otomatis menjalankan `composer install` (jika `vendor/` belum ada),
`key:generate`, menunggu MySQL siap, lalu `migrate`.

Perintah yang sering dipakai:

```bash
docker compose exec app php artisan test        # jalankan test
docker compose exec app php artisan migrate:fresh
docker compose run --rm vite npm run build      # build aset produksi
docker compose down                             # stop (data MySQL tetap di volume dbdata)
```

> Jika service `vite` berjalan, file `public/hot` dibuat dan halaman memuat aset dari dev server.
> Tanpa `vite`, aset diambil dari `public/build` (hasil `npm run build`).
