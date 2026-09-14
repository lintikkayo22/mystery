<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Chapter;
use App\Models\Scene;
use App\Models\Clue;
use App\Models\Evidence;

class PlayerGameProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mystery_case_id',
        'current_chapter_id',
        'current_scene_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mysteryCase(): BelongsTo
    {
        return $this->belongsTo(MysteryCase::class);
    }

    public function currentChapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'current_chapter_id');
    }

    public function currentScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'current_scene_id');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(PlayerInventory::class);
    }

    public function clues(): BelongsToMany
    {
        return $this->belongsToMany(
            Clue::class,
            'player_clues',
            'player_game_progress_id',
            'clue_id'
        );
    }

    public function evidences(): BelongsToMany
    {
        return $this->belongsToMany(
            Evidence::class,
            'player_evidences',
            'player_game_progress_id',
            'evidence_id'
        );
    }

    public function states(): HasMany
    {
        return $this->hasMany(PlayerGameState::class);
    }
}
