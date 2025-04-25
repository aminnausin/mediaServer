<?php

namespace App\Jobs;

use App\Services\PreviewGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePreviewImage implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $data,
        public string $path,
        public ?string $updatedAt = null
    ) {}

    public function handle(PreviewGeneratorService $previewGenerator) {
        $previewGenerator->generateImage($this->data, $this->path);
    }
}
