<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\EmailController;
use App\Http\Controllers\Api\V1\ExternalMetadataController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\MetadataController;
use App\Http\Controllers\Api\V1\PasswordController;
use App\Http\Controllers\Api\V1\PlaybackController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RecordController;
use App\Http\Controllers\Api\V1\SeriesController;
use App\Http\Controllers\Api\V1\SessionController;
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

//
// ─── Pulse API (Protected) ──────────────────────────────────────────────────
//

Route::prefix('pulse')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('pulse.dashboard');
    Route::get('/{type}', [DashboardController::class, 'show'])->name('pulse.resource');
})->middleware('auth');

//
// ─── Protected Routes ───────────────────────────────────────────────────────
//

Route::group(['middleware' => ['auth:sanctum']], function () {
    // Auth
    Route::get('/user', fn (Request $request) => $request->user());
    Route::delete('/logout', [AuthController::class, 'destroy']);

    // Settings
    Route::prefix('settings')->group(function () {
        // Account Settings
        Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.list');
        Route::delete('/sessions', [SessionController::class, 'destroyOthers'])->name('sessions.destroyOthers');
        Route::delete('/account', [ProfileController::class, 'destroy'])->name('account.destroy');
        Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
        Route::put('/email', [EmailController::class, 'update'])->name('email.update');
    });

    // Dashboard
    Route::get('/active-sessions', [UserController::class, 'sessionCount']);

    // Media and Metadata
    Route::resource('/categories', CategoryController::class)->only(['update']);
    Route::resource('/metadata', MetadataController::class)->only(['show', 'store', 'update']);
    Route::resource('/series', SeriesController::class)->only(['index', 'store', 'update']);
    Route::resource('/tags', TagController::class)->only(['index', 'store']);
    Route::patch('/metadata/{metadata}/lyrics', [MetadataController::class, 'updateLyrics']);

    // Users and Profiles
    Route::get('/profiles/search/{username?}', [ProfileController::class, 'findUser']);
    Route::resource('/users', UserController::class)->only(['index', 'destroy']);
    Route::resource('/profiles', ProfileController::class)->only(['show', 'store', 'update']);

    // Records and Views
    Route::resource('/records', RecordController::class)->only(['index', 'store', 'destroy']);
    Route::get('/user-view-count/{metadata}', [RecordController::class, 'userViewCount']);

    // Server
    Route::resource('/tasks', TaskController::class)->only(['index', 'destroy']);
    Route::resource('/sub-tasks', SubTaskController::class)->only(['show', 'destroy']);
    Route::resource('/analytics', AnalyticsController::class)->only(['index']);

    Route::post('/sub-tasks/{task}', [SubTaskController::class, 'show']);
    Route::post('/categories/privacy/{category}', [CategoryController::class, 'updatePrivacy']);

    Route::prefix('tasks')->group(function () {
        Route::get('/stats', [TaskController::class, 'stats']);
        Route::get('/wait-times', [TaskController::class, 'waitTimes']);
        Route::post('/sync', [JobController::class, 'syncFiles']);
        Route::post('/index/{category?}', [JobController::class, 'indexFiles']);
        Route::post('/verify/{category?}', [JobController::class, 'verifyFiles']);
        Route::post('/verify-folders/{category?}', [JobController::class, 'verifyFolders']);
        Route::post('/scan/{category?}', [JobController::class, 'scanFiles']);
        Route::post('/clean', [JobController::class, 'cleanPaths']); // OLD
        Route::post('/cancel/{task}', [TaskController::class, 'cancel']);
    });
});

//
// ─── Public Routes ──────────────────────────────────────────────────────────
//

// Auth
Route::get('/auth', [AuthController::class, 'authenticate']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register');
Route::post('/recovery', [PasswordResetLinkController::class, 'store'])->name('password.recovery');
Route::post('/reset-password/{token}', [PasswordController::class, 'store'])->name('password.reset');

// App Info
Route::get('/manifest', fn () => response()->json(AppManifest::info()));
Route::get('/health', fn () => response()->json(['health' => 1]));

// Libraries (categories)
Route::resource('/categories', CategoryController::class)->only(['index', 'show']);

// Folder GET by Id or by Library Id
Route::resource('/folders', FolderController::class)->only(['show']);
Route::get('/folders', [FolderController::class, 'getFrom']);

// Video Search and Views
Route::patch('/videos/watch/{video}', [VideoController::class, 'watch']);
Route::get('/videos', [VideoController::class, 'getFrom']);

// Video playback history
Route::resource('/playback', PlaybackController::class)->only(['show', 'store']);

// Lyrics Service
Route::get('/metadata/{id}/lyrics/import', [ExternalMetadataController::class, 'importLyrics']);
Route::get('/metadata/{id}/lyrics/search', [ExternalMetadataController::class, 'searchLyrics']);

// Content
Route::get('/{dir}', [DirectoryController::class, 'showDirectoryAPI']);
Route::get('/{dir}/{folderName}', [DirectoryController::class, 'showDirectoryAPI']);
