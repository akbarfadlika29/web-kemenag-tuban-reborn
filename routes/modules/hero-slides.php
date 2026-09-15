<?php

use App\Http\Controllers\Admin\HeroSlide\HeroSlideController;
use Illuminate\Support\Facades\Route;

Route::resource(
    'hero-slides',
    HeroSlideController::class
)->except([
    'show',
]);
