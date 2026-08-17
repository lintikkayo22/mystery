<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminAuthorizationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use DatabaseTransactions;

    public function test_investigator_cannot_access_admin_route(): void
    {

        $investigatorRole = Role::where('name', 'investigator')->firstOrFail();

        $user = User::factory()->create([
            'role_id' => $investigatorRole->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/test');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_route(): void
    {
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $user = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/test');

        $response->assertOk();
    }
}
