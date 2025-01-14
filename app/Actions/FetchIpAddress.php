<?php

namespace App\Actions;

interface FetchesIpAddress {
    public function __invoke(): ?string;
}

class FetchIpAddress implements FetchesIpAddress {
    public function __invoke(): ?string {
        if (app()->runningInConsole()) {
            return 'console';
        }

        return request()->ip();
    }
}
