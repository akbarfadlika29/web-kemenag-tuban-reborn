{{-- PPID_NEWS_STATUS_PERMISSION_V1 --}}
@php
    $newsPermissionService = app(\App\Services\Access\AccessService::class);
    $newsPermissionActor = auth()->user();
    $newsPermissionRecord = $news ?? null;

    $newsPermissionUnit = old(
        'unit_id',
        $newsPermissionRecord?->unit_id ?? $newsPermissionActor?->unit_id
    );

    if (!is_int($newsPermissionUnit) && !is_string($newsPermissionUnit)) {
        $newsPermissionUnit = null;
    }

    $newsCanPublish = $newsPermissionActor
        && $newsPermissionService->allowsUnit(
            $newsPermissionActor,
            'news.publish',
            $newsPermissionUnit
        );

    $newsWasPublished = $newsPermissionRecord?->status === 'published';

    if ($newsWasPublished) {
        $newsCanPublish = $newsCanPublish
            && $newsPermissionService->allowsUnit(
                $newsPermissionActor,
                'news.publish',
                $newsPermissionRecord->unit_id
            );
    }

    $newsCanWithdraw = !$newsWasPublished
        || (
            $newsPermissionActor
            && $newsPermissionService->allowsUnit(
                $newsPermissionActor,
                'news.unpublish',
                $newsPermissionRecord->unit_id
            )
        );
@endphp
@csrf
@if (isset($news))
    <input type="hidden" name="editorial_version"
           value="{{ old('editorial_version', $news->editorial_version) }}">
    <div class="ui-card ui-card-body" style="margin-bottom:18px">
        <a class="ui-btn ui-btn-secondary ui-btn-md"
           href="{{ route('admin.news.editorial', $news) }}">
            Pengajuan dan Riwayat
        </a>
        @if ($news->rejection_reason)
            <p><strong>Alasan penolakan:</strong></p>
            <p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $news->rejection_reason }}</p>
        @endif
    </div>
@else
    <p>Simpan berita terlebih dahulu, lalu gunakan tombol Pengajuan pada daftar berita.</p>
@endif

@php
    $selectedTagIds = old(
        'tag_ids',
        isset($news)
            ? $news->tags->pluck('id')->all()
            : []
    );

    $selectedTagIds = array_map(
        'intval',
        (array) $selectedTagIds
    );

    $selectedCoverId = old(
        'cover_media_id',
        $news->cover_media_id ?? null
    );
@endphp

<div
    class="news-editor-root"
    data-news-editor
    data-media-picker-url="{{ route('admin.news.media-picker') }}"
    data-selected-cover-id="{{ $selectedCoverId }}"
