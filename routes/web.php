<?php

use App\Events\TaskEnded;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\MediaController;
use App\Models\Task;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

// private

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/jobs/verifyFiles', [DirectoryController::class, 'verifyFiles']);
    Route::get('/jobs/syncFiles', [DirectoryController::class, 'syncFiles']);
    Route::get('/jobs/indexFiles', [DirectoryController::class, 'indexFiles']);
    Route::get('/jobs/cleanPaths', [DirectoryController::class, 'cleanPaths']);
});

// public

// Route::get('php', function () {
//     phpinfo();
// })->name('php');

// For serving videos in private storage folder without leaking urls
Route::get('/storage/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.serve');
Route::get('/signed-url/{path}', function ($path) {
    return URL::temporarySignedRoute(
        'media.serve',
        now()->addSeconds(5), // URL is valid for 5 seconds
        ['path' => $path]
    );
})->middleware('auth')->where('path', '.*');

Route::get('/broadcast', function () {
    $task = Task::where('id', 165)->first();
    dump($task);
    broadcast(new TaskEnded($task));
});

Route::middleware(['web'])
    ->group(function () {
        Route::get('/pulse', function () {
            return view('vendor.pulse.dashboard');
        });
    });



Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/{dir?}/{folderName?}', function () {
    return view('home');
})->name('root');

Route::get('/{any}', function () {
    return view('home');
})->where('any', '.*');
