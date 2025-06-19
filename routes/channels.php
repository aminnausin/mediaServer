<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tasks.{id}', function ($user) {
    return (int) $user->id === 1;
});

Broadcast::channel('tasks.{id}.subtasks', function ($user) {
    return (int) $user->id === 1;
});

Broadcast::channel('notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('dashboard.tasks', function ($user) {
    return (int) $user->id === 1;
});

Broadcast::channel('dashboard.libraries', function ($user) {
    return (int) $user->id === 1;
});

Broadcast::channel('dashboard', function () {
    // Unimplemented
});
