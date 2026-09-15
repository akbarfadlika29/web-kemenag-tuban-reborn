@extends('layouts.admin')

@section('title', 'Kategori PPID')

@section('content')
    <x-ui.page-header
        title="Kategori PPID"
        description="Kelola pengelompokan informasi publik."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.ppid-informations.index')"
                variant="secondary"
            >
                Informasi Publik
            </x-ui.button>

            <x-ui.button
                :href="route('admin.ppid-categories.create')"
                variant="primary"
            >
                Tambah Kategori
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="ppid-category-index">
        <x-ui.card>
            <form
                method="GET"
                action="{{ route('admin.ppid-categories.index') }}"
                class="ppid-category-filter"
            >
                <input
                    type="search"
                    name="search"
                    class="ui-control"
                    value="{{ $search }}"
                    placeholder="Cari nama atau deskripsi kategori..."
                >

                <x-ui.button
                    type="submit"
                    variant="primary"
                >
                    Cari
                </x-ui.button>

                @if ($search !== '')
                    <x-ui.button
                        :href="route('admin.ppid-categories.index')"
                        variant="secondary"
                    >
                        Atur Ulang
                    </x-ui.button>
                @endif
            </form>
        </x-ui.card>

        <div class="ppid-category-table-card">
            <div class="ppid-category-table-scroll">
                <table class="ppid-category-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Slug</th>
                            <th>Informasi</th>
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
                                        <span class="ppid-category-description">
                                            {{ Str::limit($category->description, 100) }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $category->slug }}
                                </td>

                                <td>
                                    {{ $category->informations_count }}
                                </td>

                                <td>
                                    {{ $category->sort_order }}
                                </td>

                                <td>
                                    <span class="ppid-category-status {{ $category->is_active ? 'is-active' : 'is-inactive' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="ppid-category-row-actions">
                                        <x-ui.button
                                            :href="route(
                                                'admin.ppid-categories.edit',
                                                $category
                                            )"
                                            size="sm"
                                            variant="secondary"
                                        >
                                            Ubah
                                        </x-ui.button>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.ppid-categories.destroy',
                                                $category
                                            ) }}"
                                            data-confirm="Hapus kategori ini?"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <x-ui.button
                                                type="submit"
                                                size="sm"
                                                variant="danger"
                                                :disabled="$category->informations_count > 0"
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
                                    colspan="6"
                                    class="ppid-category-empty"
                                >
                                    Belum ada kategori PPID.
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
        href="{{ asset('css/admin/pages/ppid-categories.css') }}"
    >
@endpush