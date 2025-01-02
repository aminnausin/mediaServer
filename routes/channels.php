<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tasks.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('dashboard', function () {});
