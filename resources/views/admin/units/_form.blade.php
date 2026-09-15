@csrf

<div class="units-form-layout">
    <div class="units-form-main">
        {{-- IDENTITAS --}}
        <section class="units-form-section">
            <header class="units-form-section-header">
                <div>
                    <span class="units-form-kicker">
                        Informasi Utama
                    </span>

                    <h2>
                        Identitas Unit
                    </h2>

                    <p>
                        Informasi dasar yang digunakan untuk mengenali unit kerja.
                    </p>
                </div>
            </header>

            <div class="units-form-section-body">
                <x-form.input
                    name="name"
                    label="Nama Unit"
                    :value="$unit->name ?? null"
                    required
                />

                <div class="units-field-grid">
                    <x-form.input
                        name="short_name"
                        label="Nama Singkat"
                        :value="$unit->short_name ?? null"
                        placeholder="Contoh: Seksi PHU"
                    />

                    <x-form.input
                        name="code"
                        label="Kode Unit"
                        :value="$unit->code ?? null"
                        placeholder="Kode internal unit"
                    />
                </div>

                <x-form.input
                    name="slug"
                    label="Slug"
                    :value="$unit->slug ?? null"
                    readonly
                    required
                    help="Slug dibuat otomatis berdasarkan nama unit."
                />

                <x-form.select
                    name="type"
                    label="Jenis Unit"
                    required
                >
                    <option value="">
                        -- Pilih Jenis Unit --
                    </option>

                    <option
                        value="kankemenag"
                        @selected(old('type', $unit->type ?? '') === 'kankemenag')
                    >
                        Kantor Kementerian Agama
                    </option>

                    <option
                        value="subbag"
                        @selected(old('type', $unit->type ?? '') === 'subbag')
                    >
                        Subbag
                    </option>

                    <option
                        value="seksi"
                        @selected(old('type', $unit->type ?? '') === 'seksi')
                    >
                        Seksi
                    </option>

                    <option
                        value="kua"
                        @selected(old('type', $unit->type ?? '') === 'kua')
                    >
                        KUA
                    </option>

                    <option
                        value="satker"
                        @selected(old('type', $unit->type ?? '') === 'satker')
                    >
                        Satker
                    </option>

                    <option
                        value="lainnya"
                        @selected(old('type', $unit->type ?? '') === 'lainnya')
                    >
                        Lainnya
                    </option>
                </x-form.select>

                <x-form.textarea
                    name="description"
                    label="Deskripsi"
                    :value="$unit->description ?? null"
                    rows="4"
                    placeholder="Jelaskan fungsi atau tugas unit kerja..."
                />
            </div>
        </section>

        {{-- KONTAK --}}
        <section class="units-form-section">
            <header class="units-form-section-header">
                <div>
                    <span class="units-form-kicker">
                        Kontak
                    </span>

                    <h2>
                        Informasi Kontak
                    </h2>

                    <p>
                        Alamat dan kanal komunikasi resmi unit kerja.
                    </p>
                </div>
            </header>

            <div class="units-form-section-body">
                <x-form.textarea
                    name="address"
                    label="Alamat"
                    :value="$unit->address ?? null"
                    rows="3"
                    placeholder="Alamat lengkap unit kerja..."
                />

                <div class="units-field-grid">
                    <x-form.input
                        name="phone"
                        label="Telepon"
                        :value="$unit->phone ?? null"
                        placeholder="Nomor telepon"
                    />

                    <x-form.input
                        name="email"
                        label="Email"
                        type="email"
                        :value="$unit->email ?? null"
                        placeholder="unit@example.go.id"
                    />
                </div>

                <x-form.input
                    name="website"
                    label="Website"
                    type="url"
                    :value="$unit->website ?? null"
                    placeholder="https://..."
                />
            </div>
        </section>

        {{-- PIMPINAN --}}
        <section class="units-form-section">
            <header class="units-form-section-header">
                <div>
                    <span class="units-form-kicker">
                        Penanggung Jawab
                    </span>

                    <h2>
                        Pimpinan Unit
                    </h2>

                    <p>
                        Informasi pimpinan atau penanggung jawab unit kerja.
                    </p>
                </div>
            </header>

            <div class="units-form-section-body">
                <div class="units-field-grid">
                    <x-form.input
                        name="head_name"
                        label="Nama Pimpinan"
                        :value="$unit->head_name ?? null"
                        placeholder="Nama lengkap"
                    />

                    <x-form.input
                        name="head_title"
                        label="Jabatan Pimpinan"
                        :value="$unit->head_title ?? null"
                        placeholder="Nama jabatan"
                    />
                </div>
            </div>
        </section>
    </div>

    {{-- SIDEBAR FORM --}}
    <aside class="units-form-sidebar">
        <section class="units-form-section">
            <header class="units-form-section-header">
                <div>
                    <span class="units-form-kicker">
                        Struktur
                    </span>

                    <h2>
                        Posisi Unit
                    </h2>

                    <p>
                        Atur posisi unit dalam hierarki organisasi.
                    </p>
                </div>
            </header>

            <div class="units-form-section-body">
                <x-form.select
                    name="parent_id"
                    label="Induk Unit"
                    help="Kosongkan jika unit berada pada level paling atas."
                >
                    <option value="">
                        -- Tanpa Induk / Level Utama --
                    </option>

                    @foreach ($parents as $parent)
                        @include('admin.units._parent_option', [
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
                        $unit->sort_order ?? ($nextSortOrders['root'] ?? 1)
                    )"
                    required
                    help="Urutan mengikuti induk yang dipilih, tetapi dapat diubah."
                />
            </div>
        </section>

        <section class="units-form-section">
            <header class="units-form-section-header">
                <div>
                    <span class="units-form-kicker">
                        Publikasi
                    </span>

                    <h2>
                        Status Unit
                    </h2>

                    <p>
                        Tentukan apakah unit dapat digunakan oleh sistem.
                    </p>
                </div>
            </header>

            <div class="units-form-section-body">
                <div class="units-status-box">
                    <div class="units-status-copy">
                        <strong>
                            Unit Aktif
                        </strong>

                        <span>
                            Unit aktif dapat dipilih sebagai pemilik konten dan informasi.
                        </span>
                    </div>

                    <x-form.switch
                        name="is_active"
                        label="Unit Aktif"
                        :checked="old(
                            'is_active',
                            $unit->is_active ?? true
                        )"
                    />
                </div>
            </div>
        </section>

        <div class="units-form-note">
            <strong>
                Catatan struktur
            </strong>

            <p>
                Posisi unit juga dapat diubah dengan drag-and-drop pada halaman daftar Unit Kerja.
            </p>
        </div>
    </aside>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput =
        document.getElementById('name');

    const slugInput =
        document.getElementById('slug');

    const parentInput =
        document.getElementById('parent_id');

    const sortOrderInput =
        document.getElementById('sort_order');

    function generateSlug(value) {
        return value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    if (nameInput && slugInput) {
        nameInput.addEventListener(
            'input',
            function () {
                slugInput.value =
                    generateSlug(nameInput.value);
            }
        );
    }

    if (parentInput && sortOrderInput) {
        const nextSortOrders =
            @json($nextSortOrders ?? []);

        const originalParent =
            @json(
                isset($unit)
                    ? (string) ($unit->parent_id ?? '')
                    : null
            );

        const originalSortOrder =
            @json(
                isset($unit)
                    ? $unit->sort_order
                    : null
            );

        parentInput.addEventListener(
            'change',
            function () {
                const parentId =
                    this.value || 'root';

                @if (isset($unit))
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
