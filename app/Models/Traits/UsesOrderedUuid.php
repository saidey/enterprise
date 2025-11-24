<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait UsesOrderedUuid
{
    protected static function bootUsesOrderedUuid()
    {
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::orderedUuid();
            }
        });
    }

    public function initializeUsesOrderedUuid()
    {
        $this->keyType = 'string';
        $this->incrementing = false;
    }
}