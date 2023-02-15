<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class Album extends Model
{
    use HasFactory, Usesuuid;

    /**
     * Get all of the media for the Album
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function media(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Post::class);
    }

}
