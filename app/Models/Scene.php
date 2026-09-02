<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'description',
        'order',
        'status',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
