<?php

namespace App\Support;

use Illuminate\Support\Str;

class UrlValidator {
    public function isExternalUrl(string $url): bool {
        //  ! strpos($metadata->poster_url, str_replace('http://', '', str_replace('https://', '', config('api.app_url')))
        return $url && ! Str::startsWith($url, config('app.scheme') . '://' . config('app.host'));
    }
}
