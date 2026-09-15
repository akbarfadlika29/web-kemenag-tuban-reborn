@extends('layouts.admin')

@section('title', 'Unit Kerja')

@section('content')
    <div class="admin-page units-page">
        <x-ui.page-header
            title="Unit Kerja"
            description="Kelola unit kerja dan susunan organisasi."
        >
            <x-slot:actions>
                <x-ui.button
                    :href="route('admin.units.create')"
                    variant="primary"
                >
                    Tambah Unit
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- FILTER --}}
        <section
            class="units-filter-panel"
            aria-label="Filter unit kerja"
        >
            <form
                method="GET"
                action="{{ route('admin.units.index') }}"
            >
                <div class="units-filter-grid">
                    <x-form.input
                        name="search"
                        label="Pencarian"
                        :value="$search"
                        placeholder="Nama, singkatan, atau kode..."
                    />

                    <x-form.select
                        name="type"
                        label="Jenis Unit"
                    >
                        <option value="">
                            Semua Jenis
                        </option>

                        <option
                            value="kankemenag"
                            @selected($type === 'kankemenag')
                        >
                            Kantor Kementerian Agama
                        </option>

                        <option
                            value="subbag"
                            @selected($type === 'subbag')
                        >
                            Subbag
                        </option>

                        <option
                            value="seksi"
                            @selected($type === 'seksi')
                        >
                            Seksi
                        </option>

                        <option
                            value="kua"
                            @selected($type === 'kua')
                        >
                            KUA
                        </option>

                        <option
                            value="satker"
                            @selected($type === 'satker')
                        >
                            Satker
                        </option>

                        <option
                            value="lainnya"
                            @selected($type === 'lainnya')
                        >
                            Lainnya
                        </option>
                    </x-form.select>

                    <x-form.select
                        name="status"
                        label="Status"
                    >
                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="1"
                            @selected($status === '1')
                        >
                            Aktif
                        </option>

                        <option
                            value="0"
                            @selected($status === '0')
                        >
                            Nonaktif
                        </option>
                    </x-form.select>

                    <div class="units-filter-actions">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                        >
                            Terapkan
                        </x-ui.button>

                        <x-ui.button
                            :href="route('admin.units.index')"
                            variant="secondary"
                        >
                            Atur Ulang
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </section>

        {{-- DATA --}}
        <section
            class="units-data-card"
            @if (!$hasFilter)
                data-tree-dnd
                data-tree-item-label="unit kerja"
                data-tree-error-message="Unit kerja gagal dipindahkan."
            @endif
        >
            <header class="units-data-header">
                <div>
                    <span class="units-data-kicker">
                        {{ $hasFilter ? 'Hasil Pencarian' : 'Struktur Organisasi' }}
                    </span>

                    <h2>
                        {{ $hasFilter ? 'Unit yang Ditemukan' : 'Hierarki Unit Kerja' }}
                    </h2>

                    <p>
                        @if ($hasFilter)
                            Menampilkan unit sesuai filter yang sedang digunakan.
                        @else
                            Tarik dan letakkan unit untuk mengubah hierarki atau urutannya.
                        @endif
                    </p>
                </div>

                <span class="units-view-badge">
                    {{ $hasFilter ? 'Mode Filter' : 'Mode Struktur' }}
                </span>
            </header>

            @if (!$hasFilter)
                <div
                    class="tree-dnd-root"
                    data-tree-dnd-root
                >
                    <div
                        class="tree-dnd-root-icon"
                        data-tree-dnd-root-icon
                    >
                        ↑
                    </div>

                    <div>
                        <strong>
                            Pindahkan ke Level Utama
                        </strong>

                        <span>
                            Tarik unit ke area ini untuk menjadikannya unit level paling atas.
                        </span>
                    </div>
                </div>
            @endif

            @if ($units->isEmpty())
                <div class="units-empty-wrap">
                    <x-ui.empty-state
                        title="Belum ada Unit Kerja"
                        description="Tambahkan unit kerja pertama untuk membangun struktur organisasi."
                    >
                        <x-slot:action>
                            <x-ui.button
                                :href="route('admin.units.create')"
                                variant="primary"
                            >
                                Tambah Unit
                            </x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @else
                <div class="units-table-wrap">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th class="units-column-name">
                                    Nama Unit
                                </th>

                                <th>
                                    Induk
                                </th>

                                <th>
                                    Jenis
                                </th>

                                <th>
                                    Kode
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="units-column-order">
                                    Urutan
                                </th>

                                <th class="units-column-action">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @if ($hasFilter)
                                @foreach ($units as $unit)
                                    <tr>
                                        <td>
                                            <div class="unit-name">
                                                <strong>
                                                    {{ $unit->name }}
                                                </strong>

                                                @if ($unit->short_name)
                                                    <span class="unit-subtext">
                                                        {{ $unit->short_name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="unit-parent-cell">
                                            {{ $unit->parent?->name ?? 'Level Utama' }}
                                        </td>

                                        <td>
                                            <x-ui.badge variant="info">
                                                {{ strtoupper($unit->type) }}
                                            </x-ui.badge>
                                        </td>

                                        <td class="unit-code-cell">
                                            {{ $unit->code ?? '-' }}
                                        </td>

                                        <td>
                                            @if ($unit->is_active)
                                                <x-ui.badge variant="success">
                                                    Aktif
                                                </x-ui.badge>
                                            @else
                                                <x-ui.badge variant="danger">
                                                    Nonaktif
                                                </x-ui.badge>
                                            @endif
                                        </td>

                                        <td class="unit-order-cell">
                                            {{ $unit->sort_order }}
                                        </td>

                                        <td>
                                            <div class="table-actions">
                                                <x-ui.button
                                                    :href="route('admin.units.edit', $unit)"
                                                    size="sm"
                                                >
                                                    Ubah
                                                </x-ui.button>

                                                <form
                                                    action="{{ route('admin.units.destroy', $unit) }}"
                                                    method="POST"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <x-ui.button
                                                        type="submit"
                                                        variant="danger"
                                                        size="sm"
                                                        data-confirm="Yakin ingin menghapus unit kerja ini?"
                                                    >
                                                        Hapus
                                                    </x-ui.button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach ($units as $unit)
                                    @include('admin.units._row', [
                                        'unit' => $unit,
                                        'level' => 0,
                                    ])
                                @endforeach
                            @endif
                        </tbody>
                    </x-ui.table>
                </div>
            @endif
        </section>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/units.css') }}"
    >
@endpush
