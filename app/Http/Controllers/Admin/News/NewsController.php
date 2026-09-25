<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use App\Services\Access\UnitAccessService;
use App\Http\Requests\Admin\News\StoreNewsRequest;
use App\Http\Requests\Admin\News\UpdateNewsRequest;
use App\Models\Media;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $status = (string) $request->input(
            'status',
            ''
        );

        $unitId = (string) $request->input(
            'unit_id',
            ''
        );

        $categoryId = (string) $request->input(
            'category_id',
            ''
        );

        $tagId = (string) $request->input(
            'tag_id',
            ''
        );

        $featured = (string) $request->input(
            'featured',
            ''
        );

        $news = News::query()
            ->with([
                'unit',
                'category',
                'tags',
                'author',
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
                                    'excerpt',
                                    'ilike',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'slug',
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
                        in_array($status, ['submitted', 'rejected'], true)
                            ? 'editorial_state'
                            : 'status',
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
                $categoryId !== '',
                fn ($query) =>
                    $query->where(
                        'category_id',
                        $categoryId
                    )
            )
            ->when(
                $tagId !== '',
                fn ($query) =>
                    $query->whereHas(
                        'tags',
                        fn ($tagQuery) =>
                            $tagQuery->where(
                                'news_tags.id',
                                $tagId
                            )
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
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.news.index',
            [
                'news' => $news,
                'units' => $this->activeUnits(),
                'categories' => $this->activeCategories(),
                'tags' => $this->allTags(),

                'search' => $search,
                'status' => $status,
                'unitId' => $unitId,
                'categoryId' => $categoryId,
                'tagId' => $tagId,
                'featured' => $featured,
            ]
        );
    }

    public function create(): View
    {
        return view(
            'admin.news.create',
            [
                'units' => $this->activeUnits(),
                'categories' => $this->activeCategories(),
                'tags' => $this->allTags(),
            ]
        );
    }

    public function store(
        StoreNewsRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $tagIds = $data['tag_ids'] ?? [];

        unset($data['tag_ids']);

        $data['slug'] = $this->generateSlug(
            $data['title']
        );

        $data['excerpt'] = $this->prepareExcerpt(
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

        $data['author_id'] = auth()->id();

        if (
            $data['status'] === 'published'
            && empty($data['published_at'])
        ) {
            $data['published_at'] = now();
        }

        DB::transaction(
            function () use (
                $data,
                $tagIds
            ) {
                app(\App\Services\Access\NewsWriteAccess::class)
                    ->check('create', null, $data);

                $workflow = app(\App\Services\Access\NewsEditorialService::class);
                $data = $workflow->prepareWrite(request()->user(), null, $data);
                $news = News::create($data);
                $workflow->record(
                    $news,
                    request()->user(),
                    $news->status === 'published' ? 'publish' : 'create'
                );

                $news->tags()->sync(
                    $tagIds
                );
            }
        );

        return redirect()
            ->route('admin.news.index')
            ->with(
                'success',
                'Berita berhasil ditambahkan.'
            );
    }

    public function edit(News $news): View
    {
        abort_unless(
            app(\App\Services\Access\NewsEditorialPolicy::class)
                ->canWrite(request()->user(), $news),
            403,
            'Berita tidak dapat Anda ubah pada status ini.'
        );

        $news->load([
            'tags',
            'coverMedia',
            'category',
            'unit',
        ]);

        return view(
            'admin.news.edit',
            [
                'news' => $news,
                'units' => $this->activeUnits(),
                'categories' => $this->activeCategories(),
                'tags' => $this->allTags(),
            ]
        );
    }

    public function update(
        UpdateNewsRequest $request,
        News $news
    ): RedirectResponse {
        $data = $request->validated();

        $tagIds = $data['tag_ids'] ?? [];

        unset($data['tag_ids']);

        $data['slug'] = $this->generateSlug(
            $data['title'],
            $news->id
        );

        $data['excerpt'] = $this->prepareExcerpt(
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
                $news->published_at
                ?? now();
        }

        DB::transaction(
            function () use (
                $news,
                $data,
                $tagIds
            ) {
                $news = News::query()
                    ->whereKey($news->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                app(\App\Services\Access\NewsWriteAccess::class)
                    ->check('update', $news, $data);

                $workflow = app(\App\Services\Access\NewsEditorialService::class);
                $previousStatus = $news->status;
                $data = $workflow->prepareWrite(request()->user(), $news, $data);
                $news->update($data);
                $workflow->record(
                    $news,
                    request()->user(),
                    $news->status === 'published'
                        ? 'publish'
                        : ($previousStatus === 'published' ? 'unpublish' : 'update')
                );

                $news->tags()->sync(
                    $tagIds
                );
            }
        );

        return redirect()
            ->route('admin.news.index')
            ->with(
                'success',
                'Berita berhasil diperbarui.'
            );
    }

    public function destroy(
        News $news
    ): RedirectResponse {
        DB::transaction(
            function () use ($news) {
                $news = News::query()
                    ->whereKey($news->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                app(\App\Services\Access\NewsWriteAccess::class)
                    ->check('delete', $news, []);

                $news->tags()->detach();

                $news->delete();
            }
        );

        return redirect()
            ->route('admin.news.index')
            ->with(
                'success',
                'Berita berhasil dihapus.'
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

    private function activeUnits()
    {
        $actor = request()->user();
        abort_unless($actor, 401);

        return app(UnitAccessService::class)
            ->forCurrentRoute($actor, 'news')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function activeCategories()
    {
        return NewsCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function allTags()
    {
        return NewsTag::query()
            ->orderBy('name')
            ->get();
    }

    private function generateSlug(
        string $title,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'berita';
        }

        $slug = $base;
        $counter = 2;

        while (
            News::withTrashed()
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