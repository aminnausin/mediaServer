<?php

namespace Tests\Unit\Services\Subtitles;

use App\Services\Subtitles\SubtitlePath;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubtitlePathTest extends TestCase {
    public function test_get_file_path_returns_correct_path() {
        $uuid = Str::uuid()->toString();
        $path = SubtitlePath::file($uuid, 2, 'vtt');

        $this->assertEquals('metadata/media/' . substr($uuid, 0, 2) . "/{$uuid}/subtitles/2.vtt", $path);
    }

    public function test_get_directory_path() {
        $uuid = Str::uuid()->toString();

        $this->assertEquals(
            'metadata/media/' . substr($uuid, 0, 2) . "/{$uuid}/subtitles",
            SubtitlePath::directory($uuid)
        );
    }
}
