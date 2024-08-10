<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\VideoController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// protected

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/auth', [AuthController::class, 'authenticate']);  // New
    Route::delete('/logout', [AuthController::class, 'destroy']);  // New
    Route::resource('/records', RecordController::class);
    Route::resource('/profile', ProfileController::class);
});

// public

Route::get('/{dir}', [DirectoryController::class, 'showDirectoryAPI']);
Route::get('/{dir}/{folderName}', [DirectoryController::class, 'showDirectoryAPI']);

Route::resource('/videos', VideoController::class);
Route::resource('/folders', FolderController::class);

Route::post('/login', [AuthController::class, 'login']);        // Deprecate
Route::post('/register', [AuthController::class, 'register']);  // Deprecate
Route::post('/videos', [VideoController::class, 'getFrom']);
Route::patch('/videos/watch/{video}', [VideoController::class, 'watch']);
Route::post('/folders', [FolderController::class, 'getFrom']);