<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ListingController;
use Illuminate\Support\Facades\Route;
use ProtoneMedia\LaravelXssProtection\Middleware\XssCleanInput;

Route::middleware(XssCleanInput::class)->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:registration');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');

Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->middleware(['auth:sanctum', 'can:admin']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/my-listings', [ListingController::class, 'mylistings']);

    Route::apiResource('listings', ListingController::class)
        ->except(['index', 'show'])
        ->middleware(XssCleanInput::class);
});
