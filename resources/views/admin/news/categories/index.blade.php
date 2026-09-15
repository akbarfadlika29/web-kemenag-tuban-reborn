@extends('layouts.admin')

@section('title', 'Kategori Berita')

@section('content')
    <x-ui.page-header
        title="Kategori Berita"
        description="Kelompokkan berita agar mudah ditemukan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.news-categories.create')"
                variant="primary"
            >
                Tambah Kategori
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="mb-5">
        <form
            method="GET"
            action="{{ route('admin.news-categories.index') }}"
        >
            <div class="category-filter-grid">
                <x-form.input
                    name="search"
                    label="Pencarian"
                    :value="$search"
                    placeholder="Cari kategori..."
                />

                <x-form.select
                    name="status"
                    label="Status"
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

                <div class="category-filter-actions">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Filter
                    </x-ui.button>

                    <x-ui.button
                        :href="route('admin.news-categories.index')"
                    >
                        Atur Ulang
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card :padding="false">
        @if ($categories->isEmpty())
            <x-ui.empty-state
                title="Belum ada kategori"
                description="Tambahkan kategori pertama untuk mengelompokkan berita."
            />
        @else
            <x-ui.table>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Berita</th>
                        <th>Urutan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>
                                <strong>
                                    {{ $category->name }}
                                </strong>

                                @if ($category->description)
                                    <div class="text-muted category-description">
                                        {{ $category->description }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ $category->slug }}
                            </td>

                            <td>
                                @if ($category->is_active)
                                    <x-ui.badge variant="success">
                                        Aktif
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger">
                                        Nonaktif
                                    </x-ui.badge>
                                @endif
                            </td>

                            <td>
                                {{ $category->news_count }}
                            </td>

                            <td>
                                {{ $category->sort_order }}
                            </td>

                            <td>
                                <div class="table-actions">
                                    <x-ui.button
                                        :href="route(
                                            'admin.news-categories.edit',
                                            $category
                                        )"
                                        size="sm"
                                    >
                                        Ubah
                                    </x-ui.button>

                                    <form
                                        action="{{ route(
                                            'admin.news-categories.destroy',
                                            $category
                                        ) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <x-ui.button
                                            type="submit"
                                            variant="danger"
                                            size="sm"
                                            data-confirm="Hapus kategori ini?"
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
        @endif
    </x-ui.card>

    @if ($categories->hasPages())
        <div class="mt-5">
            {{ $categories->links() }}
        </div>
    @endif
@endsection

@push('styles')
<style>
    .category-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr auto;
        gap: 14px;
        align-items: end;
    }

    .category-filter-grid .ui-form-group {
        margin-bottom: 0;
    }

    .category-filter-actions {
        display: flex;
        gap: 8px;
    }

    .category-description {
        margin-top: 4px;
        max-width: 500px;
        font-size: 12px;
    }

    @media (max-width: 800px) {
        .category-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush