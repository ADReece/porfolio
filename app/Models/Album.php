<?php

namespace App\Models;

use App\Models\Traits\ScopesPublic;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Album extends Model
{
    use HasFactory, Usesuuid, ScopesPublic;

    /**
     * Get all the media for the Album
     *
     * @return BelongsToMany
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class);
    }

}
