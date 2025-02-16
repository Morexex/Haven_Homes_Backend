<?php

use App\Modules\Property\Controllers\PropertyController;
use App\Modules\Property\Controllers\RoomController;
use App\Modules\Property\Controllers\AmenitiesController;
use App\Modules\Property\Controllers\RoomCategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('properties')->group(function () {
    Route::get('/', [PropertyController::class, 'index']);
    Route::get('/{id}', [PropertyController::class, 'show']);
    Route::put('/{id}/update', [PropertyController::class, 'update']);
    Route::delete('/{id}/delete', [PropertyController::class, 'destroy']);
});

Route::prefix('rooms')->group(function () {
    Route::get('/', [RoomController::class, 'index']); // Get all rooms
    Route::post('/', [RoomController::class, 'store']); // Create a room
    Route::get('{id}', [RoomController::class, 'show']); // Get room details
    Route::put('{id}', [RoomController::class, 'update']); // Update a room
    Route::delete('{id}', [RoomController::class, 'destroy']); // Delete a room
});

Route::prefix('amenities')->group(function () {
    Route::get('/', [AmenitiesController::class, 'index']); // Get all amenities
    Route::post('/', [AmenitiesController::class, 'store']); // Create an amenity
    Route::get('{id}', [AmenitiesController::class, 'show']); // Get amenity details
    Route::put('{id}', [AmenitiesController::class, 'update']); // Update an amenity
    Route::delete('{id}', [AmenitiesController::class, 'destroy']); // Delete an amenity
});

Route::prefix('room-categories')->group(function () {
    Route::get('/', [RoomCategoryController::class, 'index']); // Get all categories
    Route::post('/', [RoomCategoryController::class, 'store']); // Create a category
    Route::get('{id}', [RoomCategoryController::class, 'show']); // Get category details
    Route::put('{id}', [RoomCategoryController::class, 'update']); // Update a category
    Route::delete('{id}', [RoomCategoryController::class, 'destroy']); // Delete a category
});
