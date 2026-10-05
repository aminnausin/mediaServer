<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasMeaningfulChanges {
    protected function hasMeaningfulChanges(Model $model, array $ignoredFields = []): bool {
        return collect($model->getDirty())->except($ignoredFields)
            ->contains(function ($value, $key) use ($model) {
                $original = $model->getOriginal($key);

                return ! (($original === '' && $value === null) || ($original === null && $value === ''));
            });
    }
}
