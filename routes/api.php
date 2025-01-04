<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\MetadataController;
use App\Http\Controllers\Api\V1\PlaybackController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RecordController;
use App\Http\Controllers\Api\V1\SeriesController;
use App\Http\Controllers\Api\V1\SubTaskController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\TasksController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VideoController;
use App\Http\Controllers\DirectoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Robertogallea\PulseApi\Http\Controllers\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// protected

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/auth', [AuthController::class, 'authenticate']);  // New
    Route::delete('/logout', [AuthController::class, 'destroy']);  // New

    Route::get('/active-sessions', [UserController::class, 'SessionCount']);

    Route::resource('/records', RecordController::class)->only(['index', 'store', 'destroy']);
    Route::resource('/profile', ProfileController::class)->only(['show', 'store', 'update']);
    Route::resource('/series', SeriesController::class)->only(['index', 'store', 'update']);
    Route::resource('/metadata', MetadataController::class)->only(['show', 'store', 'update']);
    Route::resource('/tags', TagController::class)->only(['index', 'store']);
    Route::resource('/analytics', AnalyticsController::class)->only(['index']);
    Route::resource('/categories', CategoryController::class)->only(['index', 'update']);
    Route::resource('/users', UserController::class)->only(['index', 'destroy']);
    Route::resource('/sub-tasks', SubTaskController::class)->only(['show', 'destroy']);
    Route::resource('/tasks', TasksController::class)->only(['index', 'destroy']);

    Route::post('/sub-tasks/{task}', [SubTaskController::class, 'show']);

    Route::prefix('tasks')->group(function () {
        Route::get('/stats', [TasksController::class, 'stats']);
        Route::post('/sync', [DirectoryController::class, 'syncFiles']);
        Route::post('/index/{category?}', [DirectoryController::class, 'indexFiles']);
        Route::post('/verify/{category?}', [DirectoryController::class, 'verifyFiles']);
        Route::post('/verify-folders/{category?}', [DirectoryController::class, 'verifyFolders']);
        Route::post('/scan/{category?}', [DirectoryController::class, 'scanFiles']);
        Route::post('/clean', [DirectoryController::class, 'cleanPaths']);
        Route::post('/cancel/{task}', [TasksController::class, 'cancel']);
    });
});

Route::prefix('pulse')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/{type}', [DashboardController::class, 'show'])->name('resource');
})->middleware('auth');

// public

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::patch('/videos/watch/{video}', [VideoController::class, 'watch']);
Route::get('/folders', [FolderController::class, 'getFrom']);
Route::get('/videos', [VideoController::class, 'getFrom']);

Route::resource('/videos', VideoController::class)->only(['show', 'update']);
Route::resource('/folders', FolderController::class)->only(['show']);
Route::resource('/categories', CategoryController::class)->only(['show']);
Route::resource('/playback', PlaybackController::class)->only(['show', 'store']);

Route::get('/{dir}', [DirectoryController::class, 'showDirectoryAPI']);
Route::get('/{dir}/{folderName}', [DirectoryController::class, 'showDirectoryAPI']);
