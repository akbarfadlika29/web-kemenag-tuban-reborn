@extends('layouts.admin')

@section('title', 'Tambah Kategori PPID')

@section('content')
    <x-ui.page-header
        title="Tambah Kategori PPID"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.ppid-categories.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.ppid-categories.store') }}"
    >
        @include('admin.ppid.categories._form')
    </form>
@endsection