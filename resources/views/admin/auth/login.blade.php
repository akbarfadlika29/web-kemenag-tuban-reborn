@extends('layouts.auth')

@section('title', 'Login Admin')

@section('content')
    <main class="admin-auth">
        <div class="admin-auth-shell">
            <section class="admin-auth-brand">
                <div class="admin-auth-brand-inner">
                    <a
                        href="{{ route('home') }}"
                        class="admin-auth-identity"
                        aria-label="Kembali ke website publik"
                    >
                        <span
                            class="admin-auth-mark"
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
                                <path d="M12 3 4 7v5c0 4.8 3.1 7.7 8 9 4.9-1.3 8-4.2 8-9V7l-8-4Z" />
                                <path d="M8.5 12.2 11 14.7l4.8-5" />
                            </svg>
                        </span>

                        <span class="admin-auth-identity-copy">
                            <strong>
                                WEB PPID
                            </strong>

                            <span>
                                Kementerian Agama Kabupaten Tuban
                            </span>
                        </span>
                    </a>

                    <div class="admin-auth-brand-copy">
                        <span class="admin-auth-eyebrow">
                            Panel Administrasi
                        </span>

                        <h1>
                            Kelola informasi publik secara terpusat.
                        </h1>

                        <p>
                            Akses khusus pengelola website dan
                            layanan informasi PPID Kementerian
                            Agama Kabupaten Tuban.
                        </p>
                    </div>

                    <div class="admin-auth-brand-footer">
                        <span>
                            Sistem Informasi Publik
                        </span>

                        <span aria-hidden="true">
                            •
                        </span>

                        <span>
                            PPID
                        </span>
                    </div>
                </div>
            </section>

            <section class="admin-auth-form-panel">
                <div class="admin-auth-form-wrap">
                    <header class="admin-auth-form-header">
                        <span class="admin-auth-form-kicker">
                            Selamat Datang
                        </span>

                        <h2>
                            Masuk ke Panel Admin
                        </h2>

                        <p>
                            Gunakan akun administrator yang telah
                            terdaftar untuk melanjutkan.
                        </p>
                    </header>

                    <form
                        action="{{ route('admin.login.store') }}"
                        method="POST"
                        class="admin-auth-form"
                    >
                        @csrf

                        <x-form.input
                            name="email"
                            type="email"
                            label="Email"
                            size="lg"
                            required
                            autocomplete="username"
                            autofocus
                            placeholder="nama@kemenag.go.id"
                        />

                        <div class="ui-form-group">
                            <label
                                for="password"
                                class="ui-form-label"
                            >
                                Password

                                <span class="ui-required">
                                    *
                                </span>
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="ui-control ui-control-lg {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan password"
                            >

                            @error('password')
                                <div class="ui-form-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="admin-auth-options">
                            <label class="ui-check">
                                <input
                                    type="hidden"
                                    name="remember"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    @checked(old('remember'))
                                >

                                <span>
                                    Ingat saya
                                </span>
                            </label>
                        </div>

                        <x-ui.button
                            type="submit"
                            variant="primary"
                            size="lg"
                            block
                        >
                            Masuk ke Panel Admin
                        </x-ui.button>
                    </form>

                    <div class="admin-auth-public">
                        <a href="{{ route('home') }}">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="m15 18-6-6 6-6" />
                            </svg>

                            <span>
                                Kembali ke website publik
                            </span>
                        </a>
                    </div>

                    <footer class="admin-auth-form-footer">
                        Akses panel hanya diperuntukkan bagi
                        pengguna yang berwenang.
                    </footer>
                </div>
            </section>
        </div>
    </main>
@endsection
