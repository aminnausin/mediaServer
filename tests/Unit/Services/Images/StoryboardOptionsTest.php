<?php

namespace Tests\Unit\Services\Images;

use App\Exceptions\StoryboardNotSupportedException;
use App\Models\Metadata;
use App\Models\Video;
use App\Services\Ffmpeg\FFprobeService;
use App\Services\Images\Storyboard\StoryboardOptions;
use Tests\TestCase;

class StoryboardOptionsTest extends TestCase {
    const VIDEO_MIME_TYPE = 'video/mp4';

    public function test_landscape_video_gets_correct_dimensions(): void {
        $metadata = Metadata::factory()->make([
            'codec' => 'h264',
            'duration' => 600,
            'resolution_width' => 1920,
            'resolution_height' => 1080,
            'mime_type' => self::VIDEO_MIME_TYPE,
        ]);

        $options = StoryboardOptions::fromMetadata($metadata);

        $this->assertEquals(320, $options->width);
        $this->assertEquals(180, $options->height);
    }

    public function test_portrait_video_gets_correct_dimensions(): void {
        $metadata = Metadata::factory()->make([
            'codec' => 'h264',
            'duration' => 600,
            'resolution_width' => 1080,
            'resolution_height' => 1920,
            'mime_type' => self::VIDEO_MIME_TYPE,
        ]);

        $options = StoryboardOptions::fromMetadata($metadata);

        $this->assertEquals(180, $options->width);
        $this->assertEquals(320, $options->height);
    }

    public function test_vp9_throws_not_supported(): void {
        $this->expectException(StoryboardNotSupportedException::class);

        $metadata = Metadata::factory()->make(['codec' => 'vp9', 'duration' => 600]);
        StoryboardOptions::fromMetadata($metadata);
    }

    public function test_short_video_gets_higher_fps(): void {
        $video = Video::factory()->create();
        $metadata = Metadata::factory()->create([
            'codec' => 'h264',
            'duration' => 16,
            'resolution_width' => 1920,
            'resolution_height' => 1080,
            'mime_type' => self::VIDEO_MIME_TYPE,
            'video_id' => $video->id,
        ]);

        $ffprobeMock = $this->createMock(FFprobeService::class);
        $ffprobeMock->method('countKeyframes')->willReturn(5);

        $options = StoryboardOptions::fromMetadata($metadata);

        $this->assertEquals(1, $options->fps);
    }
}
