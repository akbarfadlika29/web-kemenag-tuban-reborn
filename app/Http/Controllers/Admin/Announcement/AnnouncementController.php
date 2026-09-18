<?php

namespace App\Http\Controllers\Admin\Announcement;

use App\Http\Controllers\Controller;
use App\Services\Access\UnitAccessService;
use App\Http\Requests\Admin\Announcement\StoreAnnouncementRequest;
use App\Http\Requests\Admin\Announcement\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Media;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnnouncementController extends Controller
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

        $pinned =
            (string) $request->input(
                'is_pinned',
                ''
            );

        $announcements =
            Announcement::query()
                ->with([
                    'unit',
                    'coverMedia',
                    'attachmentMedia',
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
                    $pinned !== '',
                    fn ($query) =>
                        $query->where(
                            'is_pinned',
                            $pinned === '1'
                        )
                )
                ->orderByDesc(
                    'is_pinned'
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
            'admin.announcements.index',
            [
                'announcements' =>
                    $announcements,

                'units' =>
                    $this->activeUnits(),

                'search' =>
                    $search,

                'status' =>
                    $status,

                'unitId' =>
                    $unitId,

                'pinned' =>
                    $pinned,
            ]
        );
    }

    public function create(): View
    {
        return view(
            'admin.announcements.create',
            [
                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function store(
        StoreAnnouncementRequest $request
    ): RedirectResponse {
        $data =
            $request->validated();

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
            $data['status'] ===
                'published'
            && empty(
                $data['published_at']
            )
        ) {
            $data['published_at'] =
                now();
        }

        DB::transaction(
            fn () =>
                Announcement::create(
                    $data
                )
        );

        return redirect()
            ->route(
                'admin.announcements.index'
            )
            ->with(
                'success',
                'Pengumuman berhasil ditambahkan.'
            );
    }

    public function edit(
        Announcement $announcement
    ): View {
        $announcement->load([
            'unit',
            'coverMedia',
            'attachmentMedia',
        ]);

        return view(
            'admin.announcements.edit',
            [
                'announcement' =>
                    $announcement,

                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function update(
        UpdateAnnouncementRequest $request,
        Announcement $announcement
    ): RedirectResponse {
        $data =
            $request->validated();

        $data['slug'] =
            $this->generateSlug(
                $data['title'],
                $announcement->id
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
            $data['status'] ===
                'published'
            && empty(
                $data['published_at']
            )
        ) {
            $data['published_at'] =
                $announcement
                    ->published_at
                ?? now();
        }

        DB::transaction(
            fn () =>
                $announcement->update(
                    $data
                )
        );

        return redirect()
            ->route(
                'admin.announcements.index'
            )
            ->with(
                'success',
                'Pengumuman berhasil diperbarui.'
            );
    }

    public function destroy(
        Announcement $announcement
    ): RedirectResponse {
        $announcement->delete();

        return redirect()
            ->route(
                'admin.announcements.index'
            )
            ->with(
                'success',
                'Pengumuman berhasil dihapus.'
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

        $type =
            (string) $request->input(
                'type',
                'all'
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
            if ($type === 'image') {
                $query->where(
                    'type',
                    'image'
                );
            }

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

                                'type' =>
                                    $item->type,

                                'mime_type' =>
                                    $item->mime_type,

                                'extension' =>
                                    $item->extension,

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
            ->forCurrentRoute($actor, 'announcements')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function generateSlug(
        string $title,
        ?int $ignoreId = null
    ): string {
        $base =
            Str::slug($title);

        if ($base === '') {
            $base =
                'pengumuman';
        }

        $slug =
            $base;

        $counter =
            2;

        while (
            Announcement::withTrashed()
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
        $excerpt =
            trim(
                (string) $excerpt
            );

        if ($excerpt !== '') {
            return $excerpt;
        }

        $plainText =
            html_entity_decode(
                strip_tags($content),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

        $plainText =
            preg_replace(
                '/\s+/u',
                ' ',
                $plainText
            );

        return Str::limit(
            trim(
                (string) $plainText
            ),
            300,
            '…'
        );
    }
}