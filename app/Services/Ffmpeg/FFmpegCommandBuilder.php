<?php

namespace App\Services\Ffmpeg;

use App\Enums\HardwareType;
use App\Services\Hardware\HardwareDetectionService;
use App\Services\Images\Storyboard\StoryboardOptions;

class FFmpegCommandBuilder {
    public function __construct(
        private HardwareDetectionService $hardware,
    ) {}

    public function storyboard(string $filePath, string $outputPattern, StoryboardOptions $options): array {
        $hw = $this->hardware->detect()->best();

        $defaultEncode = [
            'mjpeg',
        ];

        $hardwareOptions = match ($hw) {
            HardwareType::CUDA => [
                'decode' => ['-hwaccel', 'cuda'],
                'decode_flags' => ['-noautoscale'],
                'encode' => $defaultEncode,
            ],
            HardwareType::QSV => [
                'decode' => [],
                'decode_flags' => ['-noautoscale'],
                'encode' => ['mjpeg_qsv'],
            ],
            default => [
                'decode' => [],
                'decode_flags' => [],
                'encode' => $defaultEncode,
            ],
        };

        $w = $options->width;
        $h = $options->height;

        $scale = match ($hw) {
            HardwareType::QSV => "vpp_qsv=w={$w}:h={$h}:format=nv12:passthrough=0:out_range=pc:scale_mode=hq",
            default => "scale='min({$w},iw)':-2:flags=lanczos",
        };

        $qualityArgs = match ($hw) {
            HardwareType::QSV => ['-global_quality:v', '91'],
            default => ['-q:v', '3'],
        };

        $skipFrame = match (true) {
            $options->duration < 10 => [],
            default => [
                '-skip_frame',
                'nokey',
            ]
        };

        return [
            'ffmpeg',
            ...$hardwareOptions['decode'],
            ...$skipFrame,
            '-threads',
            '1',
            '-i',
            "file:{$filePath}",
            '-an',
            '-sn',
            '-vf',
            "fps={$options->fps},{$scale},setparams=color_primaries=bt709:color_trc=bt709:colorspace=bt709,tile={$options->cols}x{$options->rows}",
            '-threads',
            '1',
            '-c:v',
            ...$hardwareOptions['encode'],
            ...$qualityArgs,
            '-fps_mode',
            'passthrough',
            '-f',
            'image2',
            $outputPattern,
        ];
    }

    // region
    // TODO: Implement
    // public function posterFrame(string $filePath, string $outputPath, int $offset): array {
    //     return [];
    // }
    // public function extractAudio(string $filePath, string $outputPath): array {
    //     return [];
    // }
    // public function transcode(string $filePath, string $outputPath, TranscodeOptions $options): array {
    //     return [];
    // }
    // endregion
}
