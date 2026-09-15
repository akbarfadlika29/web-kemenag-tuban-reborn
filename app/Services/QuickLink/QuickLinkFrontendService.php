<?php

namespace App\Services\QuickLink;

use App\Models\QuickLink;
use App\Services\Routing\FrontendRouteService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class QuickLinkFrontendService
{
    public function __construct(
        private readonly FrontendRouteService $frontendRoutes
    ) {
    }

    public function getActive(): Collection
    {
        return QuickLink::query()
            ->active()
            ->with([
                'media',
                'page',
                'newsCategory',
            ])
            ->ordered()
            ->get()
            ->map(
                fn (QuickLink $quickLink) =>
                    $this->transform(
                        $quickLink
                    )
            )
            ->filter()
            ->values();
    }

    private function transform(
        QuickLink $quickLink
    ): ?array {
        $url =
            $this->resolveUrl(
                $quickLink
            );

        if ($url === null) {
            return null;
        }

        $mediaUrl = null;
        $mediaAlt = null;

        if (
            $quickLink->media
            && $quickLink->media->type === 'image'
            && $quickLink->media->is_public
        ) {
            $mediaUrl =
                Storage::disk(
                    $quickLink->media->disk
                )->url(
                    $quickLink->media->path
                );

            $mediaAlt =
                $quickLink->media->alt_text
                ?: $quickLink->label;
        }

        return [
            'id' =>
                $quickLink->id,

            'label' =>
                $quickLink->label,

            'url' =>
                $url,

            'media_url' =>
                $mediaUrl,

            'media_alt' =>
                $mediaAlt,

            'target' =>
                $quickLink->open_in_new_tab
                    ? '_blank'
                    : null,

            'rel' =>
                $quickLink->open_in_new_tab
                    ? 'noopener noreferrer'
                    : null,
        ];
    }

    private function resolveUrl(
        QuickLink $quickLink
    ): ?string {
        return match (
            $quickLink->target_type
        ) {
            QuickLink::TARGET_PAGE =>
                $this->resolvePage(
                    $quickLink
                ),

            QuickLink::TARGET_NEWS_CATEGORY =>
                $this->resolveNewsCategory(
                    $quickLink
                ),

            QuickLink::TARGET_ROUTE =>
                $this->resolveRoute(
                    $quickLink
                ),

            QuickLink::TARGET_URL =>
                $this->resolveManualUrl(
                    $quickLink->url
                ),

            default => null,
        };
    }

    private function resolvePage(
        QuickLink $quickLink
    ): ?string {
        $page =
            $quickLink->page;

        if (
            ! $page
            || ! $page->isPublished()
        ) {
            return null;
        }

        return route(
            'pages.show',
            $page->slug
        );
    }

    private function resolveNewsCategory(
        QuickLink $quickLink
    ): ?string {
        $category =
            $quickLink->newsCategory;

        if (
            ! $category
            || ! $category->is_active
        ) {
            return null;
        }

        return route(
            'news.index',
            [
                'category' =>
                    $category->slug,
            ]
        );
    }

    private function resolveRoute(
        QuickLink $quickLink
    ): ?string {
        $routeName =
            trim(
                (string) $quickLink->route_name
            );

        if (
            $routeName === ''
            || ! $this->frontendRoutes
                ->isAllowed(
                    $routeName
                )
        ) {
            return null;
        }

        return Route::has(
            $routeName
        )
            ? route($routeName)
            : null;
    }

    private function resolveManualUrl(
        ?string $url
    ): ?string {
        $url =
            trim(
                (string) $url
            );

        if ($url === '') {
            return null;
        }

        /*
         * Fragment lokal.
         *
         * Contoh:
         * #
         * #layanan
         * #informasi
         */
        if (
            str_starts_with(
                $url,
                '#'
            )
        ) {
            return $url;
        }

        /*
         * Path lokal website.
         *
         * Contoh:
         * /ppid
         * /layanan
         */
        if (
            str_starts_with(
                $url,
                '/'
            )
        ) {
            return url($url);
        }

        /*
         * URL eksternal hanya menerima HTTP/HTTPS.
         */
        if (
            filter_var(
                $url,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            return null;
        }

        $scheme =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_SCHEME
                )
            );

        if (
            ! in_array(
                $scheme,
                [
                    'http',
                    'https',
                ],
                true
            )
        ) {
            return null;
        }

        return $url;
    }
}
