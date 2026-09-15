@extends('layouts.admin')

@section('title', 'Tag Berita')

@section('content')
    <x-ui.page-header
        title="Tag Berita"
        description="Kelola penanda topik untuk berita."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.news-tags.create')"
                variant="primary"
            >
                Tambah Tag
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="mb-5">
        <form
            method="GET"
            action="{{ route('admin.news-tags.index') }}"
        >
            <div class="tag-filter-grid">
                <x-form.input
                    name="search"
                    label="Pencarian"
                    :value="$search"
                    placeholder="Cari tag..."
                />

                <div class="tag-filter-actions">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Filter
                    </x-ui.button>

                    <x-ui.button
                        :href="route('admin.news-tags.index')"
                    >
                        Atur Ulang
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card :padding="false">
        @if ($tags->isEmpty())
            <x-ui.empty-state
                title="Belum ada tag"
                description="Tambahkan tag untuk menandai topik berita."
            />
        @else
            <x-ui.table>
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Slug</th>
                        <th>Jumlah Berita</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($tags as $tag)
                        <tr>
                            <td>
                                <x-ui.badge variant="info">
                                    {{ $tag->name }}
                                </x-ui.badge>
                            </td>

                            <td>
                                {{ $tag->slug }}
                            </td>

                            <td>
                                {{ $tag->news_count }}
                            </td>

                            <td>
                                <div class="table-actions">
                                    <x-ui.button
                                        :href="route(
                                            'admin.news-tags.edit',
                                            $tag
                                        )"
                                        size="sm"
                                    >
                                        Ubah
                                    </x-ui.button>

                                    <form
                                        action="{{ route(
                                            'admin.news-tags.destroy',
                                            $tag
                                        ) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <x-ui.button
                                            type="submit"
                                            variant="danger"
                                            size="sm"
                                            data-confirm="Hapus tag ini?"
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

    @if ($tags->hasPages())
        <div class="mt-5">
            {{ $tags->links() }}
        </div>
    @endif
@endsection

@push('styles')
<style>
    .tag-filter-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 14px;
        align-items: end;
    }

    .tag-filter-grid .ui-form-group {
        margin-bottom: 0;
    }

    .tag-filter-actions {
        display: flex;
        gap: 8px;
    }

    @media (max-width: 700px) {
        .tag-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush