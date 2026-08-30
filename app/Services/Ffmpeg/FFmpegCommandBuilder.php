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
        $profile = $this->hardware->detect();
        $hw = $profile->best();

        $defaultEncode = ['mjpeg'];
        $w = $options->width;
        $h = $options->height;

        $hardwareOptions = match (true) {
            $hw === HardwareType::CUDA => [
                'init' => [],
                'decode' => ['-hwaccel', 'cuda'],
                'decode_flags' => ['-noautoscale'],
                'encode' => $defaultEncode,
            ],
            $hw === HardwareType::QSV && $profile->qsv === 'derive_vaapi' && $profile->vaapiDevice => [
                'init' => [
                    '-init_hw_device',
                    "vaapi=va:{$profile->vaapiDevice}",
                    '-init_hw_device',
                    'qsv=qs@va',
                    '-filter_hw_device',
                    'qs',
                ],
                'decode' => ['-hwaccel', 'vaapi', '-hwaccel_output_format', 'vaapi'],
                'decode_flags' => [],
                'encode' => ['mjpeg_qsv'],
            ],
            $hw === HardwareType::QSV && $profile->qsv === 'derive_d3d11va' => [
                'init' => [
                    '-init_hw_device',
                    'd3d11va=dx11:,vendor=' . HardwareDetectionService::INTEL_VENDOR_ID,
                    '-init_hw_device',
                    'qsv=qs@dx11',
                    '-filter_hw_device',
                    'qs',
                ],
                'decode' => ['-hwaccel', 'd3d11va', '-hwaccel_output_format', 'd3d11'],
                'decode_flags' => [],
                'encode' => ['mjpeg_qsv'],
            ],
            default => [
                'init' => [],
                'decode' => [],
                'decode_flags' => [],
                'encode' => $defaultEncode,
            ],
        };

        $scale = match ($hw) {
            HardwareType::QSV => "hwmap=derive_device=qsv,vpp_qsv=w={$w}:h={$h}:format=nv12:out_range=pc:scale_mode=hq",
            default => "scale='min({$w},iw)':-2:flags=lanczos",
        };

        $qualityArgs = match ($hw) {
            HardwareType::QSV => ['-global_quality:v', '91'],
            default => ['-q:v', '3'],
        };

        $skipFrame = match (true) {
            $options->skipFrameNokey => [
                '-skip_frame',
                'nokey',
            ],
            default => []
        };

        return [
            'ffmpeg',
            ...$hardwareOptions['init'],
            ...$hardwareOptions['decode'],
            ...$skipFrame,
            '-threads',
            '1',
            '-i',
            "file:{$filePath}",
            '-an',
            '-sn',
            '-vf',
            "fps={$options->fps},setparams=color_primaries=bt709:color_trc=bt709:colorspace=bt709,{$scale},tile={$options->cols}x{$options->rows}",
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
