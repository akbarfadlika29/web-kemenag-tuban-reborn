@extends('layouts.admin')

@section('title', 'Banner Beranda')

@section('content')
    <div class="admin-page hero-slides-page">
        <x-ui.page-header
            title="Banner Beranda"
            description="Kelola banner tambahan setelah banner utama beranda."
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('admin.hero-slides.create')"
                    variant="primary"
                >
                    Tambah Slide
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <section class="hero-slides-card">
            <header class="hero-slides-card-header">
                <div>
                    <span class="hero-slides-kicker">
                        Slider Homepage
                    </span>

                    <h2>
                        Slide Tambahan
                    </h2>

                    <p>
                        Slide utama tidak ditampilkan di daftar ini karena merupakan hero permanen website.
                    </p>
                </div>

                <strong>
                    {{ number_format($heroSlides->total()) }}
                    slide
                </strong>
            </header>

            @if ($heroSlides->isEmpty())
                <div class="hero-slides-empty">
                    <x-ui.empty-state
                        title="Belum Ada Slide Tambahan"
                        description="Tambahkan banner atau gambar pertama untuk hero slider."
                    >
                        <x-slot:action>
                            <x-ui.button
                                :href="route('admin.hero-slides.create')"
                                variant="primary"
                            >
                                Tambah Slide
                            </x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @else
                <div class="hero-slides-table-wrap">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th class="hero-slide-image-column">
                                    Gambar
                                </th>

                                <th>
                                    Konten
                                </th>

                                <th>
                                    Tombol / Tujuan
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="hero-slide-order-column">
                                    Urutan
                                </th>

                                <th class="hero-slide-action-column">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($heroSlides as $heroSlide)
                                @php
                                    $imageUrl = $heroSlide->media
                                        ? Storage::disk(
                                            $heroSlide->media->disk
                                        )->url(
                                            $heroSlide->media->path
                                        )
                                        : null;
                                @endphp

                                <tr>
                                    <td>
                                        <div class="hero-slide-thumb">
                                            @if ($imageUrl)
                                                <img
                                                    src="{{ $imageUrl }}"
                                                    alt="{{ $heroSlide->media->alt_text ?: ($heroSlide->title ?: 'Hero slide') }}"
                                                >
                                            @else
                                                <span>
                                                    IMG
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="hero-slide-content-summary">
                                            <strong>
                                                {{ $heroSlide->title ?: 'Gambar saja' }}
                                            </strong>

                                            @if ($heroSlide->description)
                                                <span>
                                                    {{ Str::limit(
                                                        $heroSlide->description,
                                                        80
                                                    ) }}
                                                </span>
                                            @else
                                                <span>
                                                    Tanpa deskripsi
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="hero-slide-target">
                                            @if (
                                                $heroSlide->button_label
                                                && $heroSlide->url
                                            )
                                                <strong>
                                                    {{ $heroSlide->button_label }}
                                                </strong>

                                                <span>
                                                    {{ $heroSlide->url }}
                                                </span>
                                            @else
                                                <span>
                                                    Tanpa tombol
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        @if ($heroSlide->is_active)
                                            <x-ui.badge variant="success">
                                                Aktif
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="danger">
                                                Nonaktif
                                            </x-ui.badge>
                                        @endif
                                    </td>

                                    <td class="hero-slide-order">
                                        {{ $heroSlide->sort_order }}
                                    </td>

                                    <td>
                                        <div class="table-actions">
                                            <x-ui.button
                                                :href="route(
                                                    'admin.hero-slides.edit',
                                                    $heroSlide
                                                )"
                                                size="sm"
                                            >
                                                Ubah
                                            </x-ui.button>

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'admin.hero-slides.destroy',
                                                    $heroSlide
                                                ) }}"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <x-ui.button
                                                    type="submit"
                                                    variant="danger"
                                                    size="sm"
                                                    data-confirm="Yakin ingin menghapus slide hero ini?"
                                                >
                                                    Hapus
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>

                @if ($heroSlides->hasPages())
                    <div class="hero-slides-pagination">
                        {{ $heroSlides->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/hero-slides.css') }}"
    >
@endpush
