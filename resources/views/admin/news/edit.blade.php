@extends('layouts.admin')

@section('title', 'Ubah Berita')

@section('content')
    <x-ui.page-header
        title="Ubah Berita"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.news.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.news.update', $news) }}"
    >
        @csrf
        @method('PUT')

        @include('admin.news._form')
    </form>
@endsection