<?php

use Illuminate\Support\Facades\Route;
use Lmromax\LaravelAiGuard\Http\Controllers\DashboardController;

Route::middleware(['web', 'auth'])
    ->prefix('ai-guard')
    ->name('ai-guard.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });
