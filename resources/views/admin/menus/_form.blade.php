@csrf

<div class="menus-form-layout">
    <div class="menus-form-main">
        <section class="menus-form-section">
            <header class="menus-form-section-header">
                <div>
                    <span class="menus-form-kicker">
                        Informasi Utama
                    </span>

                    <h2>
                        Identitas Menu
                    </h2>

                    <p>
                        Tentukan nama yang akan ditampilkan pada navigasi website.
                    </p>
                </div>
            </header>

            <div class="menus-form-section-body">
                <x-form.input
                    name="label"
                    label="Nama Menu"
                    :value="$menu->label ?? null"
                    required
                    placeholder="Contoh: Profil"
                />

                <div class="menus-field-grid">
                    <x-form.select
                        name="location"
                        label="Lokasi Menu"
                        required
                    >
                        <option
                            value="header"
                            @selected(
                                old(
                                    'location',
                                    $menu->location ?? 'header'
                                ) === 'header'
                            )
                        >
                            Header / Navbar
                        </option>

                        <option
                            value="footer"
                            @selected(
                                old(
                                    'location',
                                    $menu->location ?? 'header'
                                ) === 'footer'
                            )
                        >
                            Footer
                        </option>
                    </x-form.select>

                    <x-form.select
                        name="type"
                        label="Jenis Tujuan"
                        required
                    >
                        <option
                            value="group"
                            @selected(
                                old(
                                    'type',
                                    $menu->type ?? 'page'
                                ) === 'group'
                            )
                        >
                            Grup / Judul Menu
                        </option>

                        <option
                            value="page"
                            @selected(
                                old(
                                    'type',
                                    $menu->type ?? 'page'
                                ) === 'page'
                            )
                        >
                            Halaman CMS
                        </option>

                        <option
                            value="route"
                            @selected(
                                old(
                                    'type',
                                    $menu->type ?? ''
                                ) === 'route'
                            )
                        >
                            Route Internal
                        </option>

                        <option
                            value="url"
                            @selected(
                                old(
                                    'type',
                                    $menu->type ?? ''
                                ) === 'url'
                            )
                        >
                            URL Manual / Eksternal
                        </option>
                    </x-form.select>
                </div>
            </div>
        </section>

        <section class="menus-form-section">
            <header class="menus-form-section-header">
                <div>
                    <span class="menus-form-kicker">
                        Tujuan
                    </span>

                    <h2>
                        Tautan Menu
                    </h2>

                    <p>
                        Untuk grup/judul menu tidak diperlukan tautan. Selain itu, pilih halaman CMS, route aplikasi, atau masukkan URL secara manual.
                    </p>
                </div>
            </header>

            <div class="menus-form-section-body">
                <div data-menu-target="page">
                    <x-form.select
                        name="page_id"
                        label="Halaman"
                        help="Pilih halaman CMS yang akan dibuka ketika menu diklik."
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
                                        $menu->page_id ?? null
                                    ) == $page->id
                                )
                            >
                                {{ $page->title }}
                            </option>
                        @endforeach
                    </x-form.select>
                </div>

                <div data-menu-target="route">
                    @php
                        $selectedRoute = old(
                            'route_name',
                            $menu->route_name ?? null
                        );

                        $availableRouteNames = collect(
                            $frontendRoutes
                        )->pluck('name');
                    @endphp

                    <x-form.select
                        name="route_name"
                        label="Route Internal"
                        help="Hanya route frontend tanpa parameter yang dapat digunakan langsung sebagai menu."
                    >
                        <option value="">
                            -- Pilih Route Internal --
                        </option>

                        @if (
                            $selectedRoute
                            && !$availableRouteNames->contains(
                                $selectedRoute
                            )
                        )
                            <option
                                value="{{ $selectedRoute }}"
                                selected
                            >
                                {{ $selectedRoute }}
                                — route tidak tersedia
                            </option>
                        @endif

                        @foreach ($frontendRoutes as $frontendRoute)
                            <option
                                value="{{ $frontendRoute['name'] }}"
                                @selected(
                                    $selectedRoute
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

                <div data-menu-target="url">
                    <x-form.input
                        name="url"
                        label="URL"
                        :value="$menu->url ?? null"
                        placeholder="https://... atau /alamat-halaman"
                        help="Dapat berupa URL eksternal atau path internal."
                    />
                </div>
            </div>
        </section>
    </div>

    <aside class="menus-form-sidebar">
        <section class="menus-form-section">
            <header class="menus-form-section-header">
                <div>
                    <span class="menus-form-kicker">
                        Struktur
                    </span>

                    <h2>
                        Posisi Menu
                    </h2>

                    <p>
                        Tentukan parent dan urutan menu dalam navigasi.
                    </p>
                </div>
            </header>

            <div class="menus-form-section-body">
                <x-form.select
                    name="parent_id"
                    label="Induk Menu"
                    help="Kosongkan untuk menjadikannya menu utama."
                >
                    <option value="">
                        -- Menu Utama --
                    </option>

                    @foreach ($parents as $parent)
                        @include('admin.menus._parent_option', [
                            'parent' => $parent,
                            'level' => 0,
                            'excludedIds' => $excludedIds ?? [],
                        ])
                    @endforeach
                </x-form.select>

                <x-form.input
                    name="sort_order"
                    label="Urutan"
                    type="number"
                    min="1"
                    :value="old(
                        'sort_order',
                        $menu->sort_order ?? ($nextSortOrders['root'] ?? 1)
                    )"
                    required
                    help="Angka lebih kecil ditampilkan terlebih dahulu."
                />
            </div>
        </section>

        <section class="menus-form-section">
            <header class="menus-form-section-header">
                <div>
                    <span class="menus-form-kicker">
                        Pengaturan
                    </span>

                    <h2>
                        Status Menu
                    </h2>

                    <p>
                        Atur publikasi dan perilaku tautan menu.
                    </p>
                </div>
            </header>

            <div class="menus-form-section-body">
                <div class="menus-setting-row">
                    <div>
                        <strong>
                            Menu Aktif
                        </strong>

                        <span>
                            Menu aktif dapat ditampilkan pada frontend.
                        </span>
                    </div>

                    <x-form.switch
                        name="is_active"
                        label="Menu Aktif"
                        :checked="old(
                            'is_active',
                            $menu->is_active ?? true
                        )"
                    />
                </div>

                <div class="menus-setting-divider"></div>

                <div class="menus-setting-row">
                    <div>
                        <strong>
                            Buka Tab Baru
                        </strong>

                        <span>
                            Cocok untuk tautan menuju website eksternal.
                        </span>
                    </div>

                    <x-form.switch
                        name="open_in_new_tab"
                        label="Buka Tab Baru"
                        :checked="old(
                            'open_in_new_tab',
                            $menu->open_in_new_tab ?? false
                        )"
                    />
                </div>
            </div>
        </section>

        <div class="menus-form-note">
            <strong>
                Tentang submenu
            </strong>

            <p>
                Untuk membuat submenu, pilih menu lain pada bagian Induk Menu.
            </p>
        </div>
    </aside>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeInput =
        document.getElementById('type');

    const targetBlocks =
        document.querySelectorAll(
            '[data-menu-target]'
        );

    const parentInput =
        document.getElementById('parent_id');

    const sortOrderInput =
        document.getElementById('sort_order');

    function updateTargetFields() {
        if (!typeInput) {
            return;
        }

        targetBlocks.forEach(function (block) {
            const isActive =
                block.dataset.menuTarget ===
                typeInput.value;

            block.hidden = !isActive;
        });
    }

    if (typeInput) {
        updateTargetFields();

        typeInput.addEventListener(
            'change',
            updateTargetFields
        );
    }

    if (parentInput && sortOrderInput) {
        const nextSortOrders =
            @json($nextSortOrders ?? []);

        const originalParent =
            @json(
                isset($menu)
                    ? (string) ($menu->parent_id ?? '')
                    : null
            );

        const originalSortOrder =
            @json(
                isset($menu)
                    ? $menu->sort_order
                    : null
            );

        parentInput.addEventListener(
            'change',
            function () {
                const parentId =
                    this.value || 'root';

                @if (isset($menu))
                    if (
                        this.value ===
                        originalParent
                    ) {
                        sortOrderInput.value =
                            originalSortOrder;

                        return;
                    }
                @endif

                sortOrderInput.value =
                    nextSortOrders[parentId] ?? 1;
            }
        );
    }
});
</script>
@endpush
