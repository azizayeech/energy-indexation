<?php

use App\Http\Controllers\CalculateController;
use App\Http\Controllers\ConsumptionController;
use App\Http\Controllers\PriceController;
use Illuminate\Support\Facades\Route;

Route::get('/consumptions', ConsumptionController::class)
    ->name('consumptions.index');

Route::get('/prices', PriceController::class)
    ->name('prices.index');

Route::post('/calculate', CalculateController::class)
    ->middleware('throttle:calculate')
    ->name('calculate');
