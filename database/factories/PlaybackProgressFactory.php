<?php

namespace Database\Factories;

use App\Models\Metadata;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlaybackProgress>
 */
class PlaybackProgressFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'metadata_id' => Metadata::factory(),
            'record_id' => null,
            'progress_offset' => $this->faker->numberBetween(0, 100),
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'completion_count' => 0,
            'last_completed_at' => null,
        ];
    }
}
