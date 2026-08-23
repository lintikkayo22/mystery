<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chapter;
use App\Models\MysteryCase;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chapter>
 */
class ChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mystery_case_id' => MysteryCase::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'order' => 1,
            'status' => 'draft',
        ];
    }
}
