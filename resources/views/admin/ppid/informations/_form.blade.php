@csrf

@php
    $selectedCategoryId = old(
        'category_id',
        $information->category_id ?? null
    );

    $selectedUnitId = old(
        'unit_id',
        $information->unit_id ?? null
    );

    $selectedClassification = old(
        'classification',
        $information->classification ?? 'berkala'
    );

    $selectedStatus = old(
        'status',
        $information->status ?? 'draft'
    );

    $selectedAvailability = old(
        'availability',
        $information->availability ?? 'online'
    );

    $selectedAccessLevel = old(
        'access_level',
        $information->access_level ?? 'public'
    );

    $selectedInformationForm = old(
        'information_form',
        $information->information_form ?? null
    );

    $selectedRetentionUnit = old(
        'retention_unit',
        $information->retention_unit ?? null
    );

    $isFeatured = old(
        'is_featured',
        $information->is_featured ?? false
    );

    $oldDocuments = old('documents');

    if ($oldDocuments !== null) {
        $initialDocuments = collect($oldDocuments)
            ->filter(fn ($document) => !empty($document['media_id']))
            ->values();
    } elseif (isset($information)) {
        $initialDocuments = $information->documents
            ->map(function ($document) {
                return [
                    'media_id' => $document->media_id,
                    'title' => $document->title,
                    'description' => $document->description,
                    'version' => $document->version,
                    'document_status' => $document->document_status,
                    'document_date' => optional($document->document_date)->format('Y-m-d'),
                    'sort_order' => $document->sort_order,
                    'is_primary' => $document->is_primary,
                    'media' => $document->media,
                ];
            })
            ->values();
    } else {
        $initialDocuments = collect();
    }
@endphp

<div
    class="ppid-editor-root"
    data-ppid-editor
    data-media-picker-url="{{ route('admin.ppid-informations.media-picker') }}"
>
    <div class="ppid-editor-layout">

        <main class="ppid-editor-main">

            <section class="ppid-editor-section">
                <span class="ppid-kicker">
                    Informasi Publik
                </span>

                <h2 class="ppid-section-title">
                    Identitas Informasi
                </h2>

                <x-form.input
                    id="title"
                    name="title"
                    label="Judul Informasi"
                    :value="$information->title ?? null"
                    required
                    maxlength="255"
                />

                <div class="ppid-slug-preview">
                    <span>/ppid/informasi/</span>

                    <input
                        id="slug"
                        type="text"
                        value="{{ old('slug', $information->slug ?? '') }}"
                        readonly
                        tabindex="-1"
                    >
                </div>

                <div class="ppid-two-column">
                    <x-form.select
                        id="category_id"
                        name="category_id"
                        label="Kategori PPID"
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
                        id="classification"
                        name="classification"
                        label="Klasifikasi Informasi"
                        required
                    >
                        <option
                            value="berkala"
                            @selected($selectedClassification === 'berkala')
                        >
                            Informasi Berkala
                        </option>

                        <option
                            value="serta_merta"
                            @selected($selectedClassification === 'serta_merta')
                        >
                            Informasi Serta Merta
                        </option>

                        <option
                            value="setiap_saat"
                            @selected($selectedClassification === 'setiap_saat')
                        >
                            Informasi Setiap Saat
                        </option>
<option value="dikecualikan"
    @selected($selectedClassification === 'dikecualikan')>
    Informasi Dikecualikan
