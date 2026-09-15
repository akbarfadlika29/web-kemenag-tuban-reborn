@extends('layouts.admin')

@section('title', 'Tambah Pengumuman')

@section('content')
    <x-ui.page-header
        title="Tambah Pengumuman"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.announcements.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.announcements.store') }}"
    >
        @include('admin.announcements._form')
    </form>
@endsection