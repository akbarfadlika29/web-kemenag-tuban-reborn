<?php

namespace App\Http\Controllers\Admin\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Service\StoreServiceRequest;
use App\Http\Requests\Admin\Service\UpdateServiceRequest;
use App\Models\Media;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
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

        $categoryId =
            (string) $request->input(
                'category_id',
                ''
            );

        $unitId =
            (string) $request->input(
                'unit_id',
                ''
            );

        $status =
            (string) $request->input(
                'status',
                ''
            );

        $channel =
            (string) $request->input(
                'service_channel',
                ''
            );

        $featured =
            (string) $request->input(
                'is_featured',
                ''
            );

        $services =
            Service::query()
                ->with([
                    'category',
                    'unit',
                    'coverMedia',
                ])
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
                                    )
                                    ->orWhere(
                                        'service_location',
                                        'ilike',
                                        '%' . $search . '%'
                                    );
                            }
                        );
                    }
                )
                ->when(
                    $categoryId !== '',
                    fn ($query) =>
                        $query->where(
                            'category_id',
                            $categoryId
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
                    $status !== '',
                    fn ($query) =>
                        $query->where(
                            'status',
                            $status
                        )
                )
                ->when(
                    $channel !== '',
                    fn ($query) =>
                        $query->where(
                            'service_channel',
                            $channel
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
            'admin.services.index',
            [
                'services' =>
                    $services,

                'categories' =>
                    $this->activeCategories(),

                'units' =>
                    $this->activeUnits(),

                'search' =>
                    $search,

                'categoryId' =>
                    $categoryId,

                'unitId' =>
                    $unitId,

                'status' =>
                    $status,

                'channel' =>
                    $channel,

                'featured' =>
                    $featured,
            ]
        );
    }

    public function create(): View
    {
        return view(
            'admin.services.create',
            [
                'categories' =>
                    $this->activeCategories(),

                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function store(
        StoreServiceRequest $request
    ): RedirectResponse {
        $data =
            $this->prepareServiceData(
                $request->validated()
            );

        Service::create(
            $data
        );

        return redirect()
            ->route(
                'admin.services.index'
            )
            ->with(
                'success',
                'Layanan berhasil ditambahkan.'
            );
    }

    public function edit(
        Service $service
    ): View {
        $service->load([
            'category',
            'unit',
            'coverMedia',
        ]);

        return view(
            'admin.services.edit',
            [
                'service' =>
                    $service,

                'categories' =>
                    $this->activeCategories(),

                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function update(
        UpdateServiceRequest $request,
        Service $service
    ): RedirectResponse {
        $data =
            $this->prepareServiceData(
                $request->validated(),
                $service
            );

        $service->update(
            $data
        );

        return redirect()
            ->route(
                'admin.services.index'
            )
            ->with(
                'success',
                'Layanan berhasil diperbarui.'
            );
    }

    public function destroy(
        Service $service
    ): RedirectResponse {
        $service->delete();

        return redirect()
            ->route(
                'admin.services.index'
            )
            ->with(
                'success',
                'Layanan berhasil dihapus.'
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
                    'type',
                    'image'
                )
                ->where(
                    'is_public',
                    true
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

    private function activeCategories()
    {
        return ServiceCategory::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy(
                'sort_order'
            )
            ->orderBy(
                'name'
            )
            ->get();
    }

    private function activeUnits()
    {
        return Unit::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy(
                'sort_order'
            )
            ->orderBy(
                'name'
            )
            ->get();
    }

    private function prepareServiceData(
        array $data,
        ?Service $service = null
    ): array {
        $data['slug'] =
            $this->generateSlug(
                $data['title'],
                $service?->id
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
            (bool) $data['is_free']
        ) {
            $data['fee_description'] =
                null;
        }

        if (
            $data['service_channel']
            === 'offline'
        ) {
            $data['service_url'] =
                null;
        }

        if (
            $data['status']
            === 'published'
            && empty(
                $data['published_at']
            )
        ) {
            $data['published_at'] =
                $service?->published_at
                ?? now();
        }

        return $data;
    }

    private function generateSlug(
        string $title,
        ?int $ignoreId = null
    ): string {
        $base =
            Str::slug($title)
            ?: 'layanan';

        $slug = $base;
        $counter = 2;

        while (
            Service::withTrashed()
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
                . $counter++;
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

        $plain =
            preg_replace(
                '/\s+/u',
                ' ',
                strip_tags(
                    $description
                )
            );

        return Str::limit(
            trim(
                (string) $plain
            ),
            300,
            '…'
        );
    }
}