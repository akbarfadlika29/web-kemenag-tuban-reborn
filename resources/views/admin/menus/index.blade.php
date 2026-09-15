@extends('layouts.admin')

@section('title', 'Menu Navigasi')

@section('content')
    <div class="admin-page menus-page">
        <x-ui.page-header
            title="Menu Navigasi"
            description="Atur susunan menu bagian atas dan bawah website."
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('admin.menus.create')"
                    variant="primary"
                >
                    Tambah Menu
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <section
            class="menus-filter-panel"
            aria-label="Filter menu navigasi"
        >
            <form
                method="GET"
                action="{{ route('admin.menus.index') }}"
            >
                <div class="menus-filter-inline">
                    <div class="menus-filter-search">
                        <x-form.input
                            name="search"
                            :value="$search"
                            placeholder="Cari menu..."
                            aria-label="Cari menu navigasi"
                            autocomplete="off"
                        />
                    </div>

                    <div class="menus-filter-status">
                        <x-form.select
                            name="status"
                            aria-label="Filter status menu"
                            onchange="this.form.submit()"
                        >
                            <option
                                value=""
                                @selected($status === '')
                            >
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

                    <div class="menus-filter-actions">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                        >
                            Filter
                        </x-ui.button>

                        @if ($hasFilter)
                            <x-ui.button
                                :href="route('admin.menus.index')"
                                variant="secondary"
                            >
                                Atur Ulang
                            </x-ui.button>
                        @endif
                    </div>
                </div>
            </form>
        </section>

        {{-- =========================================================
             HEADER / NAVBAR
             ========================================================= --}}
        <section
            class="menus-data-card"
            data-tree-dnd
            data-tree-item-label="menu Header / Navbar"
            data-tree-error-message="Menu Header / Navbar gagal dipindahkan."
        >
            <header class="menus-data-header">
                <div>
                    <span class="menus-data-kicker">
                        Header / Navbar
                    </span>

                    <h2>
                        Struktur Menu Header
                    </h2>

                    <p>
                        @if ($hasFilter)
                            Menampilkan Header / Navbar yang sesuai filter.
                        @else
                            Tarik dan letakkan menu untuk mengubah hierarki atau urutannya.
                        @endif
                    </p>
                </div>

                <span class="menus-count">
                    {{ number_format($headerMenuCount) }}
                    item menu
                </span>
            </header>

            @if ($headerMenus->isNotEmpty())
                <div data-tree-dnd-root>
                    <div data-tree-dnd-root-icon>
                        ↑
                    </div>

                    <div>
                        <strong>
                            Pindahkan ke Menu Utama Header
                        </strong>

                        <span>
                            Tarik menu ke area ini untuk menjadikannya menu utama Header / Navbar.
                        </span>
                    </div>
                </div>
            @endif

            @if ($headerMenus->isEmpty())
                <div class="menus-empty-wrap">
                    <x-ui.empty-state
                        :title="$hasFilter
                            ? 'Tidak ada Menu Header'
                            : 'Belum ada Menu Header'"
                        :description="$hasFilter
                            ? 'Tidak ada menu Header / Navbar yang sesuai filter.'
                            : 'Tambahkan menu untuk membangun navigasi Header / Navbar.'"
                    />
                </div>
            @else
                <div class="menus-table-wrap">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th class="menus-column-label">
                                    Nama Menu
                                </th>

                                <th>
                                    Induk
                                </th>

                                <th>
                                    Tipe
                                </th>

                                <th>
                                    Tujuan
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="menus-column-order">
                                    Urutan
                                </th>

                                <th class="menus-column-action">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($headerMenus as $menu)
                                @include('admin.menus._row', [
                                    'menu' => $menu,
                                    'level' => 0,
                                    'treeDndEnabled' => true,
                                ])
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>
            @endif
        </section>

        {{-- =========================================================
             FOOTER
             ========================================================= --}}
        <section
            class="menus-data-card"
            data-tree-dnd
            data-tree-item-label="menu Footer"
            data-tree-error-message="Menu Footer gagal dipindahkan."
        >
            <header class="menus-data-header">
                <div>
                    <span class="menus-data-kicker">
                        Footer
                    </span>

                    <h2>
                        Struktur Menu Footer
                    </h2>

                    <p>
                        @if ($hasFilter)
                            Menampilkan Footer yang sesuai filter.
                        @else
                            Tarik dan letakkan menu untuk mengubah hierarki atau urutannya.
                        @endif
                    </p>
                </div>

                <span class="menus-count">
                    {{ number_format($footerMenuCount) }}
                    item menu
                </span>
            </header>

            @if ($footerMenus->isNotEmpty())
                <div data-tree-dnd-root>
                    <div data-tree-dnd-root-icon>
                        ↑
                    </div>

                    <div>
                        <strong>
                            Pindahkan ke Menu Utama Footer
                        </strong>

                        <span>
                            Tarik menu ke area ini untuk menjadikannya menu utama Footer.
                        </span>
                    </div>
                </div>
            @endif

            @if ($footerMenus->isEmpty())
                <div class="menus-empty-wrap">
                    <x-ui.empty-state
                        :title="$hasFilter
                            ? 'Tidak ada Menu Footer'
                            : 'Belum ada Menu Footer'"
                        :description="$hasFilter
                            ? 'Tidak ada menu Footer yang sesuai filter.'
                            : 'Tambahkan menu untuk membangun navigasi Footer.'"
                    />
                </div>
            @else
                <div class="menus-table-wrap">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th class="menus-column-label">
                                    Nama Menu
                                </th>

                                <th>
                                    Induk
                                </th>

                                <th>
                                    Tipe
                                </th>

                                <th>
                                    Tujuan
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="menus-column-order">
                                    Urutan
                                </th>

                                <th class="menus-column-action">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($footerMenus as $menu)
                                @include('admin.menus._row', [
                                    'menu' => $menu,
                                    'level' => 0,
                                    'treeDndEnabled' => true,
                                ])
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>
            @endif
        </section>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/menus.css') }}"
    >
@endpush
