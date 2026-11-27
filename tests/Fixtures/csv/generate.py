"""Generator CSV dummy untuk menguji metode regresi linear HamaPINTAR.
Deterministik (seed tetap) supaya hasil yang diharapkan bisa dikunci di test."""
import csv, json, math, random, sys
from datetime import date, datetime, timedelta
from pathlib import Path

OUT = Path(sys.argv[1]); OUT.mkdir(parents=True, exist_ok=True)
NODES = {'PIR 1': (-7.25180, 112.79420), 'PIR 2': (-7.25195, 112.79465), 'PIR 3': (-7.25215, 112.79440),
         'PIR 4': (-7.25230, 112.79410), 'PIR 5': (-7.25205, 112.79485)}
NIGHT_HOURS = [18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5]

def php_round(x):  # PHP round(): half away from zero
    return int(math.floor(abs(x) + 0.5)) * (1 if x >= 0 else -1)

def rows_for(day, count, rng, used):
    rows = []
    while len(rows) < count:
        node = rng.choice(list(NODES)); h = rng.choice(NIGHT_HOURS)
        ts = datetime(day.year, day.month, day.day, h, rng.randint(0, 59), rng.randint(0, 59))
        if (node, ts) in used: continue
        used.add((node, ts)); lat, lng = NODES[node]
        rows.append([ts.strftime('%Y/%m/%d %H:%M:%S'), node, f'{lat + rng.randint(-8, 8) / 1e5:.8f}', f'{lng + rng.randint(-8, 8) / 1e5:.8f}'])
    return rows

def expected(series):  # series: list of (date, total) sudah diisi nol
    n = len(series)
    if n < 2:
        last = series[-1][0] if series else None
        return {'status': 'insufficient', 'slope': 0.0, 'intercept': 0.0,
                'forecast': {str(last + timedelta(days=k)): 0 for k in (1, 2, 3)} if last else {}}
    ys = [t for _, t in series]; xs = list(range(1, n + 1))
    sx, sy = sum(xs), sum(ys); sxy = sum(x * y for x, y in zip(xs, ys)); sx2 = sum(x * x for x in xs)
    den = n * sx2 - sx ** 2; m = (n * sxy - sx * sy) / den if den else 0.0; c = (sy - m * sx) / n
    last = series[-1][0]
    return {'status': 'rising' if m > 0 else 'stable', 'slope': round(m, 6), 'intercept': round(c, 6),
            'forecast': {str(last + timedelta(days=k)): max(0, php_round(m * (n + k) + c)) for k in (1, 2, 3)}}

def scenario(name, start, counts, seed):
    """counts: daftar total per hari mulai `start`; 0 = hari tanpa deteksi (tidak ada baris)."""
    rng = random.Random(seed); used = set(); rows = []
    days = [start + timedelta(days=i) for i in range(len(counts))]
    for d, c in zip(days, counts): rows += rows_for(d, c, rng, used)
    rng.shuffle(rows)  # urutan acak seperti log alat sungguhan
    write(name, rows)
    # trim hari kosong di ujung (DB hanya tahu hari yang punya data)
    series = [(d, c) for d, c in zip(days, counts)]
    while series and series[0][1] == 0: series.pop(0)
    while series and series[-1][1] == 0: series.pop()
    return {'file': name, 'rows': len(rows), 'daily': {str(d): c for d, c in series}, **expected(series)}

def write(name, rows):
    with open(OUT / name, 'w', newline='') as f:
        w = csv.writer(f, lineterminator='\n'); w.writerow(['waktu', 'node', 'latitude', 'longitude']); w.writerows(rows)

S = date(2026, 11, 6)
results = [
    scenario('01-linear-sempurna.csv', date(2026, 11, 21), [2, 4, 6, 8, 10], 1),
    scenario('02-tren-naik.csv', S, [2, 3, 3, 4, 5, 4, 6, 7, 6, 8, 9, 8, 10, 11, 12, 11, 13, 14, 13, 15], 2),
    scenario('03-tren-turun.csv', S, [15, 14, 14, 13, 12, 12, 11, 10, 10, 9, 8, 8, 7, 6, 6, 5, 4, 4, 3, 2], 3),
    scenario('04-stabil.csv', S, [5] * 20, 4),
    scenario('05-fluktuatif.csv', S, [3, 9, 2, 8, 4, 10, 3, 7, 2, 9, 5, 8, 3, 10, 2, 8, 4, 9, 3, 7], 5),
    scenario('06-data-kurang.csv', date(2026, 11, 25), [6], 6),
    scenario('07-ada-hari-kosong.csv', date(2026, 11, 16), [4, 0, 0, 5, 0, 6, 0, 0, 8, 9], 7),
    scenario('08-lonjakan-terakhir.csv', S, [3] * 17 + [12, 15, 18], 8),
]

# 09: uji importer — valid + duplikat + baris rusak (urutan baris tetap, nomor baris diketahui)
imp = [
    ['2026/11/24 19:10:00', 'PIR 1', '-7.25180000', '112.79420000'],  # baris 2 valid
    ['2026/11/24 21:30:00', 'PIR 2', '-7.25195000', '112.79465000'],  # baris 3 valid
    ['2026-11-25 02:15:00', 'PIR 3', '-7.25215000', '112.79440000'],  # baris 4 valid (format tanggal pakai -)
    ['2026/11/24 19:10:00', 'PIR 1', '-7.25180000', '112.79420000'],  # baris 5 duplikat baris 2
    ['bukan-tanggal', 'PIR 4', '-7.25230000', '112.79410000'],        # baris 6 rusak: waktu
    ['2026/11/25 03:00:00', 'PIR 4', 'abc', '112.79410000'],          # baris 7 rusak: latitude
    ['2026/11/25 03:30:00', '', '-7.25230000', '112.79410000'],       # baris 8 rusak: node kosong
    ['2026/11/25 04:00:00', 'PIR 5', '-95.00000000', '112.79485000'], # baris 9 rusak: lat di luar -90..90
    ['2026/11/25 04:45:00', 'PIR 5'],                                 # baris 10 rusak: kolom kurang
    ['2026/11/25 05:20:00', 'PIR 5', '-7.25205000', '112.79485000'],  # baris 11 valid
]
write('09-duplikat-dan-rusak.csv', imp)
results.append({'file': '09-duplikat-dan-rusak.csv', 'rows': len(imp), 'import': {'created': 4, 'duplicates': 1, 'invalid_lines': [6, 7, 8, 9, 10]}})

(OUT / 'expected.json').write_text(json.dumps(results, indent=2, ensure_ascii=False))
for r in results:
    print(r['file'], r['rows'], r.get('status', '-'), r.get('slope', ''), list(r.get('forecast', {}).values()) or r.get('import'))
