@props([
    'title',
    'description' => null,
])

<header {{ $attributes->class(['ui-page-header']) }}>
    <div>
        <h1>{{ $title }}</h1>

        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="ui-page-actions">
            {{ $actions }}
        </div>
    @endisset
</header>