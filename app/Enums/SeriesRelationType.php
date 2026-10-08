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

    public function inverse(): ?self {
        return match ($this) {
            self::SEQUEL => self::PREQUEL,
            self::PREQUEL => self::SEQUEL,
            self::SPINOFF => self::PARENT,
            self::SIDE_STORY => self::PARENT,
            self::SUMMARY => self::PARENT,
            self::SOUNDTRACK => self::PARENT,
            self::PARENT => null,
            self::ALTERNATIVE, self::RELATED => $this,
        };
    }

    public function isStorable(): bool {
        return match ($this) {
            self::PREQUEL, self::PARENT => false,
            default => true,
        };
    }

    public function label(): string {
        return match ($this) {
            self::SPINOFF => 'Spin Off',
            self::SIDE_STORY => 'Side Story',
            default => ucfirst($this->value),
        };
    }
}
