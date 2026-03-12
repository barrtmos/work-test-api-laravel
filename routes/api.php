<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\LeadController;

Route::middleware(\App\Http\Middleware\CheckApiKey::class)->group(function () {
    Route::get('/test-get', [TestController::class, 'testGet']);
    Route::post('/test-post', [TestController::class, 'testPost']);
});

Route::post('/lead', [LeadController::class, 'store']);
