@extends('layouts.admin')

@section('title', 'Tambah Halaman')

@section('content')
    <x-ui.page-header
        title="Tambah Halaman"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.pages.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.pages.store') }}"
    >
        @include('admin.pages._form')
    </form>
@endsection