<?php

namespace App\View\Presenters;

use App\Data\DetectionStats;
use App\Data\TrendPrediction;
use Carbon\CarbonImmutable;

/** Payload JSON untuk modul grafik (resources/js/modules/charts.js). */
final class ChartPayload
{
    public static function make(DetectionStats $stats, TrendPrediction $prediction): array
    {
        return [
            'charts' => [
                'hour' => self::series($stats->byHour),
                'day' => self::series($stats->byDay),
                'prediction' => self::prediction($prediction),
            ],
        ];
    }

    private static function series(array $data): array
    {
        return ['labels' => array_keys($data), 'values' => array_values($data)];
    }

    private static function prediction(TrendPrediction $prediction): array
    {
        $history = $prediction->history;
        $forecast = $prediction->forecast;
        $historyCount = count($history);

        // Garis prediksi disambung dari titik aktual terakhir agar terlihat kontinu.
        $forecastLine = array_fill(0, max(0, $historyCount - 1), null);
        if ($historyCount > 0) {
            $forecastLine[] = $history[array_key_last($history)];
        }

        return [
            'labels' => array_map(
                fn (string $date) => CarbonImmutable::parse($date)->translatedFormat('d M'),
                [...array_keys($history), ...array_keys($forecast)],
            ),
            'actual' => [...array_values($history), ...array_fill(0, count($forecast), null)],
            'forecast' => [...$forecastLine, ...array_values($forecast)],
        ];
    }
}
