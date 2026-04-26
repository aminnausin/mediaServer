<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Metadata>
 */
class MetadataFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'composite_id' => "{$this->faker->word}/{$this->faker->word}",
            'title' => $this->faker->sentence(3),
            'description' => 'Fake Artist - something',
            'album' => $this->faker->word,
            'duration' => $this->faker->numberBetween(60, 500),
            'uuid' => Str::uuid()->toString(),
        ];
    }
}
