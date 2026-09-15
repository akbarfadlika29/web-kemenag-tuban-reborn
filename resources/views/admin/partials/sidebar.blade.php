{{-- PPID_SIDEBAR_PERMISSION_V1 --}}
@php
    $sidebarPermissionService = app(\App\Services\Access\AccessService::class);
    $sidebarPermissionActor = auth()->user();

    $sidebarRouteAllowed = function (string $name) use (
        $sidebarPermissionService,
        $sidebarPermissionActor
    ): bool {
        if (!$sidebarPermissionActor || !$sidebarPermissionActor->is_active) {
            return false;
        }

        $required = \App\Support\AdminRoutePermission::required($name);

        if ($required === null) {
            return false;
        }

        foreach ($required as $permission) {
            if (!$sidebarPermissionService->allows(
                $sidebarPermissionActor,
                $permission
            )) {
                return false;
            }
        }

        if (
            in_array($name, [
                'admin.users.index',
                'admin.access.index',
                'admin.access-roles.index',
            ], true)
            && !in_array(
                $sidebarPermissionService->role($sidebarPermissionActor),
                ['super-admin', 'admin'],
                true
            )
        ) {
            return false;
        }

        return true;
    };
@endphp
<aside
    class="sidebar"
    data-admin-sidebar
>
    <div class="sidebar-brand">
        <div
            class="sidebar-brand-mark"
            aria-hidden="true"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M3 21h18" />
                <path d="M5 21V9l7-5 7 5v12" />
                <path d="M9 13h2" />
                <path d="M13 13h2" />
                <path d="M9 17h2" />
                <path d="M13 17h2" />
            </svg>
        </div>

        <div class="sidebar-brand-copy">
            <strong>
                WEB PPID
            </strong>

            <span>
                Kemenag Kabupaten Tuban
            </span>
        </div>
    </div>

    <nav
        class="sidebar-nav"
        aria-label="Navigasi Admin"
    >
<span class="sidebar-section-title">Ringkasan</span>
@if ($sidebarRouteAllowed('admin.dashboard'))
<a
            href="{{ route('admin.dashboard') }}"
            class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            @if (request()->routeIs('admin.dashboard'))
                aria-current="page"
            @endif
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>
            </span>

            <span class="sidebar-link-label">Dasbor</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.content-statistics.index'))
<a href="{{ route('admin.content-statistics.index') }}" class="sidebar-link {{ request()->routeIs('admin.content-statistics.index') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h16M7 16v-5M12 16V4M17 16V8"/></svg></span><span class="sidebar-link-label">Statistik Konten</span></a>
@endif
<span class="sidebar-section-title">Informasi PPID</span>
@if ($sidebarRouteAllowed('admin.ppid-informations.index'))
<a
            href="{{ route('admin.ppid-informations.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.ppid-informations.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 11v5" />
                    <path d="M12 8h.01" />
                </svg>
            </span>

            <span class="sidebar-link-label">Informasi Publik</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.ppid-categories.index'))
<a
            href="{{ route('admin.ppid-categories.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.ppid-categories.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="4" y="4" width="6" height="6" rx="1" />
                    <rect x="14" y="4" width="6" height="6" rx="1" />
                    <rect x="4" y="14" width="6" height="6" rx="1" />
                    <rect x="14" y="14" width="6" height="6" rx="1" />
                </svg>
            </span>

            <span class="sidebar-link-label">Kategori PPID</span>
        </a>
@endif

        <span class="sidebar-section-title">Regulasi</span>

        @if ($sidebarRouteAllowed('admin.regulations.index'))
<a
            href="{{ route('admin.regulations.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.regulations.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.regulations.*')) aria-current="page" @endif
        >
            <span class="sidebar-link-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 2v6h6M8 13h8M8 17h6"/>
                </svg>
            </span>
            <span class="sidebar-link-label">Daftar Regulasi</span>
        </a>
@endif

        @if ($sidebarRouteAllowed('admin.regulation-types.index'))
<a
            href="{{ route('admin.regulation-types.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.regulation-types.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.regulation-types.*')) aria-current="page" @endif
        >
            <span class="sidebar-link-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 7h7l2 2h9v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <path d="M3 7V5a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2"/>
                </svg>
            </span>
            <span class="sidebar-link-label">Jenis Regulasi</span>
        </a>
@endif

<span class="sidebar-section-title">Publikasi</span>
@if ($sidebarRouteAllowed('admin.news.index'))
<a
            href="{{ route('admin.news.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M5 4h14v16H5z" />
                    <path d="M8 8h8" />
                    <path d="M8 12h8" />
                    <path d="M8 16h5" />
                </svg>
            </span>

            <span class="sidebar-link-label">Berita</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.news-categories.index'))
<a
            href="{{ route('admin.news-categories.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.news-categories.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M3 6h6l2 2h10v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                </svg>
            </span>

            <span class="sidebar-link-label">Kategori Berita</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.news-tags.index'))
