@extends('layouts.admin')

@section('title', 'Ubah Kategori PPID')

@section('content')
    <x-ui.page-header
        title="Ubah Kategori PPID"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
        action="{{ route('admin.ppid-categories.update', $category) }}"
    >
        @method('PUT')

        @include('admin.ppid.categories._form')
    </form>
@endsection