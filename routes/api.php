<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SuspectCaseController;

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
        Route::post('/scan/multi-box', [ScanController::class, 'scanMultiBox']);
        Route::get('/scan/part-no', [ScanController::class, 'getListPartNo']);
        Route::get('/scan/progress/{suspect_case_id}', [ScanController::class, 'countScanProgress']);
        Route::post('/print-queue', [ScanController::class, 'checkPrintQueue']);

        Route::get('/cases', [SuspectCaseController::class, 'indexApi']);
    });
});