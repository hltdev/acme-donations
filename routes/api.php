<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/webhooks/{gateway}', [DonationController::class, 'handleWebhook']);

// Protected
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);

    // Campaign
    Route::apiResource('campaigns', CampaignController::class);

    // Donation
    Route::apiResource('donations', DonationController::class)->only(['index', 'show', 'store']);
});
