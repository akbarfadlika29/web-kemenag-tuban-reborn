@extends('layouts.admin')

@section('title', 'Tambah Berita')

@section('content')
    <x-ui.page-header
        title="Tambah Berita"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.news.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.news.store') }}"
    >
        @include('admin.news._form')
    </form>
@endsection