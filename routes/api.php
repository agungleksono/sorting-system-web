<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SuspectCaseController;
use App\Http\Controllers\SuspectController;

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

Route::prefix('v1')->group(function () {
    Route::middleware('auth.api')->group(function () {
        Route::post('/auth/login', [AuthController::class, 'loginApi']);

        Route::post('/scan', [ScanController::class, 'scan']);
        Route::post('/scan/multi-box', [ScanController::class, 'scanMultiBox']);
        Route::get('/scan/progress/{suspect_case_id}', [ScanController::class, 'countScanProgress']);
        Route::post('/print-queue', [ScanController::class, 'checkPrintQueue']);
        Route::post('/reprint', [ScanController::class, 'reprint']);

        Route::get('/cases', [SuspectCaseController::class, 'indexApi']);
        Route::get('/cases/{suspect_case_id}', [SuspectCaseController::class, 'show']);
        Route::post('/cases', [SuspectCaseController::class, 'store']);
        Route::patch('/cases/{suspect_case_id}', [SuspectCaseController::class, 'update']);
        Route::delete('/cases/{suspect_case_id}', [SuspectCaseController::class, 'destroy']);
        Route::post('/suspects/list', [SuspectController::class, 'suspectList']);
    });
});