# CSV Dummy untuk Uji Metode Prediksi

Setiap file mewakili satu skenario dengan hasil yang sudah diketahui. Nilai
"diharapkan" dihitung terpisah oleh `generate.py` (Python), lalu dicocokkan
dengan implementasi PHP oleh `tests/Feature/PredictionScenarioTest.php`.

> Prediksi memakai **seluruh riwayat** di database. Untuk uji manual lewat
> dashboard, kosongkan dulu datanya (`php artisan migrate:fresh`) lalu unggah
> **satu file saja**.

| File | Baris | Pola total harian | Status | Gradien m | Prediksi 3 hari |
|---|---:|---|---|---:|---|
| `01-linear-sempurna.csv` | 30 | 2, 4, 6, 8, 10 (21–25 Nov) | Meningkat | 2.000 | 12, 14, 16 |
| `02-tren-naik.csv` | 164 | 2 → 15, naik bertahap (6–25 Nov) | Meningkat | 0.672 | 15, 16, 17 |
| `03-tren-turun.csv` | 173 | 15 → 2, turun bertahap | Menurun/Stabil | −0.668 | 2, 1, 0 |
| `04-stabil.csv` | 100 | 5 per hari selama 20 hari | Menurun/Stabil | 0.000 | 5, 5, 5 |
| `05-fluktuatif.csv` | 116 | naik-turun 2–10 tanpa arah jelas | Meningkat | 0.032 | 6, 6, 6 |
| `06-data-kurang.csv` | 6 | hanya 1 hari (25 Nov) | Data Kurang | – | 0, 0, 0 |
| `07-ada-hari-kosong.csv` | 32 | 4, 0, 0, 5, 0, 6, 0, 0, 8, 9 | Meningkat | 0.558 | 6, 7, 7 |
| `08-lonjakan-terakhir.csv` | 96 | 3 per hari, lalu 12, 15, 18 | Meningkat | 0.469 | 10, 10, 11 |
| `09-duplikat-dan-rusak.csv` | 10 | uji importer | – | – | 4 tersimpan, 1 duplikat, 5 rusak (baris 6–10) |

## Yang diuji tiap skenario

- **01** – cek rumus: titik-titik tepat segaris, jadi m = 2, c = 0 dan prediksi bisa dihitung di kepala.
- **02 / 03** – arah tren naik vs turun, termasuk hasil negatif yang dipotong jadi 0 (03 → hari ke-3 = 0).
- **04** – data datar: m = 0 → status "Menurun/Stabil".
- **05** – data acak tanpa tren: m hampir 0 tapi positif, jadi tetap berstatus "Meningkat".
  Ini menunjukkan keterbatasan metode — tidak ada ambang batas "tidak signifikan".
- **06** – kurang dari 2 hari data, prediksi tidak dihitung.
- **07** – hari tanpa deteksi diisi 0 (tanpa itu, m akan jauh lebih besar).
- **08** – lonjakan mendadak di 3 hari terakhir hanya terbaca sebagai kenaikan kecil (10–11, padahal
  data terakhir 18). Ini menunjukkan regresi linear seluruh riwayat lambat merespons perubahan baru.
- **09** – importer: duplikat dalam file, format tanggal `-` dan `/`, waktu/latitude/node tidak valid,
  koordinat di luar jangkauan, kolom kurang.

## Membuat ulang

```bash
python3 tests/Fixtures/csv/generate.py tests/Fixtures/csv
```

Generator memakai seed tetap, jadi isi file dan `expected.json` selalu sama.
