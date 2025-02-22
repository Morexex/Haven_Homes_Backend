<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Modules\Property\Controllers\PropertyController;

Route::middleware(['switchToMaster'])->group(function () {
    // Public routes (accessible without authentication)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('/register_super_admin', [AuthController::class, 'registerSuperAdmin']);
    Route::get('/', [PropertyController::class, 'index']);
    Route::get('/{id}', [PropertyController::class, 'show']);
    Route::put('/{id}/update', [PropertyController::class, 'update']);
    Route::delete('/{id}/delete', [PropertyController::class, 'destroy']);
});

Route::middleware(['switchToProperty'])->group(function () {
    //included the routes of a module
    require base_path('app/Modules/Property/Routes/properties.php');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/property_users/register', [AuthController::class, 'registerUser']);
    });
});

// Protected routes (accessible only to authenticated users)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});
