@extends('layouts.admin')

@section('title', 'Ubah Galeri')

@section('content')
    <x-ui.page-header
        title="Ubah Galeri"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
        action="{{ route('admin.galleries.update', $gallery) }}"
    >
        @method('PUT')

        @include('admin.galleries._form')
    </form>
@endsection