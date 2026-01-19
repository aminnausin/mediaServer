<?php

namespace App\Services\Subtitles;

class SubtitlePath {
    private const BASE_PATH = 'metadata/media';

    /**
     * Builds the ideal subtitle directory for a given metadata item
     *
     * @param  string  $metadataUuid  metadata uuid
     * @return string The ideal subtitle directory in the format /{shard}/{uuid}/subtitles
     */
    public static function buildDirectory(string $metadataUuid): string {
        $shard = substr($metadataUuid, 0, 2);

        return self::BASE_PATH . "/{$shard}/{$metadataUuid}/subtitles";
    }

    /**
     * Builds the ideal file path for a given subtitle track
     *
     * @param  string  $metadataUuid  Related metadata uuid
     * @param  int  $trackId  Track index
     * @param  string  $format  File format
     * @param  ?string  $language  Language name (ex/ en, fr, ru, jp)
     * @return string The ideal subtitle file path in the format /{shard}/{uuid}/subtitles/{trackId}.{language}.{format}
     */
    public static function buildFilePath(string $metadataUuid, int $trackId, string $format, ?string $language = null): string {
        $languageSlug = $language ? ".{$language}" : '';

        return self::buildDirectory($metadataUuid) . "/{$trackId}{$languageSlug}.{$format}";
    }
}
