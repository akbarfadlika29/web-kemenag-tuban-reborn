@extends('layouts.admin')

@section('title', 'Pengguna')

@section('content')
    <x-ui.page-header
        title="Pengguna"
        description="Kelola akun, unit kerja, dan status pengguna."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.users.create')"
                variant="primary"
            >
                Tambah Pengguna
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="mb-5">
        <div class="ui-card-body">
            <form
                method="GET"
                action="{{ route('admin.users.index') }}"
                class="users-filter"
            >
                <div>
                    <label for="search">
                        Pencarian
                    </label>

                    <input
                        id="search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Nama, email, jabatan..."
                    >
                </div>

                <div>
                    <label for="unit_id">
                        Unit Kerja
                    </label>

                    <select
                        id="unit_id"
                        name="unit_id"
                    >
                        <option value="">
                            Semua Unit
                        </option>

                        @foreach ($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected(
                                    (string) $unitId ===
                                    (string) $unit->id
                                )
                            >
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                    >
                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="active"
                            @selected(
                                $status === 'active'
                            )
                        >
                            Aktif
                        </option>

                        <option
                            value="inactive"
                            @selected(
                                $status === 'inactive'
                            )
                        >
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div class="users-filter-actions">
                    <button
                        type="submit"
                        class="ui-btn ui-btn-primary ui-btn-md"
                    >
                        Terapkan
                    </button>

                    @if (
                        $search !== ''
                        || $unitId !== ''
                        || $status !== ''
                    )
                        <a
                            href="{{ route('admin.users.index') }}"
                            class="ui-btn ui-btn-secondary ui-btn-md"
                        >
                            Atur Ulang
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="users-table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>
                            Pengguna
                        </th>

                        <th>
                            Unit Kerja
                        </th>

                        <th>
                            Jabatan
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Login Terakhir
                        </th>

                        <th class="users-table-action">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="user-identity">
                                    <div class="user-avatar">
                                        {{ strtoupper(
                                            mb_substr(
                                                $user->name,
                                                0,
                                                1
                                            )
                                        ) }}
                                    </div>

                                    <div>
                                        <strong>
                                            {{ $user->name }}
                                        </strong>

                                        <span>
                                            {{ $user->email }}
                                        </span>

                                        @if ($user->phone)
                                            <small>
                                                {{ $user->phone }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                {{ $user->unit?->name ?? '—' }}
                            </td>

                            <td>
                                {{ $user->position ?: '—' }}
                            </td>

                            <td>
                                @if ($user->is_active)
                                    <span class="user-status active">
                                        Aktif
                                    </span>
                                @else
                                    <span class="user-status inactive">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($user->last_login_at)
                                    {{ $user->last_login_at
                                        ->translatedFormat(
                                            'd M Y H:i'
                                        )
                                    }}
                                @else
                                    <span class="user-never-login">
                                        Belum pernah
                                    </span>
                                @endif
                            </td>

                            <td class="users-table-action">
                                <x-ui.button
                                    :href="route(
                                        'admin.users.edit',
                                        $user
                                    )"
                                    variant="secondary"
                                    size="sm"
                                >
                                    Ubah
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="users-empty"
                            >
                                Tidak ada pengguna yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="users-pagination">
                {{ $users->links() }}
            </div>
        @endif
    </x-ui.card>

    @push('styles')
        <link
            rel="stylesheet"
            href="{{ asset('css/admin/pages/users.css') }}"
        >
    @endpush
@endsection