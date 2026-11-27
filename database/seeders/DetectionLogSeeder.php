<?php

namespace Database\Seeders;

use App\Models\DetectionLog;
use Illuminate\Database\Seeder;

/** Data demo: 5 node PIR dengan posisi tetap, deteksi 30 hari terakhir dengan tren naik. */
class DetectionLogSeeder extends Seeder
{
    private const POSITIONS = [
        'PIR 1' => [-7.25180, 112.79420],
        'PIR 2' => [-7.25195, 112.79465],
        'PIR 3' => [-7.25215, 112.79440],
        'PIR 4' => [-7.25230, 112.79410],
        'PIR 5' => [-7.25205, 112.79485],
    ];

    public function run(): void
    {
        $rows = [];

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $count = random_int(1, 4) + intdiv(30 - $daysAgo, 6); // makin baru makin banyak
            for ($i = 0; $i < $count; $i++) {
                $node = array_rand(self::POSITIONS);
                [$lat, $lng] = self::POSITIONS[$node];
                $hour = [18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5][array_rand(range(0, 11))];

                $rows[] = [
                    'node_name' => $node,
                    'detected_at' => now()->subDays($daysAgo)->setTime($hour, random_int(0, 59), random_int(0, 59))->format('Y-m-d H:i:s'),
                    'latitude' => $lat + random_int(-8, 8) / 100000,
                    'longitude' => $lng + random_int(-8, 8) / 100000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DetectionLog::query()->insertOrIgnore($rows);
    }
}
