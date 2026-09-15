<?php

use App\Http\Controllers\Admin\Page\PageController;
use Illuminate\Support\Facades\Route;

Route::get(
    'pages/media-picker',
    [PageController::class, 'mediaPicker']
)->name('pages.media-picker');

Route::resource(
    'pages',
    PageController::class
)->except([
    'show',
]);