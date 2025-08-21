<?php

namespace App\Exceptions;

use Exception;

class DataLostException extends Exception {
    public function __construct(string $message = 'Data Lost') {
        parent::__construct($message);
    }
}
