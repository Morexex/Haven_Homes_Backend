<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


// Public routes (accessible without authentication)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('/register_super_admin', [AuthController::class, 'registerSuperAdmin']);

// Protected routes (accessible only to authenticated users)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/property_users/register', [AuthController::class, 'registerUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
