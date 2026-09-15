<?php

namespace App\Providers;

use App\Services\Navigation\NavigationService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            NavigationService::class,
            fn ($app) =>
                new NavigationService(
                    $app['request']
                )
        );
    }

    public function boot(): void
    {
        View::composer(
            'frontend.partials.navbar',
            function ($view) {
                $view->with(
                    'headerMenus',
                    app(
                        NavigationService::class
                    )->header()
                );
            }
        );

        View::composer(
            'frontend.partials.footer',
            function ($view) {
                $view->with(
                    'footerMenus',
                    app(
                        NavigationService::class
                    )->footer()
                );
            }
        );
    }
}
