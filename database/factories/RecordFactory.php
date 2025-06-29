<?php

namespace Database\Factories;

use App\Models\Metadata;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Record>
 */
class RecordFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->sentence(2),
            'created_at' => $this->faker->dateTimeBetween('-14 days', 'now'),
            'updated_at' => now(),
            'metadata_id' => Metadata::factory(),
        ];
    }
}
