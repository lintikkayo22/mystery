<?php

namespace Tests\Feature;

use App\Models\Clue;
use App\Models\MysteryCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class ClueTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use DatabaseTransactions;

    public function test_mystery_case_has_many_clues(): void
    {
        $mysteryCase = MysteryCase::factory()->create();

        Clue::factory()->count(3)->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $this->assertCount(3, $mysteryCase->clues);
    }

    public function test_clue_belongs_to_mystery_case(): void
    {
        $mysteryCase = MysteryCase::factory()->create();

        $clue = Clue::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $this->assertTrue(
            $clue->mysteryCase->is($mysteryCase)
        );
    }

    public function test_admin_can_create_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/clues", [
                'title' => 'Camera Footage',
                'content' => 'A camera recorded someone entering the building.',
                'type' => 'evidence',
                'is_revealed' => false,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('clues', [
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Camera Footage',
            'content' => 'A camera recorded someone entering the building.',
            'type' => 'evidence',
            'is_revealed' => false,
        ]);
    }

    public function test_investigator_cannot_create_clue(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($investigator)
            ->post("/mystery-cases/{$mysteryCase->id}/clues", [
                'title' => 'Secret Clue',
                'content' => 'Secret information.',
                'type' => 'document',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('clues', [
            'title' => 'Secret Clue',
        ]);
    }

    public function test_new_clue_is_hidden_by_default(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/clues", [
                'title' => 'Hidden Clue',
                'content' => 'This clue has not been revealed.',
                'type' => 'document',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('clues', [
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Hidden Clue',
            'is_revealed' => false,
        ]);
    }

    public function test_admin_can_list_all_clues(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        Clue::factory()->count(3)->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $response = $this->actingAs($admin)
            ->get("/mystery-cases/{$mysteryCase->id}/clues");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_investigator_can_only_see_revealed_clues(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        Clue::factory()->revealed()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        Clue::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'is_revealed' => false,
        ]);

        $response = $this->actingAs($investigator)
            ->get("/mystery-cases/{$mysteryCase->id}/clues");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_investigator_cannot_see_hidden_clues(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        Clue::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'is_revealed' => false,
        ]);

        $response = $this->actingAs($investigator)
            ->get("/mystery-cases/{$mysteryCase->id}/clues");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_admin_can_view_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $clue = Clue::factory()->create([
            'is_revealed' => false,
        ]);

        $response = $this->actingAs($admin)
            ->get("/clues/{$clue->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $clue->id);
    }

    public function test_investigator_can_view_revealed_clue(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $clue = Clue::factory()->revealed()->create();

        $response = $this->actingAs($investigator)
            ->get("/clues/{$clue->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $clue->id);
    }

    public function test_investigator_cannot_view_hidden_clue(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $clue = Clue::factory()->create([
            'is_revealed' => false,
        ]);

        $response = $this->actingAs($investigator)
            ->get("/clues/{$clue->id}");

        $response->assertForbidden();
    }

    public function test_admin_can_update_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $clue = Clue::factory()->create([
            'is_revealed' => false,
        ]);

        $response = $this->actingAs($admin)
            ->put("/clues/{$clue->id}", [
                'title' => 'Updated Clue Title',
                'content' => 'Updated content for the clue.',
                'type' => 'evidence',
                'is_revealed' => true,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('clues', [
            'id' => $clue->id,
            'title' => 'Updated Clue Title',
            'content' => 'Updated content for the clue.',
            'type' => 'evidence',
            'is_revealed' => true,
        ]);
    }

    public function test_investigator_cannot_update_clue(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $clue = Clue::factory()->create([
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($investigator)
            ->put("/clues/{$clue->id}", [
                'title' => 'Hacked Title',
                'content' => 'Hacked content.',
                'type' => 'evidence',
                'is_revealed' => true,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('clues', [
            'id' => $clue->id,
            'title' => 'Original Title',
        ]);
    }

    public function test_admin_cannot_change_clue_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $case1 = MysteryCase::factory()->create();
        $case2 = MysteryCase::factory()->create();

        $clue = Clue::factory()->create([
            'mystery_case_id' => $case1->id,
        ]);

        $this->actingAs($admin)
            ->put("/clues/{$clue->id}", [
                'title' => 'Updated',
                'content' => 'Updated content.',
                'type' => 'evidence',
                'is_revealed' => true,
                'mystery_case_id' => $case2->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('clues', [
            'id' => $clue->id,
            'mystery_case_id' => $case1->id,
        ]);
    }

    public function test_admin_can_delete_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $clue = Clue::factory()->create();

        $response = $this->actingAs($admin)
            ->delete("/clues/{$clue->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('clues', [
            'id' => $clue->id,
        ]);
    }

    public function test_investigator_cannot_delete_clue(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $clue = Clue::factory()->create();

        $response = $this->actingAs($investigator)
            ->delete("/clues/{$clue->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('clues', [
            'id' => $clue->id,
        ]);
    }

    public function test_admin_can_reveal_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $clue = Clue::factory()->create([
            'is_revealed' => false,
        ]);

        $response = $this->actingAs($admin)
            ->post("/clues/{$clue->id}/reveal");

        $response->assertOk();

        $this->assertDatabaseHas('clues', [
            'id' => $clue->id,
            'is_revealed' => true,
        ]);
    }

    public function test_investigator_cannot_reveal_clue(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $clue = Clue::factory()->create([
            'is_revealed' => false,
        ]);

        $response = $this->actingAs($investigator)
            ->post("/clues/{$clue->id}/reveal");

        $response->assertForbidden();

        $this->assertDatabaseHas('clues', [
            'id' => $clue->id,
            'is_revealed' => false,
        ]);
    }

    public function test_admin_cannot_reveal_already_revealed_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $clue = Clue::factory()->revealed()->create();

        $response = $this->actingAs($admin)
            ->post("/clues/{$clue->id}/reveal");

        $response->assertForbidden();

        $this->assertDatabaseHas('clues', [
            'id' => $clue->id,
            'is_revealed' => true,
        ]);
    }

    public function test_title_is_required_to_create_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/clues", [
                'content' => 'Content without a title.',
                'type' => 'document',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title']);
    }

    public function test_content_is_required_to_create_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/clues", [
                'title' => 'Clue without content',
                'type' => 'document',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['content']);
    }

    public function test_type_must_be_valid_to_create_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/clues", [
                'title' => 'Invalid clue',
                'content' => 'Some content.',
                'type' => 'invalid-type',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['type']);
    }

    public function test_title_is_required_to_update_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $clue = Clue::factory()->create();

        $response = $this->actingAs($admin)
            ->put("/clues/{$clue->id}", [
                'content' => 'Updated content.',
                'type' => 'evidence',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title']);
    }

    public function test_content_is_required_to_update_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $clue = Clue::factory()->create();

        $response = $this->actingAs($admin)
            ->put("/clues/{$clue->id}", [
                'title' => 'Updated title',
                'type' => 'evidence',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['content']);
    }

    public function test_type_must_be_valid_to_update_clue(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $clue = Clue::factory()->create();

        $response = $this->actingAs($admin)
            ->put("/clues/{$clue->id}", [
                'title' => 'Updated title',
                'content' => 'Updated content.',
                'type' => 'invalid-type',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['type']);
    }

}
