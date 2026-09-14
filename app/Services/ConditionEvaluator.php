<?php

namespace App\Services;

use App\Models\InteractionCondition;
use App\Models\PlayerGameProgress;

class ConditionEvaluator
{
    public function evaluate(InteractionCondition $condition,PlayerGameProgress $progress): bool 
    {
        if ($condition->type === 'HAS_ITEM') {
            return $progress->inventory()
                ->where('item_id', $condition->value)
                ->exists();
        }
        if ($condition->type === 'HAS_CLUE') {
            return $progress->clues()
                ->where('clues.id', $condition->value)
                ->exists();
        }
        if ($condition->type === 'HAS_EVIDENCE') {
            return $progress->evidences()
                ->where('evidence.id', $condition->value)
                ->exists();
        }
        if ($condition->type === 'STATE_IS') {
            [$key, $value] = explode(':', $condition->value, 2);

            return $progress->states()
                ->where('key', $key)
                ->where('value', $value)
                ->exists();
    }

        return false;
    }
}