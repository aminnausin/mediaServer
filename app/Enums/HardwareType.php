<?php

namespace App\Enums;

enum HardwareType: int {
    case CPU = 0;
    case CUDA = 1;
    case QSV = 2;
    case VAAPI = 3;

    public function label(): string {
        return strtolower($this->name);
    }

    public static function fromLabel(string $label): ?self {
        return collect(self::cases())->first(fn ($case) => strtolower($case->name) === strtolower($label));
    }
}
