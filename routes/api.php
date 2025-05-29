<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ExternalMetadataController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\MetadataController;
use App\Http\Controllers\Api\V1\PlaybackController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RecordController;
use App\Http\Controllers\Api\V1\SeriesController;
use App\Http\Controllers\Api\V1\SubTaskController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VideoController;
use App\Http\Controllers\DirectoryController;
use App\Support\AppManifest;
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

    Route::get('/active-sessions', [UserController::class, 'sessionCount']);
    Route::get('/user-view-count/{metadata}', [RecordController::class, 'userViewCount']);
    Route::get('/profiles/search/{username?}', [ProfileController::class, 'findUser']);

    // Media
    Route::resource('/categories', CategoryController::class)->only(['update']);
    Route::resource('/metadata', MetadataController::class)->only(['show', 'store', 'update']);
    Route::resource('/series', SeriesController::class)->only(['index', 'store', 'update']);
    Route::resource('/tags', TagController::class)->only(['index', 'store']);

    // Users
    Route::resource('/profiles', ProfileController::class)->only(['show', 'store', 'update']);
    Route::resource('/records', RecordController::class)->only(['index', 'store', 'destroy']);
    Route::resource('/users', UserController::class)->only(['index', 'destroy']);

    // Server
    Route::resource('/tasks', TaskController::class)->only(['index', 'destroy']);
    Route::resource('/sub-tasks', SubTaskController::class)->only(['show', 'destroy']);
    Route::resource('/analytics', AnalyticsController::class)->only(['index']);

    Route::post('/sub-tasks/{task}', [SubTaskController::class, 'show']);
    Route::post('/categories/privacy/{category}', [CategoryController::class, 'updatePrivacy']);


    Route::prefix('tasks')->group(function () {
        Route::get('/stats', [TaskController::class, 'stats']);
        Route::post('/sync', [JobController::class, 'syncFiles']);
        Route::post('/index/{category?}', [JobController::class, 'indexFiles']);
        Route::post('/verify/{category?}', [JobController::class, 'verifyFiles']);
        Route::post('/verify-folders/{category?}', [JobController::class, 'verifyFolders']);
        Route::post('/scan/{category?}', [JobController::class, 'scanFiles']);
        Route::post('/clean', [JobController::class, 'cleanPaths']);
        Route::post('/cancel/{task}', [TaskController::class, 'cancel']);
    });
});

Route::resource('/categories', CategoryController::class)->only(['index', 'show']);
Route::resource('/folders', FolderController::class)->only(['show']);
Route::resource('/videos', VideoController::class)->only(['show', 'update']);
Route::resource('/playback', PlaybackController::class)->only(['show', 'store']);

Route::get('/metadata/{id}/import/lyrics', [ExternalMetadataController::class, 'importLyrics']);
Route::get('/folders', [FolderController::class, 'getFrom']);
Route::patch('/videos/watch/{video}', [VideoController::class, 'watch']);
Route::get('/videos', [VideoController::class, 'getFrom']);


Route::prefix('pulse')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/{type}', [DashboardController::class, 'show'])->name('resource');
})->middleware('auth');

// public

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


Route::get('/manifest', function () {
    return response()->json(AppManifest::info());
});

// healthcheck
Route::get('/health', function () {
    return response()->json(['health' => 1]);
});

Route::get('/{dir}', [DirectoryController::class, 'showDirectoryAPI']);
Route::get('/{dir}/{folderName}', [DirectoryController::class, 'showDirectoryAPI']);
