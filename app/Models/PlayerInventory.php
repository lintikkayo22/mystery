<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PlayerGameProgress;
use App\Models\Item;

class PlayerInventory extends Model
{
    use HasFactory;

    protected $table = 'player_inventories';

    protected $fillable = [
        'player_game_progress_id',
        'item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function gameProgress(): BelongsTo
    {
        return $this->belongsTo(
            PlayerGameProgress::class,
            'player_game_progress_id'
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
