<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel('tasks.{id}', function ($_) {
    return true;
});

Broadcast::channel('tasks.{id}.subtasks', function ($_) {
    return (int) Gate::allows('admin');
});

Broadcast::channel('notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('dashboard.tasks', function ($_) {
    return (int) Gate::allows('admin');
});

Broadcast::channel('dashboard.libraries', function ($_) {
    return (int) Gate::allows('admin');
});

Broadcast::channel('dashboard', function () {
    // Unimplemented
});
