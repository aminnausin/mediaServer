<?php

namespace App\Enums;

enum SeriesRelationType: string {
    case SEQUEL = 'sequel';
    case PREQUEL = 'prequel';
    case SPINOFF = 'spinoff';
    case SIDE_STORY = 'side_story';
    case ALTERNATIVE = 'alternative';
    case SUMMARY = 'summary';
    case RELATED = 'related';
    case PARENT = 'parent';
    case SOUNDTRACK = 'soundtrack';
    case MAIN_SERIES = 'main_series';

    public function inverse(): ?self {
        return match ($this) {
            self::SEQUEL => self::PREQUEL,
            self::PREQUEL => self::SEQUEL,
            self::SPINOFF => self::PARENT,
            self::SIDE_STORY => self::PARENT,
            self::SUMMARY => self::PARENT,
            self::SOUNDTRACK => self::MAIN_SERIES,
            self::MAIN_SERIES => self::SOUNDTRACK,
            self::PARENT => null,
            self::ALTERNATIVE, self::RELATED => $this,
        };
    }

    public function isStorable(): bool {
        return match ($this) {
            self::PREQUEL, self::PARENT, self::MAIN_SERIES => false,
            default => true,
        };
    }

    public function label(): string {
        return match ($this) {
            self::SPINOFF => 'Spin Off',
            self::SIDE_STORY => 'Side Story',
            self::MAIN_SERIES => 'Main Series',
            default => ucfirst($this->value),
        };
    }
}
