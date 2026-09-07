<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MysteryCase;
use App\Models\PlayerGameProgress;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlayerGameProgress>
 */
class PlayerGameProgressFactory extends Factory
{
    protected $model = PlayerGameProgress::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mystery_case_id' => MysteryCase::factory(),
            'current_chapter_id' => null,
            'current_scene_id' => null,
        ];
    }
}
