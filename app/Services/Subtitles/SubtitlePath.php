<?php

namespace App\Services\Subtitles;

class SubtitlePath {
    private const BASE_PATH = 'metadata/media';

    public static function directory(string $metadataUuid): string {
        $shard = substr($metadataUuid, 0, 2);

        return self::BASE_PATH . "/{$shard}/{$metadataUuid}/subtitles";
    }

    public static function file(string $metadataUuid, int $trackId, string $format, ?string $language = null): string {
        $languageSlug = $language ? ".{$language}" : '';

        return self::directory($metadataUuid) . "/{$trackId}{$languageSlug}.{$format}";
    }
}
