<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PlayerGameState;
use App\Models\PlayerGameProgress;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlayerGameState>
 */
class PlayerGameStateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'player_game_progress_id' => PlayerGameProgress::factory(),
            'key' => 'cabinet',
            'value' => 'opened',
        ];
    }
}
