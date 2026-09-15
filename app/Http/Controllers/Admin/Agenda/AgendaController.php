<?php

namespace App\Http\Controllers\Admin\Agenda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Agenda\StoreAgendaRequest;
use App\Http\Requests\Admin\Agenda\UpdateAgendaRequest;
use App\Models\Agenda;
use App\Models\Media;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AgendaController extends Controller
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

        $status = (string) $request->input(
            'status',
            ''
        );

        $unitId = (string) $request->input(
            'unit_id',
            ''
        );

        $eventState = (string) $request->input(
            'event_state',
            ''
        );

        $featured = (string) $request->input(
            'is_featured',
            ''
        );

        $agendas = Agenda::query()
            ->with([
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
                                    'location',
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
                $featured !== '',
                fn ($query) =>
                    $query->where(
                        'is_featured',
                        $featured === '1'
                    )
            )
            ->when(
                $eventState === 'upcoming',
                fn ($query) =>
                    $query->where(
                        'start_at',
                        '>',
                        now()
                    )
            )
            ->when(
                $eventState === 'ongoing',
                fn ($query) =>
                    $query
                        ->where(
                            'start_at',
                            '<=',
                            now()
                        )
                        ->where(
                            function ($query) {
                                $query
                                    ->whereNull('end_at')
                                    ->orWhere(
                                        'end_at',
                                        '>=',
                                        now()
                                    );
                            }
                        )
            )
            ->when(
                $eventState === 'finished',
                fn ($query) =>
                    $query->where(
                        function ($query) {
                            $query
                                ->where(
                                    'end_at',
                                    '<',
                                    now()
                                )
                                ->orWhere(
                                    function ($query) {
                                        $query
                                            ->whereNull(
                                                'end_at'
                                            )
                                            ->where(
                                                'start_at',
                                                '<',
                                                now()
                                                    ->startOfDay()
                                            );
                                    }
                                );
                        }
                    )
            )
            ->orderByDesc('is_featured')
            ->orderBy('start_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.agendas.index',
            [
                'agendas' => $agendas,
                'units' => $this->activeUnits(),
                'search' => $search,
                'status' => $status,
                'unitId' => $unitId,
                'eventState' => $eventState,
                'featured' => $featured,
            ]
        );
    }

    public function create(): View
    {
        return view(
            'admin.agendas.create',
            [
                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function store(
        StoreAgendaRequest $request
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
            ) ?: $data['title'];

        $data['meta_description'] =
            trim(
                (string) (
                    $data['meta_description']
                    ?? ''
                )
            ) ?: Str::limit(
                $data['excerpt'],
                160,
                ''
            );

        if (
            $data['status'] === 'published'
            && empty(
                $data['published_at']
            )
        ) {
            $data['published_at'] =
                now();
        }

        Agenda::create($data);

        return redirect()
            ->route(
                'admin.agendas.index'
            )
            ->with(
                'success',
                'Agenda berhasil ditambahkan.'
            );
    }

    public function edit(
        Agenda $agenda
    ): View {
        $agenda->load([
            'unit',
            'coverMedia',
        ]);

        return view(
            'admin.agendas.edit',
            [
                'agenda' => $agenda,
                'units' =>
                    $this->activeUnits(),
            ]
        );
    }

    public function update(
        UpdateAgendaRequest $request,
        Agenda $agenda
    ): RedirectResponse {
        $data = $request->validated();

        $data['slug'] =
            $this->generateSlug(
                $data['title'],
                $agenda->id
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
            ) ?: $data['title'];

        $data['meta_description'] =
            trim(
                (string) (
                    $data['meta_description']
                    ?? ''
                )
            ) ?: Str::limit(
                $data['excerpt'],
                160,
                ''
            );

        if (
            $data['status'] === 'published'
            && empty(
                $data['published_at']
            )
        ) {
            $data['published_at'] =
                $agenda->published_at
                ?? now();
        }

        $agenda->update($data);

        return redirect()
            ->route(
                'admin.agendas.index'
            )
            ->with(
                'success',
                'Agenda berhasil diperbarui.'
            );
    }

    public function destroy(
        Agenda $agenda
    ): RedirectResponse {
        $agenda->delete();

        return redirect()
            ->route(
                'admin.agendas.index'
            )
            ->with(
                'success',
                'Agenda berhasil dihapus.'
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

        $media = $query
            ->latest()
            ->paginate(24);

        return response()->json([
            'data' =>
                $media
                    ->getCollection()
                    ->map(
                        fn (Media $item) => [
                            'id' => $item->id,

                            'title' =>
                                $item->title
                                ?: $item->original_name,

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
                        ]
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
        return Unit::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function generateSlug(
        string $title,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'agenda';
        }

        $slug = $base;
        $counter = 2;

        while (
            Agenda::withTrashed()
                ->where('slug', $slug)
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
                $base . '-' . $counter;

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

        $text = html_entity_decode(
            strip_tags($content),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = preg_replace(
            '/\s+/u',
            ' ',
            $text
        );

        return Str::limit(
            trim(
                (string) $text
            ),
            300,
            '…'
        );
    }
}