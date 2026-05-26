<?php

namespace App\Enums;

enum ImageSource: int {
    case GENERATED = 0;
    case UPLOADED = 1;
    case API = 2;
    case URL = 3; // external url, not downloaded
    case DOWNLOADED = 4;

    public function label(): string {
        return strtolower($this->name);
    }
}
