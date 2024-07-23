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

// Route::get('/', function () { return redirect('/anime/ODDTAXI'); })->name('home');

Route::get('login', [AuthController::class, 'create'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login');
// Route::get('register', [AuthController::class, 'generate'])->name('register');
// Route::post('register', [AuthController::class, 'register'])->name('register');

Route::get('/error', function () {return view('error'); })->name('error');

// private


Route::middleware('auth')->group(function () {
    // Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/jobs/indexFiles', [DirectoryController::class, 'indexFiles']);
    Route::get('/jobs/syncFiles', [DirectoryController::class, 'syncFiles']);
    // Route::get('/history', function () { return view('profile/history'); });
});

// public

Route::get('/welcome', function () { return view('welcome'); });
// Route::get('/{dir}', [DirectoryController::class, 'showDirectory']);
// Route::get('/{dir}/{folderName}', [DirectoryController::class, 'showDirectory']);
// Route::get('/{dir}', function () {return view('home'); });
Route::get('/{dir?}/{folderName?}', function () {return view('home'); });



// old

// Route::post('/ajax/generateDir', [DirectoryController::class, 'generateDirectory']);
// Route::post('/ajax/getFolders', [DirectoryController::class, 'getDirectoryContents']);
// Route::post('/ajax/getVideos', [DirectoryController::class, 'getFolderContents']);