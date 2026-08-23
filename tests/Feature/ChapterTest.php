<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\MysteryCase;
use App\Models\Chapter;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\ChapterController;

class ChapterTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use DatabaseTransactions;

    public function test_chapter_belongs_to_mystery_case(): void
    {
        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $this->assertInstanceOf(
            MysteryCase::class,
            $chapter->mysteryCase
        );
    }

    public function test_admin_can_create_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/chapters", [
                'title' => 'Chapter 1',
                'description' => 'The beginning of the story.',
                'order' => 1,
                'status' => 'draft',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('chapters', [
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Chapter 1',
            'order' => 1,
            'status' => 'draft',
        ]);
    }

    public function test_non_admin_cannot_create_chapter(): void
    {
        $userRole = Role::where('name', 'investigator')->firstOrFail();

        $user = User::factory()->create([
            'role_id' => $userRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($user)
            ->post("/mystery-cases/{$mysteryCase->id}/chapters", [
                'title' => 'Chapter 1',
                'description' => 'The beginning of the story.',
                'order' => 1,
                'status' => 'draft',
            ]);

        $response->assertStatus(403);
    }

    public function test_title_is_required_to_create_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/chapters", [
                'description' => 'The beginning of the story.',
                'order' => 1,
                'status' => 'draft',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title']);
    }

    public function test_order_is_required_to_create_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/chapters", [
                'title' => 'Chapter 1',
                'description' => 'The beginning of the story.',
                'status' => 'draft',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['order']);
    }

    public function test_order_must_be_an_integer_to_create_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/chapters", [
                'title' => 'Chapter 1',
                'description' => 'The beginning of the story.',
                'order' => 'abc',
                'status' => 'draft',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['order']);
    }

    public function test_status_is_required_to_create_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/chapters", [
                'title' => 'Chapter 1',
                'description' => 'The beginning of the story.',
                'order' => 1,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['status']);
    }

    public function test_status_must_be_valid_to_create_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/mystery-cases/{$mysteryCase->id}/chapters", [
                'title' => 'Chapter 1',
                'description' => 'The beginning of the story.',
                'order' => 1,
                'status' => 'invalid',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['status']);
    }

    public function test_admin_can_view_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Chapter 1',
            'description' => 'The beginning of the story.',
            'order' => 1,
            'status' => 'published',
        ]);

        $response = $this->actingAs($admin)
            ->get("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'id' => $chapter->id,
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Chapter 1',
            'description' => 'The beginning of the story.',
            'order' => 1,
            'status' => 'published',
        ]);
    }

    public function test_admin_cannot_view_chapter_from_another_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase1 = MysteryCase::factory()->create();
        $mysteryCase2 = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase1->id,
            'title' => 'Chapter 1',
            'description' => 'The beginning of the story.',
            'order' => 1,
            'status' => 'published',
        ]);

        $response = $this->actingAs($admin)
            ->get("/mystery-cases/{$mysteryCase2->id}/chapters/{$chapter->id}");

        $response->assertStatus(404);
    }

    public function test_admin_can_update_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Old title',
            'description' => 'Old description',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}", [
                'title' => 'Updated title',
                'description' => 'Updated description',
                'order' => 2,
                'status' => 'published',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('chapters', [
            'id' => $chapter->id,
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Updated title',
            'description' => 'Updated description',
            'order' => 2,
            'status' => 'published',
        ]);
    }

    public function test_admin_cannot_update_chapter_from_another_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase1 = MysteryCase::factory()->create();
        $mysteryCase2 = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase1->id,
            'title' => 'Old title',
            'description' => 'Old description',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase2->id}/chapters/{$chapter->id}", [
                'title' => 'Hacked title',
                'description' => 'Hacked description',
                'order' => 2,
                'status' => 'published',
            ]);

        $response->assertStatus(404);

        $this->assertDatabaseHas('chapters', [
            'id' => $chapter->id,
            'mystery_case_id' => $mysteryCase1->id,
            'title' => 'Old title',
            'description' => 'Old description',
            'order' => 1,
            'status' => 'draft',
        ]);
    }


    public function test_title_is_required_to_update_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Old title',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}", [
                'description' => 'Updated description',
                'order' => 2,
                'status' => 'published',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title']);
    }

    public function test_order_is_required_to_update_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Chapter 1',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}", [
                'title' => 'Updated Chapter',
                'description' => 'Updated description',
                'status' => 'published',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['order']);
    }

    public function test_order_must_be_an_integer_to_update_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Chapter 1',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}", [
                'title' => 'Updated Chapter',
                'description' => 'Updated description',
                'order' => 'abc',
                'status' => 'published',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['order']);
    }

    public function test_status_is_required_to_update_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Chapter 1',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}", [
                'title' => 'Updated Chapter',
                'description' => 'Updated description',
                'order' => 2,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['status']);
    }

    public function test_status_must_be_valid_to_update_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Chapter 1',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->put("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}", [
                'title' => 'Updated Chapter',
                'description' => 'Updated description',
                'order' => 2,
                'status' => 'invalid',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['status']);
    }

    public function test_admin_can_delete_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $response = $this->actingAs($admin)
            ->delete("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('chapters', [
            'id' => $chapter->id,
        ]);
    }

    public function test_admin_cannot_delete_chapter_from_another_case(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $mysteryCase1 = MysteryCase::factory()->create();
        $mysteryCase2 = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase1->id,
            'title' => 'Chapter 1',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->delete(
                "/mystery-cases/{$mysteryCase2->id}/chapters/{$chapter->id}"
            );

        $response->assertStatus(404);

        $this->assertDatabaseHas('chapters', [
            'id' => $chapter->id,
            'mystery_case_id' => $mysteryCase1->id,
        ]);
    }

    public function test_investigator_cannot_update_chapter(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
            'title' => 'Chapter 1',
            'description' => 'Original description',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($investigator)
            ->put("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}", [
                'title' => 'Hacked Chapter',
                'description' => 'Hacked description',
                'order' => 2,
                'status' => 'published',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('chapters', [
            'id' => $chapter->id,
            'title' => 'Chapter 1',
            'description' => 'Original description',
            'order' => 1,
            'status' => 'draft',
        ]);
    }

    public function test_investigator_cannot_delete_chapter(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $mysteryCase = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $mysteryCase->id,
        ]);

        $response = $this->actingAs($investigator)
            ->delete("/mystery-cases/{$mysteryCase->id}/chapters/{$chapter->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('chapters', [
            'id' => $chapter->id,
            'mystery_case_id' => $mysteryCase->id,
        ]);
    }

}
