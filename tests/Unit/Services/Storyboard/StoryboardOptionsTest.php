<?php

namespace Tests\Unit\Services\Storyboard;

use App\Exceptions\StoryboardNotSupportedException;
use App\Models\Metadata;
use App\Services\Images\Storyboard\StoryboardOptions;
use Tests\TestCase;

class StoryboardOptionsTest extends TestCase {
    public function test_landscape_video_gets_correct_dimensions(): void {
        $metadata = Metadata::factory()->make([
            'codec' => 'h264',
            'duration' => 600,
            'resolution_width' => 1920,
            'resolution_height' => 1080,
            'mime_type' => 'video/mp4',
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
            'mime_type' => 'video/mp4',
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
        $metadata = Metadata::factory()->make([
            'codec' => 'h264',
            'duration' => 16,
            'resolution_width' => 1920,
            'resolution_height' => 1080,
            'mime_type' => 'video/mp4',
        ]);

        $options = StoryboardOptions::fromMetadata($metadata);

        $this->assertEquals(1, $options->fps);
    }
}
