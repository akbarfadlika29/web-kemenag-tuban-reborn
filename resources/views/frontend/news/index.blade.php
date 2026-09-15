@extends('frontend.layouts.app')

@section('title', $archiveTitle)

@section('content')
    <x-frontend.page-header>
    <x-slot:title>{{ $archiveTitle }}</x-slot:title>
    <x-slot:description>{{ $archiveDescription }}</x-slot:description>
</x-frontend.page-header>

    <section class="section">
        <div class="container">
            <form
                method="GET"
                action="{{ route('news.index') }}"
                class="public-search"
            >
                {{-- news-archive-filters: keep the current archive on search --}}
                @if ($category !== '')
                    <input type="hidden" name="category" value="{{ $category }}">
                @endif

                @if ($tag !== '')
                    <input type="hidden" name="tag" value="{{ $tag }}">
                @endif

                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari berita..."
                >

                <button type="submit">
                    Cari
                </button>
            </form>

                        <div class="news-archive-summary">
                <span>
                    {{ number_format($news->total(), 0, ',', '.') }}
                    berita ditemukan
                    @if ($search !== '')
                        untuk “{{ $search }}”
                    @endif
                </span>

                @if ($category !== '' || $tag !== '' || $search !== '')
                    <a href="{{ route('news.index') }}">
                        Lihat semua berita
                    </a>
                @endif
            </div>
            <div class="public-card-grid">
                @forelse ($news as $item)
                    <article class="public-content-card">
                        <a
                            href="{{ route('news.show', $item->slug) }}"
                            class="public-card-cover"
                        >
                            @if ($item->coverMedia)
                                <img
                                    src="{{ Storage::disk(
                                        $item->coverMedia->disk
                                    )->url(
                                        $item->coverMedia->path
                                    ) }}"
                                    alt="{{ $item->coverMedia->alt_text ?? $item->title }}"
                                >
                            @else
                                <div class="public-cover-placeholder">
                                    Berita
                                </div>
                            @endif
                        </a>

                        <div class="public-card-body">
                            {{-- news-category-link:public-card-category --}}
@if ($item->category?->is_active)
    <a class="public-card-category news-category-link" href="{{ route('news.index', ['category' => $item->category->slug]) }}">
        {{ $item->category->name }}
    </a>
@else
    <span class="public-card-category">{{ $item->category?->name ?? 'Berita' }}</span>
@endif

                            <h2>
                                <a href="{{ route('news.show', $item->slug) }}">
                                    {{ $item->title }}
                                </a>
                            </h2>

                            <p>
                                {{ Str::limit(
                                    $item->excerpt
                                        ?: strip_tags($item->content ?? ''),
                                    140
                                ) }}
                            </p>

                            <div class="public-card-meta">
                                <span>
                                    {{ optional($item->published_at)->translatedFormat('d F Y') }}
                                </span>

                                @if ($item->unit)
                                    <span>
                                        {{ $item->unit->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-public-state">
                        Belum ada berita yang sesuai dengan pilihan ini.
                    </div>
                @endforelse
            </div>

            <div class="public-pagination">
                {{ $news->links() }}
            </div>
        </div>
    </section>
@endsection