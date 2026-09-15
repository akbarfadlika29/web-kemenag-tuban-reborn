@extends('layouts.admin')

@section('title', 'Ubah Agenda')

@section('content')
    <x-ui.page-header
        title="Ubah Agenda"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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
        action="{{ route('admin.agendas.update', $agenda) }}"
    >
        @method('PUT')

        @include('admin.agendas._form')
    </form>
@endsection