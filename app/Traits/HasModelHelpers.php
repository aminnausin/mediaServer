<?php

namespace App\Traits;

trait HasModelHelpers {
    protected function conflictsWithAnother(string $foreignKey, $existing, $id): bool {
        return $existing && $existing->{$foreignKey} != $id;
    }

    protected function updateExisting($model, array $validated) {
        $model->update($validated);

        return $model;
    }
}
