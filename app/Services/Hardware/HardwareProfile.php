<?php

namespace App\Services\Hardware;

use App\Enums\HardwareType;

class HardwareProfile {
    public function __construct(
        public bool $cuda,
        public bool $qsv,
        public bool $vaapi,
    ) {}

    public function best(): HardwareType {
        return match (true) {
            $this->cuda => HardwareType::CUDA,
            $this->qsv => HardwareType::QSV,
            $this->vaapi => HardwareType::VAAPI,
            default => HardwareType::CPU,
        };
    }
}
