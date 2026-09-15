<?php

use App\Http\Controllers\Admin\Access\UserAccessController;
use App\Http\Controllers\Admin\Access\AccessRoleController;
use Illuminate\Support\Facades\Route;

Route::get('access', [UserAccessController::class, 'index'])->name('access.index');
Route::get('access/{user}/edit', [UserAccessController::class, 'edit'])->name('access.edit');
Route::put('access/{user}', [UserAccessController::class, 'update'])->name('access.update');

Route::get('access-roles', [AccessRoleController::class, 'index'])->name('access-roles.index');
Route::post('access-roles', [AccessRoleController::class, 'store'])->name('access-roles.store');
Route::put('access-roles/{accessRole}', [AccessRoleController::class, 'update'])
    ->whereNumber('accessRole')->name('access-roles.update');
