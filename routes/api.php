<?php

use App\Infrastructure\Http\Controllers\StoreReadingController;
use App\Infrastructure\Http\Middleware\ValidateHmacSignature;
use Illuminate\Support\Facades\Route;

Route::post('/readings', StoreReadingController::class)
    ->middleware(ValidateHmacSignature::class);
