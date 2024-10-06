<?php

namespace App\Models;

use App\Models\Traits\ScopesPublic;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Album extends Model
{
    use HasFactory, Usesuuid, ScopesPublic;

    protected $fillable = [
        'name',
        'description',
        'public',
        'password'
    ];

    /**
     * Get all the media for the Album
     *
     * @return BelongsToMany
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class);
    }

    public function cover(): HasOne
    {
        return $this->hasOne(Media::class, 'id', 'cover_id')
            ->withDefault(function($model){
                return $model->media()->take(1);
            });
    }

}
