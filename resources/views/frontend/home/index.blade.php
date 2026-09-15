@extends('frontend.layouts.app')

@section('title', 'Kementerian Agama Kabupaten Tuban')

@section('content')

    {{-- HERO SLIDER --}}
    @include('frontend.home._hero-slider')

    {{-- KONTEN HOME: UTAMA + SIDEBAR --}}
    <section class="section-wrapper home-content-section">
        <div class="container home-content-layout">

            {{-- =========================================================
                 KONTEN UTAMA
                 ========================================================= --}}
            <div class="home-content-main">

                {{-- AKSES CEPAT --}}
                @include('frontend.home._quick-links', ['embedded' => true])

                {{-- LAYANAN --}}
                <div class="portal-block-card">
                    <div class="section-heading">
                        <div>
                            <span class="section-kicker">
                                Pelayanan Publik
                            </span>

                            <h2>
                                Layanan Masyarakat
                            </h2>
                        </div>

                        <a
                            href="{{ route('services.index') }}"
                            class="section-link"
                        >
                            Semua Layanan →
                        </a>
                    </div>

                    <div class="service-public-grid">
                        @forelse ($services as $service)
                            <article class="service-public-card">
                                <div class="service-public-icon">
                                    {{ strtoupper(mb_substr($service->title, 0, 1)) }}
                                </div>

                                <span class="service-public-category">
                                    {{ $service->category?->name ?? 'Layanan' }}
                                </span>

                                <h3>
                                    <a href="{{ route('services.show', $service->slug) }}">
                                        {{ $service->title }}
                                    </a>
                                </h3>

                                <p>
                                    {{ Str::limit(
                                        $service->excerpt
                                            ?: strip_tags($service->description ?? ''),
                                        100
                                    ) }}
                                </p>

                                <a
                                    href="{{ route('services.show', $service->slug) }}"
                                    class="public-action-link"
                                >
                                    Detail Layanan →
                                </a>
                            </article>
                        @empty
                            <div class="empty-public-state">
                                Belum ada layanan.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- BERITA --}}
                <div class="portal-block-card">
                    <div class="section-heading">
                        <div>
                            <span class="section-kicker">
                                Informasi Terkini
                            </span>

                            <h2>
                                Berita & Warta Satker
                            </h2>
                        </div>

                        <a
                            href="{{ route('news.index') }}"
                            class="section-link"
                        >
                            Semua Berita →
                        </a>
                    </div>

                    <div class="kemenag-news-tabs">
                        <button
                            type="button"
                            class="kemenag-tab-btn active"
                            onclick="filterKemenagNews('all', this)"
                        >
                            Terbaru
                        </button>

                        @if (isset($newsUnits))
                            @foreach ($newsUnits as $unit)
                                <button
                                    type="button"
                                    class="kemenag-tab-btn"
                                    onclick="filterKemenagNews('unit-{{ $unit->id }}', this)"
                                >
                                    {{ $unit->short_name ?: $unit->name }}
                                </button>
                            @endforeach
                        @endif
                    </div>

                    <div class="kemenag-news-grid">
                        @forelse ($latestNews as $news)
                            @php
                                $unitClass =
                                    $news->unit_id
                                        ? 'unit-' . $news->unit_id
                                        : '';

                                $coverUrl =
                                    $news->coverMedia
                                        ? Storage::disk(
                                            $news->coverMedia->disk
                                        )->url(
                                            $news->coverMedia->path
                                        )
                                        : null;
                            @endphp

                            <article
                                class="kemenag-news-card news-filter-card {{ $unitClass }}"
                            >
                                <div class="kemenag-news-thumb">
                                    <a href="{{ route('news.show', $news->slug) }}">
                                        @if ($coverUrl)
                                            <img
                                                src="{{ $coverUrl }}"
                                                alt="{{ $news->coverMedia->alt_text ?? $news->title }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="kemenag-news-fallback">
                                                <span>
                                                    Berita
                                                </span>
                                            </div>
                                        @endif
                                    </a>

                                    <span class="kemenag-news-badge">
                                        {{ $news->unit?->short_name
                                            ?: ($news->unit?->name ?? 'Kemenag')
                                        }}
                                    </span>
                                </div>

                                <div class="kemenag-news-content">
                                    <div class="kemenag-news-meta">
                                        {{-- news-category-link:kemenag-news-cat --}}
@if ($news->category?->is_active)
    <a class="kemenag-news-cat news-category-link" href="{{ route('news.index', ['category' => $news->category->slug]) }}">
        {{ $news->category->name }}
    </a>
@else
    <span class="kemenag-news-cat">{{ $news->category?->name ?? 'Berita' }}</span>