<a
            href="{{ route('admin.news-tags.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.news-tags.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M20 13 11 4H4v7l9 9a2 2 0 0 0 3 0l4-4a2 2 0 0 0 0-3Z" />
                    <circle cx="7.5" cy="7.5" r=".8" fill="currentColor" />
                </svg>
            </span>

            <span class="sidebar-link-label">Tag Berita</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.announcements.index'))
<a
            href="{{ route('admin.announcements.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="m3 11 12-5v12L3 13z" />
                    <path d="M15 9h3a3 3 0 0 1 0 6h-3" />
                    <path d="m6 14 1 5h4l-2-6" />
                </svg>
            </span>

            <span class="sidebar-link-label">Pengumuman</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.agendas.index'))
<a
            href="{{ route('admin.agendas.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.agendas.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="3" y="5" width="18" height="16" rx="2" />
                    <path d="M16 3v4" />
                    <path d="M8 3v4" />
                    <path d="M3 11h18" />
                </svg>
            </span>

            <span class="sidebar-link-label">Agenda</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.galleries.index'))
<a
            href="{{ route('admin.galleries.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.galleries.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <circle cx="8.5" cy="8.5" r="1.5" />
                    <path d="m21 15-5-5L5 21" />
                </svg>
            </span>

            <span class="sidebar-link-label">Galeri</span>
        </a>
@endif
<span class="sidebar-section-title">Layanan</span>
@if ($sidebarRouteAllowed('admin.services.index'))
<a
            href="{{ route('admin.services.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M4 6h16" />
                    <path d="M4 12h16" />
                    <path d="M4 18h10" />
                </svg>
            </span>

            <span class="sidebar-link-label">Layanan</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.service-categories.index'))
<a
            href="{{ route('admin.service-categories.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M5 6h14" />
                    <path d="M5 12h14" />
                    <path d="M5 18h14" />
                    <circle cx="3" cy="6" r=".6" fill="currentColor" />
                    <circle cx="3" cy="12" r=".6" fill="currentColor" />
                    <circle cx="3" cy="18" r=".6" fill="currentColor" />
                </svg>
            </span>

            <span class="sidebar-link-label">Kategori Layanan</span>
        </a>
@endif
<span class="sidebar-section-title">Konten &amp; Navigasi</span>
@if ($sidebarRouteAllowed('admin.pages.index'))
<a
            href="{{ route('admin.pages.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M6 2h9l5 5v15H6z" />
                    <path d="M14 2v6h6" />
                    <path d="M9 13h6" />
                    <path d="M9 17h6" />
                </svg>
            </span>

            <span class="sidebar-link-label">Halaman Informasi</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.menus.index'))
<a
            href="{{ route('admin.menus.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.menus.*'))
                aria-current="page"
            @endif
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M4 6h16" />
                    <path d="M4 12h16" />
                    <path d="M4 18h16" />
                </svg>
            </span>

            <span class="sidebar-link-label">Menu Navigasi</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.media.index'))
<a
            href="{{ route('admin.media.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M3 6h6l2 2h10v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <path d="m8 17 3-3 2 2 3-4 4 5" />
                </svg>
            </span>

            <span class="sidebar-link-label">Pustaka Media</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.hero-slides.index'))
<a
            href="{{ route('admin.hero-slides.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.hero-slides.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.hero-slides.*'))
                aria-current="page"
            @endif
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect
                        x="3"
                        y="5"
                        width="18"
                        height="14"
                        rx="2"
                    />
                    <path d="m3 15 5-5 4 4 3-3 6 6" />
                    <circle cx="16.5" cy="9" r="1.5" />
                </svg>
            </span>

            <span class="sidebar-link-label">Banner Beranda</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.quick-links.index'))
<a
            href="{{ route('admin.quick-links.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.quick-links.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.quick-links.*'))
                aria-current="page"
            @endif
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <circle cx="6" cy="12" r="2.5" />
                    <circle cx="18" cy="6" r="2.5" />
                    <circle cx="18" cy="18" r="2.5" />
                    <path d="m8.3 10.8 7.4-3.6" />
                    <path d="m8.3 13.2 7.4 3.6" />
                </svg>
            </span>

            <span class="sidebar-link-label">Akses Cepat</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.related-links.index'))
<a
            href="{{ route('admin.related-links.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.related-links.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.related-links.*'))
                aria-current="page"
            @endif
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M10 13a5 5 0 0 0 7.5.5l2-2a5 5 0 0 0-7-7l-1.1 1.1" />
                    <path d="M14 11a5 5 0 0 0-7.5-.5l-2 2a5 5 0 0 0 7 7l1.1-1.1" />
                </svg>
            </span>

            <span class="sidebar-link-label">Tautan Terkait</span>
        </a>
