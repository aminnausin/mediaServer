<?php

namespace App\Services\Hardware;

use App\Enums\HardwareType;

class HardwareProfile {
    const PROFILE_VERSION = 2;

    public function __construct(
        public bool $cuda,
        public ?string $qsv, // null | 'derive_vaapi' | 'derive_d3d11va'
        public bool $vaapi,
        public ?string $vaapiDevice,
    ) {}

    public function best(): HardwareType {
        return match (true) {
            $this->cuda => HardwareType::CUDA,
            $this->qsv !== null => HardwareType::QSV,
            default => HardwareType::CPU,
        };
    }
}
