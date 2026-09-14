<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InteractionCondition;
use App\Models\InteractionEffect;

class Interaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(InteractionCondition::class);
    }

    public function effects(): HasMany
    {
        return $this->hasMany(InteractionEffect::class);
    }
}
