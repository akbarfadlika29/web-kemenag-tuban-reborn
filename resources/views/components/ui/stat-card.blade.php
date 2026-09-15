@props([
    'label',
    'value',
    'description' => null,
])

<div {{ $attributes->class(['ui-stat-card']) }}>
    <div class="ui-stat-top">
        <div>
            <div class="ui-stat-label">{{ $label }}</div>
            <div class="ui-stat-value">{{ $value }}</div>
        </div>

        @isset($icon)
            <div class="ui-stat-icon">
                {{ $icon }}
            </div>
        @endisset
    </div>

    @if ($description)
        <div class="ui-stat-description">
            {{ $description }}
        </div>
    @endif

    @isset($footer)
        <div class="ui-stat-footer">
            {{ $footer }}
        </div>
    @endisset
</div>