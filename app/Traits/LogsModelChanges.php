<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait LogsModelChanges {
    protected function logModelChanges(Model $model, array $context = []): void {
        $diff = collect($model->getDirty())->map(
            fn ($new, $field) => "{$field}: '{$model->getOriginal($field)}' → '{$new}'"
        )->values()->toArray();

        Log::info(class_basename($model) . ' changed by user', [
            'id' => $model->getKey(),
            'diff' => $diff,
            ...$context,
        ]);
    }
}
