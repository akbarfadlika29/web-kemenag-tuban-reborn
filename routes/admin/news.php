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

Route::get('news/{news}/editorial', [
    \App\Http\Controllers\Admin\News\NewsEditorialController::class, 'show',
])->name('news.editorial');

Route::post('news/{news}/submit', [
    \App\Http\Controllers\Admin\News\NewsEditorialController::class, 'submit',
])->name('news.submit');

Route::post('news/{news}/reject', [
    \App\Http\Controllers\Admin\News\NewsEditorialController::class, 'reject',
])->name('news.reject');

Route::post('news/{news}/publish', [
    \App\Http\Controllers\Admin\News\NewsEditorialController::class, 'publish',
])->name('news.publish');