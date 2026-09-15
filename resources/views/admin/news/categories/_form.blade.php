@csrf

<x-form.input
    name="name"
    label="Nama Kategori"
    :value="$category->name ?? null"
    required
    placeholder="Contoh: Kegiatan Kantor"
/>

<x-form.input
    name="slug"
    label="Slug"
    :value="$category->slug ?? null"
    help="Kosongkan untuk membuat slug otomatis dari nama kategori."
/>

<x-form.textarea
    name="description"
    label="Deskripsi"
    :value="$category->description ?? null"
    rows="4"
/>

<x-form.input
    name="sort_order"
    label="Urutan"
    type="number"
    min="0"
    :value="$category->sort_order ?? $nextSortOrder ?? 1"
    required
/>

<x-form.switch
    name="is_active"
    label="Kategori Aktif"
    :checked="old(
        'is_active',
        $category->is_active ?? true
    )"
/>