<?php

namespace App\Services\Navigation;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class NavigationService
{
    /**
     * Cache menu selama satu request.
     *
     * @var array<string, Collection>
     */
    private array $menus = [];

    public function __construct(
        private readonly Request $request
    ) {
    }

    public function header(): Collection
    {
        return $this->location('header');
    }

    public function footer(): Collection
    {
        return $this->location('footer');
    }

    private function location(
        string $location
    ): Collection {
        if (isset($this->menus[$location])) {
            return $this->menus[$location];
        }

        $menus = MenuItem::query()
            ->where(
                'location',
                $location
            )
            ->where(
                'is_active',
                true
            )
            ->whereNull('parent_id')
            ->with([
                'page',
                'childrenRecursive.page',
            ])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $this->menus[$location] =
            $menus
                ->map(
                    fn (MenuItem $menu) =>
                        $this->transform(
                            $menu,
                            $location
                        )
                )
                ->values();

        return $this->menus[$location];
    }

    private function transform(
        MenuItem $menu,
        string $location
    ): array {
        $children = $menu
            ->childrenRecursive
            ->filter(
                fn (MenuItem $child) =>
                    $child->is_active
                    && $child->location === $location
            )
            ->sortBy([
                ['sort_order', 'asc'],
                ['label', 'asc'],
            ])
            ->map(
                fn (MenuItem $child) =>
                    $this->transform(
                        $child,
                        $location
                    )
            )
            ->values();

        $url =
            $this->resolveUrl(
                $menu
            );

        $isCurrent =
            $this->isCurrent(
                $menu,
                $url
            );

        $isActive =
            $isCurrent
            || $children->contains(
                fn (array $child) =>
                    $child['active']
            );

        return [
            'id' =>
                $menu->id,

            'label' =>
                $menu->label,

            'type' =>
                $menu->type,

            'url' =>
                $url,

            'target' =>
                $menu->open_in_new_tab
                    ? '_blank'
                    : null,

            'rel' =>
                $menu->open_in_new_tab
                    ? 'noopener noreferrer'
                    : null,

            'current' =>
                $isCurrent,

            'active' =>
                $isActive,

            'children' =>
                $children,
        ];
    }

    private function resolveUrl(
        MenuItem $menu
    ): ?string {
        return match ($menu->type) {
            'page' =>
                $this->resolvePageUrl(
                    $menu
                ),

            'route' =>
                $this->resolveRouteUrl(
                    $menu
                ),

            'url' =>
                filled($menu->url)
                    ? $menu->url
                    : null,

            default =>
                null,
        };
    }

    private function resolvePageUrl(
        MenuItem $menu
    ): ?string {
        if (
            !$menu->page
            || !$menu->page->isPublished()
        ) {
            return null;
        }

        return route(
            'pages.show',
            $menu->page->slug
        );
    }

    private function resolveRouteUrl(
        MenuItem $menu
    ): ?string {
        if (blank($menu->route_name)) {
            return null;
        }

        $route =
            Route::getRoutes()
                ->getByName(
                    $menu->route_name
                );

        if (!$route) {
            return null;
        }

        /*
         * Menu route frontend harus dapat dibuat
         * tanpa parameter dinamis.
         */
        if (
            count(
                $route->parameterNames()
            ) > 0
        ) {
            return null;
        }

        return route(
            $menu->route_name
        );
    }

    private function isCurrent(
        MenuItem $menu,
        ?string $url
    ): bool {
        if ($menu->type === 'page') {
            return
                $menu->page !== null
                && $this->request
                    ->routeIs(
                        'pages.show'
                    )
                && (string) $this->request
                    ->route('slug')
                === (string) $menu
                    ->page
                    ->slug;
        }

        if (
            $menu->type === 'route'
            && filled(
                $menu->route_name
            )
        ) {
            if (
                $this->request->routeIs(
                    $menu->route_name
                )
            ) {
                return true;
            }

            $parts =
                explode(
                    '.',
                    $menu->route_name
                );

            if (count($parts) > 1) {
                return $this->request
                    ->routeIs(
                        $parts[0] . '.*'
                    );
            }

            return false;
        }

        if (
            $menu->type === 'url'
            && filled($url)
        ) {
            $path =
                parse_url(
                    $url,
                    PHP_URL_PATH
                );

            if (!$path) {
                return false;
            }

            return trim(
                $path,
                '/'
            ) === trim(
                $this->request->path(),
                '/'
            );
        }

        return false;
    }
}
