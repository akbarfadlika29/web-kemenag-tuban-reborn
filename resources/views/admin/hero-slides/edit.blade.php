@extends('layouts.admin')

@section('title', 'Ubah Banner Beranda')

@section('content')
    <div class="admin-page hero-slides-page">
        <x-ui.page-header
            title="Ubah Banner Beranda"
            description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
        />

        <form
            method="POST"
            action="{{ route(
                'admin.hero-slides.update',
                $heroSlide
            ) }}"
        >
            @method('PUT')

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
