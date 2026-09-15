@extends('layouts.admin')

@section('title', 'Tambah Pengguna')

@section('content')
    <x-ui.page-header
        title="Tambah Pengguna"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.users.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.users.store') }}"
    >
        @csrf

        @include('admin.users._form')
    </form>
@endsection