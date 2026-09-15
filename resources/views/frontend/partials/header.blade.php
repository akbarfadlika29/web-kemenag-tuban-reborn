@php
    $siteName = filled($siteSettings['site_name'] ?? null)
        ? $siteSettings['site_name']
        : 'WEB PPID';
@endphp

<header class="site-header">
    <div class="container site-header-inner">
        <a
            href="{{ route('home') }}"
            class="site-brand"
            aria-label="{{ $siteName }}"
        >
            <div
                class="site-brand-mark {{ $siteLogo ? 'has-image' : '' }}"
                aria-hidden="true"
            >
                @if ($siteLogo)
                    <img
                        src="{{ Storage::disk($siteLogo->disk)->url($siteLogo->path) }}"
                        alt=""
                    >
                @else
                    <span>
                        W
                    </span>
                @endif
            </div>

            <div class="site-brand-copy">
                <strong>
                    {{ $siteName }}
                </strong>
            </div>
        </a>

        <div class="site-header-actions">
            <form
                action="{{ route('search.index') }}"
                method="GET"
                class="site-header-search"
                role="search"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ request()->routeIs('search.index') ? request('search') : '' }}"
                    placeholder="Cari informasi..."
                    aria-label="Cari informasi"
                    autocomplete="off"
                >

                <button
                    type="submit"
                    aria-label="Cari informasi"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle
                            cx="11"
                            cy="11"
                            r="7"
                        />

                        <path
                            d="m20 20-3.5-3.5"
                        />
                    </svg>
                </button>
            </form>

            <button
                type="button"
                class="nav-toggle"
                data-nav-toggle
                aria-label="Buka menu navigasi"
                aria-expanded="false"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>
