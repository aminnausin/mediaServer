<?php

namespace App\Jobs\Proxy;

class AsyncPlausibleEvent extends AsyncProxyEvent {
    protected function url(): string {
        return rtrim(config('services.plausible.domain'), '/') . '/api/event';
    }
}
