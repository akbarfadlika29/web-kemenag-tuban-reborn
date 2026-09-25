<?php

use App\Http\Controllers\Admin\Setting\SettingController;
use Illuminate\Support\Facades\Route;

Route::get(
    'settings',
    [SettingController::class, 'index']
)->name('settings.index');

Route::put(
    'settings',
    [SettingController::class, 'update']
)->name('settings.update');
