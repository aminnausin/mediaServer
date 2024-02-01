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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('home');
});

Route::get('/{dir}', [DirectoryController::class, 'showDirectory']);
Route::get('/storage/test', [DirectoryController::class, 'testStorage']);
Route::post('/ajax/generateDir', [DirectoryController::class, 'generateDirectory']);