>
    <div class="news-workspace">

        {{-- =========================================================
             MAIN EDITOR
             ========================================================= --}}
        <main class="news-content-panel">

            {{-- JUDUL --}}
            <section class="news-section news-section-title">
                <div class="news-section-heading">
                    <div>
                        <span class="news-section-kicker">
                            Konten Berita
                        </span>

                        <h2>
                            Informasi Utama
                        </h2>
                    </div>
                </div>

                <x-form.input
                    id="title"
                    name="title"
                    label="Judul Berita"
                    :value="$news->title ?? null"
                    required
                    placeholder="Masukkan judul berita..."
                />

                <div class="news-slug-row">
                    <span class="news-slug-label">
                        URL
                    </span>

                    <div class="news-slug-preview">
                        <span>/berita/</span>

                        <input
                            type="text"
                            name="slug"
                            id="slug"
                            value="{{ old('slug', $news->slug ?? '') }}"
                            readonly
                            tabindex="-1"
                        >
                    </div>

                    <div class="news-inline-help">
                        Slug dibuat otomatis dari judul.
                    </div>
                </div>
            </section>

            {{-- EDITOR --}}
            <section class="news-section">
                <div class="news-section-heading news-section-heading-row">
                    <div>
                        <span class="news-section-kicker">
                            Isi Berita
                        </span>

                        <h2>
                            Artikel
                        </h2>
                    </div>

                    <div class="news-editor-stats">
                        <span>
                            <strong id="news-word-count">
                                0
                            </strong>
                            kata
                        </span>

                        <span>
                            <strong id="news-character-count">
                                0
                            </strong>
                            karakter
                        </span>

                        <span>
                            ±
                            <strong id="news-reading-time">
                                1
                            </strong>
                            menit baca
                        </span>
                    </div>
                </div>

                <x-form.content-editor
                    name="content"
                    id="content"
                    :value="$news->content ?? null"
                    placeholder="Tulis isi berita di sini..."
                />

                @error('content')
                    <div class="ui-form-error news-editor-error">
                        {{ $message }}
                    </div>
                @enderror
            </section>

            {{-- RINGKASAN --}}
            <section class="news-section">
                <div class="news-section-heading news-section-heading-row">
                    <div>
                        <span class="news-section-kicker">
                            Ringkasan
                        </span>

                        <h2>
                            Ringkasan Berita
                        </h2>
                    </div>

                    <x-ui.button
                        type="button"
                        size="sm"
                        variant="secondary"
                        id="news-generate-excerpt"
                    >
                        Buat dari Isi
                    </x-ui.button>
                </div>

                <x-form.textarea
                    id="excerpt"
                    name="excerpt"
                    label="Ringkasan"
                    :value="$news->excerpt ?? null"
                    rows="4"
                    maxlength="1000"
                    placeholder="Tuliskan ringkasan singkat berita..."
                    help="Opsional. Jika kosong, sistem akan membuat ringkasan otomatis dari isi berita."
                />
            </section>

            {{-- SEO --}}
            <section class="news-section news-seo-section">
                <div class="news-section-heading">
                    <div>
                        <span class="news-section-kicker">
                            Optimasi
                        </span>

                        <h2>
                            SEO & Mesin Pencari
                        </h2>
                    </div>
                </div>

                <div class="news-seo-fields">
                    <div>
                        <x-form.input
                            id="meta_title"
                            name="meta_title"
                            label="Meta Title"
                            :value="$news->meta_title ?? null"
                            maxlength="255"
                            placeholder="Judul untuk mesin pencari..."
                            help="Jika kosong, sistem menggunakan judul berita."
                        />

                        <div class="news-field-counter">
                            <span id="news-meta-title-count">
                                0
                            </span>
                            karakter
                        </div>
                    </div>

                    <div>
                        <x-form.textarea
                            id="meta_description"
                            name="meta_description"
                            label="Meta Description"
                            :value="$news->meta_description ?? null"
                            rows="4"
                            maxlength="500"
                            placeholder="Deskripsi singkat untuk mesin pencari..."
                            help="Jika kosong, sistem menggunakan ringkasan berita."
                        />

                        <div class="news-field-counter">
                            <span id="news-meta-description-count">
                                0
                            </span>
                            karakter
                        </div>
                    </div>
                </div>

                <div class="news-search-preview">
                    <div class="news-search-preview-label">
                        Preview Mesin Pencari
                    </div>

                    <div
                        class="news-seo-title"
                        id="news-seo-title"
                    >
                        Judul berita
                    </div>

                    <div class="news-seo-url">
                        web-ppid.id/berita/<span id="news-seo-slug">slug-berita</span>
                    </div>

                    <div
                        class="news-seo-description"
                        id="news-seo-description"
                    >
                        Ringkasan berita akan tampil di sini.
                    </div>
                </div>
            </section>
        </main>

        {{-- =========================================================
             SIDEBAR
             ========================================================= --}}
        <aside class="news-settings-sidebar">
            <div class="news-settings-panel">

                {{-- PUBLIKASI --}}
                <section class="news-settings-section">
                    <div class="news-settings-heading">
                        <span class="news-settings-number">
                            01
                        </span>

                        <div>
                            <h3>
                                Publikasi
                            </h3>

                            <p>
                                Atur status dan waktu tayang.
                            </p>
                        </div>
                    </div>

                    <x-form.select
                        id="status"
                        name="status"
                        label="Status"
                        required
                    >
                        @if ($newsCanWithdraw)
<option
                            value="draft"
                            @selected(
                                old(
                                    'status',
                                    $news->status ?? 'draft'
                                ) === 'draft'
                            )
                        >
                            Draft
                        </option>
@endif

                        @if ($newsCanPublish)
