<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ScopesPublic
{
    public function scopePublic(Builder $builder) : void
    {
        $builder->where('public', true);
    }
}