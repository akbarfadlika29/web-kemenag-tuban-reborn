@props([
    'size' => 'md',
])

<span
    {{ $attributes->class([
        'ui-spinner',
        'ui-spinner-' . $size,
    ]) }}
    role="status"
    aria-label="Memuat"
></span>