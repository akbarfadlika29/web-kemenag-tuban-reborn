<div
    class="admin-media-picker"
    data-admin-media-picker
    data-list-url="{{ request()->routeIs('admin.regulations.create', 'admin.regulations.edit') ? route('admin.regulations.media.index') : route('admin.media.picker.index') }}"
    data-upload-url="{{ request()->routeIs('admin.regulations.create', 'admin.regulations.edit') ? route('admin.regulations.media.upload') : route('admin.media.picker.upload') }}"
    hidden
    aria-hidden="true"
>
    <button
        type="button"
        class="admin-media-picker-backdrop"
        data-media-picker-close
        aria-label="Tutup Media Manager"
    ></button>

    <section
        class="admin-media-picker-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="admin-media-picker-title"
    >
        <header class="admin-media-picker-header">
            <div class="admin-media-picker-heading">
                <span>
                    Media Manager
                </span>

                <h2 id="admin-media-picker-title">
                    Pilih Media
                </h2>

                <p data-media-picker-description>
                    Pilih aset dari pustaka media atau unggah file baru.
                </p>
            </div>

            <button
                type="button"
                class="admin-media-picker-close"
                data-media-picker-close
                aria-label="Tutup"
            >
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M6 6l12 12"></path>
                    <path d="M18 6 6 18"></path>
                </svg>
            </button>
        </header>

        <div class="admin-media-picker-tabs">
            <button
                type="button"
                class="admin-media-picker-tab is-active"
                data-media-picker-tab="library"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                    <circle cx="8.5" cy="9" r="1.5"></circle>
                    <path d="m5 17 4.5-4.5 3 3 2-2 4.5 3.5"></path>
                </svg>

                Pustaka Media
            </button>

            <button
                type="button"
                class="admin-media-picker-tab"
                data-media-picker-tab="upload"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 16V4"></path>
                    <path d="m7 9 5-5 5 5"></path>
                    <path d="M5 20h14"></path>
                </svg>

                Upload Baru
            </button>
        </div>

        <div class="admin-media-picker-content">

            {{-- PUSTAKA --}}
            <div
                class="admin-media-picker-panel"
                data-media-picker-panel="library"
            >
                <div class="admin-media-picker-toolbar">
                    <div class="admin-media-picker-search">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>

                        <input
                            type="search"
                            placeholder="Cari nama, judul, atau file..."
                            data-media-picker-search
                        >
                    </div>

                    <select
                        class="ui-control"
                        data-media-picker-type
                    >
                        <option value="all">
                            Semua Jenis
                        </option>

                        <option value="image">
                            Gambar
                        </option>

                        <option value="document">
                            Dokumen
                        </option>

                        <option value="video">
                            Video
                        </option>

                        <option value="audio">
                            Audio
                        </option>

                        <option value="other">
                            Lainnya
                        </option>
                    </select>

                    <select
                        class="ui-control"
                        data-media-picker-unit
                    >
                        <option value="">
                            Semua Unit
                        </option>

                        <option value="global">
                            Media Global
                        </option>
                    </select>
                </div>

                <div
                    class="admin-media-picker-message"
                    data-media-picker-loading
                    hidden
                >
                    <span class="admin-media-picker-spinner"></span>

                    Memuat media...
                </div>

                <div
                    class="admin-media-picker-message"
                    data-media-picker-empty
                    hidden
                >
                    Tidak ada media ditemukan.
                </div>

                <div
                    class="admin-media-picker-grid"
                    data-media-picker-grid
                ></div>

                <div class="admin-media-picker-load-more">
                    <button
                        type="button"
                        class="ui-button ui-button-secondary"
                        data-media-picker-more
                        hidden
                    >
                        Tampilkan Lebih Banyak
                    </button>
                </div>
            </div>

            {{-- UPLOAD --}}
            <div
                class="admin-media-picker-panel"
                data-media-picker-panel="upload"
                hidden
            >
                <form
                    class="admin-media-upload-form"
                    data-media-upload-form
                >
                    @csrf

                    <input
                        type="hidden"
                        name="is_public"
                        value="1"
                    >

                    <input
                        type="hidden"
                        name="expected_type"
                        value="all"
                        data-media-upload-type
                    >

                    <label
                        class="admin-media-dropzone"
                        data-media-dropzone
                    >
                        <input
                            type="file"
                            name="file"
                            data-media-upload-file
                            required
                        >

                        <span class="admin-media-dropzone-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 16V5"></path>
                                <path d="m7 10 5-5 5 5"></path>
                                <path d="M5 20h14"></path>
                            </svg>
                        </span>

                        <strong>
                            Pilih file atau seret ke sini
                        </strong>

                        <span data-media-upload-help>
                            Maksimal 50 MB.
                        </span>

                        <small data-media-upload-filename>
                            Belum ada file dipilih
                        </small>
                    </label>

                    <div class="admin-media-upload-fields">
                        <div class="ui-form-group">
                            <label class="ui-form-label">
                                Unit Kerja
                            </label>

                            <select
                                name="unit_id"
                                class="ui-control"
                                data-media-upload-unit
                            >
                                <option value="">
                                    Media Global
                                </option>
                            </select>
                        </div>

                        <div class="ui-form-group">
                            <label class="ui-form-label">
                                Judul Media
                            </label>

                            <input
                                type="text"
                                name="title"
                                class="ui-control ui-control-md"
                                placeholder="Opsional"
                            >
                        </div>

                        <div
                            class="ui-form-group"
                            data-media-upload-alt-group
                        >
                            <label class="ui-form-label">
                                Alt Text
                            </label>

                            <input
                                type="text"
                                name="alt_text"
                                class="ui-control ui-control-md"
                                placeholder="Deskripsi singkat gambar"
                            >
                        </div>
                    </div>

                    <div
                        class="admin-media-upload-error"
                        data-media-upload-error
                        hidden
                    ></div>

                    <div class="admin-media-upload-actions">
                        <button
                            type="submit"
                            class="ui-button ui-button-primary"
                            data-media-upload-submit
                        >
                            Upload & Gunakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <footer class="admin-media-picker-footer">
            <div class="admin-media-picker-selection">
                <strong data-media-picker-selected-count>
                    0
                </strong>

                <span>
                    media dipilih
                </span>
            </div>

            <div class="admin-media-picker-footer-actions">
                <button
                    type="button"
                    class="ui-button ui-button-secondary"
                    data-media-picker-cancel
                >
                    Batal
                </button>

                <button
                    type="button"
                    class="ui-button ui-button-primary"
                    data-media-picker-confirm
                    disabled
                >
                    Gunakan Media
                </button>
            </div>
        </footer>
    </section>
</div>
