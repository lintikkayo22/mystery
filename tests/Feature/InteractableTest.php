<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Scene;
use App\Models\Interactable;
use App\Models\User;
use App\Models\Role;

class InteractableTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use DatabaseTransactions;

    public function test_scene_has_many_interactables(): void
    {
        $scene = Scene::factory()->create();

        $interactable1 = Interactable::factory()->create([
            'scene_id' => $scene->id,
        ]);

        $interactable2 = Interactable::factory()->create([
            'scene_id' => $scene->id,
        ]);

        $this->assertCount(2, $scene->interactables);

        $this->assertTrue(
            $scene->interactables->contains($interactable1)
        );

        $this->assertTrue(
            $scene->interactables->contains($interactable2)
        );
    }

    public function test_interactable_belongs_to_scene(): void
    {
        $scene = Scene::factory()->create();

        $interactable = Interactable::factory()->create([
            'scene_id' => $scene->id,
        ]);

        $this->assertTrue(
            $interactable->scene->is($scene)
        );
    }

    public function test_admin_can_create_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            "/scenes/{$scene->id}/interactables",
            [
                'name' => 'Chiếc hộp gỗ',
                'description' => 'Một chiếc hộp cũ.',
                'type' => 'container',
                'position_x' => 50,
                'position_y' => 40,
                'width' => 20,
                'height' => 15,
                'status' => 'draft',
            ]
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('interactables', [
            'scene_id' => $scene->id,
            'name' => 'Chiếc hộp gỗ',
            'type' => 'container',
        ]);
    }

    public function test_non_admin_cannot_create_interactable(): void
    {
        $user = User::factory()->create();
        $scene = Scene::factory()->create();

        $response = $this->actingAs($user)->postJson(
            "/scenes/{$scene->id}/interactables",
            [
                'name' => 'Chiếc hộp gỗ',
                'description' => 'Một chiếc hộp cũ.',
                'type' => 'container',
                'position_x' => 50,
                'position_y' => 40,
                'width' => 20,
                'height' => 15,
                'status' => 'draft',
            ]
        );

        $response->assertStatus(403);

        $this->assertDatabaseMissing('interactables', [
            'scene_id' => $scene->id,
            'name' => 'Chiếc hộp gỗ',
            'type' => 'container',
        ]);
    }

    public function test_name_is_required_to_create_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            "/scenes/{$scene->id}/interactables",
            [
                'description' => 'Một chiếc hộp cũ.',
                'type' => 'container',
                'position_x' => 50,
                'position_y' => 40,
                'width' => 20,
                'height' => 15,
                'status' => 'draft',
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_type_must_be_valid_to_create_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            "/scenes/{$scene->id}/interactables",
            [
                'name' => 'Chiếc hộp gỗ',
                'description' => 'Một chiếc hộp cũ.',
                'type' => 'invalid_type',
                'position_x' => 50,
                'position_y' => 40,
                'width' => 20,
                'height' => 15,
                'status' => 'draft',
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function test_position_x_must_be_numeric_to_create_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            "/scenes/{$scene->id}/interactables",
            [
                'name' => 'Chiếc hộp gỗ',
                'description' => 'Một chiếc hộp cũ.',
                'type' => 'container',
                'position_x' => 'not_a_number',
                'position_y' => 40,
                'width' => 20,
                'height' => 15,
                'status' => 'draft',
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('position_x');
    }

    public function test_position_y_must_be_numeric_to_create_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            "/scenes/{$scene->id}/interactables",
            [
                'name' => 'Chiếc hộp gỗ',
                'description' => 'Một chiếc hộp cũ.',
                'type' => 'container',
                'position_x' => 50,
                'position_y' => 'not_a_number',
                'width' => 20,
                'height' => 15,
                'status' => 'draft',
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('position_y');
    }

    public function test_width_must_be_numeric_to_create_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            "/scenes/{$scene->id}/interactables",
            [
                'name' => 'Chiếc hộp gỗ',
                'description' => 'Một chiếc hộp cũ.',
                'type' => 'container',
                'position_x' => 50,
                'position_y' => 40,
                'width' => 'not_a_number',
                'height' => 15,
                'status' => 'draft',
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('width');
    }

    public function test_height_must_be_numeric_to_create_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            "/scenes/{$scene->id}/interactables",
            [
                'name' => 'Chiếc hộp gỗ',
                'description' => 'Một chiếc hộp cũ.',
                'type' => 'container',
                'position_x' => 50,
                'position_y' => 40,
                'width' => 20,
                'height' => 'not_a_number',
                'status' => 'draft',
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('height');
    }

    public function test_status_must_be_valid_to_create_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            "/scenes/{$scene->id}/interactables",
            [
                'name' => 'Chiếc hộp gỗ',
                'description' => 'Một chiếc hộp cũ.',
                'type' => 'container',
                'position_x' => 50,
                'position_y' => 40,
                'width' => 20,
                'height' => 15,
                'status' => 'invalid_status',
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_admin_can_update_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();
        $interactable = Interactable::factory()->create([
            'scene_id' => $scene->id,
        ]);

        $response = $this->actingAs($admin)->putJson(
            "/scenes/{$scene->id}/interactables/{$interactable->id}",
            [
                'name' => 'Chiếc hộp gỗ cập nhật',
                'description' => 'Một chiếc hộp cũ đã được cập nhật.',
                'type' => 'container',
                'position_x' => 60,
                'position_y' => 50,
                'width' => 25,
                'height' => 20,
                'status' => 'published',
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('interactables', [
            'id' => $interactable->id,
            'name' => 'Chiếc hộp gỗ cập nhật',
            'type' => 'container',
            'status' => 'published',
        ]);
    }

    public function test_non_admin_cannot_update_interactable(): void
    {
        $user = User::factory()->create();
        $scene = Scene::factory()->create();
        $interactable = Interactable::factory()->create([
            'scene_id' => $scene->id,
        ]);

        $response = $this->actingAs($user)->putJson(
            "/scenes/{$scene->id}/interactables/{$interactable->id}",
            [
                'name' => 'Chiếc hộp gỗ cập nhật',
                'description' => 'Một chiếc hộp cũ đã được cập nhật.',
                'type' => 'container',
                'position_x' => 60,
                'position_y' => 50,
                'width' => 25,
                'height' => 20,
                'status' => 'published',
            ]
        );

        $response->assertStatus(403);

        $this->assertDatabaseMissing('interactables', [
            'id' => $interactable->id,
            'name' => 'Chiếc hộp gỗ cập nhật',
            'type' => 'container',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_delete_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $scene = Scene::factory()->create();
        $interactable = Interactable::factory()->create([
            'scene_id' => $scene->id,
        ]);

        $response = $this->actingAs($admin)->deleteJson(
            "/scenes/{$scene->id}/interactables/{$interactable->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('interactables', [
            'id' => $interactable->id,
        ]);
    }

    public function test_non_admin_cannot_delete_interactable(): void
    {
        $user = User::factory()->create();
        $scene = Scene::factory()->create();
        $interactable = Interactable::factory()->create([
            'scene_id' => $scene->id,
        ]);

        $response = $this->actingAs($user)->deleteJson(
            "/scenes/{$scene->id}/interactables/{$interactable->id}"
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas('interactables', [
            'id' => $interactable->id,
        ]);
    }

    public function test_admin_can_view_interactable(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $scene = Scene::factory()->create();

        $interactable = Interactable::factory()->create([
            'scene_id' => $scene->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/scenes/{$scene->id}/interactables/{$interactable->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'id' => $interactable->id,
            'scene_id' => $scene->id,
            'name' => $interactable->name,
        ]);
    }

    public function test_can_list_interactables_of_scene(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $scene = Scene::factory()->create();

        Interactable::factory()->count(2)->create([
            'scene_id' => $scene->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/scenes/{$scene->id}/interactables");

        $response->assertStatus(200);

        $response->assertJsonCount(2);
    }

    public function test_cannot_view_interactable_from_another_scene(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $scene1 = Scene::factory()->create();
        $scene2 = Scene::factory()->create();

        $interactable = Interactable::factory()->create([
            'scene_id' => $scene1->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/scenes/{$scene2->id}/interactables/{$interactable->id}");

        $response->assertStatus(404);
    }

}
