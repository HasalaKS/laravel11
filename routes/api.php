<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::group(['prefix' => 'api'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    // Route::post('refresh', [AuthController::class, 'refresh']);
});

