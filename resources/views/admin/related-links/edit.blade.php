@extends('layouts.admin')

@section('title', 'Ubah Tautan Terkait')

@section('content')
    <div class="admin-page related-links-page">
        <x-ui.page-header
            title="Ubah Tautan Terkait"
            description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
        />

        <form
            method="POST"
            action="{{ route(
                'admin.related-links.update',
                $relatedLink
            ) }}"
        >
            @method('PUT')

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
