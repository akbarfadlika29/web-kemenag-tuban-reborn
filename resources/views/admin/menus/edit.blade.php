@extends('layouts.admin')

@section('title', 'Ubah Menu Navigasi')

@section('content')
    <div class="admin-page menus-form-page">
        <x-ui.page-header
            title="Ubah Menu Navigasi"
            description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
            action="{{ route('admin.menus.update', $menu) }}"
            method="POST"
            class="menus-editor-form"
        >
            @method('PUT')

            @include('admin.menus._form')

            <div class="menus-form-actions">
                <div class="menus-form-actions-copy">
                    <strong>
                        {{ $menu->label }}
                    </strong>

                    <span>
                        Simpan perubahan setelah konfigurasi menu diperiksa.
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
                        Simpan Perubahan
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
