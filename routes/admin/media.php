<?php

use App\Http\Controllers\Admin\Media\MediaController;
use App\Http\Controllers\Admin\Media\MediaPickerController;
use Illuminate\Support\Facades\Route;

Route::get(
    'media/picker',
    [MediaPickerController::class, 'index']
)->name('media.picker.index');

Route::post(
    'media/picker/upload',
    [MediaPickerController::class, 'store']
)->name('media.picker.upload');

Route::resource(
    'media',
    MediaController::class
)
    ->parameters([
        'media' => 'medium',
    ])
    ->except([
        'show',
    ]);
