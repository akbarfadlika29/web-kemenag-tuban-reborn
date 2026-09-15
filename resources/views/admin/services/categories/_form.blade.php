@csrf

@php
    $isActive = old(
        'is_active',
        $category->is_active ?? true
    );
@endphp

<x-ui.card>
    <div class="service-category-form">
        <x-form.input
            id="name"
            name="name"
            label="Nama Kategori"
            :value="$category->name ?? null"
            required
            maxlength="255"
            placeholder="Contoh: Pelayanan Haji dan Umrah"
        />

        <x-form.textarea
            id="description"
            name="description"
            label="Deskripsi"
            :value="$category->description ?? null"
            rows="5"
            maxlength="2000"
            placeholder="Jelaskan kelompok layanan secara singkat..."
        />

        <x-form.input
            id="sort_order"
            name="sort_order"
            label="Urutan"
            type="number"
            min="0"
            :value="$category->sort_order ?? 0"
            required
        />

        <input
            type="hidden"
            name="is_active"
            value="0"
        >

        <label class="service-category-switch">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked((bool) $isActive)
            >

            <span>
                <strong>Kategori Aktif</strong>

                <small>
                    Kategori aktif dapat dipilih saat membuat atau mengedit layanan.
                </small>
            </span>
        </label>

        <div class="service-category-form-actions">
            <x-ui.button
                type="submit"
                variant="primary"
            >
                {{ isset($category)
                    ? 'Simpan Perubahan'
                    : 'Simpan Kategori'
                }}
            </x-ui.button>

            <x-ui.button
                :href="route('admin.service-categories.index')"
                variant="secondary"
            >
                Batal
            </x-ui.button>
        </div>
    </div>
</x-ui.card>

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/service-categories.css') }}"
    >
@endpush

