@extends('layouts.admin')

@section('title', 'Ubah Akses Cepat')

@section('content')
    <div class="admin-page quick-links-form-page">
        <x-ui.page-header
            title="Ubah Akses Cepat"
            description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('admin.quick-links.index')"
                    variant="secondary"
                >
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <form
            method="POST"
            action="{{ route(
                'admin.quick-links.update',
                $quickLink
            ) }}"
        >
            @method('PUT')

            @include('admin.quick-links._form')
        </form>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/quick-links.css') }}"
    >
@endpush
