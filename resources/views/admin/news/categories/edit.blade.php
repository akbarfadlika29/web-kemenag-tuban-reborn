@extends('layouts.admin')

@section('title', 'Ubah Kategori Berita')

@section('content')
    <x-ui.page-header
        title="Ubah Kategori Berita"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.news-categories.index')"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <form
            action="{{ route(
                'admin.news-categories.update',
                $category
            ) }}"
            method="POST"
        >
            @method('PUT')

            @include('admin.news.categories._form')

            <x-ui.divider />

            <div class="d-flex gap-2">
                <x-ui.button
                    type="submit"
                    variant="primary"
                >
                    Simpan Perubahan
                </x-ui.button>

                <x-ui.button
                    :href="route('admin.news-categories.index')"
                >
                    Batal
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection