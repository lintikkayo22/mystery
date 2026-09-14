<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\PlayerGameProgress;
use App\Models\MysteryCase;

class Clue extends Model
{
    use HasFactory;

    protected $fillable = [
        'mystery_case_id',
        'title',
        'content',
        'type',
    ];

    public function mysteryCase(): BelongsTo
    {
        return $this->belongsTo(MysteryCase::class);
    }

    public function playerProgresses(): BelongsToMany
    {
        return $this->belongsToMany(
            PlayerGameProgress::class,
            'player_clues',
            'clue_id',
            'player_game_progress_id'
        );
    }

}
