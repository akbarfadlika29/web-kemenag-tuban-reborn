<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('error_title') — WEB PPID</title>

    <style>
        *{box-sizing:border-box}
        body{
            margin:0;min-height:100vh;min-height:100dvh;
            display:grid;place-items:center;padding:24px;
            background:#f7f9f8;color:#26352e;
            font-family:"Segoe UI",Arial,sans-serif;line-height:1.65
        }
        .error-card{
            width:100%;max-width:580px;padding:40px;
            background:#fff;border:1px solid #dfe7e2;
            border-radius:18px;box-shadow:0 12px 40px rgb(38 53 46 / 5%)
        }
        .brand{margin:0 0 28px;color:#647169;font-size:13px;font-weight:600}
        .error-icon{
            width:56px;height:56px;display:grid;place-items:center;
            border-radius:14px;background:#eff5f2;color:#247052
        }
        .error-icon svg{width:28px;height:28px}
        .code{margin:20px 0 6px;color:#647169;font-size:13px;font-weight:600}
        h1{margin:0 0 12px;font-size:clamp(24px,4vw,30px);line-height:1.3}
        .description{margin:0;color:#647169;font-size:15px}
        .actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:28px}
        .button{
            display:inline-flex;align-items:center;justify-content:center;
            gap:8px;min-height:44px;padding:10px 16px;
            border:1px solid #d1ddd5;border-radius:8px;
            background:#fff;color:#247052;text-decoration:none;
            font-size:14px;font-weight:600
        }
        .button-primary{background:#247052;border-color:#247052;color:#fff}
        .button:hover{background:#eff5f2}
        .button-primary:hover{background:#1d5b43}
        .button:focus-visible{outline:3px solid #247052;outline-offset:3px}
        .button svg{width:17px;height:17px;flex-shrink:0}
        .note{margin:26px 0 0;padding-top:18px;border-top:1px solid #edf1ee;color:#647169;font-size:13px}
        @media(max-width:480px){
            .error-card{padding:28px 22px}
            .actions{flex-direction:column}
        }
    </style>
</head>
<body>
@php
    /*
     * Halaman error tidak memakai layout admin atau site settings.
     * Jika pemeriksaan akses ikut gagal, tetap tampilkan navigasi publik.
     */
    $isAdminError = request()->is('admin', 'admin/*');
    $dashboardUrl = null;
    $loginUrl = null;

    try {
        $actor = auth()->user();

        if (
            $actor
            && \Illuminate\Support\Facades\Route::has('admin.dashboard')
            && app(\App\Services\Access\AccessService::class)
                ->allows($actor, 'dashboard.view')
        ) {
            $dashboardUrl = route('admin.dashboard');
        }

        if (!$actor && \Illuminate\Support\Facades\Route::has('admin.login')) {
            $loginUrl = route('admin.login');
        }
    } catch (\Throwable $error) {
        $dashboardUrl = null;
        $loginUrl = null;
    }
@endphp

<main class="error-card" aria-labelledby="error-title">
    <p class="brand">WEB PPID · Kementerian Agama</p>

    <div class="error-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 3 9 16H3L12 3Z"/>
            <path d="M12 9v4M12 16h.01"/>
        </svg>
    </div>

    <p class="code">Kode @yield('error_code')</p>
    <h1 id="error-title">@yield('error_title')</h1>
    <p class="description">@yield('error_description')</p>

    <nav class="actions" aria-label="Navigasi pemulihan">
        @php
            $switchAccountUrl = null;

            try {
                if (
                    auth()->check()
                    && \Illuminate\Support\Facades\Route::has('admin.logout')
                ) {
                    $switchAccountUrl = route('admin.logout');
                }
            } catch (\Throwable $exception) {
                $switchAccountUrl = null;
            }
        @endphp

        @if ($isAdminError && $switchAccountUrl)
            <form
                method="POST"
                action="{{ $switchAccountUrl }}"
                data-error-switch-account
                style="margin:0"
            >
                @csrf
                <button
                    type="submit"
                    class="button button-primary"
                    style="font-family:inherit;cursor:pointer;width:100%"
                >
                    Keluar &amp; Ganti Akun
                </button>
            </form>
        @endif

        @if ($isAdminError && $dashboardUrl)
            <a class="button button-primary" href="{{ $dashboardUrl }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <path d="m10 6-6 6 6 6M4 12h16"/>
                </svg>
                Kembali ke Dasbor
            </a>
        @elseif ($isAdminError && $loginUrl)
            <a class="button button-primary" href="{{ $loginUrl }}">
                Masuk ke Admin
            </a>
        @endif

        <a class="button" href="{{ url('/') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="m3 10 9-7 9 7M5 9v12h14V9M9 21v-8h6v8"/>
            </svg>
            Beranda Website
        </a>
    </nav>

    <p class="note">@yield('error_note')</p>
</main>
</body>
</html>
