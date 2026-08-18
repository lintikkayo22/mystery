<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MysteryCase;

class Clue extends Model
{
    use HasFactory;

    protected $fillable = [
        'mystery_case_id',
        'title',
        'content',
        'type',
        'is_revealed',
    ];

    protected $casts = [
        'is_revealed' => 'boolean',
    ];

    public function mysteryCase(): BelongsTo
    {
        return $this->belongsTo(MysteryCase::class);
    }

}
