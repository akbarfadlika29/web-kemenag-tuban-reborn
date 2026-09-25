@csrf

@php
    $selectedTargetType = old(
        'target_type',
        $quickLink->target_type ?? 'page'
    );

    $selectedMediaId = old(
        'media_id',
        $quickLink->media_id ?? null
    );

    $sortOrder = old(
        'sort_order',
        $quickLink->sort_order ?? $nextSortOrder
    );

    $isActive = old(
        'is_active',
        $quickLink->is_active ?? true
    );

    $openInNewTab = old(
        'open_in_new_tab',
        $quickLink->open_in_new_tab ?? false
    );
@endphp

<div
    class="quick-link-editor"
    data-quick-link-editor
>
    <div class="quick-link-form-layout">
        <main class="quick-link-form-main">
            <section class="quick-link-form-section">
                <header class="quick-link-form-section-header">
                    <div>
                        <span class="quick-links-kicker">
                            Informasi Utama
                        </span>

                        <h2>
                            Identitas Akses Cepat
                        </h2>

                        <p>
                            Nama dan gambar yang akan mewakili pintasan pada website.
                        </p>
                    </div>
                </header>

                <div class="quick-link-form-section-body">
                    <x-form.input
                        name="label"
                        label="Nama Akses Cepat"
                        :value="$quickLink->label ?? null"
                        required
                        maxlength="150"
                        placeholder="Contoh: Layanan Haji"
                    />

                    <div class="quick-link-media-field">
                        <label class="ui-form-label">
                            Gambar
                        </label>

                        <input
                            type="hidden"
                            name="media_id"
                            id="quick_link_media_id"
                            value="{{ $selectedMediaId }}"
                        >

                        <div
                            class="quick-link-media-preview {{ $selectedMedia && $selectedMediaUrl ? 'has-media' : '' }}"
                            data-quick-link-media-preview
                        >
                            <div
                                class="quick-link-media-empty"
                                data-quick-link-media-empty
                                @if ($selectedMedia && $selectedMediaUrl)
                                    hidden
                                @endif
                            >
                                <span class="quick-link-media-empty-icon">
                                    IMG
                                </span>

                                <strong>
                                    Belum ada gambar
                                </strong>

                                <span>
                                    Pilih gambar dari Media Manager.
                                </span>
                            </div>

                            <div
                                class="quick-link-media-selected"
                                data-quick-link-media-selected
                                @if (! $selectedMedia || ! $selectedMediaUrl)
                                    hidden
                                @endif
                            >
                                <img
                                    data-quick-link-media-image
                                    src="{{ $selectedMediaUrl ?: '' }}"
                                    alt=""
                                >

                                <div>
                                    <strong
                                        data-quick-link-media-title
                                    >
                                        {{ $selectedMedia?->title
                                            ?: $selectedMedia?->original_name }}
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

                        <div class="quick-link-media-actions">
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                id="quick-link-media-choose"
                            >
                                Pilih dari Media Manager
                            </x-ui.button>

                            <x-ui.button
                                type="button"
                                variant="secondary"
                                id="quick-link-media-remove"
                            >
                                Hapus Gambar
                            </x-ui.button>
                        </div>

                        <div class="quick-link-form-help">
                            Gunakan gambar yang jelas dan sebaiknya memiliki rasio persegi.
                        </div>
                    </div>
                </div>
            </section>

            <section class="quick-link-form-section">
                <header class="quick-link-form-section-header">
                    <div>
                        <span class="quick-links-kicker">
                            Tujuan
                        </span>

                        <h2>
                            Tautan Akses Cepat
                        </h2>

                        <p>
                            Pilih sumber tautan agar alamat tidak perlu ditulis manual jika berasal dari CMS.
                        </p>
                    </div>
                </header>

                <div class="quick-link-form-section-body">
                    <x-form.select
                        name="target_type"
                        label="Sumber Link"
                        required
                    >
                        <option
                            value="page"
                            @selected($selectedTargetType === 'page')
                        >
                            Halaman CMS
                        </option>

                        <option
                            value="news_category"
                            @selected($selectedTargetType === 'news_category')
                        >
                            Kategori Berita
                        </option>

                        <option
                            value="route"
                            @selected($selectedTargetType === 'route')
                        >
                            Modul / Route Internal
                        </option>

                        <option
                            value="url"
                            @selected($selectedTargetType === 'url')
                        >
                            URL Manual / Eksternal
                        </option>
                    </x-form.select>

                    <div
                        data-quick-link-target="page"
                    >
                        <x-form.select
                            name="page_id"
                            label="Halaman"
                            help="Pilih halaman CMS yang menjadi tujuan."
                        >
                            <option value="">
                                -- Pilih Halaman --
                            </option>

                            @foreach ($pages as $page)
                                <option
                                    value="{{ $page->id }}"
                                    @selected(
                                        old(
                                            'page_id',
                                            $quickLink->page_id ?? null
                                        ) == $page->id
                                    )
                                >
                                    {{ $page->title }}
                                    @if ($page->status !== 'published')
                                        — {{ ucfirst($page->status) }}
                                    @endif
                                </option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <div
                        data-quick-link-target="news_category"
                    >
                        <x-form.select
                            name="news_category_id"
                            label="Kategori Berita"
                            help="Pilih kategori berita yang akan ditampilkan."
                        >
                            <option value="">
                                -- Pilih Kategori Berita --
                            </option>

                            @foreach ($newsCategories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(
                                        old(
                                            'news_category_id',
                                            $quickLink->news_category_id ?? null
                                        ) == $category->id
                                    )
                                >
                                    {{ $category->name }}
                                    @unless ($category->is_active)
                                        — Nonaktif
                                    @endunless
                                </option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <div
                        data-quick-link-target="route"
                    >
                        @php
                            $currentRoute = old(
                                'route_name',
                                $quickLink->route_name ?? null
                            );
                        @endphp

                        <x-form.select
                            name="route_name"
                            label="Modul / Route Internal"
                            help="Hanya route frontend tanpa parameter yang ditampilkan."
                        >
                            <option value="">
                                -- Pilih Route Internal --
                            </option>

                            @foreach ($frontendRoutes as $frontendRoute)
                                <option
                                    value="{{ $frontendRoute['name'] }}"
                                    @selected(
                                        $currentRoute
                                        === $frontendRoute['name']
                                    )
                                >
                                    {{ $frontendRoute['name'] }}
                                    —
                                    {{ $frontendRoute['uri'] === '/'
                                        ? '/'
                                        : '/' . ltrim(
                                            $frontendRoute['uri'],
                                            '/'
                                        )
                                    }}
                                </option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <div
                        data-quick-link-target="url"
                    >
                        <x-form.input
                            name="url"
                            label="URL"
                            :value="$quickLink->url ?? null"
                            maxlength="2048"
                            placeholder="https://example.com atau /alamat"
                            help="Gunakan URL lengkap untuk website eksternal."
                        />
                    </div>
                </div>
            </section>
        </main>

        <aside class="quick-link-form-sidebar">
            <section class="quick-link-form-section">
                <header class="quick-link-form-section-header">
                    <div>
                        <span class="quick-links-kicker">
                            Tampilan
                        </span>

                        <h2>
                            Pengaturan
                        </h2>

                        <p>
                            Atur urutan dan status akses cepat.
                        </p>
                    </div>
                </header>

                <div class="quick-link-form-section-body">
                    <x-form.input
                        name="sort_order"
                        label="Urutan"
                        type="number"
                        min="1"
                        :value="$sortOrder"
                        required
                        help="Angka lebih kecil ditampilkan terlebih dahulu."
                    />

                    <div class="quick-link-setting-divider"></div>

                    <div class="quick-link-setting-row">
                        <div>
                            <strong>
                                Akses Cepat Aktif
                            </strong>

                            <span>
                                Hanya data aktif yang nantinya ditampilkan pada frontend.
                            </span>
                        </div>

                        <x-form.switch
                            name="is_active"
                            label="Aktif"
                            :checked="$isActive"
                        />
                    </div>

                    <div class="quick-link-setting-divider"></div>

                    <div class="quick-link-setting-row">
                        <div>
                            <strong>
                                Buka Tab Baru
                            </strong>

                            <span>
                                Cocok untuk tujuan menuju website eksternal.
                            </span>
                        </div>

                        <x-form.switch
                            name="open_in_new_tab"
                            label="Tab Baru"
                            :checked="$openInNewTab"
                        />
                    </div>
                </div>
            </section>

            <div class="quick-link-form-note">
                <strong>
                    Tentang gambar
                </strong>

                <p>
                    Gambar menggunakan Media Manager global, jadi tidak ada file gambar duplikat khusus untuk modul Akses Cepat.
                </p>
            </div>
        </aside>
    </div>

    <div class="quick-link-form-actions">
        <div class="quick-link-form-actions-copy">
            <strong>
                {{ isset($quickLink)
                    ? 'Simpan perubahan akses cepat'
                    : 'Tambahkan akses cepat baru'
                }}
            </strong>

            <span>
                Pastikan nama, gambar, dan tujuan tautan sudah benar.
            </span>
        </div>

        <div class="quick-link-form-actions-buttons">
            <x-ui.button
                :href="route('admin.quick-links.index')"
                variant="secondary"
            >
                Batal
            </x-ui.button>

            <x-ui.button
                type="submit"
                variant="primary"
            >
                {{ isset($quickLink)
                    ? 'Simpan Perubahan'
                    : 'Simpan Akses Cepat'
                }}
            </x-ui.button>
        </div>
    </div>

    <script
        type="application/json"
        data-quick-link-selected-media
    >@json($selectedMediaPayload)</script>
</div>

@push('scripts')
    <script
        src="{{ asset('js/admin/pages/quick-links.js') }}"
    ></script>
@endpush
