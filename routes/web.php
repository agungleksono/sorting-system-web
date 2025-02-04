<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SandboxController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuspectController;
use App\Http\Controllers\SuspectImportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [SandboxController::class, 'test']);
Route::get('/users/management', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
// Route::post('/suspect/import', [SuspectController::class, 'importSuspects']);
Route::post('/suspect/import', [SuspectImportController::class, 'import']);

Route::get('/home', function () {
    return view('pages.home');
});