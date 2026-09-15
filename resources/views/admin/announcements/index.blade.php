@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
    <x-ui.page-header
        title="Pengumuman"
        description="Kelola pengumuman dan masa publikasinya."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.announcements.create')"
                variant="primary"
            >
                Tambah Pengumuman
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @php
        $filtersActive =
            $search !== ''
            || $status !== ''
            || $unitId !== ''
            || $pinned !== '';
    @endphp

    <div class="announcements-index">

        <div class="announcements-filter-card">
            <form
                method="GET"
                action="{{ route('admin.announcements.index') }}"
                class="announcements-filter-form"
            >
                <div class="announcements-search">
                    <label for="search">
                        Cari
                    </label>

                    <input
                        type="search"
                        name="search"
                        id="search"
                        class="ui-control"
                        value="{{ $search }}"
                        placeholder="Judul, slug, atau ringkasan..."
                    >
                </div>

                <div>
                    <label for="status">
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="ui-control"
                    >
                        <option value="">
                            Semua Status
                        </option>

                        <option
                            value="draft"
                            @selected($status === 'draft')
                        >
                            Draft
                        </option>

                        <option
                            value="published"
                            @selected($status === 'published')
                        >
                            Published
                        </option>

                        <option
                            value="archived"
                            @selected($status === 'archived')
                        >
                            Archived
                        </option>
                    </select>
                </div>

                <div>
                    <label for="unit_id">
                        Unit Kerja
                    </label>

                    <select
                        name="unit_id"
                        id="unit_id"
                        class="ui-control"
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
                    <label for="is_pinned">
                        Prioritas
                    </label>

                    <select
                        name="is_pinned"
                        id="is_pinned"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
                        </option>

                        <option
                            value="1"
                            @selected($pinned === '1')
                        >
                            Disematkan
                        </option>

                        <option
                            value="0"
                            @selected($pinned === '0')
                        >
                            Normal
                        </option>
                    </select>
                </div>

                <div class="announcements-filter-actions">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Terapkan
                    </x-ui.button>

                    @if ($filtersActive)
                        <x-ui.button
                            :href="route('admin.announcements.index')"
                            variant="secondary"
                        >
                            Atur Ulang
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        <div class="announcements-result-bar">
            <strong>
                {{ $announcements->total() }} pengumuman
            </strong>

            @if ($filtersActive)
                <span>
                    Filter aktif
                </span>
            @endif
        </div>

        <div class="announcements-table-card">
            <div class="announcements-table-scroll">
                <table class="announcements-table">
                    <thead>
                        <tr>
                            <th>Pengumuman</th>
                            <th>Unit Kerja</th>
                            <th>Status</th>
                            <th>Publikasi</th>
                            <th>Berakhir</th>
                            <th>Prioritas</th>
                            <th>Views</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($announcements as $item)
                            @php
                                $state =
                                    $item->publication_state;

                                $stateLabel = match ($state) {
                                    'published' => 'Published',
                                    'scheduled' => 'Terjadwal',
                                    'expired' => 'Berakhir',
                                    'archived' => 'Archived',
                                    default => 'Draft',
                                };
                            @endphp

                            <tr>
                                <td class="announcement-title-cell">
                                    <div class="announcement-title-row">
                                        @if ($item->coverMedia)
                                            <img
                                                src="{{ Storage::disk($item->coverMedia->disk)->url($item->coverMedia->path) }}"
                                                alt="{{ $item->coverMedia->alt_text ?? $item->title }}"
                                            >
                                        @endif

                                        <div>
                                            <strong>
                                                {{ $item->title }}
                                            </strong>

                                            <span>
                                                /pengumuman/{{ $item->slug }}
                                            </span>

                                            @if ($item->excerpt)
                                                <p>
                                                    {{ Str::limit($item->excerpt, 90) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $item->unit?->name ?? '—' }}
                                </td>

                                <td>
                                    <span class="announcement-status is-{{ $state }}">
                                        {{ $stateLabel }}
                                    </span>
                                </td>

                                <td>
                                    @if ($item->published_at)
                                        {{ $item->published_at->format('d M Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    @if ($item->expires_at)
                                        {{ $item->expires_at->format('d M Y H:i') }}
                                    @else
                                        Tanpa batas
                                    @endif
                                </td>

                                <td>
                                    @if ($item->is_pinned)
                                        <span class="announcement-pinned">
                                            Disematkan
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    {{ number_format($item->view_count) }}
                                </td>

                                <td>
                                    <div class="announcement-actions">
                                        <x-ui.button
                                            :href="route(
                                                'admin.announcements.edit',
                                                $item
                                            )"
                                            size="sm"
                                            variant="secondary"
                                        >
                                            Ubah
                                        </x-ui.button>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.announcements.destroy',
                                                $item
                                            ) }}"
                                            data-confirm="Hapus pengumuman ini?"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <x-ui.button
                                                type="submit"
                                                size="sm"
                                                variant="danger"
                                            >
                                                Hapus
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="8"
                                    class="announcements-empty"
                                >
                                    Belum ada pengumuman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($announcements->hasPages())
            <div class="announcements-pagination">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/announcements-index.css') }}"
    >
@endpush