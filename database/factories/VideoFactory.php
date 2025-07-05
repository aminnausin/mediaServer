<?php

namespace Database\Factories;

use App\Models\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'folder_id' => Folder::factory(),
            'name' => $this->faker->sentence(3),
            'path' => $this->faker->unique()->filePath(),
            'date' => $this->faker->date(),
        ];
    }
}
