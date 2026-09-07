<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Item;
use App\Models\User;
use App\Models\MysteryCase;

class PlayerInventoryTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_item_can_be_created(): void
    {
        $item = Item::create([
            'name' => 'Old Key',
            'description' => 'An old rusty key.',
            'type' => 'key',
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Old Key',
            'type' => 'key',
        ]);
    }

    public function test_player_can_have_an_item_in_inventory(): void
    {
        $user = User::factory()->create();
        $case = MysteryCase::factory()->create();

        $progress = $user->gameProgress()->create([
            'mystery_case_id' => $case->id,
        ]);

        $item = Item::create([
            'name' => 'Old Key',
            'description' => 'An old rusty key.',
            'type' => 'key',
        ]);

        $inventory = $progress->inventory()->create([
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('player_inventories', [
            'id' => $inventory->id,
            'player_game_progress_id' => $progress->id,
            'item_id' => $item->id,
            'quantity' => 1,
        ]);
    }

    public function test_player_cannot_have_duplicate_item_in_inventory(): void
    {
        $user = User::factory()->create();
        $case = MysteryCase::factory()->create();

        $progress = $user->gameProgress()->create([
            'mystery_case_id' => $case->id,
        ]);

        $item = Item::create([
            'name' => 'Old Key',
            'description' => 'An old rusty key.',
            'type' => 'key',
        ]);

        $progress->inventory()->create([
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $progress->inventory()->create([
            'item_id' => $item->id,
            'quantity' => 1,
        ]);
    }

    public function test_adding_same_item_increases_quantity(): void
    {
        $user = User::factory()->create();
        $case = MysteryCase::factory()->create();

        $progress = $user->gameProgress()->create([
            'mystery_case_id' => $case->id,
        ]);

        $item = Item::create([
            'name' => 'Old Key',
            'description' => 'An old rusty key.',
            'type' => 'key',
        ]);

        $inventory = $progress->inventory()->create([
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $inventory->increment('quantity');

        $this->assertDatabaseHas('player_inventories', [
            'id' => $inventory->id,
            'item_id' => $item->id,
            'quantity' => 2,
        ]);
    }

    public function test_inventory_item_belongs_to_item(): void
    {
        $item = Item::create([
            'name' => 'Old Key',
            'description' => 'An old rusty key.',
            'type' => 'key',
        ]);

        $user = User::factory()->create();
        $case = MysteryCase::factory()->create();

        $progress = $user->gameProgress()->create([
            'mystery_case_id' => $case->id,
        ]);

        $inventory = $progress->inventory()->create([
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $this->assertTrue($inventory->item->is($item));
    }

}
