<?php

use App\Http\Controllers\Frontend\PublicInteractionController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/page-interactions/{key}',
    [PublicInteractionController::class, 'store']
)
    ->where('key', '[a-f0-9]{64}')
    ->middleware(['signed', 'throttle:60,1'])
    ->name('public.interactions.store');
