<div
    id="service-media-picker"
    class="service-modal"
    hidden
    aria-hidden="true"
>
    <div
        class="service-modal-backdrop"
        data-service-media-close
    ></div>

    <div
        class="service-modal-dialog"
        role="dialog"
        aria-modal="true"
    >
        <div class="service-modal-header">
            <div>
                <span class="service-kicker">
                    Pustaka Media
                </span>

                <h3>
                    Pilih Cover Layanan
                </h3>
            </div>

            <button
                type="button"
                class="service-modal-close"
                data-service-media-close
            >
                ×
            </button>
        </div>

        <div class="service-modal-body">
            <div class="service-media-toolbar">
                <input
                    type="search"
                    id="service-media-search"
                    class="ui-control"
                    placeholder="Cari gambar..."
                >

                <select
                    id="service-media-unit"
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

            <div
                id="service-media-loading"
                class="service-media-message"
                hidden
            >
                Memuat media...
            </div>

            <div
                id="service-media-empty"
                class="service-media-message"
                hidden
            >
                Tidak ada gambar ditemukan.
            </div>

            <div
                id="service-media-grid"
                class="service-media-grid"
            ></div>

            <div class="service-media-more">
                <x-ui.button
                    type="button"
                    id="service-media-load-more"
                    variant="secondary"
                    hidden
                >
                    Tampilkan Lebih Banyak
                </x-ui.button>
            </div>
        </div>
    </div>
</div>