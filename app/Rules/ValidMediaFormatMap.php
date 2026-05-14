<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidMediaFormatMap implements ValidationRule {
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        if (! is_array($value)) {
            $fail('The format map must be an array.');

            return;
        }

        foreach ($value as $key => $container) {
            if (! preg_match('/^[a-z0-9]{1,10}$/', $key)) {
                $fail("Invalid extension '$key'. Must be lowercase alphanumeric, max 10 characters.");
            }

            if (! is_string($container) || empty($container)) {
                $fail("Invalid container value for '$key'. Must be a non-empty string.");
            }
        }
    }
}
