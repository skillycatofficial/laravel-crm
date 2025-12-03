<?php

use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\PersonController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuoteController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

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

// API Version 1
Route::prefix('v1')->group(function () {
    
    // ==========================================
    // Public Routes (No Authentication Required)
    // ==========================================
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
    Route::post('/device/connect', [App\Http\Controllers\Api\V1\DeviceConnectionController::class, 'connectDevice'])->name('api.v1.device.connect');

    // ==========================================
    // Protected Routes (Authentication Required)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {
        
        // ==========================================
        // Authentication & Profile Routes
        // ==========================================
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('api.v1.auth.logout_all');
            Route::get('/profile', [AuthController::class, 'profile'])->name('api.v1.auth.profile');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.v1.auth.update_profile');
            Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('api.v1.auth.refresh_token');
        });

        // Device Connection (QR Code)
        Route::post('/device/qr-code', [App\Http\Controllers\Api\V1\DeviceConnectionController::class, 'generateQRCode'])->name('api.v1.device.qr');

        // ==========================================
        // Dashboard Routes
        // ==========================================
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.v1.dashboard.index');

        // ==========================================
        // Lead Routes
        // ==========================================
        Route::prefix('leads')->group(function () {
            Route::get('/', [LeadController::class, 'index'])->name('api.v1.leads.index');
            Route::post('/', [LeadController::class, 'store'])->name('api.v1.leads.store');
            Route::get('/{id}', [LeadController::class, 'show'])->name('api.v1.leads.show');
            Route::put('/{id}', [LeadController::class, 'update'])->name('api.v1.leads.update');
            Route::delete('/{id}', [LeadController::class, 'destroy'])->name('api.v1.leads.destroy');
            Route::put('/{id}/stage', [LeadController::class, 'updateStage'])->name('api.v1.leads.update_stage');
        });

        // ==========================================
        // Person (Contact) Routes
        // ==========================================
        Route::prefix('persons')->group(function () {
            Route::get('/', [PersonController::class, 'index'])->name('api.v1.persons.index');
            Route::post('/', [PersonController::class, 'store'])->name('api.v1.persons.store');
            Route::get('/{id}', [PersonController::class, 'show'])->name('api.v1.persons.show');
            Route::put('/{id}', [PersonController::class, 'update'])->name('api.v1.persons.update');
            Route::delete('/{id}', [PersonController::class, 'destroy'])->name('api.v1.persons.destroy');
        });

        // ==========================================
        // Activity Routes
        // ==========================================
        Route::prefix('activities')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('api.v1.activities.index');
            Route::post('/', [ActivityController::class, 'store'])->name('api.v1.activities.store');
            Route::get('/{id}', [ActivityController::class, 'show'])->name('api.v1.activities.show');
            Route::put('/{id}', [ActivityController::class, 'update'])->name('api.v1.activities.update');
            Route::delete('/{id}', [ActivityController::class, 'destroy'])->name('api.v1.activities.destroy');
            Route::post('/{id}/toggle-done', [ActivityController::class, 'toggleDone'])->name('api.v1.activities.toggle_done');
        });

        // ==========================================
        // Product Routes
        // ==========================================
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('api.v1.products.index');
            Route::post('/', [ProductController::class, 'store'])->name('api.v1.products.store');
            Route::get('/{id}', [ProductController::class, 'show'])->name('api.v1.products.show');
            Route::put('/{id}', [ProductController::class, 'update'])->name('api.v1.products.update');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('api.v1.products.destroy');
        });

        // ==========================================
        // Quote Routes
        // ==========================================
        Route::prefix('quotes')->group(function () {
            Route::get('/', [QuoteController::class, 'index'])->name('api.v1.quotes.index');
            Route::post('/', [QuoteController::class, 'store'])->name('api.v1.quotes.store');
            Route::get('/{id}', [QuoteController::class, 'show'])->name('api.v1.quotes.show');
            Route::put('/{id}', [QuoteController::class, 'update'])->name('api.v1.quotes.update');
            Route::delete('/{id}', [QuoteController::class, 'destroy'])->name('api.v1.quotes.destroy');
        });

        // ==========================================
        // User (Team Members) Routes
        // ==========================================
        Route::get('/users', [UserController::class, 'index'])->name('api.v1.users.index');
    });
});

// ==========================================
// API Health Check (Optional but recommended)
// ==========================================
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.health');
