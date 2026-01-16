<?php

namespace Tests\Unit\Models;

use App\Models\Metadata;
use App\Models\Subtitle;
use Tests\TestCase;

class SubtitleTest extends TestCase {
    public function test_get_file_path_returns_correct_path() {
        $metadata = Metadata::factory()->create();
        $subtitle = Subtitle::factory()->create([
            'metadata_uuid' => $metadata->uuid,
            'track_id' => 2,
        ]);

        $path = $subtitle->getFilePath('vtt');

        $this->assertEquals('metadata/media/' . substr($metadata->uuid, 0, 2) . "/{$metadata->uuid}/subtitles/2.vtt", $path);
    }

    public function test_get_directory_path() {
        $metadata = Metadata::factory()->create();
        $subtitle = Subtitle::factory()->create([
            'metadata_uuid' => $metadata->uuid,
            'track_id' => 2,
        ]);

        $this->assertEquals(
            'metadata/media/' . substr($metadata->uuid, 0, 2) . "/{$metadata->uuid}/subtitles",
            $subtitle->getDirectoryPath()
        );
    }
}
