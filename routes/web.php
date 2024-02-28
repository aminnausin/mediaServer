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

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/testing', function () {
    return view('testing', ['dir' => 'anime', 'folder_name' => 'ODDTAXI']);
})->name('testing');;

Route::get('/', function () {
    return view('testing', ['dir' => 'anime', 'folder_name' => null]);
});

// Route::get('/account', function () {
//     return view('account', ['dir' => 'none', 'folder_name' => null]);
// });




Route::get('login', [AuthController::class, 'create'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::get('register', [AuthController::class, 'generate'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register');

Route::get('/{dir}', [DirectoryController::class, 'showDirectory']);
Route::get('/{dir}/{folder_name}', [DirectoryController::class, 'showDirectory']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/storage/data', [DirectoryController::class, 'generateData']);

    Route::get('/test/folders/{dir}', [DirectoryController::class, 'getDirectoryContents']);
    Route::get('/test/videos/{folder_name}/{category_id}', [DirectoryController::class, 'getFolderContents']);

    Route::get('/{dir}', [DirectoryController::class, 'showDirectoryTest']);
    Route::get('/{dir}/{folder_name}', [DirectoryController::class, 'showDirectoryTest']);

    // Route::get('/{dir}', function ($dir) {
    //     return view('testing', ['dir' => $dir]);
    // });
    // Route::get('/{dir}/{folder_name}', function ($dir,$folder_name) {
    //     return view('testing', ['dir' => $dir, 'folder_name' => $folder_name]);
    // });
});





Route::post('/ajax/generateDir', [DirectoryController::class, 'generateDirectory']);

Route::post('/ajax/getFolders', [DirectoryController::class, 'getDirectoryContents']);
Route::post('/ajax/getVideos', [DirectoryController::class, 'getFolderContents']);


