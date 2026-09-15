@extends('layouts.admin')
@section('title', $regulation->exists ? 'Ubah Regulasi' : 'Tambah Regulasi')

@section('content')
<x-ui.page-header
    :title="$regulation->exists ? 'Ubah Regulasi' : 'Tambah Regulasi'"
    description="Lengkapi identitas, dokumen, dan pengaturan publikasi."
/>

@if($errors->any())
    <div class="ui-alert" role="alert">
        <strong>Data belum tersimpan.</strong>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        <p>Pilih kembali file upload setelah memperbaiki formulir.</p>
    </div>
@endif

@if($types->isEmpty())
    <p>Tambahkan <a href="{{ route('admin.regulation-types.index') }}">jenis regulasi</a> terlebih dahulu.</p>
@endif

<form method="POST" enctype="multipart/form-data"
    action="{{ $regulation->exists
        ? route('admin.regulations.update', $regulation)
        : route('admin.regulations.store') }}">
    @csrf
    @if($regulation->exists) @method('PUT') @endif

    <section class="ui-card ui-card-body">
        <h2>Identitas regulasi</h2>

        <div class="reg-grid">
            <label>
                Jenis regulasi *
                <select class="ui-control" name="regulation_type_id" required>
                    <option value="">Pilih jenis</option>
                    @foreach($types as $type)
                        @if($type->is_active || $regulation->regulation_type_id == $type->id)
                            <option value="{{ $type->id }}"
                                @selected(old('regulation_type_id', $regulation->regulation_type_id) == $type->id)>
                                {{ $type->name }}{{ $type->is_active ? '' : ' (nonaktif)' }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </label>

            <label>
                Unit penanggung jawab
                <select class="ui-control" name="unit_id">
                    <option value="">Tidak ditentukan</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}"
                            @selected(old('unit_id', $regulation->unit_id) == $unit->id)>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            @foreach([
                'title' => ['Judul / tentang *', 500],
                'number' => ['Nomor regulasi *', 150],
                'issuing_authority' => ['Instansi / pejabat penerbit *', 255],
                'subject' => ['Bidang / topik', 255],
            ] as $field => $definition)
                <label>
                    {{ $definition[0] }}
                    <input class="ui-control" name="{{ $field }}"
                        maxlength="{{ $definition[1] }}"
                        value="{{ old($field, $regulation->$field) }}"
                        @required($field !== 'subject')>
                </label>
            @endforeach

            <label>
                Tahun *
                <input class="ui-control" type="number" name="year"
                    value="{{ old('year', $regulation->year) }}"
                    min="1900" max="2200" required>
            </label>

            @foreach([
                'issued_at' => 'Tanggal ditetapkan',
                'effective_at' => 'Tanggal mulai berlaku',
            ] as $field => $label)
                <label>
                    {{ $label }}
                    <input class="ui-control" type="date" name="{{ $field }}"
                        value="{{ old($field, $regulation->$field?->format('Y-m-d')) }}">
                </label>
            @endforeach

            <label>
                Status keberlakuan
                <select class="ui-control" name="legal_status">
                    @foreach(\App\Models\Regulation::LEGAL as $key => $label)
                        <option value="{{ $key }}"
                            @selected(old('legal_status', $regulation->legal_status) === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Dasar / catatan keberlakuan
                <textarea class="ui-control" name="legal_status_note" rows="3">{{ old('legal_status_note', $regulation->legal_status_note) }}</textarea>
            </label>

            <label>
                Sumber resmi
                <input class="ui-control" type="url" name="source_url"
                    value="{{ old('source_url', $regulation->source_url) }}"
                    placeholder="https://...">
            </label>
        </div>

        @foreach([
            'summary' => 'Ringkasan',
            'description' => 'Keterangan tambahan',
        ] as $field => $label)
            <label class="reg-space">
                {{ $label }}
                <textarea class="ui-control" name="{{ $field }}" rows="4">{{ old($field, $regulation->$field) }}</textarea>
            </label>
        @endforeach
    </section>

    @include('admin.regulations._naskah')

    <section class="ui-card ui-card-body reg-space">
        <h2>Publikasi</h2>
        <div class="reg-grid">
            <label>
                Status publikasi
                <select class="ui-control" name="publication_status">
                    @foreach(\App\Models\Regulation::PUBLICATION as $key => $label)
                        <option value="{{ $key }}"
                            @selected(old('publication_status', $regulation->publication_status) === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Waktu publikasi
                <input class="ui-control" type="datetime-local" name="published_at"
                    value="{{ old('published_at', $regulation->published_at?->format('Y-m-d\TH:i')) }}">
                <small>Kosongkan untuk terbit saat disimpan. Waktu mengikuti zona aplikasi.</small>
            </label>

            <label>
                Judul SEO
                <input class="ui-control" name="meta_title" maxlength="255"
                    value="{{ old('meta_title', $regulation->meta_title) }}">
            </label>

            <label>
                Deskripsi SEO
                <textarea class="ui-control" name="meta_description" rows="3">{{ old('meta_description', $regulation->meta_description) }}</textarea>
            </label>
        </div>
    </section>

    <div class="reg-actions reg-space">
        <button class="ui-btn ui-btn-primary">Simpan Regulasi</button>
        <a class="ui-btn ui-btn-secondary" href="{{ route('admin.regulations.index') }}">
            Kembali
        </a>
    </div>
</form>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/regulations.css') }}">
@endpush
