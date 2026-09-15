@csrf

@php
    $selectedUnitId = old(
        'unit_id',
        $agenda->unit_id ?? null
    );

    $selectedStatus = old(
        'status',
        $agenda->status ?? 'draft'
    );

    $selectedCoverId = old(
        'cover_media_id',
        $agenda->cover_media_id ?? null
    );

    $isFeatured = old(
        'is_featured',
        $agenda->is_featured ?? false
    );
@endphp

<div
    class="agenda-editor-root"
    data-agenda-editor
    data-media-picker-url="{{ route('admin.agendas.media-picker') }}"
    data-selected-cover-id="{{ $selectedCoverId }}"
>
    <div class="agenda-editor-layout">

        <main class="agenda-editor-main">

            <section class="agenda-section">
                <span class="agenda-kicker">
                    Informasi Agenda
                </span>

                <h2 class="agenda-section-title">
                    Informasi Utama
                </h2>

                <x-form.input
                    id="title"
                    name="title"
                    label="Judul Agenda"
                    :value="$agenda->title ?? null"
                    required
                    placeholder="Masukkan judul agenda..."
                />

                <div class="agenda-slug-group">
                    <label class="ui-form-label">
                        URL
                    </label>

                    <div class="agenda-slug-preview">
                        <span>/agenda/</span>

                        <input
                            type="text"
                            id="slug"
                            value="{{ old('slug', $agenda->slug ?? '') }}"
                            readonly
                            tabindex="-1"
                        >
                    </div>
                </div>
            </section>

            <section class="agenda-section">
                <div class="agenda-heading-row">
                    <div>
                        <span class="agenda-kicker">
                            Deskripsi
                        </span>

                        <h2 class="agenda-section-title">
                            Isi Agenda
                        </h2>
                    </div>

                    <div class="agenda-editor-stats">
                        <span>
                            <strong id="agenda-word-count">0</strong>
                            kata
                        </span>

                        <span>
                            <strong id="agenda-character-count">0</strong>
                            karakter
                        </span>

                        <span>
                            ±
                            <strong id="agenda-reading-time">1</strong>
                            menit baca
                        </span>
                    </div>
                </div>

                <textarea
                    id="content"
                    name="content"
                    hidden
                >{{ old('content', $agenda->content ?? '') }}</textarea>

                <div class="agenda-rich-editor">
                    <div id="agenda-editor"></div>
                </div>

                @error('content')
                    <div class="ui-form-error">
                        {{ $message }}
                    </div>
                @enderror
            </section>

            <section class="agenda-section">
                <div class="agenda-heading-row">
                    <div>
                        <span class="agenda-kicker">
                            Ringkasan
                        </span>

                        <h2 class="agenda-section-title">
                            Ringkasan Agenda
                        </h2>
                    </div>

                    <x-ui.button
                        type="button"
                        id="agenda-generate-excerpt"
                        variant="secondary"
                        size="sm"
                    >
                        Buat dari Isi
                    </x-ui.button>
                </div>

                <x-form.textarea
                    id="excerpt"
                    name="excerpt"
                    label="Ringkasan"
                    :value="$agenda->excerpt ?? null"
                    rows="4"
                    maxlength="1000"
                    placeholder="Ringkasan singkat agenda..."
                />
            </section>

            <section class="agenda-section">
                <span class="agenda-kicker">
                    SEO
                </span>

                <h2 class="agenda-section-title">
                    Optimasi Mesin Pencari
                </h2>

                <div class="agenda-seo-grid">
                    <div>
                        <x-form.input
                            id="meta_title"
                            name="meta_title"
                            label="Meta Title"
                            :value="$agenda->meta_title ?? null"
                            maxlength="255"
                        />

                        <div class="agenda-counter">
                            <span id="agenda-meta-title-count">0</span>
                            karakter
                        </div>
                    </div>

                    <div>
                        <x-form.textarea
                            id="meta_description"
                            name="meta_description"
                            label="Meta Description"
                            :value="$agenda->meta_description ?? null"
                            rows="4"
                            maxlength="500"
                        />

                        <div class="agenda-counter">
                            <span id="agenda-meta-description-count">0</span>
                            karakter
                        </div>
                    </div>
                </div>

                <div class="agenda-seo-preview">
                    <div class="agenda-seo-preview-label">
                        Preview Mesin Pencari
                    </div>

                    <div
                        id="agenda-seo-title"
                        class="agenda-seo-title"
                    >
                        Judul agenda
                    </div>

                    <div class="agenda-seo-url">
                        web-ppid.id/agenda/<span id="agenda-seo-slug">slug-agenda</span>
                    </div>

                    <div
                        id="agenda-seo-description"
                        class="agenda-seo-description"
                    >
                        Ringkasan agenda akan tampil di sini.
                    </div>
                </div>
            </section>
        </main>

        <aside class="agenda-editor-sidebar">
            <div class="agenda-settings-panel">

                <section class="agenda-settings-section">
                    <div class="agenda-settings-heading">
                        <span>01</span>

                        <div>
                            <h3>Jadwal</h3>
                            <p>Waktu dan lokasi kegiatan.</p>
                        </div>
                    </div>

                    <x-form.input
                        id="start_at"
                        name="start_at"
                        label="Mulai"
                        type="datetime-local"
                        required
                        :value="old(
                            'start_at',
                            isset($agenda) && $agenda->start_at
                                ? $agenda->start_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <x-form.input
                        id="end_at"
                        name="end_at"
                        label="Selesai"
                        type="datetime-local"
                        :value="old(
                            'end_at',
                            isset($agenda) && $agenda->end_at
                                ? $agenda->end_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <x-form.input
                        id="location"
                        name="location"
                        label="Lokasi"
                        :value="$agenda->location ?? null"
                        maxlength="500"
                        placeholder="Contoh: Aula Kantor..."
                    />

                    <div
                        id="agenda-event-hint"
                        class="agenda-hint"
                    ></div>
                </section>

                <section class="agenda-settings-section">
                    <div class="agenda-settings-heading">
                        <span>02</span>

                        <div>
                            <h3>Publikasi</h3>
                            <p>Status publikasi agenda.</p>
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
                            Published
                        </option>

                        <option
                            value="archived"
                            @selected($selectedStatus === 'archived')
                        >
                            Archived
                        </option>
                    </x-form.select>

                    <x-form.input
                        id="published_at"
                        name="published_at"
                        label="Tanggal Publikasi"
                        type="datetime-local"
                        :value="old(
                            'published_at',
                            isset($agenda) && $agenda->published_at
                                ? $agenda->published_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <div
                        id="agenda-publication-hint"
                        class="agenda-hint"
                    ></div>

                    <input
                        type="hidden"
                        name="is_featured"
                        value="0"
                    >

                    <label class="agenda-checkbox-row">
                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            @checked($isFeatured)
                        >

                        <span>
                            <strong>
                                Agenda Unggulan
                            </strong>

                            <small>
                                Prioritaskan agenda ini.
                            </small>
                        </span>
                    </label>

                    <div class="agenda-save-area">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            block
                        >
                            {{ isset($agenda)
                                ? 'Simpan Perubahan'
                                : 'Simpan Agenda'
                            }}
                        </x-ui.button>
                    </div>
                </section>

                <section class="agenda-settings-section">
                    <div class="agenda-settings-heading">
                        <span>03</span>

                        <div>
                            <h3>Unit Kerja</h3>
                            <p>Pemilik agenda.</p>
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

                <section class="agenda-settings-section">
                    <div class="agenda-settings-heading">
                        <span>04</span>

                        <div>
                            <h3>Gambar Utama</h3>
                            <p>Cover agenda dari Media Manager.</p>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="cover_media_id"
                        id="cover_media_id"
                        value="{{ $selectedCoverId }}"
                    >

                    <div class="agenda-media-preview">
                        <div
                            id="agenda-cover-empty"
                            class="agenda-media-empty"
                        >
                            <strong>
                                Belum ada cover
                            </strong>

                            <span>
                                Pilih gambar dari Media Manager
                            </span>
                        </div>

                        <div
                            id="agenda-cover-preview"
                            hidden
                        >
                            <img
                                id="agenda-cover-image"
                                class="agenda-cover-image"
                                src=""
                                alt=""
                            >

                            <div class="agenda-media-info">
                                <strong id="agenda-cover-title"></strong>
                                <span id="agenda-cover-unit"></span>
                            </div>
                        </div>
                    </div>

                    <x-ui.button
                        type="button"
                        id="agenda-cover-picker"
                        variant="secondary"
                        block
                    >
                        Pilih Cover
                    </x-ui.button>
                </section>
            </div>
        </aside>
    </div>

    <div
        id="agenda-media-picker"
        class="agenda-modal"
        hidden
        aria-hidden="true"
    >
        <div
            class="agenda-modal-backdrop"
            data-agenda-media-close
        ></div>

        <div
            class="agenda-modal-dialog"
            role="dialog"
            aria-modal="true"
        >
            <div class="agenda-modal-header">
                <div>
                    <span class="agenda-kicker">
                        Pustaka Media
                    </span>

                    <h3 id="agenda-media-modal-title">
                        Pilih Media
                    </h3>
                </div>

                <button
                    type="button"
                    class="agenda-modal-close"
                    data-agenda-media-close
                >
                    ×
                </button>
            </div>

            <div class="agenda-modal-body">
                <div class="agenda-media-toolbar">
                    <input
                        type="search"
                        id="agenda-media-search"
                        class="ui-control"
                        placeholder="Cari gambar..."
                    >

                    <select
                        id="agenda-media-unit"
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
                    id="agenda-media-loading"
                    class="agenda-media-message"
                    hidden
                >
                    Memuat media...
                </div>

                <div
                    id="agenda-media-empty"
                    class="agenda-media-message"
                    hidden
                >
                    Tidak ada gambar ditemukan.
                </div>

                <div
                    id="agenda-media-grid"
                    class="agenda-media-grid"
                ></div>

                <div class="agenda-media-more">
                    <x-ui.button
                        type="button"
                        id="agenda-media-load-more"
                        variant="secondary"
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
        href="{{ asset('css/admin/pages/agenda-editor.css') }}"
    >
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script src="{{ asset('js/modules/agenda-editor.js') }}"></script>
@endpush