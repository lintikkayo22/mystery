<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
