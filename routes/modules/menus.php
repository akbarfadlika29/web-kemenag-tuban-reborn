<?php

use App\Http\Controllers\Admin\Menu\MenuItemController;
use Illuminate\Support\Facades\Route;

Route::patch(
    'menus/{menu}/move',
    [MenuItemController::class, 'move']
)->name('menus.move');

Route::resource(
    'menus',
    MenuItemController::class
)->except(['show']);
