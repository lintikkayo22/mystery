<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Interactable;
use App\Models\Scene;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interactable>
 */
class InteractableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scene_id' => Scene::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->paragraph(),

            'type' => fake()->randomElement([
                'object',
                'door',
                'container',
                'decoration',
            ]),

            'position_x' => fake()->randomFloat(2, 0, 100),
            'position_y' => fake()->randomFloat(2, 0, 100),

            'width' => fake()->randomFloat(2, 1, 30),
            'height' => fake()->randomFloat(2, 1, 30),

            'status' => 'draft',
        ];
    }
}
