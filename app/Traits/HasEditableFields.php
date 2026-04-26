<?php

namespace App\Traits;

trait HasEditableFields {
    public function resetEditableFields(): void {
        $fresh = new static;

        foreach ($this->getEditableFields() as $field) {
            $this->{$field} = $fresh->{$field};
        }

        $this->save();
    }

    /**
     * Static method: resets editable fields for all rows in the table (SQL bulk update).
     */
    public static function resetAllEditableFields(): void {
        $fresh = new static;
        $fields = $fresh->getEditableFields();

        $updates = [];
        foreach ($fields as $field) {
            $updates[$field] = $fresh->{$field};
        }

        static::query()->update($updates);
    }
}
