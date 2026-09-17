<?php

use App\Http\Controllers\Frontend\AgendaController;
use App\Http\Controllers\Frontend\AnnouncementController;
use App\Http\Controllers\Frontend\GalleryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NewsController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PpidController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    [HomeController::class, 'index']
)->name('home');

Route::get(
    '/pencarian',
    [SearchController::class, 'index']
)->name('search.index');


Route::get(
    '/halaman/{slug}',
    [PageController::class, 'show']
)->name('pages.show');
Route::get(
    '/berita',
    [NewsController::class, 'index']
)->name('news.index');

Route::get(
    '/berita/{slug}',
    [NewsController::class, 'show']
)->name('news.show');

Route::get(
    '/pengumuman',
    [AnnouncementController::class, 'index']
)->name('announcements.index');

Route::get(
    '/pengumuman/{slug}',
    [AnnouncementController::class, 'show']
)->name('announcements.show');

Route::get(
    '/agenda',
    [AgendaController::class, 'index']
)->name('agendas.index');

Route::get(
    '/agenda/{slug}',
    [AgendaController::class, 'show']
)->name('agendas.show');

Route::get(
    '/galeri',
    [GalleryController::class, 'index']
)->name('galleries.index');

Route::get(
    '/galeri/{slug}',
    [GalleryController::class, 'show']
)->name('galleries.show');

Route::get(
    '/ppid',
    [PpidController::class, 'index']
)->name('ppid.index');

Route::get(
    '/ppid/{slug}',
    [PpidController::class, 'show']
)->name('ppid.show');

Route::get(
    '/layanan',
    [ServiceController::class, 'index']
)->name('services.index');

Route::get(
    '/layanan/{slug}',
    [ServiceController::class, 'show']
)->name('services.show');

require __DIR__.'/frontend/regulations.php';

require __DIR__.'/frontend/interactions.php';
