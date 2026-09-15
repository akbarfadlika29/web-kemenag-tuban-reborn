@props([
    'padding' => true,
])

<section {{ $attributes->class(['ui-card']) }}>
    @isset($header)
        <div class="ui-card-header">
            {{ $header }}
        </div>
    @endisset

    <div @class(['ui-card-body' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="ui-card-footer">
            {{ $footer }}
        </div>
    @endisset
</section>