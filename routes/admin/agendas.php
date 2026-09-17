<?php

use App\Http\Controllers\Admin\Agenda\AgendaController;
use Illuminate\Support\Facades\Route;

Route::get(
    'agendas/media-picker',
    [
        AgendaController::class,
        'mediaPicker',
    ]
)->name(
    'agendas.media-picker'
);

Route::resource(
    'agendas',
    AgendaController::class
)->except([
    'show',
]);