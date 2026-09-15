@extends('layouts.admin')

@section('content')
<div class="ui-card ui-card-body">
    <h1>Tampilan & Interaksi</h1>
    <p>Atur tampilan bersama dan fitur interaksi halaman detail.</p>

    @if ($errors->any())
        <div role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.frontend-settings.update') }}">
        @csrf
        @method('PUT')

        <div class="experience-fields">
            <label>
                <span>Warna aksen</span>
                <select name="palette" class="ui-control">
                    @foreach ([
                        'emerald' => 'Emerald teduh',
                        'teal' => 'Teal lembut',
                        'slate' => 'Biru abu-abu',
                    ] as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('palette', $values['palette']) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Lebar konten</span>
                <select name="width" class="ui-control">
                    @foreach ([1100, 1180, 1280] as $width)
                        <option value="{{ $width }}"
                            @selected((int) old('width', $values['width']) === $width)>
                            {{ $width }} px
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Ukuran teks isi</span>
                <select name="font_size" class="ui-control">
                    @foreach ([15, 16, 17] as $size)
                        <option value="{{ $size }}"
                            @selected((int) old('font_size', $values['font_size']) === $size)>
                            {{ $size }} px
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <h2>Metadata dan interaksi</h2>

        <div class="experience-options">
            @foreach ([
                'metadata' => 'Tampilkan unit dan tanggal pembaruan',
                'views' => 'Aktifkan penghitung baca',
                'likes' => 'Aktifkan tombol dan jumlah suka',
                'sharing' => 'Tampilkan area bagikan',
            ] as $key => $label)
                <label>
                    <input type="hidden" name="{{ $key }}" value="0">
                    <input type="checkbox" name="{{ $key }}" value="1"
                        @checked(old($key, $values[$key]))>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <h2>Pilihan tombol bagikan</h2>

        <div class="experience-options">
            @foreach ([
                'facebook' => 'Facebook',
                'whatsapp' => 'WhatsApp',
                'telegram' => 'Telegram',
                'x' => 'X',
                'copy' => 'Salin tautan',
            ] as $key => $label)
                <label>
                    <input type="hidden" name="{{ $key }}" value="0">
                    <input type="checkbox" name="{{ $key }}" value="1"
                        @checked(old($key, $values[$key]))>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <p>Menonaktifkan penghitung atau suka tidak menghapus data yang sudah tersimpan.</p>

        <div class="experience-actions">
            <button type="submit" class="ui-btn ui-btn-md ui-btn-primary">
                Simpan Pengaturan
            </button>
            <a href="{{ route('home') }}" target="_blank" rel="noopener"
               class="ui-btn ui-btn-md ui-btn-secondary">
                Lihat Website
            </a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.experience-fields{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;margin:24px 0}
.experience-fields label{display:grid;gap:8px;font-size:14px}
.experience-fields select{width:100%;min-height:40px}
.experience-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:16px 0 28px}
.experience-options label{display:flex;align-items:center;gap:10px;padding:14px;border:1px solid #dfe7e2;border-radius:8px}
.experience-options input[type=checkbox]{width:18px;height:18px;accent-color:#247052}
.experience-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}
@media(max-width:700px){.experience-fields,.experience-options{grid-template-columns:1fr}}
</style>
@endpush
