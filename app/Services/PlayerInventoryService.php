<?php

namespace App\Services;

use App\Models\Item;
use App\Models\PlayerGameProgress;
use App\Models\PlayerInventory;

class PlayerInventoryService
{
    public function addItem(PlayerGameProgress $progress,Item $item,int $quantity = 1): PlayerInventory 
    {
        $inventory = $progress->inventory()
            ->where('item_id', $item->id)
            ->first();

        if ($inventory) {
            $inventory->increment('quantity', $quantity);

            return $inventory->refresh();
        }

        return $progress->inventory()->create([
            'item_id' => $item->id,
            'quantity' => $quantity,
        ]);
    }

    public function removeItem(PlayerGameProgress $progress,Item $item,int $quantity = 1): void 
    {
        $inventory = $progress->inventory()
            ->where('item_id', $item->id)
            ->first();

        if (!$inventory) {
            return;
        }

        if ($inventory->quantity < $quantity) {
            return;
        }

        if ($inventory->quantity === $quantity) {
            $inventory->delete();
            return;
        }

        $inventory->decrement('quantity', $quantity);
    }

}