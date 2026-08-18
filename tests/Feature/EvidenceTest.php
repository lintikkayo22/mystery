<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\MysteryCase;
use App\Models\Evidence;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EvidenceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use DatabaseTransactions;

    public function test_mystery_case_has_many_evidence(): void
    {
        $mysteryCase = MysteryCase::factory()->create();

        $evidence1 = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $evidence2 = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $this->assertCount(2, $mysteryCase->evidence);

        $this->assertTrue(
            $mysteryCase->evidence->contains($evidence1)
        );

        $this->assertTrue(
            $mysteryCase->evidence->contains($evidence2)
        );
    }

    public function test_evidence_belongs_to_mystery_case(): void
    {
        $mysteryCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $this->assertTrue(
            $evidence->mysteryCase->is($mysteryCase)
        );
    }

    public function test_admin_can_create_evidence(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/evidence", [
                'title' => 'Camera Footage',
                'description' => 'Camera footage from the third floor.',
                'type' => 'video',
                'file_path' => 'evidence/camera.mp4',
                'is_revealed' => false,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('evidence', [
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Camera Footage',
            'description' => 'Camera footage from the third floor.',
            'type' => 'video',
            'file_path' => 'evidence/camera.mp4',
            'is_revealed' => false,
        ]);
    }

    public function test_investigator_cannot_create_evidence(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($investigator)
            ->post("/mystery-cases/{$mysteryCase->id}/evidence", [
                'title' => 'Camera Footage',
                'description' => 'Camera footage from the third floor.',
                'type' => 'video',
                'file_path' => 'evidence/camera.mp4',
                'is_revealed' => false,
            ]);

        $response->assertForbidden();
    }

    public function test_admin_can_list_evidence(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $evidence1 = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Camera Footage',
        ]);

        $evidence2 = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Phone',
        ]);

        $response = $this->actingAs($admin)
            ->get("/mystery-cases/{$mysteryCase->id}/evidence");

        $response->assertOk();

        $response->assertJsonFragment([
            'title' => 'Camera Footage',
        ]);

        $response->assertJsonFragment([
            'title' => 'Phone',
        ]);
    }

    public function test_evidence_list_only_contains_evidence_from_current_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();
        $otherCase = MysteryCase::factory()->create();

        Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Correct Evidence',
        ]);

        Evidence::factory()->create([
            'mystery_case_id' => $otherCase->id,
            'title' => 'Wrong Evidence',
        ]);

        $response = $this->actingAs($admin)
            ->get("/mystery-cases/{$mysteryCase->id}/evidence");

        $response->assertOk();

        $response->assertJsonFragment([
            'title' => 'Correct Evidence',
        ]);

        $response->assertJsonMissing([
            'title' => 'Wrong Evidence',
        ]);
    }

    public function test_admin_can_view_evidence(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Camera Footage',
            'description' => 'Camera footage from the third floor.',
            'type' => 'video',
        ]);

        $response = $this->actingAs($admin)
            ->get("/mystery-cases/{$mysteryCase->id}/evidence/{$evidence->id}");

        $response->assertOk();

        $response->assertJsonFragment([
            'title' => 'Camera Footage',
        ]);
    }

    public function test_cannot_view_evidence_from_another_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();
        $otherCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $otherCase->id,
        ]);

        $response = $this->actingAs($admin)
            ->get("/mystery-cases/{$mysteryCase->id}/evidence/{$evidence->id}");

        $response->assertNotFound();
    }

    public function test_admin_can_update_evidence(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Old Title',
            'description' => 'Old description.',
            'type' => 'document',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/evidence/{$evidence->id}", [
                'title' => 'Updated Evidence',
                'description' => 'Updated description.',
                'type' => 'image',
                'file_path' => 'evidence/updated.jpg',
                'is_revealed' => true,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('evidence', [
            'id' => $evidence->id,
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Updated Evidence',
            'description' => 'Updated description.',
            'type' => 'image',
            'file_path' => 'evidence/updated.jpg',
            'is_revealed' => true,
        ]);
    }

    public function test_cannot_update_evidence_from_another_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();
        $otherCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $otherCase->id,
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/evidence/{$evidence->id}", [
                'title' => 'Hacked Evidence',
                'description' => 'Should not update.',
                'type' => 'image',
            ]);

        $response->assertNotFound();

        $this->assertDatabaseMissing('evidence', [
            'id' => $evidence->id,
            'title' => 'Hacked Evidence',
        ]);
    }

    public function test_title_is_required_to_update_evidence(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/evidence/{$evidence->id}", [
                'description' => 'Updated description.',
                'type' => 'image',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title']);
    }

    public function test_description_is_required_to_update_evidence(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/evidence/{$evidence->id}", [
                'title' => 'Updated title',
                'type' => 'image',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['description']);
    }

    public function test_type_is_required_to_update_evidence(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/evidence/{$evidence->id}", [
                'title' => 'Updated title',
                'description' => 'Updated description.',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['type']);
    }

    public function test_admin_can_delete_evidence(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $response = $this->actingAs($admin)
            ->delete("/mystery-cases/{$mysteryCase->id}/evidence/{$evidence->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('evidence', [
            'id' => $evidence->id,
        ]);
    }

    public function test_cannot_delete_evidence_from_another_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();
        $otherCase = MysteryCase::factory()->create();

        $evidence = Evidence::factory()->create([
            'mystery_case_id' => $otherCase->id,
        ]);

        $response = $this->actingAs($admin)
            ->delete("/mystery-cases/{$mysteryCase->id}/evidence/{$evidence->id}");

        $response->assertNotFound();

        $this->assertDatabaseHas('evidence', [
            'id' => $evidence->id,
        ]);
    }

    public function test_new_evidence_is_not_revealed_by_default(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/evidence", [
                'title' => 'Camera Footage',
                'description' => 'Camera footage from the third floor.',
                'type' => 'video',
                'file_path' => 'evidence/camera.mp4',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('evidence', [
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Camera Footage',
            'is_revealed' => false,
        ]);
    }

}
