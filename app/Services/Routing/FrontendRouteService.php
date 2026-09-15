<?php

namespace App\Services\Routing;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

class FrontendRouteService
{
    public function isAllowed(
        string $routeName
    ): bool {
        $route =
            Route::getRoutes()
                ->getByName($routeName);

        return
            $route !== null
            && $this->isAllowedRoute(
                $route
            );
    }

    public function options(): array
    {
        return collect(
            Route::getRoutes()
        )
            ->filter(
                fn (RoutingRoute $route) =>
                    $this->isAllowedRoute(
                        $route
                    )
            )
            ->map(
                fn (RoutingRoute $route) => [
                    'name' =>
                        (string) $route->getName(),

                    'uri' =>
                        $route->uri(),
                ]
            )
            ->sortBy('name')
            ->values()
            ->all();
    }

    private function isAllowedRoute(
        RoutingRoute $route
    ): bool {
        $name =
            (string) $route->getName();

        if ($name === '') {
            return false;
        }

        if (
            str_starts_with(
                $name,
                'admin.'
            )
        ) {
            return false;
        }

        if (
            !in_array(
                'GET',
                $route->methods(),
                true
            )
        ) {
            return false;
        }

        if (
            !str_starts_with(
                $route->getActionName(),
                'App\\Http\\Controllers\\Frontend\\'
            )
        ) {
            return false;
        }

        return count(
            $route->parameterNames()
        ) === 0;
    }
}
