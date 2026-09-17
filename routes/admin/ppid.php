<?php

use App\Http\Controllers\Admin\Ppid\PpidCategoryController;
use App\Http\Controllers\Admin\Ppid\PpidInformationController;
use Illuminate\Support\Facades\Route;

Route::get(
    'ppid-informations/media-picker',
    [
        PpidInformationController::class,
        'mediaPicker',
    ]
)->name(
    'ppid-informations.media-picker'
);

Route::resource(
    'ppid-categories',
    PpidCategoryController::class
)->except([
    'show',
]);

Route::resource(
    'ppid-informations',
    PpidInformationController::class
)->except([
    'show',
]);