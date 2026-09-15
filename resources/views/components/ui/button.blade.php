@props([
    'href' => null,
    'variant' => 'secondary',
    'size' => 'md',
    'type' => 'button',
    'block' => false,
    'disabled' => false,
])

@php
    $classes = [
        'ui-btn',
        'ui-btn-' . $variant,
        'ui-btn-' . $size,
        $block ? 'ui-btn-block' : null,
        $disabled ? 'is-disabled' : null,
    ];
@endphp

@if ($href)
    <a
        href="{{ $disabled ? '#' : $href }}"
        {{ $attributes->class($classes) }}
        @if($disabled)
            aria-disabled="true"
            tabindex="-1"
        @endif
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->class($classes) }}
        @disabled($disabled)
    >
        {{ $slot }}
    </button>
@endif