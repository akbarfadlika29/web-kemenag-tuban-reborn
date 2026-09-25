<?php

use App\Http\Controllers\Admin\Gallery\GalleryController;
use Illuminate\Support\Facades\Route;

Route::get(
    'galleries/media-picker',
    [
        GalleryController::class,
        'mediaPicker',
    ]
)->name(
    'galleries.media-picker'
);

Route::resource(
    'galleries',
    GalleryController::class
)->except([
    'show',
]);