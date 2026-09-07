<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PlayerInventory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
    ];

    public function inventories(): HasMany
    {
        return $this->hasMany(PlayerInventory::class);
    }
}
