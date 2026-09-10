<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder query()
 * @method bool save(array $options = [])
 */
trait HasEditableFields {
    abstract protected function getEditableFields(): array;

    public function resetEditableFields(): void {
        $defaults = new static;

        foreach ($this->getEditableFields() as $field) {
            $this->{$field} = $defaults->{$field};
        }

        $this->save();
    }

    /**
     * Reset editable fields for all rows in the table (SQL bulk update).
     */
    public static function resetAllEditableFields(array $extraFields = []): void {
        $defaults = new static;

        $updates = [];
        foreach ([...$defaults->getEditableFields(), ...$extraFields] as $field) {
            $updates[$field] = $defaults->{$field};
        }

        static::query()->update($updates);
    }
}
