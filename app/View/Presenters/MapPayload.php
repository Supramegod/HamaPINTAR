<?php

namespace App\View\Presenters;

use App\Models\DetectionLog;

/** Payload JSON untuk modul peta (resources/js/modules/map.js). */
final class MapPayload
{
    /** @param  iterable<DetectionLog>  $logs */
    public static function make(iterable $logs): array
    {
        $points = [];
        foreach ($logs as $log) {
            $points[] = [
                'node' => $log->node_name,
                'lat' => $log->latitude,
                'lng' => $log->longitude,
                'at' => $log->detected_at->translatedFormat('d M Y H:i'),
            ];
        }

        return [
            'map' => config('hamapintar.map'),
            'nodes' => config('hamapintar.nodes'),
            'points' => $points,
        ];
    }
}
