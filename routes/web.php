<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadFormController;

Route::get('/lead-form', [LeadFormController::class, 'show']);
Route::post('/lead-form', [LeadFormController::class, 'submit']);
