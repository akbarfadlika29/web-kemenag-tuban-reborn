@extends('layouts.admin')

@section('title', 'Tambah Kategori Layanan')

@section('content')
    <x-ui.page-header
        title="Tambah Kategori Layanan"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.service-categories.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.service-categories.store') }}"
    >
        @include('admin.services.categories._form')
    </form>
@endsection

