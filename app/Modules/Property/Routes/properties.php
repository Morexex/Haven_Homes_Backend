<?php

use App\Modules\Property\Controllers\PropertyController;
use App\Modules\Property\Controllers\RoomController;
use App\Modules\Property\Controllers\AmenitiesController;
use App\Modules\Property\Controllers\RoomCategoryController;
use App\Modules\Property\Controllers\RoomChargeController;
use App\Modules\Property\Controllers\RentalAgreementController;
use Illuminate\Support\Facades\Route;

Route::prefix('rooms')->group(function () {
    Route::get('/', [RoomController::class, 'index']); // Get all rooms
    Route::post('/', [RoomController::class, 'store']); // Create a room
    Route::get('{id}', [RoomController::class, 'show']); // Get room details
    Route::put('{id}', [RoomController::class, 'update']); // Update a room
    Route::delete('{id}', [RoomController::class, 'destroy']); // Delete a room
    Route::post('/{id}/upload-images', [RoomController::class, 'uploadImages']);
    Route::get('/{id}/images', [RoomController::class, 'getRoomImages']);
    Route::post('/{room_id}/images/{image_id}/update', [RoomController::class, 'updateImage']);
});

Route::prefix('amenities')->group(function () {
    Route::get('/', [AmenitiesController::class, 'index']); // Get all amenities
    Route::post('/', [AmenitiesController::class, 'store']); // Create an amenity
    Route::get('{id}', [AmenitiesController::class, 'show']); // Get amenity details
    Route::put('{id}', [AmenitiesController::class, 'update']); // Update an amenity
    Route::delete('{id}', [AmenitiesController::class, 'destroy']); // Delete an amenity
});

Route::prefix('charges')->group(function () {
    Route::get('/', [RoomChargeController::class, 'index']); 
    Route::post('/', [RoomChargeController::class, 'store']);
    Route::get('{id}', [RoomChargeController::class, 'show']);
    Route::put('{id}', [RoomChargeController::class, 'update']);
    Route::delete('{id}', [RoomChargeController::class, 'destroy']);
});

Route::prefix('agreements')->group(function () {
    Route::get('/', [RentalAgreementController::class, 'index']); 
    Route::post('/', [RentalAgreementController::class, 'store']);
    Route::get('{id}', [RentalAgreementController::class, 'show']);
    Route::put('{id}', [RentalAgreementController::class, 'update']);
    Route::delete('{id}', [RentalAgreementController::class, 'destroy']);
});

Route::prefix('room-categories')->group(function () {
    Route::get('/', [RoomCategoryController::class, 'index']); // Get all categories
    Route::post('/', [RoomCategoryController::class, 'store']); // Create a category
    Route::get('{id}', [RoomCategoryController::class, 'show']); // Get category details
    Route::put('{id}', [RoomCategoryController::class, 'update']); // Update a category
    Route::delete('{id}', [RoomCategoryController::class, 'destroy']); // Delete a category
});
