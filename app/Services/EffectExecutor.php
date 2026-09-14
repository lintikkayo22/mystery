<?php

namespace App\Services;

use App\Models\InteractionEffect;
use App\Models\PlayerGameProgress;
use App\Models\Item;
use App\Services\PlayerInventoryService;


class EffectExecutor
{
    public function execute(InteractionEffect $effect,PlayerGameProgress $progress): void
    {
        if ($effect->type === 'ADD_ITEM') {

            $item = Item::findOrFail($effect->value);
            $inventoryService = new PlayerInventoryService();
            $inventoryService->addItem(
                $progress,
                $item
            );
        }

        if ($effect->type === 'REMOVE_ITEM') {
            $item = Item::findOrFail($effect->value);

            $inventoryService = new PlayerInventoryService();

            $inventoryService->removeItem(
                $progress,
                $item
            );
        }

        if ($effect->type === 'REVEAL_CLUE') {
            $progress->clues()->syncWithoutDetaching([
                $effect->value,
            ]);
        }

        if ($effect->type === 'REVEAL_EVIDENCE') {
            $progress->evidences()->syncWithoutDetaching([
                $effect->value,
            ]);
        }

        if ($effect->type === 'CHANGE_STATE') {
            [$key, $value] = explode(':', $effect->value, 2);

            $progress->states()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }


    }
}