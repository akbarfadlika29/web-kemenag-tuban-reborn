<?php

use App\Providers\AppServiceProvider;
use App\Providers\SettingServiceProvider;

return [
    AppServiceProvider::class,
    SettingServiceProvider::class,
    App\Providers\NavigationServiceProvider::class,
    \App\Providers\PpidAccessScopeServiceProvider::class,
    \App\Providers\RoleAccessServiceProvider::class,
];