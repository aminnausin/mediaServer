<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedResetPassword extends BaseResetPassword implements ShouldQueue {
    use Queueable;

    public $token;

    public function __construct($token) {
        parent::__construct($token);
        $this->queue = 'high';
    }
}
