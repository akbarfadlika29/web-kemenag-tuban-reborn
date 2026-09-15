@extends('layouts.admin')

@section('title', 'Tambah Menu Navigasi')

@section('content')
    <div class="admin-page menus-form-page">
        <x-ui.page-header
            title="Tambah Menu Navigasi"
            description="Lengkapi informasi berikut, lalu simpan."
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('admin.menus.index')"
                    variant="secondary"
                >
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <form
            action="{{ route('admin.menus.store') }}"
            method="POST"
            class="menus-editor-form"
        >
            @include('admin.menus._form')

            <div class="menus-form-actions">
                <div class="menus-form-actions-copy">
                    <strong>
                        Tambah Menu
                    </strong>

                    <span>
                        Pastikan nama, tujuan, dan posisi menu sudah sesuai.
                    </span>
                </div>

                <div class="menus-form-actions-buttons">
                    <x-ui.button
                        :href="route('admin.menus.index')"
                        variant="secondary"
                    >
                        Batal
                    </x-ui.button>

                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Simpan Menu
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/menus.css') }}"
    >
@endpush
