<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MysteryCase;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clue>
 */
class ClueFactory extends Factory
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
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement([
                'document',
                'evidence',
                'statement',
                'location',
            ]),
            'is_revealed' => false,
        ];
    }

    public function revealed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_revealed' => true,
        ]);
    }
}
