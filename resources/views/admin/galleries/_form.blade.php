@csrf

@php
    $selectedUnitId = old(
        'unit_id',
        $gallery->unit_id ?? null
    );

    $selectedStatus = old(
        'status',
        $gallery->status ?? 'draft'
    );

    $selectedCoverId = old(
        'cover_media_id',
        $gallery->cover_media_id ?? null
    );

    $isFeatured = old(
        'is_featured',
        $gallery->is_featured ?? false
    );

    $oldItems = old('items');

    if ($oldItems !== null) {
        $initialItems = collect($oldItems)
            ->filter(fn ($item) => !empty($item['media_id']))
            ->values();
    } elseif (isset($gallery)) {
        $initialItems = $gallery->items
            ->map(function ($item) {
                return [
                    'media_id' => $item->media_id,
                    'caption' => $item->caption,
                    'alt_text' => $item->alt_text,
                    'sort_order' => $item->sort_order,
                    'media' => $item->media,
                ];
            })
            ->values();
    } else {
        $initialItems = collect();
    }
@endphp

<div
    class="gallery-editor-root"
    data-gallery-editor
    data-media-picker-url="{{ route('admin.galleries.media-picker') }}"
    data-selected-cover-id="{{ $selectedCoverId }}"
>
    <div class="gallery-editor-layout">

        <main class="gallery-editor-main">

            <section class="gallery-section">
                <span class="gallery-kicker">
                    Album Foto
                </span>

                <h2 class="gallery-section-title">
                    Informasi Galeri
                </h2>

                <x-form.input
                    id="title"
                    name="title"
                    label="Judul Galeri"
                    :value="$gallery->title ?? null"
                    required
                    placeholder="Masukkan judul galeri..."
                />

                <div class="gallery-slug-group">
                    <label class="ui-form-label">
                        URL
                    </label>

                    <div class="gallery-slug-preview">
                        <span>/galeri/</span>

                        <input
                            type="text"
                            id="slug"
                            value="{{ old('slug', $gallery->slug ?? '') }}"
                            readonly
                            tabindex="-1"
                        >
                    </div>
                </div>

                <x-form.textarea
                    id="excerpt"
                    name="excerpt"
                    label="Ringkasan"
                    :value="$gallery->excerpt ?? null"
                    rows="3"
                    maxlength="1000"
                    placeholder="Ringkasan singkat galeri..."
                />

                <x-form.textarea
                    id="description"
                    name="description"
                    label="Deskripsi"
                    :value="$gallery->description ?? null"
                    rows="6"
                    maxlength="5000"
                    placeholder="Deskripsi lengkap album foto..."
                />
            </section>

            <section class="gallery-section">
                <div class="gallery-heading-row">
                    <div>
                        <span class="gallery-kicker">
                            Koleksi Foto
                        </span>

                        <h2 class="gallery-section-title">
                            Foto Galeri
                        </h2>
                    </div>

                    <x-ui.button
                        type="button"
                        id="gallery-add-media"
                        variant="primary"
                        size="sm"
                    >
                        Tambah Foto
                    </x-ui.button>
                </div>

                <div class="gallery-items-help">
                    Seret foto untuk mengubah urutan. Caption dan alt text
                    dapat diatur per foto.
                </div>

                @error('items')
                    <div class="ui-form-error">
                        {{ $message }}
                    </div>
                @enderror

                <div
                    id="gallery-items-empty"
                    class="gallery-items-empty"
                    @if ($initialItems->isNotEmpty()) hidden @endif
                >
                    <strong>
                        Belum ada foto
                    </strong>

                    <span>
                        Klik “Tambah Foto” untuk memilih dari Media Manager.
                    </span>
                </div>

                <div
                    id="gallery-items"
                    class="gallery-items"
                >
                    @foreach ($initialItems as $index => $item)
                        @php
                            $media = $item['media'] ?? null;

                            if (!$media && isset($gallery)) {
                                $media = $gallery->items
                                    ->firstWhere(
                                        'media_id',
                                        (int) $item['media_id']
                                    )
                                    ?->media;
                            }
                        @endphp

                        <article
                            class="gallery-item-card"
                            data-gallery-item
                            data-media-id="{{ $item['media_id'] }}"
                        >
                            <div class="gallery-item-drag">
                                ⋮⋮
                            </div>

                            <div class="gallery-item-image-wrap">
                                @if ($media)
                                    <img
                                        class="gallery-item-image"
                                        src="{{ Storage::disk($media->disk)->url($media->path) }}"
                                        alt="{{ $item['alt_text'] ?? $media->alt_text ?? '' }}"
                                    >
                                @else
                                    <div class="gallery-item-image-placeholder">
                                        FOTO
                                    </div>
                                @endif
                            </div>

                            <div class="gallery-item-content">
                                <div class="gallery-item-heading">
                                    <div>
                                        <strong class="gallery-item-title">
                                            {{ $media?->title ?: $media?->original_name ?: 'Foto Galeri' }}
                                        </strong>

                                        <span>
                                            ID Media: {{ $item['media_id'] }}
                                        </span>
                                    </div>

                                    <button
                                        type="button"
                                        class="gallery-item-remove"
                                        data-gallery-item-remove
                                    >
                                        Hapus
                                    </button>
                                </div>

                                <input
                                    type="hidden"
                                    name="items[{{ $index }}][media_id]"
                                    value="{{ $item['media_id'] }}"
                                    data-gallery-media-id
                                >

                                <input
                                    type="hidden"
                                    name="items[{{ $index }}][sort_order]"
                                    value="{{ $index }}"
                                    data-gallery-sort-order
                                >

                                <div class="gallery-item-fields">
                                    <div>
                                        <label>
                                            Caption
                                        </label>

                                        <input
                                            type="text"
                                            class="ui-control"
                                            name="items[{{ $index }}][caption]"
                                            value="{{ $item['caption'] ?? '' }}"
                                            maxlength="255"
                                            placeholder="Caption foto..."
                                            data-gallery-caption
                                        >
                                    </div>

                                    <div>
                                        <label>
                                            Alt Text
                                        </label>

                                        <input
                                            type="text"
                                            class="ui-control"
                                            name="items[{{ $index }}][alt_text]"
                                            value="{{ $item['alt_text'] ?? '' }}"
                                            maxlength="255"
                                            placeholder="Deskripsi gambar..."
                                            data-gallery-alt-text
                                        >
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="gallery-section">
                <span class="gallery-kicker">
                    SEO
                </span>

                <h2 class="gallery-section-title">
                    Optimasi Mesin Pencari
                </h2>

                <div class="gallery-seo-grid">
                    <div>
                        <x-form.input
                            id="meta_title"
                            name="meta_title"
                            label="Meta Title"
                            :value="$gallery->meta_title ?? null"
                            maxlength="255"
                        />

                        <div class="gallery-counter">
                            <span id="gallery-meta-title-count">0</span>
                            karakter
                        </div>
                    </div>

                    <div>
                        <x-form.textarea
                            id="meta_description"
                            name="meta_description"
                            label="Meta Description"
                            :value="$gallery->meta_description ?? null"
                            rows="4"
                            maxlength="500"
                        />

                        <div class="gallery-counter">
                            <span id="gallery-meta-description-count">0</span>
                            karakter
                        </div>
                    </div>
                </div>

                <div class="gallery-seo-preview">
                    <div class="gallery-seo-preview-label">
                        Preview Mesin Pencari
                    </div>

                    <div
                        id="gallery-seo-title"
                        class="gallery-seo-title"
                    >
                        Judul galeri
                    </div>

                    <div class="gallery-seo-url">
                        web-ppid.id/galeri/<span id="gallery-seo-slug">slug-galeri</span>
                    </div>

                    <div
                        id="gallery-seo-description"
                        class="gallery-seo-description"
                    >
                        Ringkasan galeri akan tampil di sini.
                    </div>
                </div>
            </section>
        </main>

        <aside class="gallery-editor-sidebar">
            <div class="gallery-settings-panel">

                <section class="gallery-settings-section">
                    <div class="gallery-settings-heading">
                        <span>01</span>

                        <div>
                            <h3>Publikasi</h3>
                            <p>Status album galeri.</p>
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
                            isset($gallery) && $gallery->published_at
                                ? $gallery->published_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <div
                        id="gallery-publication-hint"
                        class="gallery-hint"
                    ></div>

                    <input
                        type="hidden"
                        name="is_featured"
                        value="0"
                    >

                    <label class="gallery-checkbox-row">
                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            @checked($isFeatured)
                        >

                        <span>
                            <strong>
                                Galeri Unggulan
                            </strong>

                            <small>
                                Prioritaskan album ini.
                            </small>
                        </span>
                    </label>

                    <div class="gallery-save-area">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            block
                        >
                            {{ isset($gallery)
                                ? 'Simpan Perubahan'
                                : 'Simpan Galeri'
                            }}
                        </x-ui.button>
                    </div>
                </section>

                <section class="gallery-settings-section">
                    <div class="gallery-settings-heading">
                        <span>02</span>

                        <div>
                            <h3>Unit Kerja</h3>
                            <p>Pemilik album galeri.</p>
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

                <section class="gallery-settings-section">
                    <div class="gallery-settings-heading">
                        <span>03</span>

                        <div>
                            <h3>Cover Album</h3>
                            <p>Gambar utama galeri.</p>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        id="cover_media_id"
                        name="cover_media_id"
                        value="{{ $selectedCoverId }}"
                    >

                    <div class="gallery-cover-preview-box">
                        <div
                            id="gallery-cover-empty"
                            class="gallery-cover-empty"
                        >
                            <strong>
                                Belum ada cover
                            </strong>

                            <span>
                                Pilih gambar dari Media Manager
                            </span>
                        </div>

                        <div
                            id="gallery-cover-preview"
                            hidden
                        >
                            <img
                                id="gallery-cover-image"
                                class="gallery-cover-image"
                                src=""
                                alt=""
                            >

                            <div class="gallery-cover-info">
                                <strong id="gallery-cover-title"></strong>
                                <span id="gallery-cover-unit"></span>
                            </div>
                        </div>
                    </div>

                    <x-ui.button
                        type="button"
                        id="gallery-cover-picker"
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
        id="gallery-media-picker"
        class="gallery-modal"
        hidden
        aria-hidden="true"
    >
        <div
            class="gallery-modal-backdrop"
            data-gallery-media-close
        ></div>

        <div
            class="gallery-modal-dialog"
            role="dialog"
            aria-modal="true"
        >
            <div class="gallery-modal-header">
                <div>
                    <span class="gallery-kicker">
                        Pustaka Media
                    </span>

                    <h3 id="gallery-media-modal-title">
                        Pilih Foto
                    </h3>
                </div>

                <button
                    type="button"
                    class="gallery-modal-close"
                    data-gallery-media-close
                >
                    ×
                </button>
            </div>

            <div class="gallery-modal-body">
                <div class="gallery-media-toolbar">
                    <input
                        type="search"
                        id="gallery-media-search"
                        class="ui-control"
                        placeholder="Cari foto..."
                    >

                    <select
                        id="gallery-media-unit"
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

                <div class="gallery-media-selection-bar">
                    <span>
                        <strong id="gallery-selected-count">0</strong>
                        foto dipilih
                    </span>

                    <x-ui.button
                        type="button"
                        id="gallery-confirm-selection"
                        variant="primary"
                        size="sm"
                    >
                        Tambahkan Foto
                    </x-ui.button>
                </div>

                <div
                    id="gallery-media-loading"
                    class="gallery-media-message"
                    hidden
                >
                    Memuat media...
                </div>

                <div
                    id="gallery-media-empty"
                    class="gallery-media-message"
                    hidden
                >
                    Tidak ada gambar ditemukan.
                </div>

                <div
                    id="gallery-media-grid"
                    class="gallery-media-grid"
                ></div>

                <div class="gallery-media-more">
                    <x-ui.button
                        type="button"
                        id="gallery-media-load-more"
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
        href="{{ asset('css/admin/pages/gallery-editor.css') }}"
    >
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <script src="{{ asset('js/admin/pages/gallery-editor.js') }}"></script>
@endpush