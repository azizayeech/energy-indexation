<?php

use App\Http\Controllers\CalculateController;
use Illuminate\Support\Facades\Route;

Route::post('/calculate', CalculateController::class)
    ->middleware('throttle:calculate')
    ->name('calculate');
