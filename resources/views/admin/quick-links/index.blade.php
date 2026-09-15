@extends('layouts.admin')

@section('title', 'Akses Cepat')

@section('content')
    <div class="admin-page quick-links-page">
        <x-ui.page-header
            title="Akses Cepat"
            description="Atur pintasan menuju layanan dan informasi."
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('admin.quick-links.create')"
                    variant="primary"
                >
                    Tambah Akses Cepat
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <section
            class="quick-links-filter-panel"
            aria-label="Filter akses cepat"
        >
            <form
                method="GET"
                action="{{ route('admin.quick-links.index') }}"
            >
                <div class="quick-links-filter-row">
                    <div class="quick-links-filter-search">
                        <x-form.input
                            name="search"
                            :value="$search"
                            placeholder="Cari akses cepat..."
                            aria-label="Cari akses cepat"
                            autocomplete="off"
                        />
                    </div>

                    <div class="quick-links-filter-type">
                        <x-form.select
                            name="target_type"
                            aria-label="Filter jenis tujuan"
                        >
                            <option value="">
                                Semua Tujuan
                            </option>

                            <option
                                value="page"
                                @selected($targetType === 'page')
                            >
                                Halaman
                            </option>

                            <option
                                value="news_category"
                                @selected($targetType === 'news_category')
                            >
                                Kategori Berita
                            </option>

                            <option
                                value="route"
                                @selected($targetType === 'route')
                            >
                                Route Internal
                            </option>

                            <option
                                value="url"
                                @selected($targetType === 'url')
                            >
                                URL
                            </option>
                        </x-form.select>
                    </div>

                    <div class="quick-links-filter-status">
                        <x-form.select
                            name="status"
                            aria-label="Filter status"
                        >
                            <option value="">
                                Semua Status
                            </option>

                            <option
                                value="1"
                                @selected($status === '1')
                            >
                                Aktif
                            </option>

                            <option
                                value="0"
                                @selected($status === '0')
                            >
                                Nonaktif
                            </option>
                        </x-form.select>
                    </div>

                    <div class="quick-links-filter-actions">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                        >
                            Filter
                        </x-ui.button>

                        @if ($hasFilter)
                            <x-ui.button
                                :href="route('admin.quick-links.index')"
                                variant="secondary"
                            >
                                Atur Ulang
                            </x-ui.button>
                        @endif
                    </div>
                </div>
            </form>
        </section>

        <section class="quick-links-data-card">
            <header class="quick-links-data-header">
                <div>
                    <span class="quick-links-kicker">
                        Konten Website
                    </span>

                    <h2>
                        Daftar Akses Cepat
                    </h2>

                    <p>
                        Atur gambar, tujuan tautan, status, dan urutan akses cepat.
                    </p>
                </div>

                <span class="quick-links-count">
                    {{ number_format($quickLinks->total()) }}
                    item
                </span>
            </header>

            @if ($quickLinks->isEmpty())
                <div class="quick-links-empty">
                    <x-ui.empty-state
                        :title="$hasFilter
                            ? 'Akses Cepat Tidak Ditemukan'
                            : 'Belum Ada Akses Cepat'"
                        :description="$hasFilter
                            ? 'Tidak ada data yang sesuai dengan filter.'
                            : 'Tambahkan akses cepat pertama untuk website.'"
                    >
                        @unless ($hasFilter)
                            <x-slot:action>
                                <x-ui.button
                                    :href="route('admin.quick-links.create')"
                                    variant="primary"
                                >
                                    Tambah Akses Cepat
                                </x-ui.button>
                            </x-slot:action>
                        @endunless
                    </x-ui.empty-state>
                </div>
            @else
                <div class="quick-links-table-wrap">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th class="quick-links-column-image">
                                    Gambar
                                </th>

                                <th>
                                    Nama
                                </th>

                                <th>
                                    Jenis Tujuan
                                </th>

                                <th>
                                    Tujuan
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="quick-links-column-order">
                                    Urutan
                                </th>

                                <th class="quick-links-column-action">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($quickLinks as $quickLink)
                                @php
                                    $medium = $mediaById->get(
                                        $quickLink->media_id
                                    );

                                    $mediumUrl = $mediaUrls->get(
                                        $quickLink->media_id
                                    );

                                    $page = $pagesById->get(
                                        $quickLink->page_id
                                    );

                                    $category = $newsCategoriesById->get(
                                        $quickLink->news_category_id
                                    );

                                    $targetLabel = match (
                                        $quickLink->target_type
                                    ) {
                                        'page' => 'Halaman',
                                        'news_category' => 'Kategori Berita',
                                        'route' => 'Route',
                                        'url' => 'URL',
                                        default => strtoupper(
                                            $quickLink->target_type
                                        ),
                                    };
                                @endphp

                                <tr>
                                    <td>
                                        <div class="quick-link-thumb">
                                            @if ($medium && $mediumUrl)
                                                <img
                                                    src="{{ $mediumUrl }}"
                                                    alt="{{ $medium->alt_text ?: $quickLink->label }}"
                                                >
                                            @else
                                                <span>
                                                    IMG
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="quick-link-name">
                                            <strong>
                                                {{ $quickLink->label }}
                                            </strong>

                                            @if ($quickLink->open_in_new_tab)
                                                <span>
                                                    Dibuka di tab baru
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <x-ui.badge variant="info">
                                            {{ $targetLabel }}
                                        </x-ui.badge>
                                    </td>

                                    <td>
                                        <div class="quick-link-target">
                                            @switch($quickLink->target_type)
                                                @case('page')
                                                    <strong>
                                                        {{ $page?->title ?? 'Halaman tidak tersedia' }}
                                                    </strong>

                                                    <span>
                                                        Halaman CMS
                                                    </span>
                                                    @break

                                                @case('news_category')
                                                    <strong>
                                                        {{ $category?->name ?? 'Kategori tidak tersedia' }}
                                                    </strong>

                                                    <span>
                                                        Kategori berita
                                                    </span>
                                                    @break

                                                @case('route')
                                                    <strong>
                                                        {{ $quickLink->route_name ?: '-' }}
                                                    </strong>

                                                    <span>
                                                        Named route
                                                    </span>
                                                    @break

                                                @case('url')
                                                    <strong>
                                                        {{ $quickLink->url ?: '-' }}
                                                    </strong>

                                                    <span>
                                                        URL manual
                                                    </span>
                                                    @break
                                            @endswitch
                                        </div>
                                    </td>

                                    <td>
                                        @if ($quickLink->is_active)
                                            <x-ui.badge variant="success">
                                                Aktif
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="danger">
                                                Nonaktif
                                            </x-ui.badge>
                                        @endif
                                    </td>

                                    <td class="quick-links-order">
                                        {{ $quickLink->sort_order }}
                                    </td>

                                    <td>
                                        <div class="table-actions">
                                            <x-ui.button
                                                :href="route(
                                                    'admin.quick-links.edit',
                                                    $quickLink
                                                )"
                                                size="sm"
                                            >
                                                Ubah
                                            </x-ui.button>

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'admin.quick-links.destroy',
                                                    $quickLink
                                                ) }}"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <x-ui.button
                                                    type="submit"
                                                    variant="danger"
                                                    size="sm"
                                                    data-confirm="Yakin ingin menghapus akses cepat ini?"
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

                @if ($quickLinks->hasPages())
                    <div class="quick-links-pagination">
                        {{ $quickLinks->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/quick-links.css') }}"
    >
@endpush
