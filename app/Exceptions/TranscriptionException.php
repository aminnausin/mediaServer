<?php

namespace App\Exceptions;

class TranscriptionException extends \RuntimeException {
    public function __construct(
        string $message,
        private readonly int $status,
        private readonly ?string $error = null,
    ) {
        parent::__construct($message);
    }

    public function status(): int {
        return $this->status;
    }

    public function error(): ?string {
        return $this->error;
    }
}
