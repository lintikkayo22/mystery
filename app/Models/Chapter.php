<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MysteryCase;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'order',
        'status',
    ];

    public function mysteryCase()
    {
        return $this->belongsTo(MysteryCase::class);
    }
}
