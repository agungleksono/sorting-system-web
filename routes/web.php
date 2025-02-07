<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SandboxController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuspectController;
use App\Http\Controllers\SuspectImportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Auth;

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

// Route::get('/', function () {
//     return view('pages.login');
// });

Route::get('/signin', [AuthController::class, 'loginPage'])->name('login');
Route::post('/signin', [AuthController::class, 'loginWeb']);

Route::get('/test', [SandboxController::class, 'test']);
Route::get('/users/management', [UserController::class, 'index']);
Route::get('/users/{id}/edit', [UserController::class, 'edit']);
Route::put('/users/{user_id}', [UserController::class, 'update'])->name('user.update');
Route::post('/users', [UserController::class, 'store']);
// Route::post('/suspect/import', [SuspectController::class, 'importSuspects']);
Route::post('/suspect/import', [SuspectImportController::class, 'import']);

Route::middleware(['auth.web'])->group(function () {
    Route::get('/', [SuspectImportController::class, 'index']);
    Route::get('/home', [SuspectImportController::class, 'index'])->name('suspect');

    Route::get('/scans', [SuspectController::class, 'dataScanned']);
});

// Route::get('/', [SuspectImportController::class, 'index'])->middleware('auth.web');
// Route::get('/home', [SuspectImportController::class, 'index'])->middleware('auth.web');

// Route::get('/home', function () {
//     return view('pages.home');
// });
