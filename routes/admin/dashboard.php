<?php

use App\Http\Controllers\Admin\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    [DashboardController::class, 'index']
)->name('dashboard');