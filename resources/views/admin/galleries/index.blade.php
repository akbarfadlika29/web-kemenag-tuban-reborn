@extends('layouts.admin')

@section('title', 'Galeri')

@section('content')
    <x-ui.page-header
        title="Galeri"
        description="Kelola album foto dan dokumentasi kegiatan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.galleries.create')"
                variant="primary"
            >
                Tambah Galeri
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @php
        $filtersActive =
            $search !== ''
            || $status !== ''
            || $unitId !== ''
            || $featured !== '';
    @endphp

    <div class="galleries-index">
        <div class="galleries-filter-card">
            <form
                method="GET"
                action="{{ route('admin.galleries.index') }}"
                class="galleries-filter-form"
            >
                <div class="galleries-search">
                    <label for="search">
                        Cari
                    </label>

                    <input
                        type="search"
                        name="search"
                        id="search"
                        class="ui-control"
                        value="{{ $search }}"
                        placeholder="Judul, slug, deskripsi..."
                    >
                </div>

                <div>
                    <label for="status">
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="ui-control"
                    >
                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="draft"
                            @selected($status === 'draft')
                        >
                            Draft
                        </option>

                        <option
                            value="published"
                            @selected($status === 'published')
                        >
                            Published
                        </option>

                        <option
                            value="archived"
                            @selected($status === 'archived')
                        >
                            Archived
                        </option>
                    </select>
                </div>

                <div>
                    <label for="unit_id">
                        Unit Kerja
                    </label>

                    <select
                        name="unit_id"
                        id="unit_id"
                        class="ui-control"
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
                    </select>
                </div>

                <div>
                    <label for="is_featured">
                        Prioritas
                    </label>

                    <select
                        name="is_featured"
                        id="is_featured"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
                        </option>

                        <option
                            value="1"
                            @selected($featured === '1')
                        >
                            Unggulan
                        </option>

                        <option
                            value="0"
                            @selected($featured === '0')
                        >
                            Normal
                        </option>
                    </select>
                </div>

                <div class="galleries-filter-actions">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Terapkan
                    </x-ui.button>

                    @if ($filtersActive)
                        <x-ui.button
                            :href="route('admin.galleries.index')"
                            variant="secondary"
                        >
                            Atur Ulang
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        <div class="galleries-result-bar">
            <strong>
                {{ $galleries->total() }} album
            </strong>

            @if ($filtersActive)
                <span>
                    Filter aktif
                </span>
            @endif
        </div>

        <div class="galleries-table-card">
            <div class="galleries-table-scroll">
                <table class="galleries-table">
                    <thead>
                        <tr>
                            <th>Galeri</th>
                            <th>Unit Kerja</th>
                            <th>Foto</th>
                            <th>Status</th>
                            <th>Publikasi</th>
                            <th>Unggulan</th>
                            <th>Views</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($galleries as $item)
                            @php
                                $state =
                                    $item->publication_state;

                                $stateLabel = match ($state) {
                                    'published' => 'Published',
                                    'scheduled' => 'Terjadwal',
                                    'archived' => 'Archived',
                                    default => 'Draft',
                                };
                            @endphp

                            <tr>
                                <td class="gallery-title-cell">
                                    <div class="gallery-title-row">
                                        @if ($item->coverMedia)
                                            <img
                                                src="{{ Storage::disk($item->coverMedia->disk)->url($item->coverMedia->path) }}"
                                                alt="{{ $item->coverMedia->alt_text ?? $item->title }}"
                                            >
                                        @endif

                                        <div>
                                            <strong>
                                                {{ $item->title }}
                                            </strong>

                                            <span>
                                                /galeri/{{ $item->slug }}
                                            </span>

                                            @if ($item->excerpt)
                                                <p>
                                                    {{ Str::limit($item->excerpt, 90) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $item->unit?->name ?? '—' }}
                                </td>

                                <td>
                                    <span class="gallery-photo-count">
                                        {{ $item->items_count }}
                                        foto
                                    </span>
                                </td>

                                <td>
                                    <span class="gallery-status is-{{ $state }}">
                                        {{ $stateLabel }}
                                    </span>
                                </td>

                                <td>
                                    @if ($item->published_at)
                                        {{ $item->published_at->format('d M Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    @if ($item->is_featured)
                                        <span class="gallery-featured">
                                            Unggulan
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    {{ number_format($item->view_count) }}
                                </td>

                                <td>
                                    <div class="gallery-actions">
                                        <x-ui.button
                                            :href="route(
                                                'admin.galleries.edit',
                                                $item
                                            )"
                                            size="sm"
                                            variant="secondary"
                                        >
                                            Ubah
                                        </x-ui.button>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.galleries.destroy',
                                                $item
                                            ) }}"
                                            data-confirm="Hapus galeri ini?"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <x-ui.button
                                                type="submit"
                                                size="sm"
                                                variant="danger"
                                            >
                                                Hapus
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="8"
                                    class="galleries-empty"
                                >
                                    Belum ada galeri.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($galleries->hasPages())
            <div class="galleries-pagination">
                {{ $galleries->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/galleries-index.css') }}"
    >
@endpush