@extends('layouts.admin')

@section('title', 'Ubah Halaman')

@section('content')
    <x-ui.page-header
        title="Ubah Halaman"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
        action="{{ route('admin.pages.update', $page) }}"
    >
        @method('PUT')

        @include('admin.pages._form')
    </form>
@endsection