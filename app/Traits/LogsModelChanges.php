<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait LogsModelChanges {
    protected function logModelChanges(Model $model, array $context = [], ?User $user = null): void {
        $diff = collect($model->getDirty())->map(
            fn ($new, $field) => "{$field}: '{$model->getOriginal($field)}' → '{$new}'"
        )->values()->toArray();

        $username = $user?->email ?? 'user';

        Log::info(class_basename($model) . ' changed by ' . $username, [
            'id' => $model->getKey(),
            'diff' => $diff,
            ...$context,
        ]);
    }
}
