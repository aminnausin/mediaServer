<?php

namespace Tests\Unit\Services\FFmpeg;

use App\Enums\HardwareType;
use App\Services\Ffmpeg\FFmpegCommandBuilder;
use App\Services\Hardware\HardwareDetectionService;
use App\Services\Hardware\HardwareProfile;
use App\Services\Images\Storyboard\StoryboardOptions;
use Tests\TestCase;

class FFmpegCommandBuilderTest extends TestCase {
    const INPUT_PATH = '/input.mkv';

    const OUTPUT_PATH = '/output/%d.jpg';

    private function makeBuilder(HardwareType $type): FFmpegCommandBuilder {
        $profile = new HardwareProfile(
            cuda: $type === HardwareType::CUDA,
            qsv: $type === HardwareType::QSV,
            vaapi: $type === HardwareType::VAAPI,
        );

        $detector = $this->createMock(HardwareDetectionService::class);
        $detector->method('detect')->willReturn($profile);

        return new FFmpegCommandBuilder($detector);
    }

    private function makeOptions(): StoryboardOptions {
        return new StoryboardOptions(
            width: 320,
            height: 180,
            cols: 10,
            rows: 10,
            fps: 0.1,
            tile: true,
            sourceWidth: 1920,
            sourceHeight: 1080,
            duration: 600,
            tileCount: 10,
            skipFrameNokey: true,
        );
    }

    public function test_cpu_command_has_no_hwaccel(): void {
        $command = $this->makeBuilder(HardwareType::CPU)->storyboard(self::INPUT_PATH, self::OUTPUT_PATH, $this->makeOptions());

        $this->assertNotContains('-hwaccel', $command);
        $this->assertContains('mjpeg', $command);
    }

    public function test_cuda_command_has_hwaccel(): void {
        $command = $this->makeBuilder(HardwareType::CUDA)->storyboard(self::INPUT_PATH, self::OUTPUT_PATH, $this->makeOptions());

        $this->assertContains('-hwaccel', $command);
        $this->assertContains('cuda', $command);
    }

    public function test_qsv_command_uses_mjpeg_qsv_encoder(): void {
        $command = $this->makeBuilder(HardwareType::QSV)->storyboard(self::INPUT_PATH, self::OUTPUT_PATH, $this->makeOptions());

        $this->assertContains('mjpeg_qsv', $command);
        $this->assertContains('-global_quality:v', $command);
        $this->assertNotContains('-q:v', $command);
    }

    public function test_command_contains_skip_frame_nokey(): void {
        $command = $this->makeBuilder(HardwareType::CPU)->storyboard(self::INPUT_PATH, self::OUTPUT_PATH, $this->makeOptions());

        $this->assertContains('-skip_frame', $command);
        $this->assertContains('nokey', $command);
    }

    public function test_command_contains_correct_fps(): void {
        $command = $this->makeBuilder(HardwareType::CPU)->storyboard(self::INPUT_PATH, self::OUTPUT_PATH, $this->makeOptions());

        $vfIndex = array_search('-vf', $command);
        $this->assertStringContainsString('fps=0.1', $command[$vfIndex + 1]);
    }

    public function test_command_contains_tile_filter(): void {
        $command = $this->makeBuilder(HardwareType::CPU)->storyboard(self::INPUT_PATH, self::OUTPUT_PATH, $this->makeOptions());

        $vfIndex = array_search('-vf', $command);
        $this->assertStringContainsString('tile=10x10', $command[$vfIndex + 1]);
    }
}
