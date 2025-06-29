<?php

namespace App\Support;

class RequestPresets {
    public const NON_NEGATIVE_INT = 'nullable|integer|min:0';

    public const PERCENTAGE = 'nullable|integer|min:0|max:100';

    public const LONG_TEXT = 'nullable|max:255';

    public const OPTIONAL_DATE = 'nullable|date|date_format:"F d, Y"';
}
