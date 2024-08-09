<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DirectoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// private

Route::middleware('auth')->group(function () {
    Route::get('/jobs/verifyFiles', [DirectoryController::class, 'verifyFiles']);
    Route::get('/jobs/syncFiles', [DirectoryController::class, 'syncFiles']);
    Route::get('/jobs/indexFiles', [DirectoryController::class, 'indexFiles']);
});

// public

Route::get('/welcome', function () { return view('welcome'); });
Route::get('/{dir?}/{folderName?}', function () {return view('home'); });