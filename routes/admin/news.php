<?php

use App\Http\Controllers\Admin\News\NewsCategoryController;
use App\Http\Controllers\Admin\News\NewsController;
use App\Http\Controllers\Admin\News\NewsTagController;
use Illuminate\Support\Facades\Route;

Route::get(
    'news/media-picker',
    [NewsController::class, 'mediaPicker']
)->name('news.media-picker');

Route::resource(
    'news-categories',
    NewsCategoryController::class
)->except([
    'show',
]);

Route::resource(
    'news-tags',
    NewsTagController::class
)->except([
    'show',
]);

Route::resource(
    'news',
    NewsController::class
)->except([
    'show',
]);