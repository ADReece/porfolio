<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Set extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'collection_id',
        'name',
        'hide_from_portfolio'
    ];

    protected $casts = [
        'hide_from_portfolio' => 'boolean',
    ];

    public function collection() : BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function photos() : HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
