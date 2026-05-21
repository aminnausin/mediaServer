<?php

namespace App\Services\Hardware;

use Symfony\Component\Process\Process;

class HardwareDetectionService {
    private ?HardwareProfile $cached = null;

    public function detect(): HardwareProfile {
        if ($this->cached) {
            return $this->cached;
        }

        $hwaccels = $this->getHwaccels();
        $encoders = $this->getEncoders();

        return $this->cached = new HardwareProfile(
            qsv: in_array('qsv', $hwaccels) && in_array('mjpeg_qsv', $encoders),
            cuda: in_array('cuda', $hwaccels),
            vaapi: in_array('vaapi', $hwaccels),
        );
    }

    private function getHwaccels(): array {
        $process = new Process(['ffmpeg', '-hwaccels', '-hide_banner']);
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
}
