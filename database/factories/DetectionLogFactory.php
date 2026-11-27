<?php

namespace Database\Factories;

use App\Models\DetectionLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetectionLog>
 */
class DetectionLogFactory extends Factory
{
    public function definition(): array
    {
        // Tikus aktif malam hari: jam 18:00 - 06:59
        $hour = fake()->randomElement([18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6]);

        return [
            'node_name' => fake()->randomElement(array_keys(config('hamapintar.nodes'))),
            'detected_at' => now()->subDays(fake()->numberBetween(0, 29))->setTime($hour, fake()->numberBetween(0, 59), fake()->numberBetween(0, 59)),
            'latitude' => fake()->randomFloat(8, -7.2530, -7.2510),
            'longitude' => fake()->randomFloat(8, 112.7934, 112.7954),
        ];
    }
}
