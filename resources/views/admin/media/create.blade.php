@extends('layouts.admin')

@section('title', 'Unggah Media')

@section('content')
    <x-ui.page-header
        title="Unggah Media"
        description="Lengkapi informasi berikut, lalu simpan."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.media.index')"
                variant="secondary"
            >
                Kembali
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="media-form-layout">
        <div>
            <x-ui.card>
                <form
                    action="{{ route('admin.media.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf

                    <x-form.file
                        name="file"
                        label="File Media"
                        required
                        accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.mp3,.wav,.mp4,.webm"
                        help="Maksimal 50 MB. Mendukung gambar, dokumen, audio, dan video."
                    />

                    <x-form.select
                        name="unit_id"
                        label="Unit Kerja"
                        help="Kosongkan jika media tersedia secara global."
                    >
                        <option value="">
                            -- Media Global --
                        </option>

                        @foreach ($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected(old('unit_id') == $unit->id)
                            >
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-form.input
                        name="title"
                        label="Judul Media"
                        :value="old('title')"
                        placeholder="Judul yang mudah dikenali"
                    />

                    <x-form.input
                        name="alt_text"
                        label="Alt Text"
                        :value="old('alt_text')"
                        help="Digunakan untuk aksesibilitas, terutama pada gambar."
                    />

                    <x-form.textarea
                        name="description"
                        label="Deskripsi"
                        :value="old('description')"
                        rows="5"
                    />

                    <x-form.switch
                        name="is_public"
                        label="Media dapat diakses publik"
                        :checked="old('is_public', true)"
                    />

                    <x-ui.divider />

                    <div class="d-flex gap-2">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                        >
                            Unggah Media
                        </x-ui.button>

                        <x-ui.button
                            :href="route('admin.media.index')"
                            variant="secondary"
                        >
                            Batal
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <aside>
            <x-ui.card>
                <x-slot:header>
                    <strong>Panduan Upload</strong>
                </x-slot:header>

                <div class="media-help-list">
                    <div>
                        <strong>Gambar</strong>
                        <p>
                            JPG, JPEG, PNG, WEBP, atau GIF.
                        </p>
                    </div>

                    <div>
                        <strong>Dokumen</strong>
                        <p>
                            PDF, Word, Excel, PowerPoint, TXT, dan CSV.
                        </p>
                    </div>

                    <div>
                        <strong>Audio & Video</strong>
                        <p>
                            MP3, WAV, MP4, dan WEBM.
                        </p>
                    </div>

                    <div>
                        <strong>Ukuran Maksimal</strong>
                        <p>
                            50 MB per file.
                        </p>
                    </div>
                </div>
            </x-ui.card>
        </aside>
    </div>
@endsection

@push('styles')
    <style>
        .media-form-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 310px;
            gap: 20px;
            align-items: start;
        }

        .media-help-list {
            display: grid;
            gap: 18px;
        }

        .media-help-list p {
            margin: 4px 0 0;
            color: #667085;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .media-form-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush