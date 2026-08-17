<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\MysteryCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MysteryCaseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use DatabaseTransactions;

    public function test_new_mystery_case_can_be_created(): void
    {
        $response = $this->post('/mystery-cases', [
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
            'description' => 'This is a test mystery case.',
            'cover_image' => 'https://example.com/cover.jpg',
            'difficulty' => 'medium',
        ]);
    }

    public function test_new_mystery_case_is_draft_by_default(): void
    {
        $case = MysteryCase::factory()->create();

        $this->assertEquals('draft', $case->status);
    }
}
