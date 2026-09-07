<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Item;
use App\Models\User;
use App\Models\MysteryCase;
use App\Services\PlayerInventoryService;
use App\Models\PlayerGameProgress;
use App\Models\PlayerInventory;

class PlayerInventoryServiceTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_add_item_creates_inventory_record_when_player_does_not_have_item(): void
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

        $service = new PlayerInventoryService();

        $service->addItem($progress, $item);

        $this->assertDatabaseHas('player_inventories', [
            'player_game_progress_id' => $progress->id,
            'item_id' => $item->id,
            'quantity' => 1,
        ]);
    }

    public function test_add_item_increases_quantity_when_player_already_has_item(): void
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

        $service = new PlayerInventoryService();

        $service->addItem($progress, $item);

        $this->assertDatabaseHas('player_inventories', [
            'player_game_progress_id' => $progress->id,
            'item_id' => $item->id,
            'quantity' => 2,
        ]);
    }

    public function test_add_item_can_add_multiple_quantity(): void
    {
        $user = User::factory()->create();
        $case = MysteryCase::factory()->create();

        $progress = $user->gameProgress()->create([
            'mystery_case_id' => $case->id,
        ]);

        $item = Item::create([
            'name' => 'Candle',
            'description' => 'An old candle.',
            'type' => 'tool',
        ]);

        $service = new PlayerInventoryService();

        $service->addItem($progress, $item, 3);

        $this->assertDatabaseHas('player_inventories', [
            'player_game_progress_id' => $progress->id,
            'item_id' => $item->id,
            'quantity' => 3,
        ]);
    }

    public function test_remove_item_decreases_quantity(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $item = Item::factory()->create();

        $inventory = PlayerInventory::factory()->create([
            'player_game_progress_id' => $progress->id,
            'item_id' => $item->id,
            'quantity' => 3,
        ]);

        $service = new PlayerInventoryService();

        $service->removeItem($progress, $item);

        $this->assertDatabaseHas('player_inventories', [
            'id' => $inventory->id,
            'quantity' => 2,
        ]);
    }

    public function test_remove_item_decreases_multiple_quantity(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $item = Item::factory()->create();

        $inventory = PlayerInventory::factory()->create([
            'player_game_progress_id' => $progress->id,
            'item_id' => $item->id,
            'quantity' => 5,
        ]);

        $service = new PlayerInventoryService();

        $service->removeItem($progress, $item, 2);

        $this->assertDatabaseHas('player_inventories', [
            'id' => $inventory->id,
            'quantity' => 3,
        ]);
    }

    public function test_remove_item_does_nothing_when_quantity_is_not_enough(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $item = Item::factory()->create();

        $inventory = PlayerInventory::factory()->create([
            'player_game_progress_id' => $progress->id,
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $service = new PlayerInventoryService();

        $service->removeItem($progress, $item, 2);

        $this->assertDatabaseHas('player_inventories', [
            'id' => $inventory->id,
            'quantity' => 1,
        ]);
    }

}
