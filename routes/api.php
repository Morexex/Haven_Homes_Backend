<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Modules\Property\Controllers\PropertyController;
use App\Http\Middleware\SwitchToMasterDatabase;
use App\Http\Middleware\SwitchToPropertyDatabase;

/**
 * Public Routes (No Authentication Required)
 */
Route::middleware([SwitchToMasterDatabase::class])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register_super_admin', [AuthController::class, 'registerSuperAdmin']);
});

/**
 * Protected Routes (Require Authentication)
 */
Route::middleware(['auth:sanctum'])->group(function () {
    
    // General authenticated user info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Property Module Routes
    Route::middleware([SwitchToPropertyDatabase::class])->group(function () {
        require base_path('app/Modules/Property/Routes/properties.php');
        Route::post('/property_users/register', [AuthController::class, 'registerUser']);
    });

    // Property Management Routes
    Route::middleware([SwitchToMasterDatabase::class])->group(function () {
        Route::get('/properties', [PropertyController::class, 'index']);
        Route::get('/properties/{id}', [PropertyController::class, 'show']);
        Route::put('/properties/{id}/update', [PropertyController::class, 'update']);
        Route::delete('/properties/{id}/delete', [PropertyController::class, 'destroy']);
    });
});
