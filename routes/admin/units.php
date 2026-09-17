<?php

use App\Http\Controllers\Admin\Unit\UnitController;
use Illuminate\Support\Facades\Route;

Route::patch('units/{unit}/move', [UnitController::class, 'move'])
    ->name('units.move');

Route::resource('units', UnitController::class)
    ->except(['show']);

