<div class="portal-block-card home-news-section">
    <div class="section-heading">
        <div>
            <span class="section-kicker">Informasi Terkini</span>
            <h2>Berita &amp; Warta Satker</h2>
        </div>

        <a href="{{ route('news.index') }}" class="section-link">
            Lihat Semua
        </a>
    </div>

    @php
        $homeNewsTypes = $latestNews
            ->map(fn ($item) => trim((string) ($item->unit?->type ?? '')))
            ->filter(fn ($type) => $type !== '')
            ->unique()
            ->sort()
            ->values();
    @endphp

    @if ($homeNewsTypes->isNotEmpty())
        <div
            class="home-news-tabs"
            data-home-news-types
            role="group"
            aria-label="Filter berita berdasarkan jenis unit"
        >
            <button
                type="button"
                class="home-news-tab is-active"
                data-news-type="all"
                aria-pressed="true"
            >
                Terbaru
            </button>

            @foreach ($homeNewsTypes as $type)
                <button
                    type="button"
                    class="home-news-tab"
                    data-news-type="{{ 'type-'.md5($type) }}"
                    aria-pressed="false"
                >
                    {{ mb_strtoupper(str_replace(['_', '-'], ' ', $type)) }}
                </button>
            @endforeach
        </div>
    @endif

    <div class="home-news-grid" data-home-news-grid>
        @forelse ($latestNews as $news)
            @php
                $newsUnitType = trim((string) ($news->unit?->type ?? ''));
                $unitClass = $newsUnitType !== ''
                    ? 'type-'.md5($newsUnitType)
                    : '';

                $coverUrl = $news->coverMedia
                    ? Storage::disk($news->coverMedia->disk)->url($news->coverMedia->path)
                    : null;
            @endphp

            <article
                class="home-news-item news-filter-card {{ $unitClass }}"
                data-home-news-card
            >
                <div class="home-news-media">
                    <a
                        href="{{ route('news.show', $news->slug) }}"
                        class="home-news-image-link"
                        aria-label="Baca berita: {{ $news->title }}"
                    >
                        @if ($coverUrl)
                            <img
                                src="{{ $coverUrl }}"
                                alt="{{ $news->coverMedia->alt_text ?? $news->title }}"
                                loading="lazy"
                            >
                        @else
                            <span class="home-news-fallback" aria-hidden="true">
                                Berita
                            </span>
                        @endif
                    </a>

                    <span class="home-news-unit">
                        {{ $news->unit?->short_name ?: ($news->unit?->name ?? 'Kemenag') }}
                    </span>
                </div>

                <div class="home-news-body">
                    <div class="home-news-meta">
                        @if ($news->category?->is_active)
                            <a
                                class="home-news-category news-category-link"
                                href="{{ route('news.index', ['category' => $news->category->slug]) }}"
                            >
                                {{ $news->category->name }}
                            </a>
                        @else
                            <span class="home-news-category">
                                {{ $news->category?->name ?? 'Berita' }}
                            </span>
                        @endif

                        <time
                            class="home-news-date"
                            datetime="{{ optional($news->published_at)->toDateString() }}"
                        >
                            {{ optional($news->published_at)->translatedFormat('d M Y') }}
                        </time>
                    </div>

                    <h3 class="home-news-title">
                        <a href="{{ route('news.show', $news->slug) }}">
                            {{ $news->title }}
                        </a>
                    </h3>
                </div>
            </article>
        @empty
            <div class="empty-public-state home-news-empty">
                Belum ada berita.
            </div>
        @endforelse
    </div>
</div>
