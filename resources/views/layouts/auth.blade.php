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
        @yield('title', 'Login Admin') - WEB PPID
    </title>

    <link
        rel="stylesheet"
        href="{{ asset('css/admin/auth.css') }}"
    >

    @stack('styles')
</head>

<body class="admin-auth-page">
    @yield('content')

    @stack('scripts')
</body>
</html>
