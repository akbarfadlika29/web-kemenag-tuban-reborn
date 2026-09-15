<span class="sidebar-section-title">Regulasi</span>

@foreach([
    'admin.regulations.index' => 'Daftar Regulasi',
    'admin.regulation-types.index' => 'Jenis Regulasi',
] as $regulationRoute => $regulationLabel)
    <a href="{{ route($regulationRoute) }}"
        class="sidebar-link {{ request()->routeIs(str_replace('.index', '.*', $regulationRoute)) ? 'active' : '' }}"
        @if(request()->routeIs(str_replace('.index', '.*', $regulationRoute)))
            aria-current="page"
        @endif>
        <span class="sidebar-link-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="1.8" aria-hidden="true">
                <path d="M6 3h9l4 4v14H6z"/>
                <path d="M14 3v5h5M9 12h7M9 16h7"/>
            </svg>
        </span>
        <span class="sidebar-link-label">{{ $regulationLabel }}</span>
    </a>
@endforeach
