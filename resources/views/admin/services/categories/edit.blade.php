@extends('layouts.admin')

@section('title', 'Ubah Kategori Layanan')

@section('content')
    <x-ui.page-header
        title="Ubah Kategori Layanan"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
        action="{{ route('admin.service-categories.update', $category) }}"
    >
        @method('PUT')

        @include('admin.services.categories._form')
    </form>
@endsection

