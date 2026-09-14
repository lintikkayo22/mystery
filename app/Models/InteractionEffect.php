<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Interaction;

class InteractionEffect extends Model
{
    use HasFactory;

    protected $fillable = [
        'interaction_id',
        'type',
        'value',
    ];
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(Interaction::class);
    }
}
