@extends('layouts.admin')

@section('title', 'Tambah Agenda')

@section('content')
    <x-ui.page-header
        title="Tambah Agenda"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.agendas.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form
        method="POST"
        action="{{ route('admin.agendas.store') }}"
    >
        @include('admin.agendas._form')
    </form>
@endsection