@endif

                                        <span class="kemenag-news-date">
                                            {{ optional($news->published_at)->translatedFormat('d M Y') }}
                                        </span>
                                    </div>

                                    <h3>
                                        <a href="{{ route('news.show', $news->slug) }}">
                                            {{ $news->title }}
                                        </a>
                                    </h3>

                                    <p>
                                        {{ Str::limit(
                                            $news->excerpt
                                                ?: strip_tags($news->content ?? ''),
                                            95
                                        ) }}
                                    </p>

                                    <a
                                        href="{{ route('news.show', $news->slug) }}"
                                        class="kemenag-news-link"
                                    >
                                        Baca Selengkapnya →
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="empty-public-state">
                                Belum ada berita.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- PPID --}}
                <div class="portal-block-card">
                    <div class="section-heading">
                        <div>
                            <span class="section-kicker">
                                Keterbukaan Informasi
                            </span>

                            <h2>
                                Informasi PPID
                            </h2>
                        </div>

                        <a
                            href="{{ route('ppid.index') }}"
                            class="section-link"
                        >
                            Semua Informasi →
                        </a>
                    </div>

                    <div class="ppid-public-grid">
                        @forelse ($ppidInformations as $information)
                            <article class="ppid-public-card">
                                <span>
                                    {{ $information->classification_label }}
                                </span>

                                <h3>
                                    <a href="{{ route('ppid.show', $information->slug) }}">
                                        {{ $information->title }}
                                    </a>
                                </h3>

                                <p>
                                    {{ Str::limit(
                                        $information->excerpt
                                            ?: strip_tags($information->description ?? ''),
                                        100
                                    ) }}
                                </p>

                                <a
                                    href="{{ route('ppid.show', $information->slug) }}"
                                    class="public-action-link"
                                >
                                    Lihat Dokumen →
                                </a>
                            </article>
                        @empty
                            <div class="empty-public-state">
                                Belum ada informasi PPID.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- =========================================================
                 SIDEBAR
                 ========================================================= --}}
            <aside
                class="home-content-sidebar"
                aria-label="Pengumuman dan agenda"
            >
                <div class="home-content-sidebar-sticky">

                    {{-- PENGUMUMAN --}}
                    <div class="portal-block-card">
                        <div class="section-heading compact">
                            <div>
                                <span class="section-kicker">
                                    Warta Resmi
                                </span>

                                <h2>
                                    Pengumuman
                                </h2>
                            </div>

                            <a
                                href="{{ route('announcements.index') }}"
                                class="section-link"
                            >
                                Semua Pengumuman →
                            </a>
                        </div>

                        <div class="announcement-list">
                            @forelse ($announcements as $announcement)
                                <article class="announcement-item">
                                    <div class="announcement-date">
                                        <strong>
                                            {{ optional($announcement->published_at)->format('d') }}
                                        </strong>

                                        <span>
                                            {{ optional($announcement->published_at)->translatedFormat('M') }}
                                        </span>
                                    </div>

                                    <div>
                                        <h3>
                                            <a href="{{ route('announcements.show', $announcement->slug) }}">
                                                {{ $announcement->title }}
                                            </a>
                                        </h3>

                                        <p>
                                            {{ Str::limit(
                                                $announcement->excerpt
                                                    ?: strip_tags($announcement->content ?? ''),
                                                80
                                            ) }}
                                        </p>
                                    </div>
                                </article>
                            @empty
                                <div class="empty-public-state">
                                    Belum ada pengumuman.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- AGENDA --}}
                    <div class="portal-block-card">
                        <div class="section-heading compact">
                            <div>
                                <span class="section-kicker">
                                    Jadwal
                                </span>

                                <h2>
                                    Agenda Terdekat
                                </h2>
                            </div>

                            <a
                                href="{{ route('agendas.index') }}"
                                class="section-link"
                            >
                                Semua Agenda →
                            </a>
                        </div>

                        <div class="agenda-public-list">
                            @forelse ($agendas as $agenda)
                                <article class="agenda-public-card">
                                    <div class="agenda-public-date">
                                        <strong>
                                            {{ $agenda->start_at->format('d') }}
                                        </strong>

                                        <span>
                                            {{ $agenda->start_at->translatedFormat('M') }}
                                        </span>
                                    </div>

                                    <div>
                                        <span class="agenda-public-time">
                                            {{ $agenda->start_at->format('H:i') }} WIB
                                        </span>

                                        <h3>
                                            <a href="{{ route('agendas.show', $agenda->slug) }}">
                                                {{ $agenda->title }}
                                            </a>
                                        </h3>

                                        @if ($agenda->location)
                                            <p>
                                                {{ $agenda->location }}
                                            </p>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="empty-public-state">
                                    Belum ada agenda mendatang.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>
    {{-- LINK TERKAIT --}}
    <x-frontend.related-links />
    {{-- SCRIPT TAB FILTER --}}
    <script>
        function filterKemenagNews(targetClass, btn) {
            document.querySelectorAll('.kemenag-tab-btn').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.news-filter-card').forEach(card => {
                card.style.display = (targetClass === 'all' || card.classList.contains(targetClass)) ? 'flex' : 'none';
            });
        }
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/frontend/components/hero-slider.js') }}"></script>
    <script src="{{ asset('js/frontend/components/related-links.js') }}"></script>
@endpush
