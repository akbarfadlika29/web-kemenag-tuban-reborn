@extends('frontend.layouts.app')

@section('title', 'Pengumuman')

@section('content')
    <x-frontend.page-header>
    <x-slot:title>Pengumuman</x-slot:title>
    <x-slot:description>Pengumuman resmi dan informasi penting
                untuk masyarakat.</x-slot:description>
</x-frontend.page-header>

    <section class="section">
        <div class="container public-list-layout">
            <form
                method="GET"
                action="{{ route('announcements.index') }}"
                class="public-search"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari pengumuman..."
                >

                <button type="submit">
                    Cari
                </button>
            </form>

            <div class="public-list">
                @forelse ($announcements as $item)
                    <article class="public-list-item">
                        <div class="public-list-date">
                            <strong>
                                {{ optional($item->published_at)->format('d') }}
                            </strong>

                            <span>
                                {{ optional($item->published_at)->translatedFormat('M Y') }}
                            </span>
                        </div>

                        <div>
                            @if ($item->is_pinned)
                                <span class="public-badge">
                                    Penting
                                </span>
                            @endif

                            <h2>
                                <a href="{{ route('announcements.show', $item->slug) }}">
                                    {{ $item->title }}
                                </a>
                            </h2>

                            <p>
                                {{ Str::limit(
                                    $item->excerpt
                                        ?: strip_tags($item->content ?? ''),
                                    180
                                ) }}
                            </p>

                            @if ($item->unit)
                                <span class="public-muted">
                                    {{ $item->unit->name }}
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="empty-public-state">
                        Belum ada pengumuman.
                    </div>
                @endforelse
            </div>

            {{ $announcements->links() }}
        </div>
    </section>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/pages/announcements.css') }}?v={{ filemtime(public_path('css/frontend/pages/announcements.css')) }}"
    >
@endpush
