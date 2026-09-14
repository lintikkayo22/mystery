<?php

namespace App\Services;

use App\Models\Interaction;
use App\Models\PlayerGameProgress;

class InteractionService
{
    public function __construct(
        private ConditionEvaluator $conditionEvaluator,
        private EffectExecutor $effectExecutor
    ) {
    }

    public function execute(Interaction $interaction,PlayerGameProgress $progress): void
    {
    
        if (! $interaction->is_active) {
            return;
        }

        foreach ($interaction->conditions as $condition) {
            if (! $this->conditionEvaluator->evaluate(
                $condition,
                $progress
            )) {
                return;
            }
        }

        foreach ($interaction->effects as $effect) {
            $this->effectExecutor->execute(
                $effect,
                $progress
            );
        }
    }
}