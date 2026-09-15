<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Admin') - WEB PPID
    </title>

    <link
        rel="stylesheet"
        href="{{ asset('css/admin/app.css') }}"
    >

    @stack('styles')

    {{-- WEB PPID: shared admin visual system --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/premium.css') }}?v={{ filemtime(public_path('css/admin/premium.css')) }}"
    >
    {{-- Custom admin dialogs --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/popup.css') }}?v={{ filemtime(public_path('css/admin/popup.css')) }}"
    >
    <script src="{{ asset('js/admin/popup.js') }}?v={{ filemtime(public_path('js/admin/popup.js')) }}"></script>

    {{-- Selection and bulk deletion for existing delete forms --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/bulk-delete.css') }}?v={{ filemtime(public_path('css/admin/bulk-delete.css')) }}"
    >
    <script
        defer
        src="{{ asset('js/admin/bulk-delete.js') }}?v={{ filemtime(public_path('js/admin/bulk-delete.js')) }}"
    ></script>

    {{-- Responsive sidebar controls --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/sidebar-control.css') }}?v={{ filemtime(public_path('css/admin/sidebar-control.css')) }}"
    >
    <script
        defer
        src="{{ asset('js/admin/sidebar-control.js') }}?v={{ filemtime(public_path('js/admin/sidebar-control.js')) }}"
    ></script>

    {{-- Collapsible sidebar menu groups --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/sidebar-accordion.css') }}?v={{ filemtime(public_path('css/admin/sidebar-accordion.css')) }}"
    >
    <script
        defer
        src="{{ asset('js/admin/sidebar-accordion.js') }}?v={{ filemtime(public_path('js/admin/sidebar-accordion.js')) }}"
    ></script>
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/button-consistency.css') }}?v={{ filemtime(public_path('css/admin/button-consistency.css')) }}"
    >
</head>

<body>
    <div class="admin-wrapper">
        @include('admin.partials.sidebar')

        <div class="admin-shell">
            @include('admin.partials.navbar')

            <main class="content">
                <div class="admin-content-inner">
                    @include('admin.partials.flash')

                    @yield('content')
                </div>
            </main>

            @include('admin.partials.footer')
        </div>
    </div>

    <button
        type="button"
        class="admin-sidebar-overlay"
        data-admin-sidebar-overlay
        aria-label="Tutup menu navigasi"
        tabindex="-1"
    ></button>

    <x-admin.media-picker />

    <script src="{{ asset('js/admin-ui.js') }}?v={{ filemtime(public_path('js/admin-ui.js')) }}"></script>
    <script src="{{ asset('js/admin/components/content-editor.js') }}"></script>

    <script src="{{ asset('js/admin/components/tree-dnd.js') }}?v={{ filemtime(public_path('js/admin/components/tree-dnd.js')) }}"></script>

    <script src="{{ asset('js/admin/media-picker.js') }}"></script>

    @stack('scripts')
</body>
</html>
