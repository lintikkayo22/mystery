<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Interaction;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InteractionCondition>
 */
class InteractionConditionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interaction_id' => Interaction::factory(),
            'type' => 'HAS_ITEM',
            'value' => '1',
        ];
    }
}
