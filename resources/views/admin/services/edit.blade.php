@extends('layouts.admin')

@section('title', 'Ubah Layanan')

@section('content')
    <x-ui.page-header
        title="Ubah Layanan"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.services.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.services.update', $service) }}"
    >
        @method('PUT')

        @include('admin.services._form')
    </form>
@endsection