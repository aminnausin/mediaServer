<?php

namespace App\Enums;

enum ImageType: int {
    case POSTER = 0;
    case BANNER = 1;
    case AVATAR = 2;

    public function label(): string {
        return strtolower($this->name);
    }
}
