<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SandboxController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuspectController;
use App\Http\Controllers\SuspectImportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SuspectCaseController;
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

Route::get('/', [AuthController::class, 'loginPage'])->name('login')->middleware('guest.web');
Route::post('/signin', [AuthController::class, 'loginWeb']);
Route::post('/signout', [AuthController::class, 'logoutWeb'])->name('logout');

Route::get('/test', [SandboxController::class, 'test']);

Route::middleware(['auth.web'])->group(function () {
    // Route::get('/', [SuspectImportController::class, 'index']);
    Route::get('/suspects', [SuspectImportController::class, 'index'])->name('suspects.index');
    Route::post('/suspects/import', [SuspectImportController::class, 'import'])->name('suspects.import');
    Route::post('/suspect/manual-add', [SuspectImportController::class, 'manualAdd'])->name('suspects.manual-add');
    Route::get('/suspect/download-sample', [SuspectImportController::class, 'downloadSample'])->name('suspects.download-file');
    Route::post('/suspects', [SuspectImportController::class, 'delete'])->name('suspects.delete');

    // Suspect Case Routes
    Route::get('/cases', [SuspectCaseController::class, 'index'])->name('cases.index');
    Route::get('/cases/create', [SuspectCaseController::class, 'create'])->name('cases.create');
    Route::post('/cases', [SuspectCaseController::class, 'store'])->name('cases.store');
    Route::get('/cases/{suspect_case_id}/edit', [SuspectCaseController::class, 'edit'])->name('cases.edit');
    Route::patch('/cases/{suspect_case_id}', [SuspectCaseController::class, 'update'])->name('cases.update');
    Route::delete('/cases/{suspect_case_id}', [SuspectCaseController::class, 'destroy'])->name('cases.destroy');

    Route::get('/scans', [SuspectController::class, 'dataScanned']);

    // User management route
    Route::get('/users/management', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}/edit', [UserController::class, 'edit']);
    Route::put('/users/{user_id}', [UserController::class, 'update'])->name('user.update');
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{user_id}', [UserController::class, 'destroy'])->name('users.destroy');
});
