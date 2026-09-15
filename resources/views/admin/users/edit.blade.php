@extends('layouts.admin')

@section('title', 'Ubah Pengguna')

@section('content')
    <x-ui.page-header
        title="Ubah Pengguna"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
        action="{{ route(
            'admin.users.update',
            $user
        ) }}"
    >
        @csrf
        @method('PUT')

        @include('admin.users._form')
    </form>
@endsection