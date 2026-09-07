<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use App\Models\PlayerGameProgress;
use App\Models\PlayerInventory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlayerInventory>
 */
class PlayerInventoryFactory extends Factory
{
    protected $model = PlayerInventory::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'player_game_progress_id' => PlayerGameProgress::factory(),
            'item_id' => Item::factory(),
            'quantity' => 1,
        ];
    }
}
