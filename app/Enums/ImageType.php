<?php

namespace App\Enums;

enum ImageType: string {
    case POSTER = 'poster';
    case BANNER = 'banner';
    case AVATAR = 'avatar';
    case PREVIEW = 'preview';
}
