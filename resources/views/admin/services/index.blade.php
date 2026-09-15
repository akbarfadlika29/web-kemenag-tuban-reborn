@extends('layouts.admin')

@section('title', 'Layanan')

@section('content')
    <x-ui.page-header
        title="Layanan"
        description="Kelola informasi layanan yang tersedia bagi masyarakat."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.service-categories.index')"
                variant="secondary"
            >
                Kategori Layanan
            </x-ui.button>

            <x-ui.button
                :href="route('admin.services.create')"
                variant="primary"
            >
                Tambah Layanan
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @php
        $filtersActive =
            $search !== ''
            || $categoryId !== ''
            || $unitId !== ''
            || $status !== ''
            || $channel !== ''
            || $featured !== '';
    @endphp

    <div class="services-index">
        <div class="services-filter-card">
            <form
                method="GET"
                action="{{ route('admin.services.index') }}"
                class="services-filter"
            >
                <div class="services-search">
                    <label>Cari</label>

                    <input
                        type="search"
                        name="search"
                        class="ui-control"
                        value="{{ $search }}"
                        placeholder="Nama layanan, lokasi..."
                    >
                </div>

                <div>
                    <label>Kategori</label>

                    <select
                        name="category_id"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
                        </option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(
                                    (string) $categoryId ===
                                    (string) $category->id
                                )
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Unit</label>

                    <select
                        name="unit_id"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
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
                    <label>Kanal</label>

                    <select
                        name="service_channel"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
                        </option>

                        <option
                            value="online"
                            @selected($channel === 'online')
                        >
                            Online
                        </option>

                        <option
                            value="offline"
                            @selected($channel === 'offline')
                        >
                            Offline
                        </option>

                        <option
                            value="hybrid"
                            @selected($channel === 'hybrid')
                        >
                            Online & Offline
                        </option>
                    </select>
                </div>

                <div>
                    <label>Status</label>

                    <select
                        name="status"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
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
                    <label>Prioritas</label>

                    <select
                        name="is_featured"
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

                <div class="services-filter-actions">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Terapkan
                    </x-ui.button>

                    @if ($filtersActive)
                        <x-ui.button
                            :href="route('admin.services.index')"
                            variant="secondary"
                        >
                            Atur Ulang
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        <div class="services-result">
            <strong>
                {{ $services->total() }} layanan
            </strong>

            @if ($filtersActive)
                <span>
                    Filter aktif
                </span>
            @endif
        </div>

        <div class="services-table-card">
            <div class="services-table-scroll">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Kanal</th>
                            <th>Biaya</th>
                            <th>Status</th>
                            <th>Unggulan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($services as $item)
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
                                <td class="service-title-cell">
                                    <div class="service-title-row">
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
                                                /layanan/{{ $item->slug }}
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
                                    {{ $item->category?->name ?? '—' }}
                                </td>

                                <td>
                                    {{ $item->unit?->name ?? '—' }}
                                </td>

                                <td>
                                    <span class="service-channel is-{{ $item->service_channel }}">
                                        {{ $item->service_channel_label }}
                                    </span>
                                </td>

                                <td>
                                    @if ($item->is_free)
                                        <span class="service-free">
                                            Gratis
                                        </span>
                                    @else
                                        {{ Str::limit($item->fee_description, 60) ?: 'Berbayar' }}
                                    @endif
                                </td>

                                <td>
                                    <span class="service-publication is-{{ $state }}">
                                        {{ $stateLabel }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->is_featured ? 'Ya' : '—' }}
                                </td>

                                <td>
                                    <div class="service-actions">
                                        <x-ui.button
                                            :href="route(
                                                'admin.services.edit',
                                                $item
                                            )"
                                            variant="secondary"
                                            size="sm"
                                        >
                                            Ubah
                                        </x-ui.button>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.services.destroy',
                                                $item
                                            ) }}"
                                            data-confirm="Hapus layanan ini?"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <x-ui.button
                                                type="submit"
                                                variant="danger"
                                                size="sm"
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
                                    class="services-empty"
                                >
                                    Belum ada layanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($services->hasPages())
            {{ $services->links() }}
        @endif
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/services-index.css') }}"
    >
@endpush