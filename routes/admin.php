<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
|
| Route login harus tetap berada di luar grup auth agar guest dapat
| mengakses halaman login. Route logout sudah memiliki middleware auth
| pada module auth-nya sendiri.
|
*/

require __DIR__.'/modules/auth.php';

/*
|--------------------------------------------------------------------------
| Protected Admin Area
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    \App\Http\Middleware\EnsureAdminPermission::class,
    \App\Http\Middleware\EnsureAdminObjectAccess::class,
    \App\Http\Middleware\EnsureAdminMediaAccess::class,
    \App\Http\Middleware\EnsureUserManagementAccess::class,
])->group(function () {
    require __DIR__.'/modules/regulations.php';

    require __DIR__.'/modules/dashboard.php';
    require __DIR__.'/modules/units.php';
    require __DIR__.'/modules/media.php';
    require __DIR__.'/modules/news.php';
    require __DIR__.'/modules/pages.php';
    require __DIR__.'/modules/announcements.php';
    require __DIR__.'/modules/agendas.php';
    require __DIR__.'/modules/galleries.php';
    require __DIR__.'/modules/ppid.php';
    require __DIR__.'/modules/services.php';
    require __DIR__.'/modules/users.php';
    require __DIR__.'/modules/access.php';
    require __DIR__.'/modules/menus.php';
    require __DIR__.'/modules/quick-links.php';
    require __DIR__.'/modules/hero-slides.php';
    require __DIR__.'/modules/related-links.php';
    require __DIR__.'/modules/settings.php';
    require __DIR__.'/modules/frontend-experience.php';
});
