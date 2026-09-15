@props([
    'variant' => 'info',
    'dismissible' => false,
])

<div
    {{ $attributes->class([
        'ui-alert',
        'ui-alert-' . $variant,
    ]) }}
    role="alert"
>
    <div class="ui-alert-content">
        {{ $slot }}
    </div>

    @if ($dismissible)
        <button
            type="button"
            class="ui-alert-close"
            data-alert-close
            aria-label="Tutup"
        >
            ×
        </button>
    @endif
</div>