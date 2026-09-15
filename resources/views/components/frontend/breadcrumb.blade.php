@props([
    'title',
    'parentLabel' => null,
    'parentUrl' => null,
])

<nav {{ $attributes->class(['public-breadcrumb']) }}
     aria-label="Jejak navigasi">
    <a href="{{ route('home') }}">
        <svg width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="1.7"
             stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true">
            <path d="m3 10 9-7 9 7"/>
            <path d="M5 9v12h14V9M9 21v-8h6v8"/>
        </svg>
        Beranda
    </a>

    @if ($parentLabel && $parentUrl)
        <span aria-hidden="true">/</span>
        <a href="{{ $parentUrl }}">{{ $parentLabel }}</a>
    @endif

    <span aria-hidden="true">/</span>
    <span aria-current="page">{{ $title }}</span>
</nav>