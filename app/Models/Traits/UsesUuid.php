<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait UsesUuid {
    // Ensure this trait's boot logic runs even if the model defines its own boot()
    protected static function bootUsesUuid()
    {
        static::creating(function($model)
        {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
