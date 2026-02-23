<?php

use Illuminate\Support\Facades\Route;
use Lmromax\LaravelAiGuard\Http\Controllers\DashboardController;
use Lmromax\LaravelAiGuard\Http\Controllers\OptimizerController;

Route::middleware(['web', 'auth'])
    ->prefix('ai-guard')
    ->name('ai-guard.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/optimizer', [OptimizerController::class, 'index'])->name('optimizer');
    });