</option>
                    </x-form.select>
                </div>

                <x-form.textarea
                    id="excerpt"
                    name="excerpt"
                    label="Ringkasan"
                    :value="$information->excerpt ?? null"
                    rows="3"
                    maxlength="1000"
                />

                <x-form.textarea
                    id="description"
                    name="description"
                    label="Deskripsi Informasi"
                    :value="$information->description ?? null"
                    rows="7"
                    maxlength="10000"
                />
            </section>

            <section class="ppid-editor-section">
                <span class="ppid-kicker">
                    Administrasi
                </span>

                <h2 class="ppid-section-title">
                    Metadata Administratif
                </h2>

                <div class="ppid-two-column">
                    <x-form.input
                        id="information_holder"
                        name="information_holder"
                        label="Penguasa Informasi"
                        :value="$information->information_holder ?? null"
                        maxlength="255"
                        placeholder="Unit/bagian yang menguasai informasi"
                    />

                    <x-form.input
                        id="person_in_charge"
                        name="person_in_charge"
                        label="Penanggung Jawab"
                        :value="$information->person_in_charge ?? null"
                        maxlength="255"
                    />
                </div>

                <div class="ppid-three-column">
                    <x-form.select
                        id="information_form"
                        name="information_form"
                        label="Bentuk Informasi"
                    >
                        <option value="">
                            — Pilih —
                        </option>

                        <option
                            value="digital"
                            @selected($selectedInformationForm === 'digital')
                        >
                            Digital
                        </option>

                        <option
                            value="print"
                            @selected($selectedInformationForm === 'print')
                        >
                            Cetak
                        </option>

                        <option
                            value="both"
                            @selected($selectedInformationForm === 'both')
                        >
                            Digital & Cetak
                        </option>
                    </x-form.select>

                    <x-form.input
                        id="information_format"
                        name="information_format"
                        label="Format Informasi"
                        :value="$information->information_format ?? null"
                        maxlength="255"
                        placeholder="PDF, XLSX, DOCX..."
                    />

                    <x-form.input
                        id="publication_media"
                        name="publication_media"
                        label="Media Publikasi"
                        :value="$information->publication_media ?? null"
                        maxlength="255"
                        placeholder="Website, papan informasi..."
                    />
                </div>

                <div class="ppid-three-column">
                    <x-form.select
                        id="availability"
                        name="availability"
                        label="Ketersediaan"
                        required
                    >
                        <option
                            value="online"
                            @selected($selectedAvailability === 'online')
                        >
                            Online
                        </option>

                        <option
                            value="by_request"
                            @selected($selectedAvailability === 'by_request')
                        >
                            Melalui Permohonan
                        </option>

                        <option
                            value="both"
                            @selected($selectedAvailability === 'both')
                        >
                            Online & Permohonan
                        </option>
                    </x-form.select>

                    <x-form.select
                        id="access_level"
                        name="access_level"
                        label="Tingkat Akses"
                        required
                    >
                        <option
                            value="public"
                            @selected($selectedAccessLevel === 'public')
                        >
                            Publik
                        </option>

                        <option
                            value="limited"
                            @selected($selectedAccessLevel === 'limited')
                        >
                            Terbatas
                        </option>
                    </x-form.select>

                    <x-form.input
                        id="year"
                        name="year"
                        label="Tahun Informasi"
                        type="number"
                        min="1900"
                        max="2100"
                        :value="$information->year ?? now()->year"
                    />
                </div>

                <div class="ppid-two-column">
                    <x-form.input
                        id="document_number"
                        name="document_number"
                        label="Nomor Dokumen"
                        :value="$information->document_number ?? null"
                        maxlength="255"
                    />

                    <x-form.input
                        id="document_date"
                        name="document_date"
                        label="Tanggal Dokumen"
                        type="date"
                        :value="old(
                            'document_date',
                            isset($information) && $information->document_date
                                ? $information->document_date->format('Y-m-d')
                                : null
                        )"
                    />
                </div>

                <div class="ppid-two-column">
                    <x-form.input
                        id="effective_date"
                        name="effective_date"
                        label="Tanggal Berlaku"
                        type="date"
                        :value="old(
                            'effective_date',
                            isset($information) && $information->effective_date
                                ? $information->effective_date->format('Y-m-d')
                                : null
                        )"
                    />

                    <x-form.input
                        id="last_reviewed_at"
                        name="last_reviewed_at"
                        label="Terakhir Ditinjau"
                        type="datetime-local"
                        :value="old(
                            'last_reviewed_at',
                            isset($information) && $information->last_reviewed_at
                                ? $information->last_reviewed_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />
                </div>

                <div class="ppid-retention-grid">
                    <x-form.input
                        id="retention_period"
                        name="retention_period"
                        label="Jangka Retensi"
                        type="number"
                        min="1"
                        :value="$information->retention_period ?? null"
                    />

                    <x-form.select
                        id="retention_unit"
                        name="retention_unit"
                        label="Satuan Retensi"
                    >
                        <option value="">
                            — Pilih —
                        </option>

                        <option
                            value="day"
                            @selected($selectedRetentionUnit === 'day')
                        >
                            Hari
                        </option>

                        <option
                            value="month"
                            @selected($selectedRetentionUnit === 'month')
                        >
                            Bulan
                        </option>

                        <option
                            value="year"
                            @selected($selectedRetentionUnit === 'year')
                        >
                            Tahun
                        </option>

                        <option
                            value="permanent"
                            @selected($selectedRetentionUnit === 'permanent')
                        >
                            Permanen
                        </option>
                    </x-form.select>
                </div>

                <x-form.textarea
                    id="legal_basis"
                    name="legal_basis"
                    label="Dasar Hukum"
                    :value="$information->legal_basis ?? null"
                    rows="5"
                    maxlength="5000"
                    placeholder="Peraturan, keputusan, regulasi terkait..."
                />

                <x-form.textarea
                    id="notes"
                    name="notes"
                    label="Catatan Administratif"
                    :value="$information->notes ?? null"
                    rows="4"
                    maxlength="5000"
                />
            </section>

            <section class="ppid-editor-section">
                <div class="ppid-section-heading">
                    <div>
                        <span class="ppid-kicker">
                            Dokumen
                        </span>

                        <h2 class="ppid-section-title">
                            Dokumen Informasi
                        </h2>
                    </div>

                    <x-ui.button
                        type="button"
                        id="ppid-add-document"
                        variant="primary"
                        size="sm"
                    >
                        Tambah Dokumen
                    </x-ui.button>
                </div>

                <p class="ppid-documents-help" data-ppid-sk-help>
    <strong>Khusus Informasi Dikecualikan:</strong>
    isi judul dengan nama SK, pilih tahun dan unit pengelola,
    lalu pilih satu PDF SK sebagai dokumen utama.
    Daftar informasi cukup berada di dalam PDF tersebut.
    Untuk menampilkannya di website, gunakan akses Publik,
    ketersediaan Online, dan status Terbit.
