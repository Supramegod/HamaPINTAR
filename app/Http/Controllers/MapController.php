<?php

namespace App\Http\Controllers;

use App\Models\DetectionLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $logs = DetectionLog::whereBetween(DB::raw('DATE(detected_at)'), [$startDate, $endDate])->get();
        $totalSerangan = $logs->count();

        // 1. ANALISIS DERET WAKTU: Jam
        $hoursMapping = [18 => 0, 20 => 0, 22 => 0, 0 => 0, 2 => 0, 4 => 0, 6 => 0];
        foreach ($logs as $log) {
            $hour = Carbon::parse($log->detected_at)->hour;
            if (array_key_exists($hour, $hoursMapping)) {
                $hoursMapping[$hour]++;
            }
        }

        // 2. ANALISIS DERET WAKTU: Hari
        $daysMapping = ['Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0, 'Sunday' => 0];
        foreach ($logs as $log) {
            $daysMapping[Carbon::parse($log->detected_at)->format('l')]++;
        }

        // 3. ANALISIS POSISI: Node
        $nodesMapping = ['PIR 1' => 0, 'PIR 2' => 0, 'PIR 3' => 0, 'PIR 4' => 0, 'PIR 5' => 0];
        foreach ($logs as $log) {
            if (array_key_exists($log->node_name, $nodesMapping)) {
                $nodesMapping[$log->node_name]++;
            }
        }

        // =========================================================================
        // ALGORITMA PREDIKSI TREN KEDEPAN (REGRESI LINEAR DERET WAKTU)
        // =========================================================================
        $historicalTrend = DetectionLog::select(DB::raw('DATE(detected_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $predictiveLabels = [];
        $predictiveValues = [];
        $statusPrediksi = 'Membutuhkan lebih banyak variasi data log untuk memproyeksikan tren.';

        if ($historicalTrend->count() >= 2) {
            $x = [];
            $y = [];
            $i = 1;
            foreach ($historicalTrend as $trend) {
                $x[] = $i;
                $y[] = (int) $trend->total;
                $i++;
            }

            $n = count($x);
            $sumX = array_sum($x);
            $sumY = array_sum($y);

            $sumXY = 0;
            $sumX2 = 0;
            for ($j = 0; $j < $n; $j++) {
                $sumXY += ($x[$j] * $y[$j]);
                $sumX2 += ($x[$j] * $x[$j]);
            }

            // Rumus mencari gradien tren (m) dan konstanta (c) -> y = mx + c
            $denominator = ($n * $sumX2) - ($sumX * $sumX);
            $m = ($denominator != 0) ? (($n * $sumXY) - ($sumX * $sumY)) / $denominator : 0;
            $c = ($n != 0) ? ($sumY - ($m * $sumX)) / $n : 0;

            // Proyeksi 3 hari ke depan
            $lastDate = Carbon::parse($historicalTrend->last()->date);
            for ($k = 1; $k <= 3; $k++) {
                $nextX = $n + $k;
                $predictedVal = max(0, (int) round(($m * $nextX) + $c)); // Tidak boleh ada total minus

                $predictiveLabels[] = $lastDate->copy()->addDays($k)->translatedFormat('d M');
                $predictiveValues[] = $predictedVal;
            }

            // Analisis interpretasi teks untuk sidang
            if ($m > 0) {
                $statusPrediksi = 'PERINGATAN WANING: Tren grafik menunjukkan kurva MENINGKAT sebesar '.round($m, 2).' poin per hari. Disarankan memperketat jebakan di area titik merah.';
            } else {
                $statusPrediksi = 'INFO KONDUSIF: Tren kurva menurun/stabil. Skema mitigasi di area persawahan berjalan efektif.';
            }
        } else {
            // Default jika data kosong / kurang
            $predictiveLabels = ['Besok', 'Lusa', '3 Hari Lagi'];
            $predictiveValues = [0, 0, 0];
        }

        return view('map', [
            'logs' => $logs,
            'totalSerangan' => $totalSerangan,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'chartJamData' => array_values($hoursMapping),
            'chartHariData' => array_values($daysMapping),
            'chartNodeData' => array_values($nodesMapping),
            'predictiveLabels' => $predictiveLabels,
            'predictiveValues' => $predictiveValues,
            'statusPrediksi' => $statusPrediksi,
        ]);
    }

    public function uploadCsv(Request $request): RedirectResponse
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        fgetcsv($handle, null, ',', '"', '\\'); // lewati baris header

        $dataBaruDibuat = 0;
        $dataDuplikatDilewati = 0;

        while (($row = fgetcsv($handle, 1000, ',', '"', '\\')) !== false) {
            if (! empty($row[0])) {
                $formattedDateTime = str_replace('/', '-', $row[0]);
                $nodeName = $row[1];

                $sudahAda = DetectionLog::where('detected_at', $formattedDateTime)
                    ->where('node_name', $nodeName)->exists();

                if (! $sudahAda) {
                    DetectionLog::create([
                        'node_name' => $nodeName,
                        'detected_at' => $formattedDateTime,
                        'latitude' => $row[2],
                        'longitude' => $row[3],
                    ]);
                    $dataBaruDibuat++;
                } else {
                    $dataDuplikatDilewati++;
                }
            }
        }
        fclose($handle);

        $pesan = 'Proses selesai. '.$dataBaruDibuat.' data baru berhasil disimpan.';
        if ($dataDuplikatDilewati > 0) {
            $pesan .= ' ('.$dataDuplikatDilewati.' data duplikat otomatis dilewati).';
        }

        return redirect('/')->with('success', $pesan);
    }
}
