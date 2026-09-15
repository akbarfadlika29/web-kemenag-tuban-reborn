<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\News\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\News\Category\UpdateCategoryRequest;
use App\Models\NewsCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsCategoryController extends Controller
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

        $categories = NewsCategory::query()
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
            ->when(
                $status !== '',
                fn ($query) =>
                    $query->where(
                        'is_active',
                        $status === '1'
                    )
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.news.categories.index',
            compact(
                'categories',
                'search',
                'status'
            )
        );
    }

    public function create(): View
    {
        $nextSortOrder =
            (NewsCategory::max('sort_order') ?? 0) + 1;

        return view(
            'admin.news.categories.create',
            compact('nextSortOrder')
        );
    }

public function store(
    StoreCategoryRequest $request
): RedirectResponse {
    $data = $request->validated();

    $data['slug'] = $this->generateUniqueSlug(
        $data['slug'] ?? null,
        $data['name']
    );

    NewsCategory::create($data);

    return redirect()
        ->route('admin.news-categories.index')
        ->with(
            'success',
            'Kategori berita berhasil ditambahkan.'
        );
}

    public function edit(
        NewsCategory $news_category
    ): View {
        return view(
            'admin.news.categories.edit',
            [
                'category' => $news_category,
            ]
        );
    }

public function update(
    UpdateCategoryRequest $request,
    NewsCategory $news_category
): RedirectResponse {
    $data = $request->validated();

    $data['slug'] = $this->generateUniqueSlug(
        $data['slug'] ?? null,
        $data['name'],
        $news_category->id
    );

    $news_category->update($data);

    return redirect()
        ->route('admin.news-categories.index')
        ->with(
            'success',
            'Kategori berita berhasil diperbarui.'
        );
}

    public function destroy(
        NewsCategory $news_category
    ): RedirectResponse {
        if ($news_category->news()->exists()) {
            return back()->with(
                'error',
                'Kategori masih digunakan oleh berita dan tidak dapat dihapus.'
            );
        }

        $news_category->delete();

        return back()->with(
            'success',
            'Kategori berita berhasil dihapus.'
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
        $base = 'kategori';
    }

    $slug = $base;
    $counter = 2;

    while (
        NewsCategory::withTrashed()
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
}