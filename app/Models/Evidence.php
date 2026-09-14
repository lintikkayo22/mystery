<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\PlayerGameProgress;

class Evidence extends Model
{
    use HasFactory;

    protected $table = 'evidence';

    protected $fillable = [
        'mystery_case_id',
        'title',
        'description',
        'type',
        'file_path',
    ];


    public function mysteryCase(): BelongsTo
    {
        return $this->belongsTo(MysteryCase::class);
    }

    public function playerProgresses(): BelongsToMany
    {
        return $this->belongsToMany(
            PlayerGameProgress::class,
            'player_evidences',
            'evidence_id',
            'player_game_progress_id'
        );
    }
}
