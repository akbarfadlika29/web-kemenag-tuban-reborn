<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Services\Access\UnitAccessService;
use App\Http\Requests\Admin\Page\StorePageRequest;
use App\Http\Requests\Admin\Page\UpdatePageRequest;
use App\Models\Media;
use App\Models\Page;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
   public function index(Request $request): View
{
    $search = trim(
        (string) $request->input(
            'search',
            ''
        )
    );

    $status = (string) $request->input(
        'status',
        ''
    );

    $unitId = (string) $request->input(
        'unit_id',
        ''
    );

    $parentId = (string) $request->input(
        'parent_id',
        ''
    );

    $showInMenu = (string) $request->input(
        'show_in_menu',
        ''
    );

    $filtersActive =
        $search !== ''
        || $status !== ''
        || $unitId !== ''
        || $parentId !== ''
        || $showInMenu !== '';

    /*
     * Tanpa filter:
     * tampil sebagai hierarchy/tree.
     *
     * Dengan filter:
     * tampil flat supaya hasil pencarian
     * tidak hilang karena parent tidak cocok.
     */
    if (!$filtersActive) {
        $pages = Page::query()
            ->whereNull('parent_id')
            ->with([
                'unit',
                'coverMedia',
                'childrenRecursive',
            ])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $treeMode = true;
    } else {
        $pages = Page::query()
            ->with([
                'parent',
                'unit',
                'coverMedia',
            ])
            ->withCount('children')
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'title',
                                    'ilike',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'slug',
                                    'ilike',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'excerpt',
                                    'ilike',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            )
            ->when(
                $status !== '',
                fn ($query) =>
                    $query->where(
                        'status',
                        $status
                    )
            )
            ->when(
                $unitId !== '',
                fn ($query) =>
                    $query->where(
                        'unit_id',
                        $unitId
                    )
            )
            ->when(
                $parentId === 'root',
                fn ($query) =>
                    $query->whereNull(
                        'parent_id'
                    )
            )
            ->when(
                $parentId !== ''
                && $parentId !== 'root',
                fn ($query) =>
                    $query->where(
                        'parent_id',
                        $parentId
                    )
            )
            ->when(
                $showInMenu !== '',
                fn ($query) =>
                    $query->where(
                        'show_in_menu',
                        $showInMenu === '1'
                    )
            )
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $treeMode = false;
    }

    return view(
        'admin.pages.index',
        [
            'pages' => $pages,
            'units' => $this->activeUnits(),

            'parentOptions' =>
                $this->parentOptions(),

            'search' => $search,
            'status' => $status,
            'unitId' => $unitId,
            'parentId' => $parentId,
            'showInMenu' => $showInMenu,

            'treeMode' => $treeMode,
            'filtersActive' => $filtersActive,
        ]
    );
}

    public function create(): View
    {
        return view(
            'admin.pages.create',
            [
                'units' => $this->activeUnits(),

                'parentOptions' =>
                    $this->parentOptions(),

                'nextSortOrder' =>
                    $this->nextSortOrder(),
            ]
        );
    }

    public function store(
        StorePageRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $data['slug'] =
            $this->generateSlug(
                $data['title']
            );

        $data['excerpt'] =
            $this->prepareExcerpt(
                $data['excerpt'] ?? null,
                $data['content']
            );

        $data['meta_title'] =
            trim(
                (string) (
                    $data['meta_title']
                    ?? ''
                )
            )
            ?: $data['title'];

        $data['meta_description'] =
            trim(
                (string) (
                    $data['meta_description']
                    ?? ''
                )
            )
            ?: Str::limit(
                $data['excerpt'],
                160,
                ''
            );

        if (
            $data['status'] === 'published'
            && empty($data['published_at'])
        ) {
            $data['published_at'] = now();
        }

        DB::transaction(
            fn () => Page::create($data)
        );

        return redirect()
            ->route('admin.pages.index')
            ->with(
                'success',
                'Halaman berhasil ditambahkan.'
            );
    }

    public function edit(Page $page): View
    {
        $page->load([
            'parent',
            'unit',
            'coverMedia',
        ]);

        return view(
            'admin.pages.edit',
            [
                'page' => $page,

                'units' =>
                    $this->activeUnits(),

                'parentOptions' =>
                    $this->parentOptions(
                        $page
                    ),
            ]
        );
    }

    public function update(
        UpdatePageRequest $request,
        Page $page
    ): RedirectResponse {
        $data = $request->validated();

        $data['slug'] =
            $this->generateSlug(
                $data['title'],
                $page->id
            );

        $data['excerpt'] =
            $this->prepareExcerpt(
                $data['excerpt'] ?? null,
                $data['content']
            );

        $data['meta_title'] =
            trim(
                (string) (
                    $data['meta_title']
                    ?? ''
                )
            )
            ?: $data['title'];

        $data['meta_description'] =
            trim(
                (string) (
                    $data['meta_description']
                    ?? ''
                )
            )
            ?: Str::limit(
                $data['excerpt'],
                160,
                ''
            );

        if (
            $data['status'] === 'published'
            && empty($data['published_at'])
        ) {
            $data['published_at'] =
                $page->published_at
                ?? now();
        }

        DB::transaction(
            fn () => $page->update($data)
        );

        return redirect()
            ->route('admin.pages.index')
            ->with(
                'success',
                'Halaman berhasil diperbarui.'
            );
    }

    public function destroy(
        Page $page
    ): RedirectResponse {
        if (
            $page->children()
                ->exists()
        ) {
            return back()->with(
                'error',
                'Halaman tidak dapat dihapus karena masih memiliki halaman turunan.'
            );
        }

        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with(
                'success',
                'Halaman berhasil dihapus.'
            );
    }

    public function mediaPicker(
        Request $request
    ): JsonResponse {
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $unitId = (string) $request->input(
            'unit_id',
            ''
        );

        $mediaId = (int) $request->input(
            'id',
            0
        );

        $query = Media::query()
            ->with('unit')
            ->where('type', 'image')
            ->where('is_public', true);

        if ($mediaId > 0) {
            $query->where(
                'id',
                $mediaId
            );
        } else {
            $query
                ->when(
                    $search !== '',
                    function ($query) use ($search) {
                        $query->where(
                            function ($query) use ($search) {
                                $query
                                    ->where(
                                        'title',
                                        'ilike',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'original_name',
                                        'ilike',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'alt_text',
                                        'ilike',
                                        '%' . $search . '%'
                                    );
                            }
                        );
                    }
                )
                ->when(
                    $unitId === 'global',
                    fn ($query) =>
                        $query->whereNull(
                            'unit_id'
                        )
                )
                ->when(
                    $unitId !== ''
                    && $unitId !== 'global',
                    fn ($query) =>
                        $query->where(
                            'unit_id',
                            $unitId
                        )
                );
        }

        $media = $query
            ->latest()
            ->paginate(24);

        return response()->json([
            'data' =>
                $media
                    ->getCollection()
                    ->map(
                        function (Media $item) {
                            return [
                                'id' =>
                                    $item->id,

                                'title' =>
                                    $item->title
                                    ?: $item->original_name,

                                'original_name' =>
                                    $item->original_name,

                                'alt_text' =>
                                    $item->alt_text
                                    ?: $item->title
                                    ?: $item->original_name,

                                'url' =>
                                    Storage::disk(
                                        $item->disk
                                    )->url(
                                        $item->path
                                    ),

                                'unit_id' =>
                                    $item->unit_id,

                                'unit_name' =>
                                    $item->unit?->name
                                    ?? 'Global',
                            ];
                        }
                    )
                    ->values(),

            'meta' => [
                'current_page' =>
                    $media->currentPage(),

                'last_page' =>
                    $media->lastPage(),

                'has_more' =>
                    $media->hasMorePages(),

                'total' =>
                    $media->total(),
            ],
        ]);
    }

    private function activeUnits()
    {
        $actor = request()->user();
        abort_unless($actor, 401);

        return app(UnitAccessService::class)
            ->forCurrentRoute($actor, 'pages')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function parentOptions(
        ?Page $editingPage = null
    ): array {
        $excludedIds = [];

        if ($editingPage) {
            $excludedIds = [
                $editingPage->id,
                ...$editingPage->descendantIds(),
            ];
        }

        $roots = Page::query()
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $options = [];

        foreach ($roots as $root) {
            $this->appendPageOption(
                $root,
                $options,
                $excludedIds
            );
        }

        return $options;
    }

    private function appendPageOption(
        Page $page,
        array &$options,
        array $excludedIds,
        int $depth = 0
    ): void {
        if (
            !in_array(
                $page->id,
                $excludedIds,
                true
            )
        ) {
            $options[] = [
                'id' => $page->id,

                'label' =>
                    str_repeat(
                        '— ',
                        $depth
                    )
                    . $page->title,
            ];
        }

        foreach (
            $page->childrenRecursive
            as $child
        ) {
            $this->appendPageOption(
                $child,
                $options,
                $excludedIds,
                $depth + 1
            );
        }
    }

    private function nextSortOrder(): int
    {
        return (
            Page::query()
                ->whereNull('parent_id')
                ->max('sort_order')
            ?? 0
        ) + 1;
    }

    private function generateSlug(
        string $title,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'halaman';
        }

        $slug = $base;
        $counter = 2;

        while (
            Page::withTrashed()
                ->where(
                    'slug',
                    $slug
                )
                ->when(
                    $ignoreId !== null,
                    fn ($query) =>
                        $query->where(
                            'id',
                            '!=',
                            $ignoreId
                        )
                )
                ->exists()
        ) {
            $slug =
                $base
                . '-'
                . $counter;

            $counter++;
        }

        return $slug;
    }

    private function prepareExcerpt(
        ?string $excerpt,
        string $content
    ): string {
        $excerpt = trim(
            (string) $excerpt
        );

        if ($excerpt !== '') {
            return $excerpt;
        }

        $plainText = html_entity_decode(
            strip_tags($content),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $plainText = preg_replace(
            '/\s+/u',
            ' ',
            $plainText
        );

        return Str::limit(
            trim((string) $plainText),
            300,
            '…'
        );
    }
}