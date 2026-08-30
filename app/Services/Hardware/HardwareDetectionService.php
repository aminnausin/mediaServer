<?php

namespace App\Services\Hardware;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class HardwareDetectionService {
    private ?HardwareProfile $cached = null;

    const PROFILE_VERSION = 2;

    const CACHE_KEY = 'ffmpeg_hardware_profile_v' . self::PROFILE_VERSION;

    const INTEL_VENDOR_ID = '0x8086';

    const DEFAULT_ARGUMENTS = [
        '-hide_banner',
        '-loglevel',
        'error',
    ];

    public function detect(): HardwareProfile {
        if ($this->cached) {
            return $this->cached;
        }

        $profile = Cache::remember(self::CACHE_KEY, 86400, function () {
            $hwaccels = $this->getHwaccels();
            $encoders = $this->getEncoders();
            $vaapiDevice = $this->findIntelRenderDevice();

            return [
                'cuda' => in_array('cuda', $hwaccels) && $this->validateCuda(),
                'qsv' => in_array('qsv', $hwaccels) && in_array('mjpeg_qsv', $encoders) ? $this->validateQsv($vaapiDevice) : null,
                'vaapi' => in_array('vaapi', $hwaccels) && $this->validateVaapi(),
                'vaapi_device' => $vaapiDevice,
            ];
        });

        return $this->cached = new HardwareProfile(
            cuda: $profile['cuda'],
            qsv: $profile['qsv'],
            vaapi: $profile['vaapi'],
            vaapiDevice: $profile['vaapi_device']
        );
    }

    private function getHwaccels(): array {
        $process = new Process(['ffmpeg', '-hwaccels', ...self::DEFAULT_ARGUMENTS]);
        $process->run();
        $output = $process->getOutput();

        $lines = array_slice(explode("\n", trim($output)), 1);

        return array_values(array_filter(array_map('trim', $lines)));
    }

    private function getEncoders(): array {
        $process = new Process(['ffmpeg', '-encoders', '-hide_banner']);
        $process->run();
        preg_match_all('/\s(\w+)\s/', $process->getOutput(), $matches);

        return $matches[1] ?? [];
    }

    private function validateCuda(): bool {
        $process = new Process([
            'ffmpeg',
            ...self::DEFAULT_ARGUMENTS,
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

    private function validateVaapi(): bool {
        $process = new Process([
            'ffmpeg',
            ...self::DEFAULT_ARGUMENTS,
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

    private function validateQsv(?string $vaapiDevice): ?string {
        return match (PHP_OS_FAMILY) {
            'Windows' => $this->validateQsvD3d11va() ? 'derive_d3d11va' : null,
            'Linux' => $this->validateQsvVaapi($vaapiDevice) ? 'derive_vaapi' : null,
            default => null
        };
    }

    /**
     * Linux QSV detection using vaapi and a specific device path
     */
    private function validateQsvVaapi(string $devicePath): bool {
        if (! $devicePath) {
            return false;
        }
        $process = new Process([
            'ffmpeg',
            ...self::DEFAULT_ARGUMENTS,
            '-init_hw_device',
            "vaapi=va:{$devicePath}",
            '-init_hw_device',
            'qsv=qs@va',
            '-filter_hw_device',
            'qs',
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

    /**
     * Windows QSV detection using D3D11VA and the Intel vendor ID
     */
    private function validateQsvD3d11va(): bool {
        $process = new Process([
            'ffmpeg',
            ...self::DEFAULT_ARGUMENTS,
            '-init_hw_device',
            'd3d11va=dx11:,vendor=' . self::INTEL_VENDOR_ID,
            '-init_hw_device',
            'qsv=qs@dx11',
            '-filter_hw_device',
            'qs',
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

    /**
     * Searches for a render node with the Intel vendor ID
     */
    private function findIntelRenderDevice(): ?string {
        $renderNodes = glob('/dev/dri/renderD*') ?: [];

        foreach ($renderNodes as $node) {
            $name = basename($node);
            $vendorPath = "/sys/class/drm/{$name}/device/vendor";

            if (is_readable($vendorPath) && trim(file_get_contents($vendorPath)) === self::INTEL_VENDOR_ID) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Old way of deriving basic qsv without vaapi or D3d11va
     * This method was often slower than just using the CPU
     */
    private function validateQsvPlain(): bool {
        $process = new Process([
            'ffmpeg',
            ...self::DEFAULT_ARGUMENTS,
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
