<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Clue;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MysteryCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'difficulty',
        'status',
    ];

    public function clues(): HasMany
    {
        return $this->hasMany(Clue::class);
    }

}
