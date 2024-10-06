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

    public function collection() : BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function media() : HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
