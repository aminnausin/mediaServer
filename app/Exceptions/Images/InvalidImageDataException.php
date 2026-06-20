<?php

namespace App\Exceptions\Images;

use Exception;

class InvalidImageDataException extends Exception {
    private array $context = [];

    public function __construct(string $message, ?array $context = []) {
        $this->context = $context ?? [];
        parent::__construct($message);
    }

    public function getContext(): array {
        return $this->context;
    }
}
