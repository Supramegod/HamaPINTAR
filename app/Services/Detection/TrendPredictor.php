<?php

namespace App\Services\Detection;

use App\Data\TrendPrediction;
use App\Enums\TrendStatus;
use Carbon\CarbonImmutable;
use Illuminate\Container\Attributes\Config;

/**
 * Prediksi tren serangan dengan regresi linear sederhana (least squares)
 * pada deret total deteksi harian: y = m·x + c.
 */
final class TrendPredictor
{
    public function __construct(
        #[Config('hamapintar.prediction_days')] private readonly int $days = 3,
        #[Config('hamapintar.prediction_history_days')] private readonly int $historyDays = 7,
    ) {}

    /**
     * @param  iterable<string, int>  $dailyTotals  ['Y-m-d' => total], urut menaik
     */
    public function predict(iterable $dailyTotals): TrendPrediction
    {
        $series = $this->fillGaps($dailyTotals);

        if (count($series) < 2) {
            return new TrendPrediction(TrendStatus::Insufficient, 0.0, $series, $this->emptyForecast($series));
        }

        [$m, $c] = $this->fit(array_values($series));

        $n = count($series);
        $lastDate = CarbonImmutable::parse(array_key_last($series));
        $forecast = [];
        for ($k = 1; $k <= $this->days; $k++) {
            $value = (int) round($m * ($n + $k) + $c);
            $forecast[$lastDate->addDays($k)->toDateString()] = max(0, $value); // tidak boleh minus
        }

        return new TrendPrediction(
            status: $m > 0 ? TrendStatus::Rising : TrendStatus::Stable,
            slope: $m,
            history: array_slice($series, -$this->historyDays, preserve_keys: true),
            forecast: $forecast,
        );
    }

    /**
     * Least squares, x = 1..n.
     *
     * @param  list<int>  $y
     * @return array{0: float, 1: float} [gradien m, konstanta c]
     */
    private function fit(array $y): array
    {
        $n = count($y);
        $sumX = $sumY = $sumXY = $sumX2 = 0;

        foreach ($y as $i => $value) {
            $x = $i + 1;
            $sumX += $x;
            $sumY += $value;
            $sumXY += $x * $value;
            $sumX2 += $x * $x;
        }

        $denominator = ($n * $sumX2) - ($sumX ** 2);
        $m = $denominator != 0 ? (($n * $sumXY) - ($sumX * $sumY)) / $denominator : 0.0;
        $c = ($sumY - $m * $sumX) / $n;

        return [(float) $m, (float) $c];
    }

    /**
     * Hari tanpa deteksi dihitung 0 agar sumbu x mewakili waktu yang sebenarnya.
     *
     * @return array<string, int>
     */
    private function fillGaps(iterable $dailyTotals): array
    {
        $totals = [];
        foreach ($dailyTotals as $date => $total) {
            $totals[(string) $date] = (int) $total;
        }

        if (count($totals) < 2) {
            return $totals;
        }

        $series = [];
        $cursor = CarbonImmutable::parse(array_key_first($totals));
        $end = CarbonImmutable::parse(array_key_last($totals));
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $series[$key] = $totals[$key] ?? 0;
            $cursor = $cursor->addDay();
        }

        return $series;
    }

    /** @return array<string, int> */
    private function emptyForecast(array $series): array
    {
        $from = $series === [] ? CarbonImmutable::today() : CarbonImmutable::parse(array_key_last($series));
        $forecast = [];
        for ($k = 1; $k <= $this->days; $k++) {
            $forecast[$from->addDays($k)->toDateString()] = 0;
        }

        return $forecast;
    }
}
