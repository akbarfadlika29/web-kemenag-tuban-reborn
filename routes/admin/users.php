<?php

use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Support\Facades\Route;

Route::resource(
    'users',
    UserController::class
)->only([
    'index',
    'create',
    'store',
    'edit',
    'update',
]);