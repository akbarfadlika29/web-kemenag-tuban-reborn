@extends('layouts.admin')

@section('title', 'Pengaturan Website')

@section('content')
    @php
        $tabIcons = [
            'identity' => 'building',
            'contact' => 'phone',
            'social' => 'share',
            'seo' => 'search',
        ];

        $activeGroup = array_key_first($groups);

        foreach ($groups as $groupKey => $group) {
            foreach ($group['fields'] ?? [] as $key => $field) {
                if ($errors->has('settings.' . $key)) {
                    $activeGroup = $groupKey;
                    break 2;
                }
            }
        }

        $identity = $groups['identity'] ?? null;
        $logoField = $identity['fields']['logo_media_id'] ?? null;

        $logoValue = old(
            'settings.logo_media_id',
            $values['logo_media_id'] ?? ''
        );
    @endphp

    <div
        class="settings-page"
        data-settings-page
        data-settings-active-tab="{{ $activeGroup }}"
    >
        <x-ui.page-header
            title="Pengaturan Website"
            description="Atur identitas instansi, kontak, media sosial, dan informasi website."
        />

        <form
            action="{{ route('admin.settings.update') }}"
            method="POST"
            class="settings-form"
        >
            @csrf
            @method('PUT')

            <div class="settings-layout">
                {{-- =====================================================
                     MAIN SETTINGS
                     ===================================================== --}}
                <div class="settings-main">
                    <section class="settings-panel">
                        <nav
                            class="settings-tabs"
                            aria-label="Kelompok pengaturan website"
                            role="tablist"
                        >
                            @foreach ($groups as $groupKey => $group)
                                <button
                                    type="button"
                                    class="settings-tab {{ $activeGroup === $groupKey ? 'is-active' : '' }}"
                                    data-settings-tab="{{ $groupKey }}"
                                    role="tab"
                                    aria-selected="{{ $activeGroup === $groupKey ? 'true' : 'false' }}"
                                    aria-controls="settings-panel-{{ $groupKey }}"
                                >
                                    <span class="settings-tab-icon">
                                        @switch($tabIcons[$groupKey] ?? null)
                                            @case('building')
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
                                                    <path d="M9 10h2" />
                                                    <path d="M13 10h2" />
                                                    <path d="M9 14h2" />
                                                    <path d="M13 14h2" />
                                                </svg>
                                                @break

                                            @case('phone')
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true"
                                                >
                                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92Z" />
                                                </svg>
                                                @break

                                            @case('share')
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true"
                                                >
                                                    <circle cx="18" cy="5" r="3" />
                                                    <circle cx="6" cy="12" r="3" />
                                                    <circle cx="18" cy="19" r="3" />
                                                    <path d="m8.6 10.5 6.8-4" />
                                                    <path d="m8.6 13.5 6.8 4" />
                                                </svg>
                                                @break

                                            @default
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true"
                                                >
                                                    <circle cx="11" cy="11" r="7" />
                                                    <path d="m20 20-4-4" />
                                                </svg>
                                        @endswitch
                                    </span>

                                    <span>
                                        {{ $group['label'] }}
                                    </span>

                                    @php
                                        $groupHasError = false;

                                        foreach ($group['fields'] ?? [] as $key => $field) {
                                            if ($errors->has('settings.' . $key)) {
                                                $groupHasError = true;
                                                break;
                                            }
                                        }
                                    @endphp

                                    @if ($groupHasError)
                                        <span
                                            class="settings-tab-error"
                                            title="Ada field yang perlu diperiksa"
                                        ></span>
                                    @endif
                                </button>
                            @endforeach
                        </nav>

                        <div class="settings-tab-content">
                            @foreach ($groups as $groupKey => $group)
                                <section
                                    id="settings-panel-{{ $groupKey }}"
                                    class="settings-tab-panel {{ $activeGroup === $groupKey ? 'is-active' : '' }}"
                                    data-settings-panel="{{ $groupKey }}"
                                    role="tabpanel"
                                    @if ($activeGroup !== $groupKey)
                                        hidden
                                    @endif
                                >
                                    <header class="settings-section-header">
                                        <div>
                                            <span class="settings-section-kicker">
                                                Pengaturan Website
                                            </span>

                                            <h2>
                                                {{ $group['label'] }}
                                            </h2>

                                            @if (! empty($group['description']))
                                                <p>
                                                    {{ $group['description'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </header>

                                    <div class="settings-fields">
                                        @foreach ($group['fields'] as $key => $field)
                                            @if ($key === 'logo_media_id')
                                                @continue
                                            @endif

                                            @php
                                                $type =
                                                    $field['type']
                                                    ?? 'text';

                                                $fullWidth =
                                                    $type === 'textarea';
                                            @endphp

                                            <div
                                                class="settings-field {{ $fullWidth ? 'settings-field-full' : '' }}"
                                            >
                                                <label
                                                    for="setting-{{ $key }}"
                                                    class="settings-label"
                                                >
                                                    {{ $field['label'] }}

                                                    @if (! empty($field['required']))
                                                        <span class="settings-required">
                                                            *
                                                        </span>
                                                    @endif
                                                </label>

                                                @if ($type === 'textarea')
                                                    <textarea
                                                        id="setting-{{ $key }}"
                                                        name="settings[{{ $key }}]"
                                                        rows="4"
                                                        class="settings-control settings-textarea @error('settings.' . $key) is-invalid @enderror"
                                                    >{{ old('settings.' . $key, $values[$key] ?? '') }}</textarea>
                                                @else
                                                    <input
                                                        id="setting-{{ $key }}"
                                                        type="{{ $type }}"
                                                        name="settings[{{ $key }}]"
                                                        value="{{ old('settings.' . $key, $values[$key] ?? '') }}"
                                                        class="settings-control @error('settings.' . $key) is-invalid @enderror"
                                                    >
                                                @endif

                                                @error('settings.' . $key)
                                                    <div class="settings-error">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </section>
                </div>

                {{-- =====================================================
                     SIDEBAR
                     ===================================================== --}}
                <aside class="settings-sidebar">
                    <section class="settings-side-card settings-overview-card">
                        <header class="settings-side-header">
                            <span class="settings-side-icon">
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
                                    <path d="m8.5 12 2.2 2.2 4.8-5" />
                                </svg>
                            </span>

                            <div>
                                <span class="settings-side-kicker">
                                    Konfigurasi
                                </span>

                                <h3>
                                    Lihat Website
                                </h3>
                            </div>
                        </header>

                        <div class="settings-overview-list">
                            <div>
                                <span>
                                    Nama Website
                                </span>

                                <strong>
                                    {{ $values['site_name'] ?: 'Belum diatur' }}
                                </strong>
                            </div>

                            <div>
                                <span>
                                    Instansi
                                </span>

                                <strong>
                                    {{ $values['institution_short_name']
                                        ?: $values['institution_name']
                                        ?: 'Belum diatur'
                                    }}
                                </strong>
                            </div>

                            <div>
                                <span>
                                    Kelompok Pengaturan
                                </span>

                                <strong>
                                    {{ count($groups) }} kelompok
                                </strong>
                            </div>
                        </div>

                        <p class="settings-overview-note">
                            Perubahan pada halaman ini digunakan sebagai konfigurasi bawaan website publik.
                        </p>
                    </section>

                    @if ($logoField)
                        <section class="settings-side-card settings-logo-card">
                            <header class="settings-side-header">
                                <span class="settings-side-icon">
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
                                            y="4"
                                            width="18"
                                            height="16"
                                            rx="2"
                                        />
                                        <circle
                                            cx="9"
                                            cy="9"
                                            r="2"
                                        />
                                        <path d="m21 15-5-5L5 20" />
                                    </svg>
                                </span>

                                <div>
                                    <span class="settings-side-kicker">
                                        Identitas Visual
                                    </span>

                                    <h3>
                                        Logo Website
                                    </h3>
                                </div>
                            </header>

                            <input
                                type="hidden"
                                id="setting-logo_media_id"
                                name="settings[logo_media_id]"
                                value="{{ $logoValue }}"
                                data-setting-logo-input
                            >

                            <div class="settings-logo-preview">
                                @if ($logoMedia)
                                    <img
                                        data-setting-logo-image
                                        src="{{ Storage::disk($logoMedia->disk)->url($logoMedia->path) }}"
                                        alt="{{ $logoMedia->alt_text ?: 'Logo website' }}"
                                    >

                                    <div
                                        class="settings-media-empty"
                                        data-setting-logo-empty
                                        hidden
                                    >
                                        <span class="settings-media-empty-icon">
                                            +
                                        </span>

                                        <strong>
                                            Belum ada logo
                                        </strong>

                                        <span>
                                            Pilih gambar dari Media Manager.
                                        </span>
                                    </div>
                                @else
                                    <img
                                        data-setting-logo-image
                                        src=""
                                        alt=""
                                        hidden
                                    >

                                    <div
                                        class="settings-media-empty"
                                        data-setting-logo-empty
                                    >
                                        <span class="settings-media-empty-icon">
                                            +
                                        </span>

                                        <strong>
                                            Belum ada logo
                                        </strong>

                                        <span>
                                            Pilih gambar dari Media Manager.
                                        </span>
                                    </div>
                                @endif
                            </div>

                            @error('settings.logo_media_id')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="settings-logo-actions">
                                <x-ui.button
                                    type="button"
                                    variant="secondary"
                                    data-setting-logo-select
                                >
                                    Pilih Logo
                                </x-ui.button>

                                <div
                                    data-setting-logo-remove-wrap
                                    @if (! $logoMedia) hidden @endif
                                >
                                    <x-ui.button
                                        type="button"
                                        variant="secondary"
                                        data-setting-logo-remove
                                    >
                                        Hapus
                                    </x-ui.button>
                                </div>
                            </div>

                            <p class="settings-logo-help">
                                Gunakan logo dengan latar transparan dan resolusi yang cukup agar tetap tajam pada header website.
                            </p>
                        </section>
                    @endif
                </aside>
            </div>

            <div class="settings-actions">
                <div>
                    <strong>
                        Simpan perubahan konfigurasi
                    </strong>

                    <span>
                        Pastikan data sudah benar sebelum disimpan.
                    </span>
                </div>

                <x-ui.button
                    type="submit"
                    variant="primary"
                >
                    Simpan Pengaturan
                </x-ui.button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/settings.css') }}"
    >
@endpush

@push('scripts')
    <script src="{{ asset('js/modules/settings.js') }}"></script>
@endpush
