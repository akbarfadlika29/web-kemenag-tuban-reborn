<?php

namespace App\Http\Controllers\Admin\Gallery;

use App\Http\Controllers\Controller;
use App\Services\Access\UnitAccessService;
use App\Http\Requests\Admin\Gallery\StoreGalleryRequest;
use App\Http\Requests\Admin\Gallery\UpdateGalleryRequest;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GalleryController extends Controller
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

        $status =
            (string) $request->input(
                'status',
                ''
            );

        $unitId =
            (string) $request->input(
                'unit_id',
                ''
            );

        $featured =
            (string) $request->input(
                'is_featured',
                ''
            );

        $galleries =
            Gallery::query()
                ->with([
                    'unit',
                    'coverMedia',
                ])
                ->withCount(
                    'items'
                )
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
                                    )
                                    ->orWhere(
                                        'description',
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
                    $featured !== '',
                    fn ($query) =>
                        $query->where(
                            'is_featured',
                            $featured === '1'
                        )
                )
                ->orderByDesc(
                    'is_featured'
                )
                ->orderByDesc(
                    'published_at'
                )
                ->orderByDesc(
                    'created_at'
                )
                ->paginate(20)
                ->withQueryString();

        return view(
            'admin.galleries.index',
            [
                'galleries' =>
                    $galleries,

                'units' =>
                    $this->activeUnits(),

                'search' =>
                    $search,

                'status' =>
                    $status,

                'unitId' =>
                    $unitId,

                'featured' =>
                    $featured,
            ]
        );
    }

    public function create(): View
    {
        return view(
            'admin.galleries.create',
            [
                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function store(
        StoreGalleryRequest $request
    ): RedirectResponse {
        $data =
            $request->validated();

        $items =
            $data['items'];

        unset(
            $data['items']
        );

        $data =
            $this->prepareGalleryData(
                $data
            );

        DB::transaction(
            function () use (
                $data,
                $items
            ) {
                $gallery =
                    Gallery::create(
                        $data
                    );

                $this->syncItems(
                    $gallery,
                    $items
                );
            }
        );

        return redirect()
            ->route(
                'admin.galleries.index'
            )
            ->with(
                'success',
                'Galeri berhasil ditambahkan.'
            );
    }

    public function edit(
        Gallery $gallery
    ): View {
        $gallery->load([
            'unit',
            'coverMedia',
            'items.media',
        ]);

        return view(
            'admin.galleries.edit',
            [
                'gallery' =>
                    $gallery,

                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function update(
        UpdateGalleryRequest $request,
        Gallery $gallery
    ): RedirectResponse {
        $data =
            $request->validated();

        $items =
            $data['items'];

        unset(
            $data['items']
        );

        $data =
            $this->prepareGalleryData(
                $data,
                $gallery
            );

        DB::transaction(
            function () use (
                $gallery,
                $data,
                $items
            ) {
                $gallery->update(
                    $data
                );

                $this->syncItems(
                    $gallery,
                    $items
                );
            }
        );

        return redirect()
            ->route(
                'admin.galleries.index'
            )
            ->with(
                'success',
                'Galeri berhasil diperbarui.'
            );
    }

    public function destroy(
        Gallery $gallery
    ): RedirectResponse {
        DB::transaction(
            function () use ($gallery) {
                $gallery
                    ->items()
                    ->delete();

                $gallery->delete();
            }
        );

        return redirect()
            ->route(
                'admin.galleries.index'
            )
            ->with(
                'success',
                'Galeri berhasil dihapus.'
            );
    }

    public function mediaPicker(
        Request $request
    ): JsonResponse {
        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );

        $unitId =
            (string) $request->input(
                'unit_id',
                ''
            );

        $mediaId =
            (int) $request->input(
                'id',
                0
            );

        $query =
            Media::query()
                ->with('unit')
                ->where(
                    'is_public',
                    true
                )
                ->where(
                    'type',
                    'image'
                );

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

        $media =
            $query
                ->latest()
                ->paginate(24);

        return response()->json([
            'data' =>
                $media
                    ->getCollection()
                    ->map(
                        function (
                            Media $item
                        ) {
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
            ->forCurrentRoute($actor, 'galleries')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function prepareGalleryData(
        array $data,
        ?Gallery $gallery = null
    ): array {
        $data['slug'] =
            $this->generateSlug(
                $data['title'],
                $gallery?->id
            );

        $data['excerpt'] =
            $this->prepareExcerpt(
                $data['excerpt'] ?? null,
                $data['description'] ?? null
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
                $data['excerpt'] ?? '',
                160,
                ''
            );

        if (
            $data['status']
                === 'published'
            && empty(
                $data['published_at']
            )
        ) {
            $data['published_at'] =
                $gallery?->published_at
                ?? now();
        }

        return $data;
    }

    private function syncItems(
        Gallery $gallery,
        array $items
    ): void {
        $gallery
            ->items()
            ->delete();

        foreach (
            array_values(
                $items
            ) as $index => $item
        ) {
            GalleryItem::create([
                'gallery_id' =>
                    $gallery->id,

                'media_id' =>
                    $item['media_id'],

                'caption' =>
                    trim(
                        (string) (
                            $item['caption']
                            ?? ''
                        )
                    ) ?: null,

                'alt_text' =>
                    trim(
                        (string) (
                            $item['alt_text']
                            ?? ''
                        )
                    ) ?: null,

                'sort_order' =>
                    $index,
            ]);
        }
    }

    private function generateSlug(
        string $title,
        ?int $ignoreId = null
    ): string {
        $base =
            Str::slug(
                $title
            );

        if ($base === '') {
            $base = 'galeri';
        }

        $slug = $base;
        $counter = 2;

        while (
            Gallery::withTrashed()
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
        ?string $description
    ): ?string {
        $excerpt =
            trim(
                (string) $excerpt
            );

        if ($excerpt !== '') {
            return $excerpt;
        }

        $description =
            trim(
                (string) $description
            );

        if ($description === '') {
            return null;
        }

        return Str::limit(
            preg_replace(
                '/\s+/u',
                ' ',
                strip_tags(
                    $description
                )
            ),
            300,
            '…'
        );
    }
}