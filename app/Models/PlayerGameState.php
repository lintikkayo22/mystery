<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerGameState extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_game_progress_id',
        'key',
        'value',
    ];

    public function gameProgress(): BelongsTo
    {
        return $this->belongsTo(
            PlayerGameProgress::class,
            'player_game_progress_id'
        );
    }
}
