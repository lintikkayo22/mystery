<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MysteryCase;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evidence>
 */
class EvidenceFactory extends Factory
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
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement([
                'image',
                'video',
                'audio',
                'document',
            ]),
            'file_path' => null,
            'is_revealed' => false,
        ];
    }

    public function revealed(): static
    {
        return $this->state([
            'is_revealed' => true,
        ]);
    }
}
