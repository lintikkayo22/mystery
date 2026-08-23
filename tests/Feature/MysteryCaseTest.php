<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\MysteryCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Role;
use App\Models\User;

class MysteryCaseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use DatabaseTransactions;

    public function test_admin_can_create_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($admin)->post('/mystery-cases', [
            'title' => 'Test Mystery Case',
            'slug' => 'test-mystery-case',
            'description' => 'This is a test mystery case.',
            'cover_image' => 'https://example.com/cover.jpg',
            'difficulty' => 'medium',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('mystery_cases', [
            'title' => 'Test Mystery Case',
            'slug' => 'test-mystery-case',
            'difficulty' => 'medium',
            'status' => 'draft',
        ]);
    }

    public function test_investigator_cannot_create_mystery_case(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $response = $this->actingAs($investigator)->post('/mystery-cases', [
            'title' => 'Test Mystery Case',
            'slug' => 'test-mystery-case',
            'description' => 'This is a test mystery case.',
            'cover_image' => 'https://example.com/cover.jpg',
            'difficulty' => 'medium',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('mystery_cases', [
            'slug' => 'test-mystery-case',
        ]);
    }

    public function test_new_mystery_case_is_draft_by_default(): void
    {
        $case = MysteryCase::factory()->create();

        $this->assertEquals('draft', $case->status);
    }

    public function test_title_is_required_when_creating_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($admin)->post('/mystery-cases', [
            'slug' => 'test-mystery-case',
            'description' => 'This is a test mystery case.',
            'difficulty' => 'medium',
        ]);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseMissing('mystery_cases', [
            'slug' => 'test-mystery-case',
        ]);
    }

    public function test_difficulty_must_be_valid_when_creating_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($admin)->post('/mystery-cases', [
            'title' => 'Test Mystery Case',
            'slug' => 'test-mystery-case',
            'description' => 'This is a test mystery case.',
            'difficulty' => 'impossible',
        ]);

        $response->assertSessionHasErrors('difficulty');
    }

    public function test_slug_must_be_unique_when_creating_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        MysteryCase::factory()->create([
            'slug' => 'existing-case',
        ]);

        $response = $this->actingAs($admin)->post('/mystery-cases', [
            'title' => 'Another Mystery Case',
            'slug' => 'existing-case',
            'description' => 'This is another mystery case.',
            'difficulty' => 'medium',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_investigator_can_only_see_published_mystery_cases(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        MysteryCase::factory()->published()->create([
            'title' => 'Published Case',
        ]);

        MysteryCase::factory()->create([
            'title' => 'Draft Case',
        ]);

        $response = $this->actingAs($investigator)
            ->get('/mystery-cases');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonFragment([
            'title' => 'Published Case',
        ]);

        $response->assertJsonMissing([
            'title' => 'Draft Case',
        ]);
    }

    public function test_admin_can_see_all_mystery_cases(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        MysteryCase::factory()->create([
            'title' => 'Draft Case',
        ]);

        MysteryCase::factory()->published()->create([
            'title' => 'Published Case',
        ]);

        MysteryCase::factory()->archived()->create([
            'title' => 'Archived Case',
        ]);

        $response = $this->actingAs($admin)
            ->get('/mystery-cases');

        $response->assertOk();

        $response->assertJsonCount(3, 'data');

        $response->assertJsonFragment([
            'title' => 'Draft Case',
        ]);

        $response->assertJsonFragment([
            'title' => 'Published Case',
        ]);

        $response->assertJsonFragment([
            'title' => 'Archived Case',
        ]);
    }

    public function test_investigator_can_view_published_mystery_case(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()
            ->published()
            ->create();

        $response = $this->actingAs($investigator)
            ->get("/mystery-cases/{$mysteryCase->id}");

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $mysteryCase->id,
            'title' => $mysteryCase->title,
        ]);
    }

    public function test_investigator_cannot_view_draft_mystery_case(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($investigator)
            ->get("/mystery-cases/{$mysteryCase->id}");

        $response->assertForbidden();
    }

    public function test_investigator_cannot_view_archived_mystery_case(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()
            ->archived()
            ->create();

        $response = $this->actingAs($investigator)
            ->get("/mystery-cases/{$mysteryCase->id}");

        $response->assertForbidden();
    }

    public function test_admin_can_view_any_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $statuses = ['draft', 'published', 'archived'];

        foreach ($statuses as $status) {
            $mysteryCase = MysteryCase::factory()->create([
                'status' => $status,
            ]);

            $response = $this->actingAs($admin)
                ->get("/mystery-cases/{$mysteryCase->id}");

            $response->assertOk();

            $response->assertJsonFragment([
                'id' => $mysteryCase->id,
                'title' => $mysteryCase->title,
            ]);
        }
    }

    public function test_admin_can_update_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create([
            'title' => 'Old Title',
            'slug' => 'old-title',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}", [
                'title' => 'Updated Title',
                'slug' => 'updated-title',
                'description' => 'Updated description.',
                'cover_image' => 'https://example.com/updated.jpg',
                'difficulty' => 'hard',
                'status' => 'published',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'difficulty' => 'hard',
            'status' => 'published',
        ]);
    }

    public function test_investigator_cannot_update_mystery_case(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($investigator)
            ->put("/mystery-cases/{$mysteryCase->id}", [
                'title' => 'Updated Title',
                'slug' => 'updated-title',
                'description' => 'Updated description.',
                'difficulty' => 'hard',
                'status' => 'published',
            ]);

        $response->assertForbidden();
    }

    public function test_mystery_case_can_keep_its_existing_slug_when_updated(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create([
            'slug' => 'original-slug',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}", [
                'title' => 'Updated Title',
                'slug' => 'original-slug',
                'description' => 'Updated description.',
                'difficulty' => 'medium',
                'status' => 'draft',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'slug' => 'original-slug',
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_delete_draft_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->delete("/mystery-cases/{$mysteryCase->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('mystery_cases', [
            'id' => $mysteryCase->id,
        ]);
    }

    public function test_admin_can_delete_archived_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->archived()->create();

        $response = $this->actingAs($admin)
            ->delete("/mystery-cases/{$mysteryCase->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('mystery_cases', [
            'id' => $mysteryCase->id,
        ]);
    }

    public function test_admin_cannot_delete_published_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->published()->create();

        $response = $this->actingAs($admin)
            ->delete("/mystery-cases/{$mysteryCase->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
        ]);
    }

    public function test_investigator_cannot_delete_mystery_case(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($investigator)
            ->delete("/mystery-cases/{$mysteryCase->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
        ]);
    }

    public function test_admin_can_publish_draft_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/publish");

        $response->assertOk();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'status' => 'published',
        ]);
    }

    public function test_investigator_cannot_publish_mystery_case(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->actingAs($investigator)
            ->post("/mystery-cases/{$mysteryCase->id}/publish");

        $response->assertForbidden();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_cannot_publish_already_published_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->published()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/publish");

        $response->assertForbidden();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_cannot_publish_archived_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->archived()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/publish");

        $response->assertForbidden();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'status' => 'archived',
        ]);
    }

    public function test_admin_can_archive_published_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->published()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/archive");

        $response->assertOk();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'status' => 'archived',
        ]);
    }

    public function test_investigator_cannot_archive_mystery_case(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->published()->create();

        $response = $this->actingAs($investigator)
            ->post("/mystery-cases/{$mysteryCase->id}/archive");

        $response->assertForbidden();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_cannot_archive_draft_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/archive");

        $response->assertForbidden();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_cannot_archive_already_archived_mystery_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->archived()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/archive");

        $response->assertForbidden();

        $this->assertDatabaseHas('mystery_cases', [
            'id' => $mysteryCase->id,
            'status' => 'archived',
        ]);
    }

}
