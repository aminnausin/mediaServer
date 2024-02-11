<?php

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

Route::get('/', function () {
    return view('home', ['dir' => 'anime', 'folder_name' => null]);
});

Route::get('/account', function () {
    return view('account', ['dir' => 'none', 'folder_name' => null]);
});

Route::get('/test/folders/{dir}', [DirectoryController::class, 'getDirectoryContents']);
Route::get('/test/videos/{folder_name}/{category_id}', [DirectoryController::class, 'getFolderContents']);

Route::get('/storage/data', [DirectoryController::class, 'generateData']);
Route::post('/ajax/generateDir', [DirectoryController::class, 'generateDirectory']);

Route::post('/ajax/getFolders', [DirectoryController::class, 'getDirectoryContents']);
Route::post('/ajax/getVideos', [DirectoryController::class, 'getFolderContents']);

Route::get('/{dir}', [DirectoryController::class, 'showDirectory']);
Route::get('/{dir}/{folder_name}', [DirectoryController::class, 'showDirectory']);