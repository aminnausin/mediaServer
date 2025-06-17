<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Folder>
 */
class FolderFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->sentence(2),
            'path' => $this->faker->unique()->word(), // or a real-looking folder path
        ];
    }
}
