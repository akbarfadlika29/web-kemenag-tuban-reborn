@extends('frontend.layouts.app')

@section(
    'title',
    $search !== ''
        ? 'Pencarian: ' . $search
        : 'Pencarian'
)

@section(
    'meta_description',
    'Pencarian informasi pada website.'
)

@section('content')
    <section class="public-page-header">
        <div class="container">
            <h1>
                Pencarian
            </h1>

            <p>
                Temukan berita, pengumuman,
                informasi PPID, dan layanan publik.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <form
                action="{{ route('search.index') }}"
                method="GET"
                class="public-search"
                role="search"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari informasi..."
                    aria-label="Cari informasi"
                    autocomplete="off"
                >

                <button type="submit">
                    Cari
                </button>
            </form>

            @if ($search === '')
                <p class="public-muted">
                    Masukkan kata kunci untuk memulai pencarian.
                </p>
            @else
                @php
                    $totalResults =
                        $news->count()
                        + $announcements->count()
                        + $ppidInformations->count()
                        + $services->count();
                @endphp

                @if ($totalResults === 0)
                    <p class="public-muted">
                        Tidak ditemukan hasil untuk
                        “{{ $search }}”.
                    </p>
                @else
                    <p class="public-muted">
                        Ditemukan {{ $totalResults }}
                        hasil untuk “{{ $search }}”.
                    </p>

                    @if ($news->isNotEmpty())
                        <div class="public-detail-section">
                            <h2>
                                Berita
                            </h2>

                            <div class="public-related-grid">
                                @foreach ($news as $item)
                                    <a
                                        href="{{ route('news.show', $item->slug) }}"
                                        class="public-related-card"
                                    >
                                        <strong>
                                            {{ $item->title }}
                                        </strong>

                                        <span>
                                            Berita
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($announcements->isNotEmpty())
                        <div class="public-detail-section">
                            <h2>
                                Pengumuman
                            </h2>

                            <div class="public-related-grid">
                                @foreach ($announcements as $item)
                                    <a
                                        href="{{ route('announcements.show', $item->slug) }}"
                                        class="public-related-card"
                                    >
                                        <strong>
                                            {{ $item->title }}
                                        </strong>

                                        <span>
                                            Pengumuman
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($ppidInformations->isNotEmpty())
                        <div class="public-detail-section">
                            <h2>
                                Informasi PPID
                            </h2>

                            <div class="public-related-grid">
                                @foreach ($ppidInformations as $item)
                                    <a
                                        href="{{ route('ppid.show', $item->slug) }}"
                                        class="public-related-card"
                                    >
                                        <strong>
                                            {{ $item->title }}
                                        </strong>

                                        <span>
                                            Informasi PPID
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($services->isNotEmpty())
                        <div class="public-detail-section">
                            <h2>
                                Layanan
                            </h2>

                            <div class="public-related-grid">
                                @foreach ($services as $item)
                                    <a
                                        href="{{ route('services.show', $item->slug) }}"
                                        class="public-related-card"
                                    >
                                        <strong>
                                            {{ $item->title }}
                                        </strong>

                                        <span>
                                            Layanan Publik
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </section>
@endsection
