@extends('frontend.layouts.app')

@section('title', 'Galeri')

@section('content')
    <x-frontend.page-header>
    <x-slot:title>Galeri</x-slot:title>
    <x-slot:description>Dokumentasi kegiatan dan aktivitas
                Kementerian Agama Kabupaten Tuban.</x-slot:description>
</x-frontend.page-header>

    <section class="section">
        <div class="container">
            <div class="gallery-public-grid">
                @forelse ($galleries as $gallery)
                    <a
                        href="{{ route('galleries.show', $gallery->slug) }}"
                        class="gallery-public-card"
                    >
                        <div class="gallery-public-cover">
                            @if ($gallery->coverMedia)
                                <img
                                    src="{{ Storage::disk(
                                        $gallery->coverMedia->disk
                                    )->url(
                                        $gallery->coverMedia->path
                                    ) }}"
                                    alt="{{ $gallery->title }}"
                                >
                            @else
                                <div class="public-cover-placeholder">
                                    Galeri
                                </div>
                            @endif

                            <span>
                                {{ $gallery->items_count }} foto
                            </span>
                        </div>

                        <div class="gallery-public-body">
                            <h2>
                                {{ $gallery->title }}
                            </h2>

                            <p>
                                {{ Str::limit(
                                    $gallery->excerpt
                                        ?: strip_tags($gallery->description ?? ''),
                                    120
                                ) }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="empty-public-state">
                        Belum ada galeri.
                    </div>
                @endforelse
            </div>

            {{ $galleries->links() }}
        </div>
    </section>
@endsection