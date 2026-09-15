<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(
        Request $request
    ): View {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $category = trim((string) ($validated['category'] ?? ''));
        $tag = trim((string) ($validated['tag'] ?? ''));

        /*
         * Invalid, inactive, or deleted categories return 404.
         * Deleted tags also return 404 through their model scope.
         */
        $selectedCategory = $category !== ''
            ? \App\Models\NewsCategory::query()
                ->where('slug', $category)
                ->where('is_active', true)
                ->firstOrFail()
            : null;

        $selectedTag = $tag !== ''
            ? \App\Models\NewsTag::query()
                ->where('slug', $tag)
                ->firstOrFail()
            : null;

        $news = News::query()
            ->published()
            ->with([
                'category',
                'unit',
                'coverMedia',
            ])
            ->when(
                $selectedCategory,
                fn ($query) => $query->where(
                    'category_id',
                    $selectedCategory->id
                )
            )
            ->when(
                $selectedTag,
                fn ($query) => $query->whereHas(
                    'tags',
                    fn ($tags) => $tags->where(
                        'news_tags.id',
                        $selectedTag->id
                    )
                )
            )
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('title', 'ilike', '%' . $search . '%')
                            ->orWhere(
                                'excerpt',
                                'ilike',
                                '%' . $search . '%'
                            );
                    });
                }
            )
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $archiveTitle = 'Berita';
        $archiveDescription =
            'Informasi dan kegiatan terbaru Kementerian Agama Kabupaten Tuban.';

        if ($selectedCategory && $selectedTag) {
            $archiveTitle = $selectedCategory->name
                . ' — Tag: ' . $selectedTag->name;

            $archiveDescription =
                'Berita dalam kategori ini yang memiliki tag terpilih.';
        } elseif ($selectedCategory) {
            $archiveTitle = 'Kategori: ' . $selectedCategory->name;
            $archiveDescription =
                'Kumpulan berita dalam kategori ' . $selectedCategory->name . '.';
        } elseif ($selectedTag) {
            $archiveTitle = 'Tag: ' . $selectedTag->name;
            $archiveDescription =
                'Kumpulan berita dengan tag ' . $selectedTag->name . '.';
        }

        return view('frontend.news.index', compact(
            'news',
            'search',
            'category',
            'tag',
            'selectedCategory',
            'selectedTag',
            'archiveTitle',
            'archiveDescription'
        ));
    }

    public function show(
        string $slug
    ): View {
        $news = News::query()
            ->published()
            ->with([
                'category',
                'unit',
                'coverMedia',
                'tags',
            ])
            ->where(
                'slug',
                $slug
            )
            ->firstOrFail();

        $relatedNews = News::query()
            ->published()
            ->with('coverMedia')
            ->whereKeyNot(
                $news->id
            )
            ->when(
                $news->category_id,
                fn ($query) =>
                    $query->where(
                        'category_id',
                        $news->category_id
                    )
            )
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view(
            'frontend.news.show',
            compact(
                'news',
                'relatedNews'
            )
        );
    }
}
