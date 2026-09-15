@php
    $navigationAccess = app(\App\Services\Access\AccessService::class);
    $navigationUser = auth()->user();
    $showRoleTab = $navigationAccess->allows($navigationUser, 'roles.view');
    $showUserTab = $navigationAccess->allows($navigationUser, 'access.view');
@endphp

<header class="role-access-heading">
    <h1>Role &amp; Akses</h1>
    <p>Atur paket permission pada role, lalu tentukan pengguna yang memakainya.</p>
</header>

<nav class="role-access-tabs" aria-label="Navigasi Role dan Akses">
    @if ($showRoleTab)
        <a href="{{ route('admin.access-roles.index') }}"
           class="{{ request()->routeIs('admin.access-roles.*') ? 'is-active' : '' }}"
           @if (request()->routeIs('admin.access-roles.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7z"/>
                <path d="m8 12 3 3 5-6"/>
            </svg>
            Role &amp; Permission
        </a>
    @endif

    @if ($showUserTab)
        <a href="{{ route('admin.access.index') }}"
           class="{{ request()->routeIs('admin.access.*') ? 'is-active' : '' }}"
           @if (request()->routeIs('admin.access.*')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <circle cx="9" cy="8" r="3"/>
                <path d="M3 21v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6M21 21v-2a6 6 0 0 0-4-5.7"/>
            </svg>
            Pengguna &amp; Role
        </a>
    @endif
</nav>

@push('styles')
<style>
.role-access-heading{margin-bottom:20px}
.role-access-heading h1{margin:0 0 8px}
.role-access-heading p{margin:0;color:#647169;font-size:14px;line-height:1.7}
.role-access-tabs{display:flex;gap:8px;overflow-x:auto;padding:4px 2px 16px;margin-bottom:24px;border-bottom:1px solid #e1e8e3}
.role-access-tabs a{display:inline-flex;align-items:center;justify-content:center;gap:8px;flex-shrink:0;min-height:44px;padding:10px 16px;border:1px solid #dce5df;border-radius:8px;color:#46534c;background:#fff;font-size:14px;font-weight:600;text-decoration:none}
.role-access-tabs a.is-active{background:#eff5f2;color:#1d5b43;border-color:#aac6b6;box-shadow:inset 0 -2px 0 #247052}
.role-access-tabs a:hover{background:#f3f8f5}
.role-access-tabs a:focus-visible{outline:3px solid #247052;outline-offset:2px}
.role-access-tabs svg{width:18px;height:18px;flex-shrink:0}
</style>
@endpush
