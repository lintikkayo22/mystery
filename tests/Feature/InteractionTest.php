<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Interaction;
use App\Models\InteractionCondition;
use App\Models\InteractionEffect;
use App\Models\PlayerGameProgress;
use App\Models\Item;
use App\Services\ConditionEvaluator;
use App\Services\EffectExecutor;
use App\Models\Clue;
use App\Models\Evidence;
use App\Models\PlayerGameState;
use App\Services\InteractionService;

class InteractionTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_interaction_can_be_created(): void
    {
        Interaction::create([
            'name' => 'Examine Family Photo',
            'description' => 'Look closely at the photo.',
            'is_active' => true,
        ]);

        $this->assertDatabaseCount('interactions', 1);
    }

    public function test_interaction_can_have_a_condition(): void
    {
        $interaction = Interaction::create([
            'name' => 'Open Drawer',
            'description' => 'Try to open the drawer.',
            'is_active' => true,
        ]);

        $condition = $interaction->conditions()->create([
            'type' => 'HAS_ITEM',
            'value' => 'key:1',
        ]);

        $this->assertDatabaseHas('interaction_conditions', [
            'interaction_id' => $interaction->id,
            'type' => 'HAS_ITEM',
            'value' => 'key:1',
        ]);
    }

    public function test_interaction_can_have_multiple_conditions(): void
    {
        $interaction = Interaction::create([
            'name' => 'Open Secret Door',
            'description' => 'Open the secret door.',
            'is_active' => true,
        ]);

        $interaction->conditions()->createMany([
            [
                'type' => 'HAS_ITEM',
                'value' => 'key:1',
            ],
            [
                'type' => 'STATE_IS',
                'value' => 'altar:activated',
            ],
        ]);

        $this->assertCount(2, $interaction->conditions);

        $this->assertDatabaseCount('interaction_conditions', 2);
    }

    public function test_interaction_can_have_an_effect(): void
    {
        $interaction = Interaction::create([
            'name' => 'Open Drawer',
            'description' => 'Open the drawer.',
            'is_active' => true,
        ]);

        $interaction->effects()->create([
            'type' => 'CHANGE_STATE',
            'value' => 'drawer:opened',
        ]);

        $this->assertDatabaseHas('interaction_effects', [
            'interaction_id' => $interaction->id,
            'type' => 'CHANGE_STATE',
            'value' => 'drawer:opened',
        ]);
    }

    public function test_interaction_can_have_multiple_effects(): void
    {
        $interaction = Interaction::create([
            'name' => 'Open Drawer',
            'description' => 'Open the drawer.',
            'is_active' => true,
        ]);

        $interaction->effects()->createMany([
            [
                'type' => 'CHANGE_STATE',
                'value' => 'drawer:opened',
            ],
            [
                'type' => 'REVEAL_EVIDENCE',
                'value' => 'old_letter',
            ],
        ]);

        $this->assertCount(2, $interaction->effects);

        $this->assertDatabaseCount('interaction_effects', 2);
    }

    public function test_condition_has_item_passes_when_player_has_item(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $item = Item::factory()->create();

        $progress->inventory()->create([
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $condition = InteractionCondition::create([
            'interaction_id' => Interaction::factory()->create()->id,
            'type' => 'HAS_ITEM',
            'value' => (string) $item->id,
        ]);

        $evaluator = new ConditionEvaluator();

        $this->assertTrue(
            $evaluator->evaluate($condition, $progress)
        );
    }

    public function test_condition_has_item_fails_when_player_does_not_have_item(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $item = Item::factory()->create();

        $condition = InteractionCondition::create([
            'interaction_id' => Interaction::factory()->create()->id,
            'type' => 'HAS_ITEM',
            'value' => (string) $item->id,
        ]);

        $evaluator = new ConditionEvaluator();

        $this->assertFalse(
            $evaluator->evaluate($condition, $progress)
        );
    }

    public function test_player_can_have_a_revealed_clue(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $clue = Clue::factory()->create();

        $progress->clues()->attach($clue->id);

        $this->assertDatabaseHas('player_clues', [
            'player_game_progress_id' => $progress->id,
            'clue_id' => $clue->id,
        ]);
    }

    public function test_clue_discovery_is_independent_between_players(): void
    {
        $progressA = PlayerGameProgress::factory()->create();
        $progressB = PlayerGameProgress::factory()->create();

        $clue = Clue::factory()->create();

        $progressA->clues()->attach($clue->id);

        $this->assertTrue(
            $progressA->clues()->where('clues.id', $clue->id)->exists()
        );

        $this->assertFalse(
            $progressB->clues()->where('clues.id', $clue->id)->exists()
        );
    }

    public function test_has_clue_condition_passes_when_player_has_clue(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $clue = Clue::factory()->create();

        $progress->clues()->attach($clue->id);

        $condition = InteractionCondition::factory()->create([
            'type' => 'HAS_CLUE',
            'value' => (string) $clue->id,
        ]);

        $evaluator = new ConditionEvaluator();

        $this->assertTrue(
            $evaluator->evaluate($condition, $progress)
        );
    }

    public function test_has_clue_condition_fails_when_player_does_not_have_clue(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $clue = Clue::factory()->create();

        $condition = InteractionCondition::factory()->create([
            'type' => 'HAS_CLUE',
            'value' => (string) $clue->id,
        ]);

        $evaluator = new ConditionEvaluator();

        $this->assertFalse(
            $evaluator->evaluate($condition, $progress)
        );
    }

    public function test_has_evidence_condition_passes_when_player_has_evidence(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $evidence = Evidence::factory()->create();

        $progress->evidences()->attach($evidence->id);

        $condition = InteractionCondition::factory()->create([
            'type' => 'HAS_EVIDENCE',
            'value' => (string) $evidence->id,
        ]);

        $evaluator = new ConditionEvaluator();

        $this->assertTrue(
            $evaluator->evaluate($condition, $progress)
        );
    }

    public function test_has_evidence_condition_fails_when_player_does_not_have_evidence(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $evidence = Evidence::factory()->create();

        $condition = InteractionCondition::factory()->create([
            'type' => 'HAS_EVIDENCE',
            'value' => (string) $evidence->id,
        ]);

        $evaluator = new ConditionEvaluator();

        $this->assertFalse(
            $evaluator->evaluate($condition, $progress)
        );
    }

    public function test_add_item_effect_adds_item_to_inventory(): void
    {
        $progress = PlayerGameProgress::factory()->create();

        $item = Item::factory()->create();

        $effect = InteractionEffect::factory()->create([
            'type' => 'ADD_ITEM',
            'value' => (string) $item->id,
        ]);

        $executor = new EffectExecutor();

        $executor->execute($effect, $progress);

        $this->assertDatabaseHas('player_inventories', [
            'player_game_progress_id' => $progress->id,
            'item_id' => $item->id,
            'quantity' => 1,
        ]);
    }

    public function test_reveal_clue_effect_reveals_clue_to_player(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $clue = Clue::factory()->create();

        $effect = InteractionEffect::factory()->create([
            'type' => 'REVEAL_CLUE',
            'value' => (string) $clue->id,
        ]);

        $executor = new EffectExecutor();

        $executor->execute($effect, $progress);

        $this->assertDatabaseHas('player_clues', [
            'player_game_progress_id' => $progress->id,
            'clue_id' => $clue->id,
        ]);
    }

    public function test_reveal_evidence_effect_reveals_evidence_to_player(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $evidence = Evidence::factory()->create();

        $effect = InteractionEffect::factory()->create([
            'type' => 'REVEAL_EVIDENCE',
            'value' => (string) $evidence->id,
        ]);

        $executor = new EffectExecutor();

        $executor->execute($effect, $progress);

        $this->assertDatabaseHas('player_evidences', [
            'player_game_progress_id' => $progress->id,
            'evidence_id' => $evidence->id,
        ]);
    }

    public function test_player_game_progress_can_have_game_states(): void
    {
        $progress = PlayerGameProgress::factory()->create();

        $state = PlayerGameState::factory()->create([
            'player_game_progress_id' => $progress->id,
            'key' => 'cabinet',
            'value' => 'opened',
        ]);

        $this->assertTrue(
            $progress->states->contains($state)
        );
    }

    public function test_change_state_effect_changes_player_game_state(): void
    {
        $progress = PlayerGameProgress::factory()->create();

        $effect = InteractionEffect::factory()->create([
            'type' => 'CHANGE_STATE',
            'value' => 'cabinet:opened',
        ]);

        $executor = new EffectExecutor();

        $executor->execute($effect, $progress);

        $this->assertDatabaseHas('player_game_states', [
            'player_game_progress_id' => $progress->id,
            'key' => 'cabinet',
            'value' => 'opened',
        ]);
    }

    public function test_change_state_effect_updates_existing_player_game_state(): void
    {
        $progress = PlayerGameProgress::factory()->create();

        PlayerGameState::factory()->create([
            'player_game_progress_id' => $progress->id,
            'key' => 'cabinet',
            'value' => 'locked',
        ]);

        $effect = InteractionEffect::factory()->create([
            'type' => 'CHANGE_STATE',
            'value' => 'cabinet:opened',
        ]);

        $executor = new EffectExecutor();

        $executor->execute($effect, $progress);

        $this->assertDatabaseHas('player_game_states', [
            'player_game_progress_id' => $progress->id,
            'key' => 'cabinet',
            'value' => 'opened',
        ]);

        $this->assertDatabaseCount('player_game_states', 1);
    }
    
    public function test_state_is_condition_passes_when_player_state_matches(): void
    {
        $progress = PlayerGameProgress::factory()->create();

        PlayerGameState::factory()->create([
            'player_game_progress_id' => $progress->id,
            'key' => 'cabinet',
            'value' => 'opened',
        ]);

        $condition = InteractionCondition::factory()->create([
            'type' => 'STATE_IS',
            'value' => 'cabinet:opened',
        ]);

        $evaluator = new ConditionEvaluator();

        $this->assertTrue(
            $evaluator->evaluate($condition, $progress)
        );
    }

    public function test_state_is_condition_fails_when_player_state_does_not_match(): void
    {
        $progress = PlayerGameProgress::factory()->create();

        PlayerGameState::factory()->create([
            'player_game_progress_id' => $progress->id,
            'key' => 'cabinet',
            'value' => 'locked',
        ]);

        $condition = InteractionCondition::factory()->create([
            'type' => 'STATE_IS',
            'value' => 'cabinet:opened',
        ]);

        $evaluator = new ConditionEvaluator();

        $this->assertFalse(
            $evaluator->evaluate($condition, $progress)
        );
    }

    public function test_remove_item_effect_removes_item_from_inventory(): void
    {
        $progress = PlayerGameProgress::factory()->create();
        $item = Item::factory()->create();

        $progress->inventory()->create([
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $effect = InteractionEffect::factory()->create([
            'type' => 'REMOVE_ITEM',
            'value' => (string) $item->id,
        ]);

        $executor = new EffectExecutor();

        $executor->execute($effect, $progress);

        $this->assertDatabaseMissing('player_inventories', [
            'player_game_progress_id' => $progress->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_interaction_executes_effects_when_all_conditions_pass(): void
    {
        $progress = PlayerGameProgress::factory()->create();

        $item = Item::factory()->create();

        $interaction = Interaction::factory()->create();

        InteractionCondition::factory()->create([
            'interaction_id' => $interaction->id,
            'type' => 'HAS_ITEM',
            'value' => (string) $item->id,
        ]);

        $progress->inventory()->create([
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        InteractionEffect::factory()->create([
            'interaction_id' => $interaction->id,
            'type' => 'CHANGE_STATE',
            'value' => 'cabinet:opened',
        ]);

        $service = new InteractionService(
            new ConditionEvaluator(),
            new EffectExecutor()
        );

        $service->execute($interaction, $progress);

        $this->assertDatabaseHas('player_game_states', [
            'player_game_progress_id' => $progress->id,
            'key' => 'cabinet',
            'value' => 'opened',
        ]);
    }

    public function test_interaction_does_not_execute_effects_when_a_condition_fails(): void
    {
        $progress = PlayerGameProgress::factory()->create();

        $item = Item::factory()->create();

        $interaction = Interaction::factory()->create();

        InteractionCondition::factory()->create([
            'interaction_id' => $interaction->id,
            'type' => 'HAS_ITEM',
            'value' => (string) $item->id,
        ]);

        InteractionEffect::factory()->create([
            'interaction_id' => $interaction->id,
            'type' => 'CHANGE_STATE',
            'value' => 'cabinet:opened',
        ]);

        $service = new InteractionService(
            new ConditionEvaluator(),
            new EffectExecutor()
        );

        $service->execute($interaction, $progress);

        $this->assertDatabaseMissing('player_game_states', [
            'player_game_progress_id' => $progress->id,
            'key' => 'cabinet',
            'value' => 'opened',
        ]);
    }

    public function test_inactive_interaction_does_not_execute_effects(): void
    {
        $progress = PlayerGameProgress::factory()->create();

        $interaction = Interaction::factory()->create([
            'is_active' => false,
        ]);

        InteractionEffect::factory()->create([
            'interaction_id' => $interaction->id,
            'type' => 'CHANGE_STATE',
            'value' => 'cabinet:opened',
        ]);

        $service = new InteractionService(
            new ConditionEvaluator(),
            new EffectExecutor()
        );

        $service->execute($interaction, $progress);

        $this->assertDatabaseMissing('player_game_states', [
            'player_game_progress_id' => $progress->id,
            'key' => 'cabinet',
            'value' => 'opened',
        ]);
    }


}
