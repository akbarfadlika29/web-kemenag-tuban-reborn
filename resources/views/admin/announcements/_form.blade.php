@csrf

@php
    $selectedCoverId = old(
        'cover_media_id',
        $announcement->cover_media_id ?? null
    );

    $selectedAttachmentId = old(
        'attachment_media_id',
        $announcement->attachment_media_id ?? null
    );

    $selectedUnitId = old(
        'unit_id',
        $announcement->unit_id ?? null
    );

    $selectedStatus = old(
        'status',
        $announcement->status ?? 'draft'
    );

    $isPinned = old(
        'is_pinned',
        $announcement->is_pinned ?? false
    );
@endphp

<div
    class="announcement-editor-root"
    data-announcement-editor
    data-media-picker-url="{{ route('admin.announcements.media-picker') }}"
    data-selected-cover-id="{{ $selectedCoverId }}"
    data-selected-attachment-id="{{ $selectedAttachmentId }}"
>
    <div class="announcement-editor-layout">

        <main class="announcement-editor-main">

            <section class="announcement-section">
                <span class="announcement-kicker">
                    Konten Pengumuman
                </span>

                <h2 class="announcement-section-title">
                    Informasi Utama
                </h2>

                <x-form.input
                    id="title"
                    name="title"
                    label="Judul Pengumuman"
                    :value="$announcement->title ?? null"
                    required
                    placeholder="Masukkan judul pengumuman..."
                />

                <div class="announcement-slug-group">
                    <label class="ui-form-label">
                        URL
                    </label>

                    <div class="announcement-slug-preview">
                        <span>/pengumuman/</span>

                        <input
                            type="text"
                            id="slug"
                            value="{{ old('slug', $announcement->slug ?? '') }}"
                            readonly
                            tabindex="-1"
                        >
                    </div>

                    <div class="announcement-help">
                        Slug dibuat otomatis dari judul.
                    </div>
                </div>
            </section>

            <section class="announcement-section">
                <div class="announcement-heading-row">
                    <div>
                        <span class="announcement-kicker">
                            Isi Pengumuman
                        </span>

                        <h2 class="announcement-section-title">
                            Konten
                        </h2>
                    </div>

                    <div class="announcement-editor-stats">
                        <span>
                            <strong id="announcement-word-count">0</strong>
                            kata
                        </span>

                        <span>
                            <strong id="announcement-character-count">0</strong>
                            karakter
                        </span>

                        <span>
                            ±
                            <strong id="announcement-reading-time">1</strong>
                            menit baca
                        </span>
                    </div>
                </div>

                <textarea
                    name="content"
                    id="content"
                    hidden
                >{{ old('content', $announcement->content ?? '') }}</textarea>

                <div class="announcement-rich-editor">
                    <div id="announcement-editor"></div>
                </div>

                @error('content')
                    <div class="ui-form-error">
                        {{ $message }}
                    </div>
                @enderror
            </section>

            <section class="announcement-section">
                <div class="announcement-heading-row">
                    <div>
                        <span class="announcement-kicker">
                            Ringkasan
                        </span>

                        <h2 class="announcement-section-title">
                            Ringkasan Pengumuman
                        </h2>
                    </div>

                    <x-ui.button
                        type="button"
                        size="sm"
                        variant="secondary"
                        id="announcement-generate-excerpt"
                    >
                        Buat dari Isi
                    </x-ui.button>
                </div>

                <x-form.textarea
                    id="excerpt"
                    name="excerpt"
                    label="Ringkasan"
                    :value="$announcement->excerpt ?? null"
                    rows="4"
                    maxlength="1000"
                    placeholder="Ringkasan singkat pengumuman..."
                />
            </section>

            <section class="announcement-section">
                <span class="announcement-kicker">
                    Optimasi
                </span>

                <h2 class="announcement-section-title">
                    SEO & Mesin Pencari
                </h2>

                <div class="announcement-seo-grid">
                    <div>
                        <x-form.input
                            id="meta_title"
                            name="meta_title"
                            label="Meta Title"
                            :value="$announcement->meta_title ?? null"
                            maxlength="255"
                            placeholder="Judul untuk mesin pencari..."
                        />

                        <div class="announcement-counter">
                            <span id="announcement-meta-title-count">0</span>
                            karakter
                        </div>
                    </div>

                    <div>
                        <x-form.textarea
                            id="meta_description"
                            name="meta_description"
                            label="Meta Description"
                            :value="$announcement->meta_description ?? null"
                            rows="4"
                            maxlength="500"
                            placeholder="Deskripsi untuk mesin pencari..."
                        />

                        <div class="announcement-counter">
                            <span id="announcement-meta-description-count">0</span>
                            karakter
                        </div>
                    </div>
                </div>

                <div class="announcement-seo-preview">
                    <div class="announcement-seo-preview-label">
                        Preview Mesin Pencari
                    </div>

                    <div
                        id="announcement-seo-title"
                        class="announcement-seo-title"
                    >
                        Judul pengumuman
                    </div>

                    <div class="announcement-seo-url">
                        web-ppid.id/pengumuman/<span id="announcement-seo-slug">slug-pengumuman</span>
                    </div>

                    <div
                        id="announcement-seo-description"
                        class="announcement-seo-description"
                    >
                        Ringkasan pengumuman akan tampil di sini.
                    </div>
                </div>
            </section>
        </main>

        <aside class="announcement-editor-sidebar">
            <div class="announcement-settings-panel">

                <section class="announcement-settings-section">
                    <div class="announcement-settings-heading">
                        <span>01</span>

                        <div>
                            <h3>Publikasi</h3>
                            <p>Status dan masa berlaku pengumuman.</p>
                        </div>
                    </div>

                    <x-form.select
                        id="status"
                        name="status"
                        label="Status"
                        required
                    >
                        <option
                            value="draft"
                            @selected($selectedStatus === 'draft')
                        >
                            Draft
                        </option>

                        <option
                            value="published"
                            @selected($selectedStatus === 'published')
                        >
                            Dipublikasikan
                        </option>

                        <option
                            value="archived"
                            @selected($selectedStatus === 'archived')
                        >
                            Diarsipkan
                        </option>
                    </x-form.select>

                    <x-form.input
                        id="published_at"
                        name="published_at"
                        label="Tanggal Publikasi"
                        type="datetime-local"
                        :value="old(
                            'published_at',
                            isset($announcement) && $announcement->published_at
                                ? $announcement->published_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <x-form.input
                        id="expires_at"
                        name="expires_at"
                        label="Tanggal Berakhir"
                        type="datetime-local"
                        :value="old(
                            'expires_at',
                            isset($announcement) && $announcement->expires_at
                                ? $announcement->expires_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <div
                        id="announcement-publication-hint"
                        class="announcement-publication-hint"
                    ></div>

                    <input
                        type="hidden"
                        name="is_pinned"
                        value="0"
                    >

                    <label class="announcement-checkbox-row">
                        <input
                            type="checkbox"
                            name="is_pinned"
                            value="1"
                            @checked($isPinned)
                        >

                        <span>
                            <strong>
                                Sematkan Pengumuman
                            </strong>

                            <small>
                                Pengumuman tampil lebih prioritas.
                            </small>
                        </span>
                    </label>

                    <div class="announcement-save-area">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            block
                        >
                            {{ isset($announcement)
                                ? 'Simpan Perubahan'
                                : 'Simpan Pengumuman'
                            }}
                        </x-ui.button>
                    </div>
                </section>

                <section class="announcement-settings-section">
                    <div class="announcement-settings-heading">
                        <span>02</span>

                        <div>
                            <h3>Unit Kerja</h3>
                            <p>Pemilik pengumuman.</p>
                        </div>
                    </div>

                    <x-form.select
                        id="unit_id"
                        name="unit_id"
                        label="Unit Kerja"
                        required
                    >
                        <option value="">
                            — Pilih Unit Kerja —
                        </option>

                        @foreach ($units as $unitItem)
                            <option
                                value="{{ $unitItem->id }}"
                                @selected(
                                    (string) $selectedUnitId ===
                                    (string) $unitItem->id
                                )
                            >
                                {{ $unitItem->name }}
                            </option>
                        @endforeach
                    </x-form.select>
                </section>

                <section class="announcement-settings-section">
                    <div class="announcement-settings-heading">
                        <span>03</span>

                        <div>
                            <h3>Gambar Utama</h3>
                            <p>Cover pengumuman.</p>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="cover_media_id"
                        id="cover_media_id"
                        value="{{ $selectedCoverId }}"
                    >

                    <div class="announcement-media-preview">
                        <div
                            id="announcement-cover-empty"
                            class="announcement-media-empty"
                        >
                            <strong>
                                Belum ada cover
                            </strong>

                            <span>
                                Pilih gambar dari Media Manager
                            </span>
                        </div>

                        <div
                            id="announcement-cover-preview"
                            hidden
                        >
                            <img
                                id="announcement-cover-image"
                                class="announcement-cover-image"
                                src=""
                                alt=""
                            >

                            <div class="announcement-media-info">
                                <strong id="announcement-cover-title"></strong>
                                <span id="announcement-cover-unit"></span>
                            </div>
                        </div>
                    </div>

                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="announcement-cover-picker"
                        block
                    >
                        Pilih Cover
                    </x-ui.button>
                </section>

                <section class="announcement-settings-section">
                    <div class="announcement-settings-heading">
                        <span>04</span>

                        <div>
                            <h3>Lampiran</h3>
                            <p>Dokumen atau media pendukung.</p>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="attachment_media_id"
                        id="attachment_media_id"
                        value="{{ $selectedAttachmentId }}"
                    >

                    <div class="announcement-media-preview">
                        <div
                            id="announcement-attachment-empty"
                            class="announcement-media-empty"
                        >
                            <strong>
                                Belum ada lampiran
                            </strong>

                            <span>
                                Pilih dari Media Manager
                            </span>
                        </div>

                        <div
                            id="announcement-attachment-preview"
                            class="announcement-attachment-preview"
                            hidden
                        >
                            <div class="announcement-file-icon">
                                FILE
                            </div>

                            <div class="announcement-media-info">
                                <strong id="announcement-attachment-title"></strong>
                                <span id="announcement-attachment-meta"></span>
                            </div>
                        </div>
                    </div>

                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="announcement-attachment-picker"
                        block
                    >
                        Pilih Lampiran
                    </x-ui.button>

                    <div class="announcement-upload-link">
                        <x-ui.button
                            :href="route('admin.media.create')"
                            target="_blank"
                            variant="secondary"
                            size="sm"
                            block
                        >
                            Upload Media Baru
                        </x-ui.button>
                    </div>
                </section>
            </div>
        </aside>
    </div>

    <div
        id="announcement-media-picker"
        class="announcement-modal"
        hidden
        aria-hidden="true"
    >
        <div
            class="announcement-modal-backdrop"
            data-announcement-media-close
        ></div>

        <div
            class="announcement-modal-dialog"
            role="dialog"
            aria-modal="true"
        >
            <div class="announcement-modal-header">
                <div>
                    <span class="announcement-kicker">
                        Pustaka Media
                    </span>

                    <h3 id="announcement-media-modal-title">
                        Pilih Media
                    </h3>
                </div>

                <button
                    type="button"
                    class="announcement-modal-close"
                    data-announcement-media-close
                >
                    ×
                </button>
            </div>

            <div class="announcement-modal-body">
                <div class="announcement-media-toolbar">
                    <input
                        type="search"
                        id="announcement-media-search"
                        class="ui-control"
                        placeholder="Cari media..."
                    >

                    <select
                        id="announcement-media-unit"
                        class="ui-control"
                    >
                        <option value="">
                            Semua Unit
                        </option>

                        <option value="global">
                            Media Global
                        </option>

                        @foreach ($units as $unitItem)
                            <option value="{{ $unitItem->id }}">
                                {{ $unitItem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div
                    id="announcement-media-loading"
                    class="announcement-media-message"
                    hidden
                >
                    Memuat media...
                </div>

                <div
                    id="announcement-media-empty"
                    class="announcement-media-message"
                    hidden
                >
                    Tidak ada media ditemukan.
                </div>

                <div
                    id="announcement-media-grid"
                    class="announcement-media-grid"
                ></div>

                <div class="announcement-media-more">
                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="announcement-media-load-more"
                        hidden
                    >
                        Tampilkan Lebih Banyak
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/announcement-editor.css') }}"
    >
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script src="{{ asset('js/admin/pages/announcement-editor.js') }}"></script>
@endpush