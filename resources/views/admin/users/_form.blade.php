@php
    $editing = isset($user);
@endphp

<div class="user-form-grid">
    <div class="user-form-main">
        <x-ui.card>
            <div class="ui-card-body">
                <div class="user-form-section">
                    <div class="user-form-section-heading">
                        <h2>
                            Informasi Akun
                        </h2>

                        <p>
                            Data utama akun pengguna sistem.
                        </p>
                    </div>

                    <div class="user-form-fields">
                        <x-form.input
                            name="name"
                            label="Nama Lengkap"
                            :value="old(
                                'name',
                                $user->name ?? ''
                            )"
                            required
                        />

                        <x-form.input
                            name="email"
                            type="email"
                            label="Email"
                            :value="old(
                                'email',
                                $user->email ?? ''
                            )"
                            required
                        />

                        <x-form.select
                            name="unit_id"
                            label="Unit Kerja"
                        >
                            <option value="">
                                Tidak terikat unit kerja
                            </option>

                            @foreach ($units as $unit)
                                <option
                                    value="{{ $unit->id }}"
                                    @selected(
                                        (string) old(
                                            'unit_id',
                                            $user->unit_id ?? ''
                                        ) ===
                                        (string) $unit->id
                                    )
                                >
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <div class="user-form-columns">
                            <x-form.input
                                name="position"
                                label="Jabatan"
                                :value="old(
                                    'position',
                                    $user->position ?? ''
                                )"
                                placeholder="Contoh: Pranata Komputer"
                            />

                            <x-form.input
                                name="phone"
                                label="Nomor Telepon"
                                :value="old(
                                    'phone',
                                    $user->phone ?? ''
                                )"
                                placeholder="08xxxxxxxxxx"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="ui-card-body">
                <div class="user-form-section">
                    <div class="user-form-section-heading">
                        <h2>
                            Keamanan
                        </h2>

                        <p>
                            @if ($editing)
                                Kosongkan password apabila
                                tidak ingin mengubahnya.
                            @else
                                Tetapkan password awal
                                untuk akun pengguna.
                            @endif
                        </p>
                    </div>

                    <div class="user-form-columns">
                        <x-form.input
                            name="password"
                            type="password"
                            label="{{ $editing
                                ? 'Password Baru'
                                : 'Password'
                            }}"
                            :required="! $editing"
                            autocomplete="new-password"
                        />

                        <x-form.input
                            name="password_confirmation"
                            type="password"
                            label="Konfirmasi Password"
                            :required="! $editing"
                            autocomplete="new-password"
                        />
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>

    <aside class="user-form-sidebar">
        <x-ui.card>
            <div class="ui-card-body">
                <div class="user-form-section-heading">
                    <h2>
                        Status Akun
                    </h2>

                    <p>
                        Akun nonaktif tidak dapat digunakan
                        untuk masuk ke sistem setelah
                        autentikasi diterapkan.
                    </p>
                </div>

                <input
                    type="hidden"
                    name="is_active"
                    value="0"
                >

                <label class="user-status-toggle">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(
                            old(
                                'is_active',
                                $user->is_active ?? true
                            )
                        )
                    >

                    <span>
                        <strong>
                            Akun Aktif
                        </strong>

                        <small>
                            Izinkan akun digunakan.
                        </small>
                    </span>
                </label>
            </div>
        </x-ui.card>

        @if (
            $editing
            && $user->last_login_at
        )
            <x-ui.card>
                <div class="ui-card-body">
                    <div class="user-account-meta">
                        <span>
                            Login Terakhir
                        </span>

                        <strong>
                            {{ $user->last_login_at
                                ->translatedFormat(
                                    'd F Y H:i'
                                )
                            }}
                        </strong>
                    </div>
                </div>
            </x-ui.card>
        @endif
    </aside>
</div>

<div class="user-form-actions">
    <x-ui.button
        :href="route('admin.users.index')"
        variant="secondary"
    >
        Batal
    </x-ui.button>

    <x-ui.button
        type="submit"
        variant="primary"
    >
        {{ $editing
            ? 'Simpan Perubahan'
            : 'Tambah User'
        }}
    </x-ui.button>
</div>

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/users.css') }}"
    >
@endpush