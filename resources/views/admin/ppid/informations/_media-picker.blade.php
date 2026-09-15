<div
    id="ppid-media-picker"
    class="ppid-modal"
    hidden
    aria-hidden="true"
>
    <div
        class="ppid-modal-backdrop"
        data-ppid-media-close
    ></div>

    <div
        class="ppid-modal-dialog"
        role="dialog"
        aria-modal="true"
    >
        <div class="ppid-modal-header">
            <div>
                <span class="ppid-kicker">
                    Pustaka Media
                </span>

                <h3>
                    Pilih Dokumen
                </h3>
            </div>

            <button
                type="button"
                class="ppid-modal-close"
                data-ppid-media-close
            >
                ×
            </button>
        </div>

        <div class="ppid-modal-body">
            <div class="ppid-media-toolbar">
                <input
                    type="search"
                    id="ppid-media-search"
                    class="ui-control"
                    placeholder="Cari dokumen..."
                >

                <select
                    id="ppid-media-unit"
                    class="ui-control"
                >
                    <option value="">
                        Semua Unit
                    </option>

                    <option value="global">
                        Media Global
                    </option>

                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ppid-media-selection">
                <span>
                    <strong id="ppid-selected-count">0</strong>
                    dokumen dipilih
                </span>

                <x-ui.button
                    type="button"
                    id="ppid-confirm-documents"
                    variant="primary"
                    size="sm"
                >
                    Tambahkan Dokumen
                </x-ui.button>
            </div>

            <div
                id="ppid-media-loading"
                class="ppid-media-message"
                hidden
            >
                Memuat media...
            </div>

            <div
                id="ppid-media-empty"
                class="ppid-media-message"
                hidden
            >
                Tidak ada media ditemukan.
            </div>

            <div
                id="ppid-media-grid"
                class="ppid-media-grid"
            ></div>

            <div class="ppid-media-more">
                <x-ui.button
                    type="button"
                    id="ppid-media-load-more"
                    variant="secondary"
                    hidden
                >
                    Tampilkan Lebih Banyak
                </x-ui.button>
            </div>
        </div>
    </div>
</div>