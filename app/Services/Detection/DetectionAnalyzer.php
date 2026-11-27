<?php

namespace App\Services\Detection;

use App\Data\DetectionStats;
use Carbon\CarbonInterface;
use Illuminate\Container\Attributes\Config;

/**
 * Mengelompokkan log deteksi menjadi deret jam, hari, dan node.
 * Murni (tanpa query) sehingga mudah diuji.
 */
final class DetectionAnalyzer
{
    private const DAY_LABELS = [
        1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min',
    ];

    /**
     * @param  array<string, string>  $nodes  nama node => warna
     * @param  list<int>  $hourSlots
     */
    public function __construct(
        #[Config('hamapintar.nodes')] private readonly array $nodes,
        #[Config('hamapintar.hour_slots')] private readonly array $hourSlots,
    ) {}

    /**
     * @param  iterable<object{node_name: string, detected_at: CarbonInterface}>  $logs
     */
    public function analyze(iterable $logs): DetectionStats
    {
        $byHour = array_fill_keys($this->hourSlots, 0);
        $byDay = array_fill_keys(array_values(self::DAY_LABELS), 0);
        $byNode = array_fill_keys(array_keys($this->nodes), 0);
        $total = 0;

        foreach ($logs as $log) {
            $total++;
            $at = $log->detected_at;

            $slot = $at->hour - ($at->hour % 2); // bucket 2 jam
            if (array_key_exists($slot, $byHour)) {
                $byHour[$slot]++;
            }

            $byDay[self::DAY_LABELS[$at->isoWeekday()]]++;

            if (array_key_exists($log->node_name, $byNode)) {
                $byNode[$log->node_name]++;
            }
        }

        $hourLabels = array_map(fn (int $h) => sprintf('%02d:00', $h), array_keys($byHour));

        return new DetectionStats(
            total: $total,
            byHour: array_combine($hourLabels, array_values($byHour)),
            byDay: $byDay,
            byNode: $byNode,
        );
    }
}
