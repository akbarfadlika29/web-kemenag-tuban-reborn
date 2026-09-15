@extends('layouts.admin')

@section('title', 'Tambah Banner Beranda')

@section('content')
    <div class="admin-page hero-slides-page">
        <x-ui.page-header
            title="Tambah Banner Beranda"
            description="Lengkapi informasi berikut, lalu simpan."
        />

        <form
            method="POST"
            action="{{ route('admin.hero-slides.store') }}"
        >
            @include('admin.hero-slides._form')
        </form>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/hero-slides.css') }}"
    >
@endpush
