@csrf

@php
    $selectedCoverId = old(
        'cover_media_id',
        $page->cover_media_id ?? null
    );

    $selectedParentId = old(
        'parent_id',
        $page->parent_id ?? null
    );

    $selectedUnitId = old(
        'unit_id',
        $page->unit_id ?? null
    );

    $selectedTemplate = old(
        'template',
        $page->template ?? 'default'
    );

    $selectedStatus = old(
        'status',
        $page->status ?? 'draft'
    );

    $showInMenu = old(
        'show_in_menu',
        $page->show_in_menu ?? false
    );

    $sortOrder = old(
        'sort_order',
        $page->sort_order ?? $nextSortOrder ?? 0
    );
@endphp

<div
    class="page-editor-root"
    data-page-editor
    data-media-picker-url="{{ route('admin.pages.media-picker') }}"
    data-selected-cover-id="{{ $selectedCoverId }}"
>
    <div class="page-editor-layout">

        <main class="page-editor-main">

            <section class="page-editor-section">
                <span class="page-editor-kicker">
                    Konten Halaman
                </span>

                <h2 class="page-editor-section-title">
                    Informasi Utama
                </h2>

                <x-form.input
                    id="title"
                    name="title"
                    label="Judul Halaman"
                    :value="$page->title ?? null"
                    required
                    placeholder="Contoh: Visi dan Misi"
                />

                <div class="page-slug-group">
                    <label class="ui-form-label">
                        URL Halaman
                    </label>

                    <div class="page-slug-preview">
                        <span>/halaman/</span>

                        <input
                            type="text"
                            id="slug"
                            value="{{ old('slug', $page->slug ?? '') }}"
                            readonly
                            tabindex="-1"
                        >
                    </div>

                    <div class="page-editor-help">
                        URL dibuat otomatis dari judul halaman.
                    </div>
                </div>
            </section>

            <section class="page-editor-section">
                <div class="page-editor-heading-row">
                    <div>
                        <span class="page-editor-kicker">
                            Isi Halaman
                        </span>

                        <h2 class="page-editor-section-title">
                            Konten
                        </h2>
                    </div>

                    <div class="page-editor-stats">
                        <span>
                            <strong id="page-word-count">0</strong>
                            kata
                        </span>

                        <span>
                            <strong id="page-character-count">0</strong>
                            karakter
                        </span>

                        <span>
                            ±
                            <strong id="page-reading-time">1</strong>
                            menit baca
                        </span>
                    </div>
                </div>

                <x-form.content-editor
                    name="content"
                    id="content"
                    :value="$page->content ?? null"
                    placeholder="Tulis isi halaman di sini..."
                />

                @error('content')
                    <div class="ui-form-error page-editor-error">
                        {{ $message }}
                    </div>
                @enderror
            </section>

            <section class="page-editor-section">
                <div class="page-editor-heading-row">
                    <div>
                        <span class="page-editor-kicker">
                            Ringkasan
                        </span>

                        <h2 class="page-editor-section-title">
                            Deskripsi Singkat
                        </h2>
                    </div>

                    <x-ui.button
                        type="button"
                        size="sm"
                        variant="secondary"
                        id="page-generate-excerpt"
                    >
                        Buat dari Isi
                    </x-ui.button>
                </div>

                <x-form.textarea
                    id="excerpt"
                    name="excerpt"
                    label="Ringkasan"
                    :value="$page->excerpt ?? null"
                    rows="4"
                    maxlength="1000"
                    placeholder="Ringkasan singkat isi halaman..."
                />
            </section>

            <section class="page-editor-section">
                <span class="page-editor-kicker">
                    Optimasi
                </span>

                <h2 class="page-editor-section-title">
                    SEO & Mesin Pencari
                </h2>

                <div class="page-seo-grid">
                    <div>
                        <x-form.input
                            id="meta_title"
                            name="meta_title"
                            label="Meta Title"
                            :value="$page->meta_title ?? null"
                            maxlength="255"
                            placeholder="Judul halaman untuk mesin pencari..."
                        />

                        <div class="page-field-counter">
                            <span id="page-meta-title-count">0</span>
                            karakter
                        </div>
                    </div>

                    <div>
                        <x-form.textarea
                            id="meta_description"
                            name="meta_description"
                            label="Meta Description"
                            :value="$page->meta_description ?? null"
                            rows="4"
                            maxlength="500"
                            placeholder="Deskripsi untuk mesin pencari..."
                        />

                        <div class="page-field-counter">
                            <span id="page-meta-description-count">0</span>
                            karakter
                        </div>
                    </div>
                </div>

                <div class="page-search-preview">
                    <div class="page-search-preview-label">
                        Preview Mesin Pencari
                    </div>

                    <div
                        id="page-seo-title"
                        class="page-seo-title"
                    >
                        Judul halaman
                    </div>

                    <div class="page-seo-url">
                        web-ppid.id/halaman/<span id="page-seo-slug">slug-halaman</span>
                    </div>

                    <div
                        id="page-seo-description"
                        class="page-seo-description"
                    >
                        Ringkasan halaman akan tampil di sini.
                    </div>
                </div>
            </section>
        </main>

        <aside class="page-editor-sidebar">
            <div class="page-settings-panel">

                <section class="page-settings-section">
                    <div class="page-settings-heading">
                        <span>01</span>

                        <div>
                            <h3>Publikasi</h3>
                            <p>Status dan waktu tampil halaman.</p>
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
                            isset($page) && $page->published_at
                                ? $page->published_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <div
                        id="page-publication-hint"
                        class="page-publication-hint"
                    ></div>

                    <div class="page-save-area">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            block
                        >
                            {{ isset($page)
                                ? 'Simpan Perubahan'
                                : 'Simpan Halaman'
                            }}
                        </x-ui.button>
                    </div>
                </section>

                <section class="page-settings-section">
                    <div class="page-settings-heading">
                        <span>02</span>

                        <div>
                            <h3>Struktur</h3>
                            <p>Atur posisi halaman dalam hierarki.</p>
                        </div>
                    </div>

                    <x-form.select
                        id="parent_id"
                        name="parent_id"
                        label="Parent Halaman"
                    >
                        <option value="">
                            — Halaman Utama —
                        </option>

                        @foreach ($parentOptions as $option)
                            <option
                                value="{{ $option['id'] }}"
                                @selected(
                                    (string) $selectedParentId ===
                                    (string) $option['id']
                                )
                            >
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-form.input
                        id="sort_order"
                        name="sort_order"
                        label="Urutan"
                        type="number"
                        min="0"
                        :value="$sortOrder"
                        required
                    />

                    <div class="page-checkbox-box">
                        <input
                            type="hidden"
                            name="show_in_menu"
                            value="0"
                        >

                        <label class="page-checkbox-row">
                            <input
                                type="checkbox"
                                name="show_in_menu"
                                value="1"
                                @checked($showInMenu)
                            >

                            <span>
                                <strong>
                                    Tampilkan di Menu
                                </strong>

                                <small>
                                    Halaman dapat digunakan pada navigasi website.
                                </small>
                            </span>
                        </label>
                    </div>
                </section>

                <section class="page-settings-section">
                    <div class="page-settings-heading">
                        <span>03</span>

                        <div>
                            <h3>Template</h3>
                            <p>Pilih pola tampilan halaman.</p>
                        </div>
                    </div>

                    <x-form.select
                        id="template"
                        name="template"
                        label="Template Halaman"
                        required
                    >
                        <option
                            value="default"
                            @selected($selectedTemplate === 'default')
                        >
                            Default
                        </option>

                        <option
                            value="full-width"
                            @selected($selectedTemplate === 'full-width')
                        >
                            Full Width
                        </option>

                        <option
                            value="landing"
                            @selected($selectedTemplate === 'landing')
                        >
                            Landing Page
                        </option>
                    </x-form.select>
                </section>

                <section class="page-settings-section">
                    <div class="page-settings-heading">
                        <span>04</span>

                        <div>
                            <h3>Unit Kerja</h3>
                            <p>Pemilik atau pengelola halaman.</p>
                        </div>
                    </div>

                    <x-form.select
                        id="unit_id"
                        name="unit_id"
                        label="Unit Kerja"
                    >
                        <option value="">
                            — Global —
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

                <section class="page-settings-section">
                    <div class="page-settings-heading">
                        <span>05</span>

                        <div>
                            <h3>Gambar Utama</h3>
                            <p>Cover opsional untuk halaman.</p>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="cover_media_id"
                        id="cover_media_id"
                        value="{{ $selectedCoverId }}"
                    >

                    <div class="page-cover-box">
                        <div
                            id="page-cover-empty"
                            class="page-cover-empty"
                        >
                            <strong>
                                Belum ada gambar
                            </strong>

                            <span>
                                Pilih dari Media Manager
                            </span>
                        </div>

                        <div
                            id="page-cover-preview"
                            class="page-cover-preview"
                            hidden
                        >
                            <img
                                id="page-cover-image"
                                src=""
                                alt=""
                            >

                            <div class="page-cover-info">
                                <strong id="page-cover-title"></strong>
                                <span id="page-cover-unit"></span>
                            </div>
                        </div>
                    </div>

                    @error('cover_media_id')
                        <div class="ui-form-error">
                            {{ $message }}
                        </div>
                    @enderror

                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="page-cover-picker-button"
                        block
                    >
                        Pilih dari Media Manager
                    </x-ui.button>

                    <div style="margin-top: 8px;">
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
        id="page-media-picker"
        class="page-media-modal"
        aria-hidden="true"
        hidden
    >
        <div
            class="page-media-backdrop"
            data-page-media-close
        ></div>

        <div
            class="page-media-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="page-media-modal-title"
        >
            <div class="page-media-header">
                <div>
                    <span class="page-editor-kicker">
                        Pustaka Media
                    </span>

                    <h3 id="page-media-modal-title">
                        Pilih Gambar
                    </h3>
                </div>

                <button
                    type="button"
                    class="page-media-close"
                    data-page-media-close
                    aria-label="Tutup"
                >
                    ×
                </button>
            </div>

            <div class="page-media-body">
                <div class="page-media-toolbar">
                    <input
                        type="search"
                        id="page-media-search"
                        class="ui-control"
                        placeholder="Cari gambar..."
                        autocomplete="off"
                    >

                    <select
                        id="page-media-unit"
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
                    id="page-media-loading"
                    class="page-media-message"
                    hidden
                >
                    Memuat media...
                </div>

                <div
                    id="page-media-empty"
                    class="page-media-message"
                    hidden
                >
                    Tidak ada gambar ditemukan.
                </div>

                <div
                    id="page-media-grid"
                    class="page-media-grid"
                ></div>

                <div class="page-media-more">
                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="page-media-load-more"
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
        href="{{ asset('css/admin/pages/page-editor.css') }}"
    >
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script src="{{ asset('js/modules/page-editor.js') }}"></script>
@endpush
