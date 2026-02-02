<?php

namespace App\Traits;

trait HasModelHelpers {
    protected function conflictsWithAnother(string $foreignKey, $existing, $id): bool {
        return $existing && $existing->{$foreignKey} != $id;
    }

    protected function updateExisting($model, array $validated, bool $disableTimestamps = false) {
        if ($disableTimestamps) {
            $model->timestamps = false;
        }

        $model->update($validated);
        $model->timestamps = true;

        return $model;
    }
}
