@csrf

@php
    $medium = $heroSlide->media ?? null;

    $mediaUrl = $medium
        ? Storage::disk(
            $medium->disk
        )->url(
            $medium->path
        )
        : null;

    $mediaPayload = $medium
        ? [
            'id' => $medium->id,
            'type' => $medium->type,
            'url' => $mediaUrl,
            'title' => $medium->title,
            'original_name' => $medium->original_name,
            'alt_text' => $medium->alt_text,
        ]
        : null;

    $currentSortOrder = old(
        'sort_order',
        $heroSlide->sort_order
            ?? $nextSortOrder
            ?? 1
    );

    $isActive = old(
        'is_active',
        isset($heroSlide)
            ? (int) $heroSlide->is_active
            : 1
    );
@endphp

<div
    class="hero-slide-editor"
    data-hero-slide-editor
>
    <section class="hero-slide-form-card">
        <header>
            <span>
                Konten Slide
            </span>

            <h2>
                Slide Hero Tambahan
            </h2>

            <p>
                Gambar wajib dipilih. Judul, deskripsi, dan tombol bersifat opsional.
            </p>
        </header>

        <div class="hero-slide-form-body">
            <div class="hero-slide-media-field">
                <label class="hero-slide-field-label">
                    Gambar Banner
                    <span>*</span>
                </label>

                <input
                    type="hidden"
                    name="media_id"
                    id="hero_slide_media_id"
                    value="{{ old(
                        'media_id',
                        $heroSlide->media_id ?? ''
                    ) }}"
                >

                <div
                    class="hero-slide-media-preview"
                    data-hero-slide-media-preview
                >
                    <div
                        class="hero-slide-media-empty"
                        data-hero-slide-media-empty
                        @if ($medium)
                            hidden
                        @endif
                    >
                        <span>
                            IMG
                        </span>

                        <strong>
                            Belum ada gambar banner
                        </strong>

                        <small>
                            Pilih gambar dari Media Manager.
                        </small>
                    </div>

                    <div
                        class="hero-slide-media-selected"
                        data-hero-slide-media-selected
                        @if (! $medium)
                            hidden
                        @endif
                    >
                        <img
                            src="{{ $mediaUrl ?: '' }}"
                            alt=""
                            data-hero-slide-media-image
                        >

                        <div>
                            <strong
                                data-hero-slide-media-title
                            >
                                {{ $medium?->title
                                    ?: $medium?->original_name }}
                            </strong>

                            <span>
                                Gambar banner terpilih
                            </span>
                        </div>
                    </div>
                </div>

                @error('media_id')
                    <div class="hero-slide-field-error">
                        {{ $message }}
                    </div>
                @enderror

                <div class="hero-slide-media-actions">
                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="hero-slide-media-choose"
                    >
                        Pilih Gambar
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="hero-slide-media-remove"
                    >
                        Hapus Gambar
                    </x-ui.button>
                </div>
            </div>

            <div class="hero-slide-form-grid">
                <x-form.input
                    name="title"
                    label="Judul"
                    :value="$heroSlide->title ?? null"
                    maxlength="180"
                    placeholder="Opsional, contoh: Pelayanan Haji 2026"
                />

                <x-form.input
                    name="button_label"
                    label="Label Tombol"
                    :value="$heroSlide->button_label ?? null"
                    maxlength="100"
                    placeholder="Contoh: Selengkapnya"
                />
            </div>

            <div class="hero-slide-field">
                <label
                    for="hero-slide-description"
                    class="hero-slide-field-label"
                >
                    Deskripsi
                </label>

                <textarea
                    id="hero-slide-description"
                    name="description"
                    rows="5"
                    maxlength="2000"
                    placeholder="Teks singkat yang tampil di atas banner. Boleh dikosongkan."
                >{{ old(
                    'description',
                    $heroSlide->description ?? ''
                ) }}</textarea>

                @error('description')
                    <div class="hero-slide-field-error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <x-form.input
                name="url"
                label="Alamat URL Tombol"
                :value="$heroSlide->url ?? null"
                maxlength="2048"
                placeholder="https://..., /layanan, atau #informasi"
            />

            <div class="hero-slide-form-grid hero-slide-form-grid-bottom">
                <x-form.input
                    name="sort_order"
                    type="number"
                    label="Urutan"
                    :value="$currentSortOrder"
                    min="1"
                    max="9999"
                    required
                />

                <div class="hero-slide-status-field">
                    <span class="hero-slide-field-label">
                        Status
                    </span>

                    <input
                        type="hidden"
                        name="is_active"
                        value="0"
                    >

                    <label class="hero-slide-status-toggle">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked((int) $isActive === 1)
                        >

                        <span class="hero-slide-status-control"></span>

                        <span>
                            Aktifkan slide
                        </span>
                    </label>

                    <small>
                        Slide nonaktif tetap tersimpan tetapi tidak akan tampil di homepage.
                    </small>
                </div>
            </div>
        </div>
    </section>

    <div class="hero-slide-form-actions">
        <x-ui.button
            :href="route('admin.hero-slides.index')"
            variant="secondary"
        >
            Batal
        </x-ui.button>

        <x-ui.button
            type="submit"
            variant="primary"
        >
            {{ isset($heroSlide)
                ? 'Simpan Perubahan'
                : 'Simpan Slide'
            }}
        </x-ui.button>
    </div>

    <script
        type="application/json"
        data-hero-slide-selected-media
    >@json($mediaPayload)</script>
</div>

@push('scripts')
    <script
        src="{{ asset('js/admin/modules/hero-slides.js') }}"
    ></script>
@endpush
