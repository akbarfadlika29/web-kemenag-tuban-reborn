@extends('layouts.admin')

@section('title', 'Kategori Layanan')

@section('content')
    <x-ui.page-header
        title="Kategori Layanan"
        description="Kelompokkan layanan agar mudah ditemukan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.services.index')"
                variant="secondary"
            >
                Daftar Layanan
            </x-ui.button>

            <x-ui.button
                :href="route('admin.service-categories.create')"
                variant="primary"
            >
                Tambah Kategori
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="service-category-index">
        <x-ui.card>
            <form
                method="GET"
                action="{{ route('admin.service-categories.index') }}"
                class="service-category-filter"
            >
                <input
                    type="search"
                    name="search"
                    class="ui-control"
                    value="{{ $search }}"
                    placeholder="Cari kategori layanan..."
                >

                <x-ui.button
                    type="submit"
                    variant="primary"
                >
                    Cari
                </x-ui.button>

                @if ($search !== '')
                    <x-ui.button
                        :href="route('admin.service-categories.index')"
                        variant="secondary"
                    >
                        Atur Ulang
                    </x-ui.button>
                @endif
            </form>
        </x-ui.card>

        <div class="service-category-table-card">
            <div class="service-category-table-scroll">
                <table class="service-category-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Slug</th>
                            <th>Layanan</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>
                                    <strong>
                                        {{ $category->name }}
                                    </strong>

                                    @if ($category->description)
                                        <span>
                                            {{ Str::limit($category->description, 100) }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $category->slug }}
                                </td>

                                <td>
                                    {{ $category->services_count }}
                                </td>

                                <td>
                                    {{ $category->sort_order }}
                                </td>

                                <td>
                                    <span
                                        class="service-category-status {{ $category->is_active ? 'is-active' : 'is-inactive' }}"
                                    >
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="service-category-actions">
                                        <x-ui.button
                                            :href="route(
                                                'admin.service-categories.edit',
                                                $category
                                            )"
                                            variant="secondary"
                                            size="sm"
                                        >
                                            Ubah
                                        </x-ui.button>

                                        @if ($category->services_count === 0)
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'admin.service-categories.destroy',
                                                    $category
                                                ) }}"
                                                data-confirm="Hapus kategori ini?"
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
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="service-category-empty"
                                >
                                    Belum ada kategori layanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($categories->hasPages())
            {{ $categories->links() }}
        @endif
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/service-categories.css') }}"
    >
@endpush

