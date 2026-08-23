<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'mystery_case_id',
        'title',
        'description',
        'type',
        'file_path',
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
