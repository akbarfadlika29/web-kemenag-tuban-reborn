<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\News\Tag\StoreTagRequest;
use App\Http\Requests\Admin\News\Tag\UpdateTagRequest;
use App\Models\NewsTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsTagController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $tags = NewsTag::query()
            ->withCount('news')
            ->when(
                $search !== '',
                fn ($query) =>
                    $query->where(
                        'name',
                        'ilike',
                        '%' . $search . '%'
                    )
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.news.tags.index',
            compact(
                'tags',
                'search'
            )
        );
    }

    public function create(): View
    {
        return view(
            'admin.news.tags.create'
        );
    }

    public function store(
        StoreTagRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $data['slug'] = $this->generateUniqueSlug(
            $data['slug'] ?? null,
            $data['name']
        );

        NewsTag::create($data);

        return redirect()
            ->route('admin.news-tags.index')
            ->with(
                'success',
                'Tag berita berhasil ditambahkan.'
            );
    }

    public function edit(
        NewsTag $news_tag
    ): View {
        return view(
            'admin.news.tags.edit',
            [
                'tag' => $news_tag,
            ]
        );
    }

    public function update(
        UpdateTagRequest $request,
        NewsTag $news_tag
    ): RedirectResponse {
        $data = $request->validated();

        $data['slug'] = $this->generateUniqueSlug(
            $data['slug'] ?? null,
            $data['name'],
            $news_tag->id
        );

        $news_tag->update($data);

        return redirect()
            ->route('admin.news-tags.index')
            ->with(
                'success',
                'Tag berita berhasil diperbarui.'
            );
    }

    public function destroy(
        NewsTag $news_tag
    ): RedirectResponse {
        $news_tag->news()->detach();

        $news_tag->delete();

        return redirect()
            ->route('admin.news-tags.index')
            ->with(
                'success',
                'Tag berita berhasil dihapus.'
            );
    }

    private function generateUniqueSlug(
        ?string $requestedSlug,
        string $name,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug(
            $requestedSlug ?: $name
        );

        if ($base === '') {
            $base = 'tag';
        }

        $slug = $base;
        $counter = 2;

        while (
            NewsTag::withTrashed()
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
                $base . '-' . $counter;

            $counter++;
        }

        return $slug;
    }
}