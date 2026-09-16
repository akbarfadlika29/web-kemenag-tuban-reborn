<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    @php
        $defaultTitle = filled($siteSettings['default_meta_title'] ?? null)
            ? $siteSettings['default_meta_title']
            : ($siteSettings['institution_name'] ?? 'WEB PPID');

        $defaultDescription = filled($siteSettings['default_meta_description'] ?? null)
            ? $siteSettings['default_meta_description']
            : (
                'Website resmi '
                . ($siteSettings['institution_name'] ?? 'WEB PPID')
                . '.'
            );
    @endphp

    <title>
        @yield('title', $defaultTitle)
    </title>

    <meta
        name="description"
        content="@yield('meta_description', $defaultDescription)"
    >

    @include('frontend.partials.base-styles')

    @stack('styles')

    {{-- WEB PPID: frontend visual system --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/premium.css') }}?v={{ filemtime(public_path('css/frontend/premium.css')) }}"
    >
    @include('frontend.partials.shared-styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/themes/emerald-soft.css') }}?v={{ filemtime(public_path('css/frontend/themes/emerald-soft.css')) }}"
    >
@include('frontend.partials.experience-style')
<link rel="stylesheet"
      href="{{ asset('css/frontend/layout/navigation-state.css') }}?v={{ filemtime(public_path('css/frontend/layout/navigation-state.css')) }}">
<link rel="stylesheet"
      href="{{ asset('css/frontend/components/page-header.css') }}?v={{ filemtime(public_path('css/frontend/components/page-header.css')) }}">
<link rel="stylesheet"
      href="{{ asset('css/frontend/components/filter-panel.css') }}?v={{ filemtime(public_path('css/frontend/components/filter-panel.css')) }}">
<link rel="stylesheet"
      href="{{ asset('css/frontend/components/table-scroll.css') }}?v={{ filemtime(public_path('css/frontend/components/table-scroll.css')) }}">
</head>

<body class="ppid-public ppid-consistent ppid-soft">
    @include('frontend.partials.global-loading')
    <a href="#public-main" class="public-skip-link">Langsung ke konten utama</a>
    @include('frontend.partials.header')

    @include('frontend.partials.navbar')

    <main id="public-main" tabindex="-1">
        @yield('content')
    </main>

    @include('frontend.partials.footer')

    <script defer src="{{ asset('js/frontend/components/navigation.js') }}?v={{ filemtime(public_path('js/frontend/components/navigation.js')) }}"></script>

    @stack('scripts')
</body>
</html>
