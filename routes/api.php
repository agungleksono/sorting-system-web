<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v1')->group(function () {
    // Route::post('/auth/login', [AuthController::class, 'loginApi']);

    Route::middleware('auth.api')->group(function () {
        Route::post('/auth/login', [AuthController::class, 'loginApi']);

        Route::post('/scan', [ScanController::class, 'scan']);
        Route::get('/scan/part-no', [ScanController::class, 'getListPartNo']);
        Route::post('/print-queue', [ScanController::class, 'checkPrintQueue']);
    });
});