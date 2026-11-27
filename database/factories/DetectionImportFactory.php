<?php

namespace Database\Factories;

use App\Models\DetectionImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetectionImport>
 */
class DetectionImportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'original_name' => fake()->slug(2).'.csv',
            'file_size' => fake()->numberBetween(500, 50_000),
            'file_hash' => hash('sha256', fake()->unique()->uuid()),
            'created_count' => fake()->numberBetween(0, 200),
            'duplicate_count' => fake()->numberBetween(0, 20),
            'invalid_count' => 0,
        ];
    }
}
