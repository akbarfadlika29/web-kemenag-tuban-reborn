@extends('layouts.admin')

@section('title', 'Tambah Galeri')

@section('content')
    <x-ui.page-header
        title="Tambah Galeri"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.galleries.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.galleries.store') }}"
    >
        @include('admin.galleries._form')
    </form>
@endsection