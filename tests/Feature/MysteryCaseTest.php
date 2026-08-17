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

}
