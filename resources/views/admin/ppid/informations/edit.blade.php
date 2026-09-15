@extends('layouts.admin')

@section('title', 'Ubah Informasi Publik')

@section('content')
    <x-ui.page-header
        title="Ubah Informasi Publik"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
        action="{{ route(
            'admin.ppid-informations.update',
            $information
        ) }}"
    >
        @method('PUT')

        @include('admin.ppid.informations._form')
    </form>
@endsection