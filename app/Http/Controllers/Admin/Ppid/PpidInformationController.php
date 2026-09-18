<?php

namespace App\Http\Controllers\Admin\Ppid;

use App\Http\Controllers\Controller;
use App\Services\Access\UnitAccessService;
use App\Http\Requests\Admin\Ppid\Information\StorePpidInformationRequest;
use App\Http\Requests\Admin\Ppid\Information\UpdatePpidInformationRequest;
use App\Models\Media;
use App\Models\PpidCategory;
use App\Models\PpidInformation;
use App\Models\PpidInformationDocument;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PpidInformationController extends Controller
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

        $classification =
            (string) $request->input(
                'classification',
                ''
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

        $year =
            (string) $request->input(
                'year',
                ''
            );

        $availability =
            (string) $request->input(
                'availability',
                ''
            );

        $accessLevel =
            (string) $request->input(
                'access_level',
                ''
            );

        $informations =
            PpidInformation::query()
                ->with([
                    'category',
                    'unit',
                    'primaryDocument.media',
                ])
                ->withCount(
                    'documents'
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
                                        'document_number',
                                        'ilike',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'information_holder',
                                        'ilike',
                                        '%' . $search . '%'
                                    );
                            }
                        );
                    }
                )
                ->when(
                    $classification !== '',
                    fn ($query) =>
                        $query->where(
                            'classification',
                            $classification
                        )
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
                    $year !== '',
                    fn ($query) =>
                        $query->where(
                            'year',
                            $year
                        )
                )
                ->when(
                    $availability !== '',
                    fn ($query) =>
                        $query->where(
                            'availability',
                            $availability
                        )
                )
                ->when(
                    $accessLevel !== '',
                    fn ($query) =>
                        $query->where(
                            'access_level',
                            $accessLevel
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
            'admin.ppid.informations.index',
            [
                'informations' =>
                    $informations,

                'categories' =>
                    $this->activeCategories(),

                'units' =>
                    $this->activeUnits(),

                'search' =>
                    $search,

                'classification' =>
                    $classification,

                'categoryId' =>
                    $categoryId,

                'unitId' =>
                    $unitId,

                'status' =>
                    $status,

                'year' =>
                    $year,

                'availability' =>
                    $availability,

                'accessLevel' =>
                    $accessLevel,
            ]
        );
    }

    public function create(): View
    {
        return view(
            'admin.ppid.informations.create',
            [
                'categories' =>
                    $this->activeCategories(),

                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function store(
        StorePpidInformationRequest $request
    ): RedirectResponse {
        $data =
            $request->validated();

        $documents =
            $data['documents'];

        unset(
            $data['documents']
        );

        $data =
            $this->prepareInformationData(
                $data
            );

        DB::transaction(
            function () use (
                $data,
                $documents
            ) {
                $information =
                    PpidInformation::create(
                        $data
                    );

                $this->syncDocuments(
                    $information,
                    $documents
                );
            }
        );

        return redirect()
            ->route(
                'admin.ppid-informations.index'
            )
            ->with(
                'success',
                'Informasi PPID berhasil ditambahkan.'
            );
    }

    public function edit(
        PpidInformation $ppidInformation
    ): View {
        $ppidInformation->load([
            'category',
            'unit',
            'documents.media',
        ]);

        return view(
            'admin.ppid.informations.edit',
            [
                'information' =>
                    $ppidInformation,

                'categories' =>
                    $this->activeCategories(),

                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function update(
        UpdatePpidInformationRequest $request,
        PpidInformation $ppidInformation
    ): RedirectResponse {
        $data =
            $request->validated();

        $documents =
            $data['documents'];

        unset(
            $data['documents']
        );

        $data =
            $this->prepareInformationData(
                $data,
                $ppidInformation
            );

        DB::transaction(
            function () use (
                $ppidInformation,
                $data,
                $documents
            ) {
                $ppidInformation->update(
                    $data
                );

                $this->syncDocuments(
                    $ppidInformation,
                    $documents
                );
            }
        );

        return redirect()
            ->route(
                'admin.ppid-informations.index'
            )
            ->with(
                'success',
                'Informasi PPID berhasil diperbarui.'
            );
    }

    public function destroy(
        PpidInformation $ppidInformation
    ): RedirectResponse {
        DB::transaction(
            function () use (
                $ppidInformation
            ) {
                /*
                 * File di Media Manager tidak dihapus.
                 */
                $ppidInformation
                    ->documents()
                    ->delete();

                $ppidInformation
                    ->delete();
            }
        );

        return redirect()
            ->route(
                'admin.ppid-informations.index'
            )
            ->with(
                'success',
                'Informasi PPID berhasil dihapus.'
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

                                'url' =>
                                    Storage::disk(
                                        $item->disk
                                    )->url(
                                        $item->path
                                    ),

                                'type' =>
                                    $item->type,

                                'mime_type' =>
                                    $item->mime_type,

                                'extension' =>
                                    $item->extension,

                                'size' =>
                                    $item->size,

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
        return PpidCategory::query()
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
        $actor = request()->user();
        abort_unless($actor, 401);

        return app(UnitAccessService::class)
            ->forCurrentRoute($actor, 'ppid-informations')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function prepareInformationData(
        array $data,
        ?PpidInformation $information = null
    ): array {
        $data['slug'] =
            $this->generateSlug(
                $data['title'],
                $information?->id
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

        /*
         * Permanent tidak membutuhkan angka retensi.
         */
        if (
            ($data['retention_unit'] ?? null)
            === 'permanent'
        ) {
            $data['retention_period'] =
                null;
        }

        if (
            $data['status'] ===
                'published'
            && empty(
                $data['published_at']
            )
        ) {
            $data['published_at'] =
                $information?->published_at
                ?? now();
        }

        return $data;
    }

    private function syncDocuments(
        PpidInformation $information,
        array $documents
    ): void {
        $information
            ->documents()
            ->delete();

        $primaryFound =
            false;

        foreach (
            array_values(
                $documents
            ) as $index => $document
        ) {
            $isPrimary =
                (bool) (
                    $document['is_primary']
                    ?? false
                );

            if (
                $isPrimary
                && $primaryFound
            ) {
                $isPrimary = false;
            }

            if ($isPrimary) {
                $primaryFound = true;
            }

            PpidInformationDocument::create([
                'information_id' =>
                    $information->id,

                'media_id' =>
                    $document['media_id'],

                'title' =>
                    trim(
                        (string) (
                            $document['title']
                            ?? ''
                        )
                    ) ?: null,

                'description' =>
                    trim(
                        (string) (
                            $document['description']
                            ?? ''
                        )
                    ) ?: null,

                'version' =>
                    trim(
                        (string) (
                            $document['version']
                            ?? ''
                        )
                    ) ?: null,

                'document_status' =>
                    $document[
                        'document_status'
                    ],

                'document_date' =>
                    $document[
                        'document_date'
                    ] ?? null,

                'sort_order' =>
                    $index,

                'is_primary' =>
                    $isPrimary,
            ]);
        }

        /*
         * Kalau tidak ada yang ditandai utama,
         * jadikan dokumen pertama sebagai primary.
         */
        if (
            !$primaryFound
            && $information
                ->documents()
                ->exists()
        ) {
            $information
                ->documents()
                ->orderBy(
                    'sort_order'
                )
                ->first()
                ?->update([
                    'is_primary' =>
                        true,
                ]);
        }
    }

    private function generateSlug(
        string $title,
        ?int $ignoreId = null
    ): string {
        $base =
            Str::slug($title)
            ?: 'informasi-ppid';

        $slug = $base;
        $counter = 2;

        while (
            PpidInformation::withTrashed()
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

        $description =
            preg_replace(
                '/\s+/u',
                ' ',
                strip_tags(
                    $description
                )
            );

        return Str::limit(
            trim(
                (string) $description
            ),
            300,
            '…'
        );
    }
}