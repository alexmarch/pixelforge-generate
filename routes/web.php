<?php

use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('user.dashboard');
    }
    return inertia('Welcome');
});

Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/user/auth', [AuthController::class, 'auth'])->name('user.auth');
Route::post('/webhooks/pixelforge', [DashboardController::class, 'webhook'])->name('user.webhook');

Route::middleware('auth:web')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('user.logout');
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('user.dashboard');
    // Dashboard routes for authenticated users
    Route::prefix('dashboard')->group(function () {
        Route::get('/wallet', [DashboardController::class, 'wallet'])->name('user.wallet');
        Route::post('/price', [DashboardController::class, 'price'])->name('user.price');
        Route::post('/generate', [DashboardController::class, 'generate'])->name('dashboard.task')
            ->middleware([HandlePrecognitiveRequests::class]);
        Route::get('/generations', [DashboardController::class, 'generations'])->name('dashboard.generations');
    });
});