<option
                            value="published"
                            @selected(
                                old(
                                    'status',
                                    $news->status ?? 'draft'
                                ) === 'published'
                            )
                        >
                            Dipublikasikan
                        </option>
@endif

                        @if ($newsCanWithdraw && $newsPermissionActor && (
                            $newsPermissionService->allowsUnit(
                                $newsPermissionActor, 'news.review', $newsPermissionUnit
                            ) || $newsCanPublish
                        ))
<option
                            value="archived"
                            @selected(
                                old(
                                    'status',
                                    $news->status ?? 'draft'
                                ) === 'archived'
                            )
                        >
                            Diarsipkan
                        </option>
@endif
                    </x-form.select>

                    <x-form.input
                        id="published_at"
                        name="published_at"
                        label="Tanggal Publikasi"
                        type="datetime-local"
                        :value="old(
                            'published_at',
                            isset($news) && $news->published_at
                                ? $news->published_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <div
                        id="news-publication-hint"
                        class="news-publication-hint"
                    ></div>

                    <div class="news-featured-row">
                        <x-form.switch
                            name="is_featured"
                            label="Berita Unggulan"
                            :checked="old(
                                'is_featured',
                                $news->is_featured ?? false
                            )"
                        />
                    </div>

                    <div class="news-save-area">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            block
                        >
                            {{ isset($news)
                                ? 'Simpan Perubahan'
                                : 'Simpan Berita'
                            }}
                        </x-ui.button>

                        <span>
                            Pastikan informasi sudah benar sebelum disimpan.
                        </span>
                    </div>
                </section>

                {{-- KLASIFIKASI --}}
                <section class="news-settings-section">
                    <div class="news-settings-heading">
                        <span class="news-settings-number">
                            02
                        </span>

                        <div>
                            <h3>
                                Klasifikasi
                            </h3>

                            <p>
                                Kelompokkan berita agar mudah ditemukan.
                            </p>
                        </div>
                    </div>

                    <x-form.select
                        name="category_id"
                        label="Kategori"
                        required
                    >
                        <option value="">
                            -- Pilih Kategori --
                        </option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(
                                    old(
                                        'category_id',
                                        $news->category_id ?? null
                                    ) == $category->id
                                )
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <div class="ui-form-group">
                        <div class="news-tag-heading">
                            <label class="ui-form-label">
                                Tag
                            </label>

                            <span id="news-tag-count">
                                {{ count($selectedTagIds) }} dipilih
                            </span>
                        </div>

                        <select
                            name="tag_ids[]"
                            id="tag_ids"
                            class="news-tag-native"
                            multiple
                        >
                            @foreach ($tags as $tag)
                                <option
                                    value="{{ $tag->id }}"
                                    @selected(
                                        in_array(
                                            (int) $tag->id,
                                            $selectedTagIds,
                                            true
                                        )
                                    )
                                >
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="news-tag-chips">
                            @foreach ($tags as $tag)
                                @php
                                    $selected = in_array(
                                        (int) $tag->id,
                                        $selectedTagIds,
                                        true
                                    );
                                @endphp

                                <button
                                    type="button"
                                    class="news-tag-chip {{ $selected ? 'is-selected' : '' }}"
                                    data-news-tag="{{ $tag->id }}"
                                    aria-pressed="{{ $selected ? 'true' : 'false' }}"
                                >
                                    {{ $tag->name }}
                                </button>
                            @endforeach
                        </div>

                        @if ($tags->isEmpty())
                            <div class="news-tag-empty">
                                Belum ada tag.
                            </div>
                        @endif

                        @error('tag_ids')
                            <div class="ui-form-error">
                                {{ $message }}
                            </div>
                        @enderror

                        @error('tag_ids.*')
                            <div class="ui-form-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="news-manage-links">
                        <x-ui.button
                            :href="route('admin.news-categories.index')"
                            size="sm"
                            variant="secondary"
                            target="_blank"
                        >
                            Kelola Kategori
                        </x-ui.button>

                        <x-ui.button
                            :href="route('admin.news-tags.index')"
                            size="sm"
                            variant="secondary"
                            target="_blank"
                        >
                            Kelola Tag
                        </x-ui.button>
                    </div>
                </section>

                {{-- UNIT KERJA --}}
                <section class="news-settings-section">
                    <div class="news-settings-heading">
                        <span class="news-settings-number">
                            03
                        </span>

                        <div>
                            <h3>
                                Unit Kerja
                            </h3>

                            <p>
                                Tentukan pemilik konten berita.
                            </p>
                        </div>
                    </div>

                    <x-form.select
                        id="unit_id"
                        name="unit_id"
                        label="Unit Kerja"
                        required
                    >
                        <option value="">
                            -- Pilih Unit Kerja --
                        </option>

                        @foreach ($units as $unitItem)
                            <option
                                value="{{ $unitItem->id }}"
                                @selected(
                                    old(
                                        'unit_id',
                                        $news->unit_id ?? null
                                    ) == $unitItem->id
                                )
                            >
                                {{ $unitItem->name }}
                            </option>
                        @endforeach
                    </x-form.select>
                </section>

                {{-- COVER --}}
                <section class="news-settings-section">
                    <div class="news-settings-heading">
                        <span class="news-settings-number">
                            04
                        </span>

                        <div>
                            <h3>
                                Gambar Utama
                            </h3>

                            <p>
                                Gambar utama yang tampil pada berita.
                            </p>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="cover_media_id"
                        id="cover_media_id"
                        value="{{ $selectedCoverId }}"
                    >

                    <div class="news-cover-box">
                        <div
                            id="news-cover-empty"
                            class="news-cover-empty"
                        >
                            <div class="news-cover-empty-icon">
                                IMG
                            </div>

                            <strong>
                                Belum ada gambar
                            </strong>

                            <span>
                                Pilih gambar dari Media Manager
                            </span>
                        </div>

                        <div
                            id="news-cover-preview"
                            class="news-cover-preview"
                            hidden
                        >
                            <img
                                id="news-cover-image"
                                src=""
                                alt=""
                            >

                            <div class="news-cover-info">
                                <strong id="news-cover-title"></strong>
                                <span id="news-cover-unit"></span>
                            </div>
                        </div>
                    </div>

                    @error('cover_media_id')
                        <div class="ui-form-error news-cover-error">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="news-cover-actions">
                        <x-ui.button
                            type="button"
                            variant="secondary"
                            id="news-cover-picker-button"
                            block
                        >
                            Pilih dari Media Manager
                        </x-ui.button>

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

    {{-- =========================================================
         MEDIA PICKER MODAL
         ========================================================= --}}
    <div
        id="news-media-picker"
        class="news-media-modal"
        aria-hidden="true"
        hidden
    >
        <div
            class="news-media-modal-backdrop"
            data-news-media-close
        ></div>

        <div
            class="news-media-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="news-media-modal-title"
        >
            <div class="news-media-modal-header">
                <div>
                    <span class="news-section-kicker">
                        Pustaka Media
                    </span>

                    <h3 id="news-media-modal-title">
                        Pilih Gambar
                    </h3>

                    <p>
                        Gunakan gambar yang sudah tersedia di Media Manager.
                    </p>
                </div>

                <button
                    type="button"
                    class="news-media-modal-close"
                    data-news-media-close
                    aria-label="Tutup"
                >
                    ×
                </button>
            </div>

            <div class="news-media-modal-body">
                <div class="news-media-picker-toolbar">
                    <input
                        type="search"
                        id="news-media-search"
                        class="ui-control"
                        placeholder="Cari judul atau nama gambar..."
                        autocomplete="off"
                    >

                    <select
                        id="news-media-unit"
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
                    id="news-media-loading"
                    class="news-media-loading"
                    hidden
                >
                    Memuat media...
                </div>

                <div
                    id="news-media-empty"
                    class="news-media-empty"
                    hidden
                >
                    Tidak ada gambar ditemukan.
                </div>

                <div
                    id="news-media-grid"
                    class="news-media-grid"
                ></div>

                <div class="news-media-load-more">
                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="news-media-load-more"
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
        href="{{ asset('css/admin/pages/news-editor.css') }}"
    >
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script src="{{ asset('js/admin/pages/news-editor.js') }}?v={{ filemtime(public_path('js/admin/pages/news-editor.js')) }}"></script>
@endpush