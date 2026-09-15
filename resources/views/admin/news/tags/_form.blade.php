@csrf

<x-form.input
    name="name"
    label="Nama Tag"
    :value="$tag->name ?? null"
    required
    placeholder="Contoh: Haji"
/>

<x-form.input
    name="slug"
    label="Slug"
    :value="$tag->slug ?? null"
    help="Kosongkan agar slug dibuat otomatis dari nama tag."
/>