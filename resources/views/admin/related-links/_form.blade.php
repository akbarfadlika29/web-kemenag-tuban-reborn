@csrf

@php
    $medium = $relatedLink->media ?? null;

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
            'extension' => $medium->extension,
        ]
        : null;
@endphp

<div
    class="related-link-editor"
    data-related-link-editor
>
    <section class="related-link-form-card">
        <header>
            <span>
                Informasi Link
            </span>

            <h2>
                Data Link Terkait
            </h2>

            <p>
                Isi nama website, alamat URL, dan pilih gambar atau logo.
            </p>
        </header>

        <div class="related-link-form-body">
            <x-form.input
                name="name"
                label="Nama Link"
                :value="$relatedLink->name ?? null"
                maxlength="150"
                required
                placeholder="Contoh: Kementerian Agama RI"
            />

            <x-form.input
                name="url"
                label="Alamat URL"
                :value="$relatedLink->url ?? null"
                maxlength="2048"
                required
                placeholder="https://www.kemenag.go.id"
                help="Gunakan alamat lengkap dengan http:// atau https://"
            />

            <div class="related-link-media-field">
                <label class="ui-form-label">
                    Gambar
                </label>

                <input
                    type="hidden"
                    name="media_id"
                    id="related_link_media_id"
                    value="{{ old(
                        'media_id',
                        $relatedLink->media_id ?? ''
                    ) }}"
                >

                <div
                    class="related-link-media-preview"
                    data-related-link-media-preview
                >
                    <div
                        class="related-link-media-empty"
                        data-related-link-media-empty
                        @if ($medium)
                            hidden
                        @endif
                    >
                        <span>
                            IMG
                        </span>

                        <strong>
                            Belum ada gambar
                        </strong>

                        <small>
                            Pilih logo dari Media Manager.
                        </small>
                    </div>

                    <div
                        class="related-link-media-selected"
                        data-related-link-media-selected
                        @if (! $medium)
                            hidden
                        @endif
                    >
                        <img
                            src="{{ $mediaUrl ?: '' }}"
                            alt=""
                            data-related-link-media-image
                        >

                        <div>
                            <strong
                                data-related-link-media-title
                            >
                                {{ $medium?->title
                                    ?: $medium?->original_name }}
                            </strong>

                            <span>
                                Gambar terpilih
                            </span>
                        </div>
                    </div>
                </div>

                @error('media_id')
                    <div class="ui-form-error">
                        {{ $message }}
                    </div>
                @enderror

                <div class="related-link-media-actions">
                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="related-link-media-choose"
                    >
                        Pilih Gambar
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        variant="secondary"
                        id="related-link-media-remove"
                    >
                        Hapus Gambar
                    </x-ui.button>
                </div>
            </div>
        </div>
    </section>

    <div class="related-link-form-actions">
        <x-ui.button
            :href="route('admin.related-links.index')"
            variant="secondary"
        >
            Batal
        </x-ui.button>

        <x-ui.button
            type="submit"
            variant="primary"
        >
            {{ isset($relatedLink)
                ? 'Simpan Perubahan'
                : 'Simpan Link'
            }}
        </x-ui.button>
    </div>

    <script
        type="application/json"
        data-related-link-selected-media
    >@json($mediaPayload)</script>
</div>

@push('scripts')
    <script
        src="{{ asset('js/admin/pages/related-links.js') }}"
    ></script>
@endpush
