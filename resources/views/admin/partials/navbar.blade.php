<header class="admin-navbar">
    <div class="admin-navbar-inner">
        <div class="admin-navbar-left">
            <button
                type="button"
                class="admin-navbar-toggle"
                data-admin-sidebar-toggle
                aria-label="Buka menu navigasi"
                aria-expanded="false"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div
                class="admin-navbar-context-mark"
                aria-hidden="true"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <rect
                        x="3"
                        y="3"
                        width="18"
                        height="18"
                        rx="2"
                    />
                    <path d="M8 8h8" />
                    <path d="M8 12h8" />
                    <path d="M8 16h5" />
                </svg>
            </div>

            <div class="admin-navbar-context">
                

                <strong>
                    Panel Administrasi
                </strong>

                <span class="admin-navbar-context-meta">
                    Kementerian Agama Kabupaten Tuban
                </span>
            </div>
        </div>

        <div class="admin-navbar-actions">
            <div class="admin-navbar-date">
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
                        height="16"
                        rx="2"
                    />
                    <path d="M16 3v4" />
                    <path d="M8 3v4" />
                    <path d="M3 11h18" />
                </svg>

                <span>
                    {{ now()->translatedFormat('d F Y') }}
                </span>
            </div>

            <a
                href="{{ route('home') }}"
                class="admin-navbar-public-link" aria-label="Lihat website publik"
                target="_blank"
                rel="noopener noreferrer"
            >
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

                <span>
                    Lihat Website
                </span>
            </a>

            <div class="admin-navbar-user">
                <div
                    class="admin-navbar-user-avatar"
                    aria-hidden="true"
                >
                    {{
                        mb_strtoupper(
                            mb_substr(
                                auth()->user()->name,
                                0,
                                1
                            )
                        )
                    }}
                </div>

                <div class="admin-navbar-user-copy">
                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <span>
                        {{
                            auth()->user()->position
                                ?: auth()->user()->email
                        }}
                    </span>
                </div>
            </div>

            <form
                action="{{ route('admin.logout') }}"
                method="POST"
                class="admin-navbar-logout-form"
            >
                @csrf

                <button
                    type="submit"
                    class="admin-navbar-logout" aria-label="Keluar dari akun"
                    title="Keluar"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <path d="m16 17 5-5-5-5" />
                        <path d="M21 12H9" />
                    </svg>

                    <span>
                        Keluar
                    </span>
                </button>
            </form>
        </div>
    </div>
</header>
