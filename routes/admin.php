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

require __DIR__.'/admin/auth.php';

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
    require __DIR__.'/admin/regulations.php';

    require __DIR__.'/admin/dashboard.php';
    require __DIR__.'/admin/units.php';
    require __DIR__.'/admin/media.php';
    require __DIR__.'/admin/news.php';
    require __DIR__.'/admin/pages.php';
    require __DIR__.'/admin/announcements.php';
    require __DIR__.'/admin/agendas.php';
    require __DIR__.'/admin/galleries.php';
    require __DIR__.'/admin/ppid.php';
    require __DIR__.'/admin/services.php';
    require __DIR__.'/admin/users.php';
    require __DIR__.'/admin/access.php';
    require __DIR__.'/admin/menus.php';
    require __DIR__.'/admin/quick-links.php';
    require __DIR__.'/admin/hero-slides.php';
    require __DIR__.'/admin/related-links.php';
    require __DIR__.'/admin/settings.php';
    require __DIR__.'/admin/frontend-experience.php';
});
