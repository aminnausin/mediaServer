<?php

namespace App\Jobs\Proxy;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

abstract class AsyncProxyEvent implements ShouldQueue {
    use Dispatchable, Queueable;

    public function __construct(
        public string $body,
        public array $headers = [],
    ) {
        $this->onQueue('high');
    }

    abstract protected function url(): string;

    public function handle(): void {
        Http::timeout(2)
            ->connectTimeout(1)
            ->withHeaders($this->headers)
            ->withBody($this->body, $this->headers['Content-Type'] ?? 'application/json')
            ->post($this->url());
    }
}