@endif
<span class="sidebar-section-title">Pengaturan</span>
@if ($sidebarRouteAllowed('admin.settings.index'))
<a
            href="{{ route('admin.settings.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.settings.*'))
                aria-current="page"
            @endif
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="3" />
                    <path d="M12 2v2" />
                    <path d="M12 20v2" />
                    <path d="M4.93 4.93l1.41 1.41" />
                    <path d="m17.66 17.66 1.41 1.41" />
                    <path d="M2 12h2" />
                    <path d="M20 12h2" />
                    <path d="m6.34 17.66-1.41 1.41" />
                    <path d="m19.07 4.93-1.41 1.41" />
                </svg>
            </span>

            <span class="sidebar-link-label">Pengaturan Website</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.frontend-settings.edit'))
<a href="{{ route('admin.frontend-settings.edit') }}" class="sidebar-link {{ request()->routeIs('admin.frontend-settings.edit') ? 'active' : '' }}"><span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16M8 3v6M16 9v6M10 15v6"/></svg></span><span class="sidebar-link-label">Tampilan &amp; Interaksi</span></a>
@endif
<span class="sidebar-section-title">Administrasi</span>
@php
    $sidebarAccess = app(\App\Services\Access\AccessService::class);
    $sidebarActor = auth()->user();
    $canManageRoles = $sidebarActor
        && $sidebarAccess->allows($sidebarActor, 'roles.view');
    $canManageAssignments = $sidebarActor
        && $sidebarAccess->allows($sidebarActor, 'access.view');
    $isAccessManager = $sidebarActor
        && in_array($sidebarAccess->role($sidebarActor), ['super-admin', 'admin'], true);
@endphp

@if ($isAccessManager && ($canManageRoles || $canManageAssignments))
    <a
        data-role-access-menu
        href="{{ $canManageRoles ? route('admin.access-roles.index') : route('admin.access.index') }}"
        class="sidebar-link {{ request()->routeIs('admin.access.*', 'admin.access-roles.*') ? 'active' : '' }}"
        @if (request()->routeIs('admin.access.*', 'admin.access-roles.*')) aria-current="page" @endif
    >
        <span class="sidebar-link-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7z"/>
                <path d="m8 12 3 3 5-6"/>
            </svg>
        </span>
        <span class="sidebar-link-label">Role &amp; Akses</span>
    </a>
@endif

@if ($sidebarRouteAllowed('admin.users.index'))
<a
            href="{{ route('admin.users.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
            @if (request()->routeIs('admin.users.*'))
                aria-current="page"
            @endif
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <circle cx="9" cy="7" r="4" />
                    <path d="M2 21v-2a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v2" />
                    <path d="M17 11a4 4 0 0 1 4 4v2" />
                </svg>
            </span>

            <span class="sidebar-link-label">Pengguna</span>
        </a>
@endif
@if ($sidebarRouteAllowed('admin.units.index'))
<a
            href="{{ route('admin.units.index') }}"
            class="sidebar-link {{ request()->routeIs('admin.units.*') ? 'active' : '' }}"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M3 21h18" />
                    <path d="M6 21V7l6-4 6 4v14" />
                    <path d="M9 10h1" />
                    <path d="M14 10h1" />
                    <path d="M9 14h1" />
                    <path d="M14 14h1" />
                </svg>
            </span>

            <span class="sidebar-link-label">Unit Kerja</span>
        </a>
@endif
</nav>

    <div class="sidebar-bottom">
        @if ($sidebarRouteAllowed('home'))
<a
            href="{{ route('home') }}"
            class="sidebar-public-link"
            target="_blank"
            rel="noopener noreferrer"
        >
            <span class="sidebar-link-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="9" />
                    <path d="M3 12h18" />
                    <path d="M12 3a15 15 0 0 1 4 9 15 15 0 0 1-4 9 15 15 0 0 1-4-9 15 15 0 0 1 4-9Z" />
                </svg>
            </span>

            <span class="sidebar-public-copy">
                <strong>
                    Lihat Website
                </strong>

                <small>
                    Buka halaman utama
                </small>
            </span>

            <svg
                class="sidebar-public-arrow"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M7 17 17 7" />
                <path d="M7 7h10v10" />
            </svg>
        </a>
@endif
    </div>
</aside>

<script>
(() => {
    document.querySelectorAll(
        '[data-admin-sidebar] .sidebar-nav'
    ).forEach(nav => {
        Array.from(nav.children).forEach(title => {
            if (!title.matches('.sidebar-section-title')) return;

            let sibling = title.nextElementSibling;
            let hasLink = false;

            while (
                sibling &&
                !sibling.matches('.sidebar-section-title')
            ) {
                if (
                    sibling.matches('a.sidebar-link') ||
                    sibling.querySelector('a.sidebar-link')
                ) {
                    hasLink = true;
                    break;
                }

                sibling = sibling.nextElementSibling;
            }

            if (!hasLink) title.remove();
        });
    });
})();
</script>