</p>
<p class="ppid-documents-help">
                    Dokumen tidak wajib diunggah. Untuk ketersediaan Online, tambahkan dokumen publik agar dapat dibuka pengunjung. Seret dokumen untuk mengubah urutan. Satu dokumen dapat
                    ditetapkan sebagai dokumen utama.
                </p>

                @error('documents')
                    <div class="ui-form-error">
                        {{ $message }}
                    </div>
                @enderror

                <div
                    id="ppid-documents-empty"
                    class="ppid-documents-empty"
                    @if ($initialDocuments->isNotEmpty()) hidden @endif
                >
                    <strong>Belum ada dokumen</strong>
                    <span>
                        Pilih dokumen dari Media Manager.
                    </span>
                </div>

                <div
                    id="ppid-documents"
                    class="ppid-documents"
                >
                    @foreach ($initialDocuments as $index => $document)
                        @php
                            $media = $document['media'] ?? null;

                            if (!$media && isset($information)) {
                                $media = $information->documents
                                    ->firstWhere(
                                        'media_id',
                                        (int) $document['media_id']
                                    )
                                    ?->media;
                            }

                            $isPrimaryDocument = filter_var(
                                $document['is_primary'] ?? false,
                                FILTER_VALIDATE_BOOLEAN
                            );
                        @endphp

                        <article
                            class="ppid-document-card"
                            data-ppid-document
                            data-media-id="{{ $document['media_id'] }}"
                        >
                            <div class="ppid-document-drag">
                                ⋮⋮
                            </div>

                            <div class="ppid-document-icon">
                                {{ strtoupper($media?->extension ?: 'FILE') }}
                            </div>

                            <div class="ppid-document-content">
                                <div class="ppid-document-heading">
                                    <div>
                                        <strong>
                                            {{ $media?->title ?: $media?->original_name ?: 'Dokumen PPID' }}
                                        </strong>

                                        <span>
                                            ID Media: {{ $document['media_id'] }}
                                        </span>
                                    </div>

                                    <div class="ppid-document-heading-actions">
                                        <button
                                            type="button"
                                            class="ppid-primary-button {{ $isPrimaryDocument ? 'is-primary' : '' }}"
                                            data-ppid-primary
                                        >
                                            {{ $isPrimaryDocument
                                                ? 'Dokumen Utama'
                                                : 'Jadikan Utama'
                                            }}
                                        </button>

                                        <button
                                            type="button"
                                            class="ppid-document-remove"
                                            data-ppid-document-remove
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>

                                <input
                                    type="hidden"
                                    name="documents[{{ $index }}][media_id]"
                                    value="{{ $document['media_id'] }}"
                                    data-ppid-media-id
                                >

                                <input
                                    type="hidden"
                                    name="documents[{{ $index }}][sort_order]"
                                    value="{{ $index }}"
                                    data-ppid-sort-order
                                >

                                <input
                                    type="hidden"
                                    name="documents[{{ $index }}][is_primary]"
                                    value="{{ $isPrimaryDocument ? 1 : 0 }}"
                                    data-ppid-is-primary
                                >

                                <div class="ppid-document-grid">
                                    <div>
                                        <label>Judul Dokumen</label>

                                        <input
                                            type="text"
                                            class="ui-control"
                                            name="documents[{{ $index }}][title]"
                                            value="{{ $document['title'] ?? '' }}"
                                            maxlength="255"
                                            data-ppid-document-title
                                        >
                                    </div>

                                    <div>
                                        <label>Versi</label>

                                        <input
                                            type="text"
                                            class="ui-control"
                                            name="documents[{{ $index }}][version]"
                                            value="{{ $document['version'] ?? '' }}"
                                            maxlength="50"
                                            placeholder="Contoh: 1.0"
                                            data-ppid-version
                                        >
                                    </div>

                                    <div>
                                        <label>Status Dokumen</label>

                                        <select
                                            class="ui-control"
                                            name="documents[{{ $index }}][document_status]"
                                            data-ppid-document-status
                                        >
                                            <option
                                                value="active"
                                                @selected(
                                                    ($document['document_status'] ?? 'active') === 'active'
                                                )
                                            >
                                                Aktif
                                            </option>

                                            <option
                                                value="superseded"
                                                @selected(
                                                    ($document['document_status'] ?? '') === 'superseded'
                                                )
                                            >
                                                Digantikan
                                            </option>

                                            <option
                                                value="expired"
                                                @selected(
                                                    ($document['document_status'] ?? '') === 'expired'
                                                )
                                            >
                                                Kedaluwarsa
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label>Tanggal Dokumen</label>

                                        <input
                                            type="date"
                                            class="ui-control"
                                            name="documents[{{ $index }}][document_date]"
                                            value="{{ $document['document_date'] ?? '' }}"
                                            data-ppid-document-date
                                        >
                                    </div>
                                </div>

                                <div class="ppid-document-description">
                                    <label>
                                        Keterangan
                                    </label>

                                    <textarea
                                        class="ui-control"
                                        name="documents[{{ $index }}][description]"
                                        rows="2"
                                        maxlength="2000"
                                        data-ppid-document-description
                                    >{{ $document['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="ppid-editor-section">
                <span class="ppid-kicker">
                    SEO
                </span>

                <h2 class="ppid-section-title">
                    Optimasi Mesin Pencari
                </h2>

                <div class="ppid-two-column">
                    <x-form.input
                        id="meta_title"
                        name="meta_title"
                        label="Meta Title"
                        :value="$information->meta_title ?? null"
                        maxlength="255"
                    />

                    <x-form.textarea
                        id="meta_description"
                        name="meta_description"
                        label="Meta Description"
                        :value="$information->meta_description ?? null"
                        rows="4"
                        maxlength="500"
                    />
                </div>

                <div class="ppid-seo-preview">
                    <strong id="ppid-seo-title">
                        Judul informasi
                    </strong>

                    <span>
                        web-ppid.id/ppid/informasi/<span id="ppid-seo-slug">slug-informasi</span>
                    </span>

                    <p id="ppid-seo-description">
                        Ringkasan informasi akan tampil di sini.
                    </p>
                </div>
            </section>
        </main>

        <aside class="ppid-editor-sidebar">
            <div class="ppid-settings-panel">

                <section class="ppid-settings-section">
                    <div class="ppid-settings-heading">
                        <span>01</span>

                        <div>
                            <h3>Publikasi</h3>
                            <p>Status informasi publik.</p>
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
                            isset($information) && $information->published_at
                                ? $information->published_at->format('Y-m-d\TH:i')
                                : null
                        )"
                    />

                    <div
                        id="ppid-publication-hint"
                        class="ppid-hint"
                    ></div>

                    <input
                        type="hidden"
                        name="is_featured"
                        value="0"
                    >

                    <label class="ppid-checkbox-row">
                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            @checked($isFeatured)
                        >

                        <span>
                            <strong>Informasi Unggulan</strong>
                            <small>
                                Prioritaskan informasi ini.
                            </small>
                        </span>
                    </label>

                    <div class="ppid-save-area">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            block
                        >
                            {{ isset($information)
                                ? 'Simpan Perubahan'
                                : 'Simpan Informasi'
                            }}
                        </x-ui.button>
                    </div>
                </section>

                <section class="ppid-settings-section">
                    <div class="ppid-settings-heading">
                        <span>02</span>

                        <div>
                            <h3>Unit Kerja</h3>
                            <p>Unit pemilik informasi.</p>
                        </div>
                    </div>

                    <x-form.select
                        id="unit_id"
                        name="unit_id"
                        label="Unit Kerja"
                        required
                    >
                        <option value="">
                            — Pilih Unit —
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
            </div>
        </aside>
    </div>

    @include('admin.ppid.informations._media-picker')
</div>

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/ppid-information-editor.css') }}"
    >
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <script src="{{ asset('js/modules/ppid-information-editor.js') }}"></script>
@endpush