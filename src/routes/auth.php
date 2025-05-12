<?php

use Hyvor\Internal\Laravel\AuthController;
use Illuminate\Support\Facades\Route;

Route::domain(config('internal.auth.routes_domain'))->group(function () {
    Route::post('/api/auth/check', [AuthController::class, 'check']);
    Route::get('/api/auth/login', [AuthController::class, 'login']);
    Route::get('/api/auth/signup', [AuthController::class, 'signup']);
    Route::get('/api/auth/logout', [AuthController::class, 'logout']);
});
