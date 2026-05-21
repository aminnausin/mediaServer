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
        if ($this->cuda) {
            return HardwareType::CUDA;
        }
        if ($this->qsv) {
            return HardwareType::QSV;
        }
        if ($this->vaapi) {
            return HardwareType::VAAPI;
        }

        return HardwareType::CPU;
    }
}
