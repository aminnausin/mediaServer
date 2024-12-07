<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\URL;

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
    Route::get('/jobs/cleanPaths', [DirectoryController::class, 'cleanPaths']);
});

// public

// Route::get('php', function () {
//     phpinfo();
// })->name('php');

// Route::get('/storage/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.serve');
// Route::get('/signed-url/{path}', function ($path) {
//     return URL::temporarySignedRoute(
//         'media.serve',
//         now()->addSeconds(5), // URL is valid for 5 minutes
//         ['path' => $path]
//     );
// })->middleware('auth')->where('path', '.*');

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/{dir?}/{folderName?}', function () {
    return view('home');
})->name('root');
