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

Route::get('/', function () { return redirect('/anime/ODDTAXI'); })->name('home');

Route::get('login', [AuthController::class, 'create'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::get('register', [AuthController::class, 'generate'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register');

Route::get('/error', function () {return view('error'); })->name('error');

// private


Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/jobs/indexFiles', [DirectoryController::class, 'indexFiles']);
    Route::get('/welcome', function () { return view('welcome'); });
    Route::get('/legacy', [DirectoryController::class, 'showDirectory'])->defaults('dir', 'legacy')->defaults('folder_name', 'Unsorted');
    Route::get('/legacy/*', [DirectoryController::class, 'showDirectory'])->defaults('dir', 'legacy')->defaults('folder_name', 'Unsorted');
});

// public

// Route::get('/', function () { return view('home', ['dir' => 'anime', 'folder_name' => null]); })->name('home');
// Route::get('/testing', function () { return redirect()->route('home'); })->name('testing');
// Route::get('/welcome', function () { return view('welcome'); });


Route::get('/{dir}', [DirectoryController::class, 'showDirectory']);
Route::get('/{dir}/{folder_name}', [DirectoryController::class, 'showDirectory']);


// old

Route::post('/ajax/generateDir', [DirectoryController::class, 'generateDirectory']);
Route::post('/ajax/getFolders', [DirectoryController::class, 'getDirectoryContents']);
Route::post('/ajax/getVideos', [DirectoryController::class, 'getFolderContents']);