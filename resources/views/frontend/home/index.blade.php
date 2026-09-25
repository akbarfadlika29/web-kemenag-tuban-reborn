@extends('frontend.layouts.app')

@section('title', 'Kementerian Agama Kabupaten Tuban')

@section('content')

    {{-- HERO SLIDER --}}
    @include('frontend.home.partials.hero-slider')

    {{-- KONTEN HOME: UTAMA + SIDEBAR --}}
    <section class="section-wrapper home-content-section">
        <div class="container home-content-layout">

            {{-- =========================================================
                 KONTEN UTAMA
                 ========================================================= --}}
            <div class="home-content-main">

                {{-- AKSES CEPAT --}}
                @include('frontend.home.partials.quick-links', ['embedded' => true])

                {{-- LAYANAN --}}
                @include('frontend.home.partials.services-directory')

                {{-- BERITA --}}
                @include('frontend.home.partials.news-grid')

                {{-- PPID --}}
                @include('frontend.home.partials.ppid-directory')
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
                                Lihat Semua
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
                                    Agenda
                                </h2>
                            </div>

                            <a
                                href="{{ route('agendas.index') }}"
                                class="section-link"
                            >
                                Lihat Semua
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
                                        <div class="agenda-public-meta">
                                            <span class="agenda-public-time">
                                                {{ $agenda->start_at->format('H:i') }} WIB
                                            </span>

                                            @php
                                                $agendaState = $agenda->event_state;
                                                $agendaStateLabel = match ($agendaState) {
                                                    'upcoming' => 'Akan Datang',
                                                    'ongoing' => 'Berlangsung',
                                                    default => 'Selesai',
                                                };
                                            @endphp

                                            <span class="agenda-public-status is-{{ $agendaState }}">
                                                {{ $agendaStateLabel }}
                                            </span>
                                        </div>

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
                                    Belum ada agenda.
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
    
@endsection


@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/pages/home/index.css') }}?v={{ filemtime(public_path('css/frontend/pages/home/index.css')) }}"
    >
@endpush

@push('page-styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/pages/home/directory.css') }}?v={{ filemtime(public_path('css/frontend/pages/home/directory.css')) }}"
    >
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/pages/home/news.css') }}?v={{ filemtime(public_path('css/frontend/pages/home/news.css')) }}"
    >
@endpush

@push('scripts')
    <script defer
            src="{{ asset('js/frontend/pages/home/news-types.js') }}?v={{ filemtime(public_path('js/frontend/pages/home/news-types.js')) }}"></script>
    <script src="{{ asset('js/frontend/pages/home/hero-slider.js') }}"></script>
    <script src="{{ asset('js/frontend/pages/home/related-links.js') }}"></script>
@endpush
