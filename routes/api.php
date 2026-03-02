<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::get('/test-get', [TestController::class, 'testGet']);
Route::post('/test-post', [TestController::class, 'testPost']);
