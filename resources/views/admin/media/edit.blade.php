@extends('layouts.admin')

@section('title', 'Ubah Media')

@section('content')
    <x-ui.page-header
        title="Ubah Media"
        description="Perbarui informasi yang diperlukan, lalu simpan perubahan."
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

    <div class="media-edit-layout">
        <aside>
            <x-ui.card>
                <x-slot:header>
                    <strong>Preview Media</strong>
                </x-slot:header>

                <div class="media-edit-preview">
                    @if ($medium->type === 'image')
                        <img
                            src="{{ asset('storage/' . $medium->path) }}"
                            alt="{{ $medium->alt_text ?: $medium->title ?: $medium->original_name }}"
                        >
                    @elseif ($medium->extension === 'pdf')
                        <iframe
                            src="{{ asset('storage/' . $medium->path) }}#toolbar=0&navpanes=0"
                        ></iframe>
                    @elseif ($medium->type === 'video')
                        <video
                            controls
                            preload="metadata"
                            src="{{ asset('storage/' . $medium->path) }}"
                        ></video>
                    @elseif ($medium->type === 'audio')
                        <div class="media-edit-audio">
                            <div class="media-edit-type">
                                AUDIO
                            </div>

                            <audio
                                controls
                                src="{{ asset('storage/' . $medium->path) }}"
                            ></audio>
                        </div>
                    @else
                        <div class="media-edit-placeholder">
                            {{ strtoupper($medium->extension ?: 'FILE') }}
                        </div>
                    @endif
                </div>

                <x-ui.divider />

                <div class="media-info-list">
                    <div>
                        <span>Nama File</span>
                        <strong>{{ $medium->original_name }}</strong>
                    </div>

                    <div>
                        <span>Jenis</span>
                        <strong>{{ strtoupper($medium->type) }}</strong>
                    </div>

                    <div>
                        <span>Ekstensi</span>
                        <strong>
                            {{ strtoupper($medium->extension ?: '-') }}
                        </strong>
                    </div>

                    <div>
                        <span>Ukuran</span>
                        <strong>
                            {{ number_format($medium->size / 1024, 1) }} KB
                        </strong>
                    </div>

                    <div>
                        <span>MIME Type</span>
                        <strong>
                            {{ $medium->mime_type ?: '-' }}
                        </strong>
                    </div>
                </div>

                <x-ui.divider />

                <x-ui.button
                    :href="asset('storage/' . $medium->path)"
                    target="_blank"
                    rel="noopener noreferrer"
                    variant="secondary"
                    block
                >
                    Buka File
                </x-ui.button>
            </x-ui.card>
        </aside>

        <div>
            <x-ui.card>
                <form
                    action="{{ route('admin.media.update', $medium) }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf
                    @method('PUT')

                    <x-form.file
                        name="file"
                        label="Ganti File"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.mp3,.wav,.mp4,.webm"
                        help="Kosongkan jika file fisik tidak ingin diganti."
                    />

                    <x-form.select
                        name="unit_id"
                        label="Unit Kerja"
                        help="Kosongkan untuk media global."
                    >
                        <option value="">
                            -- Media Global --
                        </option>

                        @foreach ($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected(
                                    old(
                                        'unit_id',
                                        $medium->unit_id
                                    ) == $unit->id
                                )
                            >
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-form.input
                        name="title"
                        label="Judul Media"
                        :value="$medium->title"
                    />

                    <x-form.input
                        name="alt_text"
                        label="Alt Text"
                        :value="$medium->alt_text"
                        help="Digunakan untuk aksesibilitas, terutama pada gambar."
                    />

                    <x-form.textarea
                        name="description"
                        label="Deskripsi"
                        :value="$medium->description"
                        rows="5"
                    />

                    <x-form.switch
                        name="is_public"
                        label="Media dapat diakses publik"
                        :checked="$medium->is_public"
                    />

                    <x-ui.divider />

                    <div class="d-flex gap-2">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                        >
                            Simpan Perubahan
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
    </div>
@endsection

@push('styles')
    <style>
        .media-edit-layout {
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr);
            gap: 20px;
            align-items: start;
        }

        .media-edit-preview {
            min-height: 230px;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            border: 1px solid #e1e7ef;
            border-radius: 12px;

            background:
                linear-gradient(
                    180deg,
                    #f8fafc 0%,
                    #eef2f7 100%
                );
        }

        .media-edit-preview img,
        .media-edit-preview iframe,
        .media-edit-preview video {
            width: 100%;
            height: 230px;
            border: 0;
            object-fit: cover;
        }

        .media-edit-audio {
            width: 100%;
            padding: 22px;

            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .media-edit-audio audio {
            width: 100%;
        }

        .media-edit-type,
        .media-edit-placeholder {
            padding: 14px 18px;

            background: #ffffff;
            border: 1px solid #dce3ec;
            border-radius: 12px;

            color: #123563;
            font-size: 18px;
            font-weight: 800;
        }

        .media-info-list {
            display: grid;
            gap: 14px;
        }

        .media-info-list div {
            display: grid;
            gap: 3px;
        }

        .media-info-list span {
            color: #667085;
            font-size: 12px;
        }

        .media-info-list strong {
            font-size: 13px;
            word-break: break-word;
        }

        @media (max-width: 900px) {
            .media-edit-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush