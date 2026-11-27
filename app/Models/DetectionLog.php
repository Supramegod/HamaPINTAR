<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $node_name
 * @property Carbon $detected_at
 * @property float $latitude
 * @property float $longitude
 */
class DetectionLog extends Model
{
    use HasFactory;

    protected $fillable = ['node_name', 'detected_at', 'latitude', 'longitude'];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /** Filter berdasarkan rentang tanggal (inklusif, memakai index detected_at). */
    #[Scope]
    protected function detectedBetween(Builder $query, CarbonInterface $start, CarbonInterface $end): void
    {
        $query->whereBetween('detected_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
    }

    /**
     * Total deteksi per tanggal untuk seluruh riwayat, urut menaik.
     *
     * @return Collection<string, int> ['Y-m-d' => total]
     */
    public static function dailyTotals(): Collection
    {
        return static::query()
            ->selectRaw('DATE(detected_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->map(fn ($total) => (int) $total);
    }
}
