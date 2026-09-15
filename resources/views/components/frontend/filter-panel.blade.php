@props(['label' => 'Cari dan saring informasi'])

<section class="public-filter-panel" aria-label="{{ $label }}">
    <div class="public-filter-panel-body">
        {{ $slot }}
    </div>
</section>