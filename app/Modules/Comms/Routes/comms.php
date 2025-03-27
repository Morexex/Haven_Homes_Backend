<?php

use App\Modules\Comms\Controllers\BulkCommunicationController;
use App\Modules\Comms\Controllers\ComplaintsController;
use App\Modules\Comms\Controllers\NoticesController;
use App\Modules\Comms\Controllers\VacationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('communication')->group(function () {
    Route::get('/', [BulkCommunicationController::class, 'index']); 
    Route::post('/', [BulkCommunicationController::class, 'store']);
    Route::get('{id}', [BulkCommunicationController::class, 'show']);
    Route::post('{id}/bulk-communication', [BulkCommunicationController::class, 'sendBulkMessages']);
    Route::delete('{id}', [BulkCommunicationController::class, 'destroy']);
});

Route::prefix('complaints')->group(function () {
    Route::get('/', [ComplaintsController::class, 'index']); 
    Route::post('/', [ComplaintsController::class, 'store']); 
    Route::get('{id}', [ComplaintsController::class, 'show']); 
    Route::put('{id}', [ComplaintsController::class, 'update']);
    Route::delete('{id}', [ComplaintsController::class, 'destroy']);
});

Route::prefix('notices')->group(function () {
    Route::get('/', [NoticesController::class, 'index']); 
    Route::post('/', [NoticesController::class, 'store']);
    Route::get('{id}', [NoticesController::class, 'show']);
    Route::put('{id}', [NoticesController::class, 'update']);
    Route::delete('{id}', [NoticesController::class, 'destroy']);
});

Route::prefix('vacations')->group(function () {
    Route::get('/', [VacationsController::class, 'index']); 
    Route::post('/', [VacationsController::class, 'store']);
    Route::get('{id}', [VacationsController::class, 'show']);
    Route::put('{id}', [VacationsController::class, 'update']);
    Route::delete('{id}', [VacationsController::class, 'destroy']);
});
