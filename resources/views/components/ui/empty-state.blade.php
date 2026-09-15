@props([
    'title' => 'Belum ada data',
    'description' => null,
])

<div {{ $attributes->class(['ui-empty']) }}>
    @isset($icon)
        <div class="ui-empty-icon">
            {{ $icon }}
        </div>
    @endisset

    <h3>{{ $title }}</h3>

    @if ($description)
        <p>{{ $description }}</p>
    @endif

    @isset($action)
        <div class="ui-empty-action">
            {{ $action }}
        </div>
    @endisset
</div>