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

// public

Route::resource('/videos', VideoController::class);
Route::resource('/folders', FolderController::class);

Route::post('/videos', [VideoController::class, 'getFrom']);
Route::post('/folders', [FolderController::class, 'getFrom']);
Route::post('/login', [AuthController::class, 'login']);        // Deprecate
Route::post('/register', [AuthController::class, 'register']);  // Deprecate

// protected

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/auth', [AuthController::class, 'authenticate']);  // New
    Route::post('/logout', [AuthController::class, 'logout']);      // Deprecate
    Route::resource('/records', RecordController::class);
    Route::resource('/profile', ProfileController::class);
});

Route::get('/{dir}', [DirectoryController::class, 'showDirectoryAPI']);
Route::get('/{dir}/{folderName}', [DirectoryController::class, 'showDirectoryAPI']);