@extends('layouts.admin')

@section('title', 'Tambah Layanan')

@section('content')
    <x-ui.page-header
        title="Tambah Layanan"
        description="Lengkapi informasi berikut, lalu simpan."
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
        action="{{ route('admin.services.store') }}"
    >
        @include('admin.services._form')
    </form>
@endsection