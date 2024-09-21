<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\MetadataController;
use App\Http\Controllers\Api\V1\PlaybackController;
use App\Http\Controllers\Api\V1\SeriesController;
use App\Http\Controllers\Api\V1\VideoController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RecordController;
use App\Http\Controllers\DirectoryController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// protected

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/auth', [AuthController::class, 'authenticate']);  // New
    Route::delete('/logout', [AuthController::class, 'destroy']);  // New
    Route::resource('/records', RecordController::class)->only(['index', 'store', 'destroy']);
    Route::resource('/profile', ProfileController::class)->only(['show', 'store', 'update']);
    Route::resource('/series', SeriesController::class)->only(['index', 'store', 'update']);
    Route::resource('/metadata', MetadataController::class)->only(['store', 'update']);
});

// public

Route::post('/login', [AuthController::class, 'login']);        // Deprecate
Route::post('/register', [AuthController::class, 'register']);  // Deprecate
Route::post('/videos', [VideoController::class, 'getFrom']);
Route::patch('/videos/watch/{video}', [VideoController::class, 'watch']);
Route::post('/folders', [FolderController::class, 'getFrom']);

Route::resource('/videos', VideoController::class)->only(['show', 'update']);
Route::resource('/folders', FolderController::class)->only(['show']);
Route::resource('/categories', CategoryController::class)->only(['show']);
Route::resource('/playback', PlaybackController::class)->only(['store']);

Route::get('/{dir}', [DirectoryController::class, 'showDirectoryAPI']);
Route::get('/{dir}/{folderName}', [DirectoryController::class, 'showDirectoryAPI']);