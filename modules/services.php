<?php

use App\Http\Controllers\Admin\Service\ServiceCategoryController;
use App\Http\Controllers\Admin\Service\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get(
    'services/media-picker',
    [
        ServiceController::class,
        'mediaPicker',
    ]
)->name(
    'services.media-picker'
);

Route::resource(
    'service-categories',
    ServiceCategoryController::class
)->except([
    'show',
]);

Route::resource(
    'services',
    ServiceController::class
)->except([
    'show',
]);