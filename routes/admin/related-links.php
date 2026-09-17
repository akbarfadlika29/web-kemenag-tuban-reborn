<?php

use App\Http\Controllers\Admin\RelatedLink\RelatedLinkController;
use Illuminate\Support\Facades\Route;

Route::resource(
    'related-links',
    RelatedLinkController::class
)->except([
    'show',
]);
