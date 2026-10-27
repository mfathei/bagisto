<?php

namespace Webkul\Saas\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Assign a UUID when the model is created without one.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
