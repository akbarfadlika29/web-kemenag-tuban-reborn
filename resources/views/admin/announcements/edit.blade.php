@extends('layouts.admin')

@section('title', 'Ubah Pengumuman')

@section('content')
    <x-ui.page-header
        title="Ubah Pengumuman"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
        action="{{ route('admin.announcements.update', $announcement) }}"
    >
        @method('PUT')

        @include('admin.announcements._form')
    </form>
@endsection