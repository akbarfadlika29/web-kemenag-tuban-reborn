@extends('layouts.admin')

@section('title', 'Tambah Tag Berita')

@section('content')
    <x-ui.page-header
        title="Tambah Tag Berita"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.news-tags.index')"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <form
            action="{{ route('admin.news-tags.store') }}"
            method="POST"
        >
            @include('admin.news.tags._form')

            <x-ui.divider />

            <div class="d-flex gap-2">
                <x-ui.button
                    type="submit"
                    variant="primary"
                >
                    Simpan Tag
                </x-ui.button>

                <x-ui.button
                    :href="route('admin.news-tags.index')"
                >
                    Batal
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection