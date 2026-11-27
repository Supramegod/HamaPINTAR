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
        return [
            'node_name' => 'PIR '.fake()->numberBetween(1, 5),
            'detected_at' => fake()->dateTimeBetween('-7 days')->format('Y-m-d H:i:s'),
            'latitude' => fake()->randomFloat(8, -7.2530, -7.2510),
            'longitude' => fake()->randomFloat(8, 112.7934, 112.7954),
        ];
    }
}
