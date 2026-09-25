@csrf

@php
    $selectedCategoryId = old(
        'category_id',
        $service->category_id ?? null
    );

    $selectedUnitId = old(
        'unit_id',
        $service->unit_id ?? null
    );

    $selectedStatus = old(
        'status',
        $service->status ?? 'draft'
    );

    $selectedChannel = old(
        'service_channel',
        $service->service_channel ?? 'offline'
    );

    $selectedCoverId = old(
        'cover_media_id',
        $service->cover_media_id ?? null
    );

    $isFree = old(
        'is_free',
        $service->is_free ?? true
    );

    $isFeatured = old(
        'is_featured',
        $service->is_featured ?? false
    );
@endphp

<div
    class="service-editor-root"
    data-service-editor
    data-media-picker-url="{{ route('admin.services.media-picker') }}"
    data-selected-cover-id="{{ $selectedCoverId }}"
>
    <div class="service-editor-layout">

        <main class="service-editor-main">

            <section class="service-editor-section">
                <span class="service-kicker">
                    Informasi Layanan
                </span>

                <h2 class="service-section-title">
                    Informasi Utama
                </h2>

                <x-form.input
                    id="title"
                    name="title"
                    label="Nama Layanan"
                    :value="$service->title ?? null"
                    required
                    maxlength="255"
                    placeholder="Contoh: Pelayanan Pendaftaran Haji"
                />

                <div class="service-slug-preview">
                    <span>/layanan/</span>

                    <input
                        type="text"
                        id="slug"
                        value="{{ old('slug', $service->slug ?? '') }}"
                        readonly
                        tabindex="-1"
                    >
                </div>

                <div class="service-two-column">
                    <x-form.select
                        id="category_id"
                        name="category_id"
                        label="Kategori Layanan"
                        required
                    >
                        <option value="">
                            — Pilih Kategori —
                        </option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(
                                    (string) $selectedCategoryId ===
                                    (string) $category->id
                                )
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-form.select
                        id="service_channel"
                        name="service_channel"
                        label="Kanal Layanan"
                        required
                    >
                        <option
                            value="offline"
                            @selected($selectedChannel === 'offline')
                        >
                            Offline
                        </option>

                        <option
                            value="online"
                            @selected($selectedChannel === 'online')
                        >
                            Online
                        </option>

                        <option
                            value="hybrid"
                            @selected($selectedChannel === 'hybrid')
                        >
                            Online & Offline
                        </option>
                    </x-form.select>
                </div>

                <x-form.textarea
                    id="excerpt"
                    name="excerpt"
                    label="Ringkasan"
                    :value="$service->excerpt ?? null"
                    rows="3"
                    maxlength="1000"
                />

                <x-form.textarea
                    id="description"
                    name="description"
                    label="Deskripsi Layanan"
                    :value="$service->description ?? null"
                    rows="7"
                    maxlength="10000"
                />
            </section>

            <section class="service-editor-section">
                <span class="service-kicker">
                    Standar Pelayanan
                </span>

                <h2 class="service-section-title">
                    Persyaratan & Prosedur
                </h2>

                <x-form.textarea
                    id="requirements"
                    name="requirements"
                    label="Persyaratan"
                    :value="$service->requirements ?? null"
                    rows="8"
                    maxlength="10000"
                    placeholder="Tuliskan persyaratan layanan..."
                />

                <x-form.textarea
                    id="procedure"
                    name="procedure"
                    label="Prosedur / Alur Pelayanan"
                    :value="$service->procedure ?? null"
                    rows="8"
                    maxlength="10000"
                    placeholder="Tuliskan tahapan pelayanan..."
                />

                <div class="service-two-column">
                    <x-form.input
                        id="completion_time"
                        name="completion_time"
                        label="Waktu Penyelesaian"
                        :value="$service->completion_time ?? null"
                        maxlength="255"
                        placeholder="Contoh: 1 hari kerja"
                    />

                    <x-form.textarea
                        id="service_output"
                        name="service_output"
                        label="Produk / Hasil Layanan"
                        :value="$service->service_output ?? null"
                        rows="3"
                        maxlength="5000"
                    />
                </div>
            </section>

            <section class="service-editor-section">
                <span class="service-kicker">
                    Tarif
                </span>

                <h2 class="service-section-title">
                    Biaya Layanan
                </h2>

                <input
                    type="hidden"
                    name="is_free"
                    value="0"
                >

                <label class="service-checkbox-row">
                    <input
                        type="checkbox"
                        name="is_free"
                        id="is_free"
                        value="1"
                        @checked($isFree)
                    >

                    <span>
                        <strong>Gratis / Tidak Dipungut Biaya</strong>

                        <small>
                            Aktifkan jika layanan tidak memiliki tarif.
                        </small>
                    </span>
                </label>

                <div
                    id="service-fee-section"
                    class="service-fee-section"
                >
                    <x-form.textarea
                        id="fee_description"
                        name="fee_description"
                        label="Keterangan Biaya / Tarif"
                        :value="$service->fee_description ?? null"
                        rows="5"
                        maxlength="3000"
                        placeholder="Contoh: Sesuai ketentuan peraturan yang berlaku..."
                    />
                </div>
            </section>

            <section class="service-editor-section">
                <span class="service-kicker">
                    Kanal Pelayanan
                </span>

                <h2 class="service-section-title">
                    Akses & Lokasi
                </h2>

                <div
                    id="service-online-fields"
                    class="service-channel-section"
                >
                    <x-form.input
                        id="service_url"
                        name="service_url"
                        label="URL Layanan Online"
                        type="url"
                        :value="$service->service_url ?? null"
                        maxlength="2048"
                        placeholder="https://..."
                    />
                </div>

                <div
                    id="service-offline-fields"
                    class="service-channel-section"
                >
                    <div class="service-two-column">
                        <x-form.input
                            id="service_location"
                            name="service_location"
                            label="Lokasi Pelayanan"
                            :value="$service->service_location ?? null"
                            maxlength="500"
                        />

                        <x-form.input
                            id="service_hours"
                            name="service_hours"
                            label="Jam Pelayanan"
                            :value="$service->service_hours ?? null"
                            maxlength="500"
                            placeholder="Senin–Jumat, 08.00–16.00"
                        />
                    </div>
                </div>

                <div class="service-three-column">
                    <x-form.input
                        id="contact_name"
                        name="contact_name"
                        label="Kontak / Petugas"
                        :value="$service->contact_name ?? null"
                        maxlength="255"
                    />

                    <x-form.input
                        id="contact_phone"
                        name="contact_phone"
                        label="Telepon"
                        :value="$service->contact_phone ?? null"
                        maxlength="100"
                    />

                    <x-form.input
                        id="contact_email"
                        name="contact_email"
                        label="Email"
                        type="email"
                        :value="$service->contact_email ?? null"
                        maxlength="255"
                    />
                </div>
            </section>

            <section class="service-editor-section">
                <span class="service-kicker">
                    Regulasi
                </span>

                <h2 class="service-section-title">
                    Dasar Hukum
                </h2>

                <x-form.textarea
                    id="legal_basis"
                    name="legal_basis"
                    label="Dasar Hukum"
                    :value="$service->legal_basis ?? null"
                    rows="7"
                    maxlength="10000"
                    placeholder="Peraturan, keputusan, atau regulasi terkait layanan..."
                />
            </section>

            <section class="service-editor-section">
                <span class="service-kicker">
                    SEO
                </span>

                <h2 class="service-section-title">
                    Optimasi Mesin Pencari
                </h2>

                <div class="service-two-column">
                    <x-form.input
                        id="meta_title"
                        name="meta_title"
                        label="Meta Title"
                        :value="$service->meta_title ?? null"
                        maxlength="255"
                    />

                    <x-form.textarea
                        id="meta_description"
                        name="meta_description"
                        label="Meta Description"
                        :value="$service->meta_description ?? null"
                        rows="4"
                        maxlength="500"
                    />
                </div>

                <div class="service-seo-preview">
                    <strong id="service-seo-title">
                        Nama layanan
                    </strong>

                    <span>
                        web-ppid.id/layanan/<span id="service-seo-slug">slug-layanan</span>
                    </span>

                    <p id="service-seo-description">
                        Ringkasan layanan akan tampil di sini.
                    </p>
                </div>
            </section>
        </main>

        <aside class="service-editor-sidebar">
            <div class="service-settings-panel">

                <section class="service-settings-section">
                    <div class="service-settings-heading">
                        <span>01</span>

                        <div>
                            <h3>Publikasi</h3>
                            <p>Status layanan.</p>
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
                            isset($service) && $service->published_at
                                ? $service->published_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <div
                        id="service-publication-hint"
                        class="service-hint"
                    ></div>

                    <input
                        type="hidden"
                        name="is_featured"
                        value="0"
                    >

                    <label class="service-checkbox-row">
                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            @checked($isFeatured)
                        >

                        <span>
                            <strong>Layanan Unggulan</strong>

                            <small>
                                Prioritaskan layanan ini.
                            </small>
                        </span>
                    </label>

                    <div class="service-save-area">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            block
                        >
                            {{ isset($service)
                                ? 'Simpan Perubahan'
                                : 'Simpan Layanan'
                            }}
                        </x-ui.button>
                    </div>
                </section>

                <section class="service-settings-section">
                    <div class="service-settings-heading">
                        <span>02</span>

                        <div>
                            <h3>Unit Kerja</h3>
                            <p>Pengelola layanan.</p>
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

                        @foreach ($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected(
                                    (string) $selectedUnitId ===
                                    (string) $unit->id
                                )
                            >
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </x-form.select>
                </section>

                <section class="service-settings-section">
                    <div class="service-settings-heading">
                        <span>03</span>

                        <div>
                            <h3>Cover Layanan</h3>
                            <p>Gambar dari Media Manager.</p>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="cover_media_id"
                        id="cover_media_id"
                        value="{{ $selectedCoverId }}"
                    >

                    <div class="service-cover-box">
                        <div
                            id="service-cover-empty"
                            class="service-cover-empty"
                        >
                            <strong>
                                Belum ada cover
                            </strong>

                            <span>
                                Pilih gambar dari Media Manager
                            </span>
                        </div>

                        <div
                            id="service-cover-preview"
                            hidden
                        >
                            <img
                                id="service-cover-image"
                                class="service-cover-image"
                                src=""
                                alt=""
                            >

                            <div class="service-cover-info">
                                <strong id="service-cover-title"></strong>
                                <span id="service-cover-unit"></span>
                            </div>
                        </div>
                    </div>

                    <x-ui.button
                        type="button"
                        id="service-cover-picker"
                        variant="secondary"
                        block
                    >
                        Pilih Cover
                    </x-ui.button>
                </section>
            </div>
        </aside>
    </div>

    @include('admin.services._media-picker')
</div>

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/service-editor.css') }}"
    >
@endpush

@push('scripts')
    <script src="{{ asset('js/admin/pages/service-editor.js') }}"></script>
@endpush