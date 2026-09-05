<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Chapter;
use App\Models\Interactable;

class Scene extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'description',
        'background_image',
        'order',
        'status',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function interactables()
    {
        return $this->hasMany(Interactable::class);
    }
}
