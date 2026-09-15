<?php

namespace App\Providers;

use App\Services\Setting\SiteSettingService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            SiteSettingService::class,
            fn () => new SiteSettingService()
        );
    }

    public function boot(): void
    {
        View::composer(
            'frontend.*',
            function ($view) {
                $settings = app(
                    SiteSettingService::class
                );

                $view->with([
                    'siteSettings' =>
                        $settings->all(),

                    'siteLogo' =>
                        $settings->logo(),
                ]);
            }
        );
    }
}
