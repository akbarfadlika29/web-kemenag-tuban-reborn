@extends('layouts.admin')

@section('title', 'Halaman')

@section('content')
    <x-ui.page-header
        title="Halaman"
        description="Kelola halaman informasi dan susunannya."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.pages.create')"
                variant="primary"
            >
                Tambah Halaman
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="pages-index">

        {{-- FILTER --}}
        <div class="pages-filter-card">
            <form
                method="GET"
                action="{{ route('admin.pages.index') }}"
                class="pages-filter-form"
            >
                <div class="pages-filter-search">
                    <label for="search">
                        Cari Halaman
                    </label>

                    <input
                        type="search"
                        name="search"
                        id="search"
                        class="ui-control"
                        value="{{ $search }}"
                        placeholder="Judul, slug, atau ringkasan..."
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
                    <label for="parent_id">
                        Parent
                    </label>

                    <select
                        name="parent_id"
                        id="parent_id"
                        class="ui-control"
                    >
                        <option value="">
                            Semua Struktur
                        </option>

                        <option
                            value="root"
                            @selected($parentId === 'root')
                        >
                            Halaman Utama
                        </option>

                        @foreach ($parentOptions as $option)
                            <option
                                value="{{ $option['id'] }}"
                                @selected(
                                    (string) $parentId ===
                                    (string) $option['id']
                                )
                            >
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="show_in_menu">
                        Menu
                    </label>

                    <select
                        name="show_in_menu"
                        id="show_in_menu"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
                        </option>

                        <option
                            value="1"
                            @selected($showInMenu === '1')
                        >
                            Tampil di Menu
                        </option>

                        <option
                            value="0"
                            @selected($showInMenu === '0')
                        >
                            Tidak di Menu
                        </option>
                    </select>
                </div>

                <div class="pages-filter-actions">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Terapkan
                    </x-ui.button>

                    @if ($filtersActive)
                        <x-ui.button
                            :href="route('admin.pages.index')"
                            variant="secondary"
                        >
                            Atur Ulang
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        {{-- INFORMATION BAR --}}
        <div class="pages-result-bar">
            <div>
                @if ($treeMode)
                    <strong>
                        Tampilan Hierarki
                    </strong>

                    <span>
                        Menampilkan struktur parent dan child halaman.
                    </span>
                @else
                    <strong>
                        {{ $pages->total() }} hasil
                    </strong>

                    <span>
                        Filter aktif — hasil ditampilkan secara flat.
                    </span>
                @endif
            </div>

            @if ($filtersActive)
                <span class="pages-filter-active">
                    Filter Aktif
                </span>
            @endif
        </div>

        {{-- TABLE --}}
        <div class="pages-table-card">
            <div class="pages-table-scroll">
                <table class="pages-table">
                    <thead>
                        <tr>
                            <th>
                                Halaman
                            </th>

                            <th>
                                Unit Kerja
                            </th>

                            <th>
                                Template
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Menu
                            </th>

                            <th>
                                Urutan
                            </th>

                            <th>
                                Publikasi
                            </th>

                            <th class="pages-actions-column">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($pages as $item)
                            @include(
                                'admin.pages._row',
                                [
                                    'item' => $item,
                                    'depth' => 0,
                                    'recursive' => $treeMode,
                                    'treeMode' => $treeMode,
                                ]
                            )
                        @empty
                            <tr>
                                <td
                                    colspan="8"
                                    class="pages-empty"
                                >
                                    <strong>
                                        Belum ada halaman.
                                    </strong>

                                    <span>
                                        Tambahkan halaman baru atau ubah filter pencarian.
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($pages->hasPages())
            <div class="pages-pagination">
                {{ $pages->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/pages-index.css') }}"
    >
@endpush