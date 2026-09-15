@extends('layouts.admin')
@section('title', 'Jenis Regulasi')

@section('content')
<x-ui.page-header title="Jenis Regulasi"
    description="Kelompokkan dokumen menurut bentuk regulasinya." />

@if($errors->any())
    <div class="ui-alert" role="alert">
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form class="ui-card ui-card-body reg-grid" method="POST"
    action="{{ $editing->exists
        ? route('admin.regulation-types.update', $editing)
        : route('admin.regulation-types.store') }}">
    @csrf
    @if($editing->exists) @method('PUT') @endif

    <label>
        Nama jenis *
        <input class="ui-control" name="name" required maxlength="150"
            value="{{ old('name', $editing->name) }}">
    </label>

    <label>
        Urutan
        <input class="ui-control" type="number" name="sort_order"
            min="0" max="9999" required
            value="{{ old('sort_order', $editing->sort_order) }}">
    </label>

    <label>
        Deskripsi
        <textarea class="ui-control" name="description" rows="3">{{ old('description', $editing->description) }}</textarea>
    </label>

    <label>
        Status
        <select class="ui-control" name="is_active">
            <option value="1" @selected(old('is_active', $editing->is_active) == 1)>Aktif</option>
            <option value="0" @selected(old('is_active', $editing->is_active) == 0)>Nonaktif</option>
        </select>
    </label>

    <div class="reg-actions">
        <button class="ui-btn ui-btn-primary">
            {{ $editing->exists ? 'Simpan Perubahan' : 'Tambah Jenis' }}
        </button>
        @if($editing->exists)
            <a class="ui-btn ui-btn-secondary"
                href="{{ route('admin.regulation-types.index') }}">Batal</a>
        @endif
    </div>
</form>

<div class="ui-card reg-space">
    <div class="ui-table-responsive">
        <table class="ui-table">
            <thead><tr><th>Jenis</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($types as $type)
                    <tr>
                        <td><strong>{{ $type->name }}</strong></td>
                        <td>{{ $type->sort_order }}</td>
                        <td>{{ $type->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>
                            <div class="reg-actions">
                                <a class="ui-btn ui-btn-secondary ui-btn-sm"
                                    href="{{ route('admin.regulation-types.index', ['edit' => $type->id]) }}">Ubah</a>
                                <form method="POST"
                                    action="{{ route('admin.regulation-types.destroy', $type) }}"
                                    data-confirm="Hapus jenis regulasi ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="ui-btn ui-btn-danger ui-btn-sm"
                                        data-confirm="Hapus jenis regulasi ini?">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Tambahkan jenis regulasi terlebih dahulu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="ui-card-body">{{ $types->links() }}</div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/regulations.css') }}">
@endpush
