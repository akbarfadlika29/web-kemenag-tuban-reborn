<?php

use App\Http\Controllers\Admin\Regulation\RegulationController;
use App\Http\Controllers\Admin\Regulation\RegulationTypeController;
use Illuminate\Support\Facades\Route;

Route::resource('regulation-types', RegulationTypeController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::resource('regulations', RegulationController::class)
    ->except('show');

Route::get(
    'regulations/{regulation}/documents/{document}',
    [\App\Http\Controllers\Frontend\RegulationController::class, 'document']
)->whereNumber('document')->name('regulations.document');

Route::get(
    'regulation-media',
    [\App\Http\Controllers\Admin\Regulation\RegulationMediaController::class, 'index']
)->name('regulations.media.index');

Route::post(
    'regulation-media',
    [\App\Http\Controllers\Admin\Regulation\RegulationMediaController::class, 'upload']
)->name('regulations.media.upload');

Route::get(
    'regulation-media/{media}',
    [\App\Http\Controllers\Admin\Regulation\RegulationMediaController::class, 'preview']
)->whereNumber('media')->name('regulations.media.preview');
