<section {{ $attributes->class(['public-page-header']) }}>
    <div class="container">
        <x-frontend.breadcrumb :title="$title" />

        <h1>{{ $title }}</h1>

        @isset($description)
            @if (trim((string) $description) !== '')
                <p>{{ $description }}</p>
            @endif
        @endisset

        @isset($actions)
            <div class="public-page-header-actions">
                {{ $actions }}
            </div>
        @endisset
    </div>
</section>