<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::middleware(\App\Http\Middleware\CheckApiKey::class)->group(function () {
    Route::get('/test-get', [TestController::class, 'testGet']);
    Route::post('/test-post', [TestController::class, 'testPost']);
});
