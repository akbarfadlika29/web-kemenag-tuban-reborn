@extends('layouts.admin')

@section('title', 'Tambah Informasi Publik')

@section('content')
    <x-ui.page-header
        title="Tambah Informasi Publik"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.ppid-informations.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.ppid-informations.store') }}"
    >
        @include('admin.ppid.informations._form')
    </form>
@endsection