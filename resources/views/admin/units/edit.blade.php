@extends('layouts.admin')

@section('title', 'Ubah Unit Kerja')

@section('content')
    <div class="admin-page units-form-page">
        <x-ui.page-header
            title="Ubah Unit Kerja"
            description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
            action="{{ route('admin.units.update', $unit) }}"
            method="POST"
            class="units-editor-form"
        >
            @method('PUT')

            @include('admin.units._form')

            <div class="units-form-actions">
                <div class="units-form-actions-copy">
                    <strong>
                        {{ $unit->name }}
                    </strong>

                    <span>
                        Simpan perubahan setelah informasi unit diperiksa.
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
        href="{{ asset('css/admin/pages/units-form.css') }}"
    >
@endpush
