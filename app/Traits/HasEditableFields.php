<?php

namespace App\Traits;

trait HasEditableFields {
    public function resetEditableFields(): void {
        foreach ($this->getEditableFields() as $field) {
            $this->{$field} = null;
        }

        $this->save();
    }

    /**
     * Static method: resets editable fields for all rows in the table (SQL bulk update).
     */
    public static function resetAllEditableFields(): void {
        $fields = (new static)->getEditableFields();
        $updates = array_fill_keys($fields, null);

        static::query()->update($updates);
    }
}
