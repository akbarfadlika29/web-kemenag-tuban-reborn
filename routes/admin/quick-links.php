<?php

use App\Http\Controllers\Admin\QuickLink\QuickLinkController;
use Illuminate\Support\Facades\Route;

Route::resource(
    'quick-links',
    QuickLinkController::class
)->except([
    'show',
]);
