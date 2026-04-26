<?php

namespace Database\Factories;

use App\Enums\SubtitleSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subtitle>
 */
class SubtitleFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $track = fake()->unique()->randomNumber(3);

        return [
            'track_id' => $track,
            'language' => 'eng',
            'source_key' => SubtitleSource::EMBEDDED->makeKey($track),
        ];
    }
}
