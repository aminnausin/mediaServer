<?php

namespace App\Services\Hardware;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class HardwareDetectionService {
    private ?HardwareProfile $cached = null;

    public function detect(): HardwareProfile {
        if ($this->cached) {
            return $this->cached;
        }

        $cached = Cache::remember('ffmpeg_hardware_profile', 86400, function () {
            $hwaccels = $this->getHwaccels();
            $encoders = $this->getEncoders();

            return [
                'cuda' => in_array('cuda', $hwaccels) && $this->validateCuda(),
                'qsv' => in_array('qsv', $hwaccels) && in_array('mjpeg_qsv', $encoders) && $this->validateQsv(),
                'vaapi' => in_array('vaapi', $hwaccels) && $this->validateVaapi(),
            ];
        });

        return $this->cached = new HardwareProfile(
            cuda: $cached['cuda'],
            qsv: $cached['qsv'],
            vaapi: $cached['vaapi'],
        );
    }

    public function getHwaccels(): array {
        $process = new Process(['ffmpeg', '-hwaccels', '-hide_banner']);
        $process->run();
        $output = $process->getOutput();

        $lines = array_slice(explode("\n", trim($output)), 1);

        return array_values(array_filter(array_map('trim', $lines)));
    }

    public function getEncoders(): array {
        $process = new Process(['ffmpeg', '-encoders', '-hide_banner']);
        $process->run();
        preg_match_all('/\s(\w+)\s/', $process->getOutput(), $matches);

        return $matches[1] ?? [];
    }

    public function validateCuda(): bool {
        $process = new Process([
            'ffmpeg',
            '-hide_banner',
            '-loglevel',
            'error',
            '-hwaccel',
            'cuda',
            '-hwaccel_output_format',
            'cuda',
            '-f',
            'lavfi',
            '-i',
            'nullsrc=s=16x16',
            '-frames:v',
            '1',
            '-f',
            'null',
            '-',
        ]);
        $process->run();

        return $process->isSuccessful();
    }

    public function validateVaapi(): bool {
        $process = new Process([
            'ffmpeg',
            '-hide_banner',
            '-loglevel',
            'error',
            '-hwaccel',
            'vaapi',
            '-hwaccel_device',
            '/dev/dri/renderD128',
            '-f',
            'lavfi',
            '-i',
            'nullsrc=s=16x16',
            '-frames:v',
            '1',
            '-f',
            'null',
            '-',
        ]);
        $process->run();

        return $process->isSuccessful();
    }

    private function validateQsv(): bool {
        $process = new Process([
            'ffmpeg',
            '-hide_banner',
            '-loglevel',
            'error',
            '-hwaccel',
            'qsv',
            '-f',
            'lavfi',
            '-i',
            'color=c=black:s=16x16:r=1',
            '-frames:v',
            '1',
            '-f',
            'null',
            '-',
        ]);
        $process->setTimeout(10);
        $process->run();

        return $process->isSuccessful();
    }
}
