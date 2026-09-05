<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Scene;
use App\Models\User;
use App\Models\Role;
use App\Models\Chapter;
use App\Models\MysteryCase;
use App\Database\Factories\SceneFactory;
use App\Database\Factories\ChapterFactory;

class SceneTest extends TestCase
{
    
    public function test_admin_can_create_scene(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/chapters/{$chapter->id}/scenes", [
                'title' => 'Front Gate',
                'description' => 'The entrance of the old house.',
                'order' => 1,
                'status' => 'draft',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('scenes', [
            'chapter_id' => $chapter->id,
            'title' => 'Front Gate',
        ]);
    }

    public function test_chapter_has_many_scenes(): void
    {
        $chapter = Chapter::factory()->create();

        $scene1 = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $scene2 = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $this->assertCount(2, $chapter->scenes);

        $this->assertTrue(
            $chapter->scenes->contains($scene1)
        );

        $this->assertTrue(
            $chapter->scenes->contains($scene2)
        );
    }

    public function test_scene_title_is_required(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/chapters/{$chapter->id}/scenes", [
                'description' => 'The entrance of the old house.',
                'order' => 1,
                'status' => 'draft',
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_order_is_required(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/chapters/{$chapter->id}/scenes", [
                'title' => 'Front Gate',
                'description' => 'The entrance of the old house.',
                'status' => 'draft',
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_order_must_be_integer(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/chapters/{$chapter->id}/scenes", [
                'title' => 'Front Gate',
                'description' => 'The entrance of the old house.',
                'order' => 'abc',
                'status' => 'draft',
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_order_must_be_at_least_one(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/chapters/{$chapter->id}/scenes", [
                'title' => 'Front Gate',
                'description' => 'The entrance of the old house.',
                'order' => 0,
                'status' => 'draft',
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_status_is_required(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/chapters/{$chapter->id}/scenes", [
                'title' => 'Front Gate',
                'description' => 'The entrance of the old house.',
                'order' => 1,
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_status_must_be_valid(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/chapters/{$chapter->id}/scenes", [
                'title' => 'Front Gate',
                'description' => 'The entrance of the old house.',
                'order' => 1,
                'status' => 'invalid',
            ]);

        $response->assertStatus(422);
    }

    public function test_investigator_cannot_create_scene(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $response = $this->actingAs($investigator)
            ->postJson("/chapters/{$chapter->id}/scenes", [
                'title' => 'Front Gate',
                'description' => 'The entrance of the old house.',
                'order' => 1,
                'status' => 'draft',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('scenes', [
            'chapter_id' => $chapter->id,
            'title' => 'Front Gate',
        ]);
    }

    public function test_admin_can_view_scene(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/chapters/{$chapter->id}/scenes/{$scene->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'id' => $scene->id,
            'chapter_id' => $chapter->id,
            'title' => $scene->title,
        ]);
    }

    public function test_scene_cannot_be_viewed_from_another_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter1 = Chapter::factory()->create();
        $chapter2 = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter1->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/chapters/{$chapter2->id}/scenes/{$scene->id}");

        $response->assertStatus(404);
    }

    public function test_admin_can_update_scene(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Old Title',
            'order' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/chapters/{$chapter->id}/scenes/{$scene->id}", [
                'title' => 'New Title',
                'description' => 'Updated description.',
                'order' => 2,
                'status' => 'published',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'chapter_id' => $chapter->id,
            'title' => 'New Title',
            'order' => 2,
            'status' => 'published',
        ]);
    }

    public function test_scene_update_title_is_required(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/chapters/{$chapter->id}/scenes/{$scene->id}", [
                'description' => 'Updated description.',
                'order' => 2,
                'status' => 'published',
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_update_order_is_required(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/chapters/{$chapter->id}/scenes/{$scene->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated description.',
                'status' => 'published',
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_update_order_must_be_integer(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/chapters/{$chapter->id}/scenes/{$scene->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated description.',
                'order' => 'abc',
                'status' => 'published',
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_update_order_must_be_at_least_one(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/chapters/{$chapter->id}/scenes/{$scene->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated description.',
                'order' => 0,
                'status' => 'published',
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_update_status_is_required(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/chapters/{$chapter->id}/scenes/{$scene->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated description.',
                'order' => 2,
            ]);

        $response->assertStatus(422);
    }

    public function test_scene_update_status_must_be_valid(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/chapters/{$chapter->id}/scenes/{$scene->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated description.',
                'order' => 2,
                'status' => 'invalid',
            ]);

        $response->assertStatus(422);
    }

    public function test_investigator_cannot_update_scene(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($investigator)
            ->putJson("/chapters/{$chapter->id}/scenes/{$scene->id}", [
                'title' => 'Hacked Title',
                'description' => 'Changed by investigator.',
                'order' => 2,
                'status' => 'published',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'title' => 'Original Title',
        ]);
    }

    public function test_scene_cannot_be_updated_from_another_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter1 = Chapter::factory()->create();
        $chapter2 = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter1->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($admin)
            ->putJson("/chapters/{$chapter2->id}/scenes/{$scene->id}", [
                'title' => 'Hacked Title',
                'description' => 'Changed description.',
                'order' => 2,
                'status' => 'published',
            ]);

        $response->assertStatus(404);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'chapter_id' => $chapter1->id,
            'title' => 'Original Title',
        ]);
    }

    public function test_admin_can_delete_scene(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson("/chapters/{$chapter->id}/scenes/{$scene->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('scenes', [
            'id' => $scene->id,
        ]);
    }

    public function test_investigator_cannot_delete_scene(): void
    {
        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $investigator = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $chapter = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $response = $this->actingAs($investigator)
            ->deleteJson("/chapters/{$chapter->id}/scenes/{$scene->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
        ]);
    }

    public function test_scene_cannot_be_deleted_from_another_chapter(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $chapter1 = Chapter::factory()->create();
        $chapter2 = Chapter::factory()->create();

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter1->id,
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson("/chapters/{$chapter2->id}/scenes/{$scene->id}");

        $response->assertStatus(404);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'chapter_id' => $chapter1->id,
        ]);
    }

    public function test_scene_can_store_background_image(): void
    {
        $chapter = Chapter::factory()->create();

        $scene = Scene::create([
            'chapter_id' => $chapter->id,
            'title' => 'Phòng tân hôn',
            'description' => 'Một căn phòng cũ.',
            'background_image' => 'scenes/bedroom.jpg',
            'order' => 1,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'background_image' => 'scenes/bedroom.jpg',
        ]);
    }

    public function test_scene_can_be_created_without_background_image(): void
    {
        $chapter = Chapter::factory()->create();

        $scene = Scene::create([
            'chapter_id' => $chapter->id,
            'title' => 'Phòng tân hôn',
            'description' => 'Một căn phòng cũ.',
            'order' => 1,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'background_image' => null,
        ]);
    }

}
