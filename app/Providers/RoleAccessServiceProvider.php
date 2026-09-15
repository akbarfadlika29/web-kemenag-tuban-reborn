<?php

namespace App\Providers;

use App\Services\Access\AccessService;
use App\Services\Access\RoleAccessService;
use Illuminate\Support\ServiceProvider;

class RoleAccessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccessService::class, RoleAccessService::class);
    }
}
