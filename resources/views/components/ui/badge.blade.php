@props([
    'variant' => 'neutral',
    'size' => 'md',
])

<span
    {{ $attributes->class([
        'ui-badge',
        'ui-badge-' . $variant,
        'ui-badge-' . $size,
    ]) }}
>
    {{ $slot }}
</span>