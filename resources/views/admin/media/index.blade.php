@extends('layouts.admin')

@section('title', 'Pustaka Media')

@section('content')
    <div class="admin-page media-page">
        <x-ui.page-header
            title="Pustaka Media"
            description="Kelola gambar, dokumen, audio, dan video."
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('admin.media.create')"
                    variant="primary"
                >
                    Unggah Media
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- FILTER --}}
        <section class="media-filter-card">
            <form
                method="GET"
                action="{{ route('admin.media.index') }}"
            >
                <div class="media-filter-grid">
                    <x-form.input
                        name="search"
                        label="Pencarian"
                        :value="$search"
                        placeholder="Cari nama file atau judul..."
                    />

                    <x-form.select
                        name="type"
                        label="Jenis Media"
                    >
                        <option value="">
                            Semua Jenis
                        </option>

                        <option
                            value="image"
                            @selected($type === 'image')
                        >
                            Gambar
                        </option>

                        <option
                            value="document"
                            @selected($type === 'document')
                        >
                            Dokumen
                        </option>

                        <option
                            value="video"
                            @selected($type === 'video')
                        >
                            Video
                        </option>

                        <option
                            value="audio"
                            @selected($type === 'audio')
                        >
                            Audio
                        </option>

                        <option
                            value="other"
                            @selected($type === 'other')
                        >
                            Lainnya
                        </option>
                    </x-form.select>

                    <x-form.select
                        name="unit_id"
                        label="Unit Kerja"
                    >
                        <option value="">
                            Semua Unit
                        </option>

                        @foreach ($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected(
                                    (string) $unitId ===
                                    (string) $unit->id
                                )
                            >
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <div class="media-filter-actions">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                        >
                            Filter
                        </x-ui.button>

                        <x-ui.button
                            :href="route('admin.media.index')"
                            variant="secondary"
                        >
                            Atur Ulang
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </section>

        @if ($media->isEmpty())
            <section class="media-empty-card">
                <x-ui.empty-state
                    title="Belum ada media"
                    description="Upload aset digital pertama untuk mulai membangun pustaka media."
                >
                    <x-slot:action>
                        <x-ui.button
                            :href="route('admin.media.create')"
                            variant="primary"
                        >
                            Unggah Media
                        </x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </section>
        @else
            <section class="media-table-card">
                <header class="media-table-header">
                    <div>
                        <span class="media-table-kicker">
                            Pustaka Media
                        </span>

                        <h2>
                            Daftar Media
                        </h2>

                        <p>
                            {{ number_format($media->total()) }}
                            aset ditemukan.
                        </p>
                    </div>
                </header>

                <div class="media-table-wrap">
                    <table class="media-table">
                        <thead>
                            <tr>
                                <th class="media-column-preview">
                                    Preview
                                </th>

                                <th class="media-column-name">
                                    Nama Media
                                </th>

                                <th>
                                    Jenis
                                </th>

                                <th>
                                    Unit Kerja
                                </th>

                                <th>
                                    Ukuran
                                </th>

                                <th>
                                    Akses
                                </th>

                                <th>
                                    Diunggah
                                </th>

                                <th class="media-column-actions">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($media as $item)
                                @php
                                    $size = (int) $item->size;

                                    $formattedSize = $size >= 1048576
                                        ? number_format(
                                            $size / 1048576,
                                            1
                                        ) . ' MB'
                                        : number_format(
                                            $size / 1024,
                                            1
                                        ) . ' KB';
                                @endphp

                                <tr>
                                    <td>
                                        <a
                                            href="{{ asset('storage/' . $item->path) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="media-table-preview"
                                            title="Buka media"
                                        >
                                            @if ($item->type === 'image')
                                                <img
                                                    src="{{ asset('storage/' . $item->path) }}"
                                                    alt="{{ $item->alt_text ?: $item->title ?: $item->original_name }}"
                                                    loading="lazy"
                                                >
                                            @else
                                                <span>
                                                    {{
                                                        strtoupper(
                                                            $item->extension
                                                                ?: $item->type
                                                                ?: 'FILE'
                                                        )
                                                    }}
                                                </span>
                                            @endif
                                        </a>
                                    </td>

                                    <td>
                                        <div class="media-file-info">
                                            <strong>
                                                {{
                                                    $item->title
                                                        ?: $item->original_name
                                                }}
                                            </strong>

                                            <span>
                                                {{ $item->original_name }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="media-type-badge">
                                            {{ strtoupper($item->type) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="media-unit-name">
                                            {{ $item->unit?->name ?? 'Global' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="media-size">
                                            {{ $formattedSize }}
                                        </span>
                                    </td>

                                    <td>
                                        @if ($item->is_public)
                                            <span class="media-status media-status-public">
                                                Publik
                                            </span>
                                        @else
                                            <span class="media-status media-status-private">
                                                Privat
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="media-date">
                                            <strong>
                                                {{
                                                    optional(
                                                        $item->created_at
                                                    )->translatedFormat(
                                                        'd M Y'
                                                    ) ?: '-'
                                                }}
                                            </strong>

                                            @if ($item->created_at)
                                                <span>
                                                    {{ $item->created_at->format('H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="media-row-actions">
                                            <x-ui.button
                                                :href="asset('storage/' . $item->path)"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                size="sm"
                                                variant="secondary"
                                            >
                                                Preview
                                            </x-ui.button>

                                            <x-ui.button
                                                :href="route('admin.media.edit', $item)"
                                                size="sm"
                                                variant="secondary"
                                            >
                                                Ubah
                                            </x-ui.button>

                                            <form
                                                action="{{ route('admin.media.destroy', $item) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <x-ui.button
                                                    type="submit"
                                                    variant="danger"
                                                    size="sm"
                                                    data-confirm="Yakin ingin menghapus media ini?"
                                                >
                                                    Hapus
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <footer class="media-table-footer">
                    <div class="media-result-info">
                        Menampilkan

                        <strong>
                            {{ $media->firstItem() }}
                        </strong>

                        –

                        <strong>
                            {{ $media->lastItem() }}
                        </strong>

                        dari

                        <strong>
                            {{ number_format($media->total()) }}
                        </strong>

                        media
                    </div>

                    @if ($media->hasPages())
                        @php
                            $currentPage = $media->currentPage();
                            $lastPage = $media->lastPage();

                            $startPage = max(
                                1,
                                $currentPage - 2
                            );

                            $endPage = min(
                                $lastPage,
                                $currentPage + 2
                            );
                        @endphp

                        <nav
                            class="media-pagination"
                            aria-label="Navigasi halaman media"
                        >
                            @if ($media->onFirstPage())
                                <span
                                    class="media-page-button is-disabled"
                                    aria-disabled="true"
                                >
                                    ‹
                                </span>
                            @else
                                <a
                                    href="{{ $media->previousPageUrl() }}"
                                    class="media-page-button"
                                    aria-label="Halaman sebelumnya"
                                >
                                    ‹
                                </a>
                            @endif

                            @if ($startPage > 1)
                                <a
                                    href="{{ $media->url(1) }}"
                                    class="media-page-button"
                                >
                                    1
                                </a>

                                @if ($startPage > 2)
                                    <span class="media-page-ellipsis">
                                        …
                                    </span>
                                @endif
                            @endif

                            @for ($page = $startPage; $page <= $endPage; $page++)
                                @if ($page === $currentPage)
                                    <span
                                        class="media-page-button is-active"
                                        aria-current="page"
                                    >
                                        {{ $page }}
                                    </span>
                                @else
                                    <a
                                        href="{{ $media->url($page) }}"
                                        class="media-page-button"
                                    >
                                        {{ $page }}
                                    </a>
                                @endif
                            @endfor

                            @if ($endPage < $lastPage)
                                @if ($endPage < $lastPage - 1)
                                    <span class="media-page-ellipsis">
                                        …
                                    </span>
                                @endif

                                <a
                                    href="{{ $media->url($lastPage) }}"
                                    class="media-page-button"
                                >
                                    {{ $lastPage }}
                                </a>
                            @endif

                            @if ($media->hasMorePages())
                                <a
                                    href="{{ $media->nextPageUrl() }}"
                                    class="media-page-button"
                                    aria-label="Halaman berikutnya"
                                >
                                    ›
                                </a>
                            @else
                                <span
                                    class="media-page-button is-disabled"
                                    aria-disabled="true"
                                >
                                    ›
                                </span>
                            @endif
                        </nav>
                    @endif
                </footer>
            </section>
        @endif
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/media.css') }}"
    >
@endpush
