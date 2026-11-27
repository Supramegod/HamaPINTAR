<?php

namespace App\Services\Detection;

use App\Data\DetectionStats;
use App\Data\TrendPrediction;
use App\Models\DetectionLog;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Titik masuk tunggal untuk data laporan deteksi yang dipakai semua halaman.
 */
final class DetectionReportService
{
    public function __construct(
        private readonly DetectionAnalyzer $analyzer,
        private readonly TrendPredictor $predictor,
    ) {}

    /** @return Builder<DetectionLog> */
    public function query(CarbonImmutable $start, CarbonImmutable $end, ?string $node = null): Builder
    {
        return DetectionLog::query()
            ->detectedBetween($start, $end)
            ->when($node, fn (Builder $q) => $q->where('node_name', $node))
            ->orderByDesc('detected_at');
    }

    /** @return Collection<int, DetectionLog> */
    public function logs(CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return $this->query($start, $end)->get(['id', 'node_name', 'detected_at', 'latitude', 'longitude']);
    }

    public function stats(iterable $logs): DetectionStats
    {
        return $this->analyzer->analyze($logs);
    }

    /** Prediksi memakai seluruh riwayat agar tren tidak bergantung pada filter. */
    public function prediction(): TrendPrediction
    {
        return $this->predictor->predict(DetectionLog::dailyTotals());
    }
}
