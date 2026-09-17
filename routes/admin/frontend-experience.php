<?php

use App\Http\Controllers\Admin\Frontend\ExperienceController;
use Illuminate\Support\Facades\Route;

Route::get('frontend-settings', [ExperienceController::class, 'edit'])
    ->name('frontend-settings.edit');

Route::put('frontend-settings', [ExperienceController::class, 'update'])
    ->name('frontend-settings.update');

Route::get('content-statistics', [ExperienceController::class, 'statistics'])
    ->name('content-statistics.index');
