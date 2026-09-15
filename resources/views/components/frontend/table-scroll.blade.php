@props(['label' => 'Tabel informasi'])

<div class="public-table-scroll"
     role="region"
     aria-label="{{ $label }}"
     tabindex="0">
    {{ $slot }}
</div>