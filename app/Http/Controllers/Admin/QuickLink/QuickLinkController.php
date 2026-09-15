<?php

namespace App\Http\Controllers\Admin\QuickLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuickLink\StoreQuickLinkRequest;
use App\Http\Requests\Admin\QuickLink\UpdateQuickLinkRequest;
use App\Models\Media;
use App\Models\NewsCategory;
use App\Models\Page;
use App\Models\QuickLink;
use App\Services\QuickLink\QuickLinkTargetNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QuickLinkController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $targetType = (string) $request->input(
            'target_type',
            ''
        );

        if (
            ! in_array(
                $targetType,
                [
                    '',
                    'page',
                    'news_category',
                    'route',
                    'url',
                ],
                true
            )
        ) {
            $targetType = '';
        }

        $status = (string) $request->input(
            'status',
            ''
        );

        if (
            ! in_array(
                $status,
                [
                    '',
                    '1',
                    '0',
                ],
                true
            )
        ) {
            $status = '';
        }

        $quickLinks = QuickLink::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'label',
                                    'ilike',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'route_name',
                                    'ilike',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'url',
                                    'ilike',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            )
            ->when(
                $targetType !== '',
                fn ($query) =>
                    $query->where(
                        'target_type',
                        $targetType
                    )
            )
            ->when(
                $status !== '',
                fn ($query) =>
                    $query->where(
                        'is_active',
                        $status === '1'
                    )
            )
            ->orderBy('sort_order')
            ->orderBy('label')
            ->paginate(20)
            ->withQueryString();

        $items = $quickLinks
            ->getCollection();

        $mediaById = Media::query()
            ->whereIn(
                'id',
                $items
                    ->pluck('media_id')
                    ->filter()
                    ->unique()
                    ->values()
            )
            ->get()
            ->keyBy('id');

        $mediaUrls = $mediaById
            ->mapWithKeys(
                fn (Media $medium) => [
                    $medium->id =>
                        $this->mediaUrl(
                            $medium
                        ),
                ]
            );

        $pagesById = Page::query()
            ->whereIn(
                'id',
                $items
                    ->pluck('page_id')
                    ->filter()
                    ->unique()
                    ->values()
            )
            ->get([
                'id',
                'title',
                'slug',
            ])
            ->keyBy('id');

        $newsCategoriesById =
            NewsCategory::query()
                ->whereIn(
                    'id',
                    $items
                        ->pluck(
                            'news_category_id'
                        )
                        ->filter()
                        ->unique()
                        ->values()
                )
                ->get([
                    'id',
                    'name',
                    'slug',
                ])
                ->keyBy('id');

        $hasFilter =
            $search !== ''
            || $targetType !== ''
            || $status !== '';

        return view(
            'admin.quick-links.index',
            compact(
                'quickLinks',
                'mediaById',
                'mediaUrls',
                'pagesById',
                'newsCategoriesById',
                'search',
                'targetType',
                'status',
                'hasFilter'
            )
        );
    }

    public function create(): View
    {
        return view(
            'admin.quick-links.create',
            $this->formData()
        );
    }

    public function store(
        StoreQuickLinkRequest $request,
        QuickLinkTargetNormalizer $normalizer
    ): RedirectResponse {
        QuickLink::create(
            $normalizer->normalize(
                $request->validated()
            )
        );

        return redirect()
            ->route(
                'admin.quick-links.index'
            )
            ->with(
                'success',
                'Akses cepat berhasil ditambahkan.'
            );
    }

    public function edit(
        QuickLink $quickLink
    ): View {
        return view(
            'admin.quick-links.edit',
            array_merge(
                [
                    'quickLink' =>
                        $quickLink,
                ],
                $this->formData(
                    $quickLink
                )
            )
        );
    }

    public function update(
        UpdateQuickLinkRequest $request,
        QuickLink $quickLink,
        QuickLinkTargetNormalizer $normalizer
    ): RedirectResponse {
        $quickLink->update(
            $normalizer->normalize(
                $request->validated()
            )
        );

        return redirect()
            ->route(
                'admin.quick-links.index'
            )
            ->with(
                'success',
                'Akses cepat berhasil diperbarui.'
            );
    }

    public function destroy(
        QuickLink $quickLink
    ): RedirectResponse {
        $quickLink->delete();

        return redirect()
            ->route(
                'admin.quick-links.index'
            )
            ->with(
                'success',
                'Akses cepat berhasil dihapus.'
            );
    }

    private function formData(
        ?QuickLink $quickLink = null
    ): array {
        $pages = Page::query()
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'slug',
                'status',
            ]);

        $newsCategories =
            NewsCategory::query()
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'slug',
                    'is_active',
                ]);

        $frontendRoutes =
            $this->frontendRouteOptions();

        $nextSortOrder =
            ((int) QuickLink::query()
                ->max('sort_order')) + 1;

        $selectedMedia = null;
        $selectedMediaUrl = null;
        $selectedMediaPayload = null;

        if (
            $quickLink
            && $quickLink->media_id
        ) {
            $selectedMedia =
                Media::find(
                    $quickLink->media_id
                );

            if ($selectedMedia) {
                $selectedMediaUrl =
                    $this->mediaUrl(
                        $selectedMedia
                    );

                $selectedMediaPayload = [
                    'id' =>
                        $selectedMedia->id,

                    'type' =>
                        $selectedMedia->type,

                    'url' =>
                        $selectedMediaUrl,

                    'title' =>
                        $selectedMedia->title,

                    'original_name' =>
                        $selectedMedia
                            ->original_name,

                    'alt_text' =>
                        $selectedMedia
                            ->alt_text,

                    'extension' =>
                        $selectedMedia
                            ->extension,
                ];
            }
        }

        return compact(
            'pages',
            'newsCategories',
            'frontendRoutes',
            'nextSortOrder',
            'selectedMedia',
            'selectedMediaUrl',
            'selectedMediaPayload'
        );
    }

    private function frontendRouteOptions(): array
    {
        return collect(
            Route::getRoutes()
        )
            ->filter(
                fn ($route) =>
                    filled(
                        $route->getName()
                    )
            )
            ->filter(
                fn ($route) =>
                    in_array(
                        'GET',
                        $route->methods(),
                        true
                    )
            )
            ->filter(
                fn ($route) =>
                    str_starts_with(
                        $route
                            ->getActionName(),
                        'App\\Http\\Controllers\\Frontend\\'
                    )
            )
            ->reject(
                fn ($route) =>
                    str_starts_with(
                        (string) $route
                            ->getName(),
                        'admin.'
                    )
            )
            ->filter(
                fn ($route) =>
                    count(
                        $route
                            ->parameterNames()
                    ) === 0
            )
            ->map(
                fn ($route) => [
                    'name' =>
                        (string) $route
                            ->getName(),

                    'uri' =>
                        $route->uri(),
                ]
            )
            ->sortBy('name')
            ->values()
            ->all();
    }

    private function mediaUrl(
        Media $medium
    ): string {
        return Storage::disk(
            $medium->disk
        )->url(
            $medium->path
        );
    }
}
