@extends('layouts.admin')

@section('title', 'Tambah Tautan Terkait')

@section('content')
    <div class="admin-page related-links-page">
        <x-ui.page-header
            title="Tambah Tautan Terkait"
            description="Lengkapi informasi berikut, lalu simpan."
        />

        <form
            method="POST"
            action="{{ route('admin.related-links.store') }}"
        >
            @include('admin.related-links._form')
        </form>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/related-links.css') }}"
    >
@endpush
