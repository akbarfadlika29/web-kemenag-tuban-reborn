@extends('layouts.admin')

@section('title', 'Tambah Unit Kerja')

@section('content')
    <div class="admin-page units-form-page">
        <x-ui.page-header
            title="Tambah Unit Kerja"
            description="Lengkapi informasi berikut, lalu simpan."
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('admin.units.index')"
                    variant="secondary"
                >
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <form
            action="{{ route('admin.units.store') }}"
            method="POST"
            class="units-editor-form"
        >
            @include('admin.units._form')

            <div class="units-form-actions">
                <div class="units-form-actions-copy">
                    <strong>
                        Tambah Unit Kerja
                    </strong>

                    <span>
                        Pastikan informasi dan posisi unit sudah sesuai sebelum disimpan.
                    </span>
                </div>

                <div class="units-form-actions-buttons">
                    <x-ui.button
                        :href="route('admin.units.index')"
                        variant="secondary"
                    >
                        Batal
                    </x-ui.button>

                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Simpan Unit
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/units-form.css') }}"
    >
@endpush
