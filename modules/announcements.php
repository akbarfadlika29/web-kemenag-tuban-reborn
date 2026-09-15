<?php

use App\Http\Controllers\Admin\Announcement\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::get(
    'announcements/media-picker',
    [
        AnnouncementController::class,
        'mediaPicker',
    ]
)->name(
    'announcements.media-picker'
);

Route::resource(
    'announcements',
    AnnouncementController::class
)->except([
    'show',
]);