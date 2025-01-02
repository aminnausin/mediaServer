<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tasks.{id}', function ($user, $id) {
    dump('dam');
    return (int) $user->id === (int) $id;
});

Broadcast::channel('management.{id}', function ($user, $id) {
    dump('dam');
    return (int) $user->id === (int) $id;
});


Broadcast::channel('dashboard', function () {
});
