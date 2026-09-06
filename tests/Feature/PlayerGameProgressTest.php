<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Chapter;
use App\Models\MysteryCase;
use App\Models\Scene;

class PlayerGameProgressTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_users_have_independent_progress_for_the_same_mystery_case(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $case = MysteryCase::factory()->create();

        $chapter = Chapter::factory()->create([
            'mystery_case_id' => $case->id,
        ]);

        $scene1 = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $scene2 = Scene::factory()->create([
            'chapter_id' => $chapter->id,
        ]);

        $progressA = $userA->gameProgress()->create([
            'mystery_case_id' => $case->id,
            'current_chapter_id' => $chapter->id,
            'current_scene_id' => $scene2->id,
        ]);

        $progressB = $userB->gameProgress()->create([
            'mystery_case_id' => $case->id,
            'current_chapter_id' => $chapter->id,
            'current_scene_id' => $scene1->id,
        ]);

        $this->assertNotEquals(
            $progressA->current_scene_id,
            $progressB->current_scene_id
        );
    }

    public function test_user_cannot_have_duplicate_progress_for_the_same_mystery_case(): void
    {
        $user = User::factory()->create();
        $case = MysteryCase::factory()->create();

        $user->gameProgress()->create([
            'mystery_case_id' => $case->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $user->gameProgress()->create([
            'mystery_case_id' => $case->id,
        ]);
    }

    
}
