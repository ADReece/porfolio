<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'name',
        'user_id',
        'status',
        'event_date',
        'cover_photo_id',
        'private',
        'password',
        'watermarked',
        'hide_from_portfolio',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'private' => 'boolean',
        'watermarked' => 'boolean',
        'hide_from_portfolio' => 'boolean',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sets() : HasMany
    {
        return $this->hasMany(Set::class);
    }

    public function coverPhoto() : BelongsTo
    {
        return $this->belongsTo(Photo::class, 'cover_photo_id');
    }
}
