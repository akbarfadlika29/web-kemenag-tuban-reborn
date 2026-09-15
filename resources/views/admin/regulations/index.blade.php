@extends('layouts.admin')
@section('title', 'Regulasi')

@section('content')
<x-ui.page-header
    title="Regulasi"
    description="Kelola naskah regulasi, lampiran, dan publikasinya."
>
    <x-slot:actions>
        <a class="ui-btn ui-btn-primary" href="{{ route('admin.regulations.create') }}">
            Tambah Regulasi
        </a>
    </x-slot:actions>
</x-ui.page-header>

<form class="ui-card ui-card-body reg-filter" method="GET">
    <input class="ui-control" name="search" value="{{ request('search') }}"
        placeholder="Cari judul atau nomor" aria-label="Cari regulasi">

    <select class="ui-control" name="type" aria-label="Jenis regulasi">
        <option value="">Semua jenis</option>
        @foreach($types as $type)
            <option value="{{ $type->id }}" @selected(request('type') == $type->id)>
                {{ $type->name }}
            </option>
        @endforeach
    </select>

    <input class="ui-control" type="number" name="year"
        value="{{ request('year') }}" placeholder="Tahun"
        aria-label="Tahun" min="1900" max="2200">

    <select class="ui-control" name="status" aria-label="Publikasi">
        <option value="">Semua publikasi</option>
        @foreach(\App\Models\Regulation::PUBLICATION as $key => $label)
            <option value="{{ $key }}" @selected(request('status') === $key)>
                {{ $label }}
            </option>
        @endforeach
    </select>

    <button class="ui-btn ui-btn-primary">Terapkan</button>
    <a class="ui-btn ui-btn-secondary" href="{{ route('admin.regulations.index') }}">
        Atur Ulang
    </a>
</form>

<div class="ui-card reg-space">
    <div class="ui-table-responsive">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Regulasi</th>
                    <th>Jenis / tahun</th>
                    <th>Keberlakuan</th>
                    <th>Publikasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->title }}</strong>
                            <div>{{ $item->number }}</div>
                        </td>
                        <td>{{ $item->type->name }}<div>{{ $item->year }}</div></td>
                        <td>{{ $item->legal_label }}</td>
                        <td>
                            {{ \App\Models\Regulation::PUBLICATION[$item->publication_status] }}
                            @if($item->published_at?->isFuture())
                                <div>Terjadwal</div>
                            @endif
                        </td>
                        <td>
                            <div class="reg-actions">
                                <a class="ui-btn ui-btn-secondary ui-btn-sm"
                                    href="{{ route('admin.regulations.edit', $item) }}">Ubah</a>

                                <form method="POST"
                                    action="{{ route('admin.regulations.destroy', $item) }}"
                                    data-confirm="Hapus regulasi ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="ui-btn ui-btn-danger ui-btn-sm"
                                        data-confirm="Hapus regulasi ini?">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada regulasi yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="ui-card-body">{{ $items->links() }}</div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/regulations.css') }}">
@endpush
