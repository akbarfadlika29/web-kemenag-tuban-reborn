<?php

use App\Http\Controllers\Frontend\RegulationController;
use Illuminate\Support\Facades\Route;

Route::get('/regulasi', [RegulationController::class, 'index'])
    ->name('regulations.index');

Route::get(
    '/regulasi/{regulation:slug}/dokumen/{document}',
    [RegulationController::class, 'document']
)->whereNumber('document')->name('regulations.document');

Route::get(
    '/regulasi/{regulation:slug}',
    [RegulationController::class, 'show']
)->name('regulations.show');
