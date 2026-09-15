@extends('layouts.admin')

@section('title', 'Agenda')

@section('content')
    <x-ui.page-header
        title="Agenda"
        description="Kelola jadwal dan informasi kegiatan instansi."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.agendas.create')"
                variant="primary"
            >
                Tambah Agenda
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @php
        $filtersActive =
            $search !== ''
            || $status !== ''
            || $unitId !== ''
            || $eventState !== ''
            || $featured !== '';
    @endphp

    <div class="agendas-index">
        <div class="agendas-filter-card">
            <form
                method="GET"
                action="{{ route('admin.agendas.index') }}"
                class="agendas-filter-form"
            >
                <div class="agendas-search">
                    <label for="search">
                        Cari
                    </label>

                    <input
                        type="search"
                        name="search"
                        id="search"
                        class="ui-control"
                        value="{{ $search }}"
                        placeholder="Judul, lokasi, slug..."
                    >
                </div>

                <div>
                    <label for="event_state">
                        Agenda
                    </label>

                    <select
                        name="event_state"
                        id="event_state"
                        class="ui-control"
                    >
                        <option value="">
                            Semua Agenda
                        </option>

                        <option
                            value="upcoming"
                            @selected($eventState === 'upcoming')
                        >
                            Akan Datang
                        </option>

                        <option
                            value="ongoing"
                            @selected($eventState === 'ongoing')
                        >
                            Berlangsung
                        </option>

                        <option
                            value="finished"
                            @selected($eventState === 'finished')
                        >
                            Selesai
                        </option>
                    </select>
                </div>

                <div>
                    <label for="status">
                        Publikasi
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
                    <label for="is_featured">
                        Prioritas
                    </label>

                    <select
                        name="is_featured"
                        id="is_featured"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
                        </option>

                        <option
                            value="1"
                            @selected($featured === '1')
                        >
                            Unggulan
                        </option>

                        <option
                            value="0"
                            @selected($featured === '0')
                        >
                            Normal
                        </option>
                    </select>
                </div>

                <div class="agendas-filter-actions">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Terapkan
                    </x-ui.button>

                    @if ($filtersActive)
                        <x-ui.button
                            :href="route('admin.agendas.index')"
                            variant="secondary"
                        >
                            Atur Ulang
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        <div class="agendas-result-bar">
            <strong>
                {{ $agendas->total() }} agenda
            </strong>

            @if ($filtersActive)
                <span>
                    Filter aktif
                </span>
            @endif
        </div>

        <div class="agendas-table-card">
            <div class="agendas-table-scroll">
                <table class="agendas-table">
                    <thead>
                        <tr>
                            <th>Agenda</th>
                            <th>Unit Kerja</th>
                            <th>Jadwal</th>
                            <th>Lokasi</th>
                            <th>Kegiatan</th>
                            <th>Publikasi</th>
                            <th>Unggulan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($agendas as $item)
                            @php
                                $eventStateItem =
                                    $item->event_state;

                                $eventLabel = match ($eventStateItem) {
                                    'upcoming' => 'Akan Datang',
                                    'ongoing' => 'Berlangsung',
                                    default => 'Selesai',
                                };

                                $publicationState =
                                    $item->publication_state;

                                $publicationLabel = match ($publicationState) {
                                    'published' => 'Published',
                                    'scheduled' => 'Terjadwal',
                                    'archived' => 'Archived',
                                    default => 'Draft',
                                };
                            @endphp

                            <tr>
                                <td class="agenda-title-cell">
                                    <div class="agenda-title-row">
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
                                                /agenda/{{ $item->slug }}
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
                                    <strong class="agenda-date">
                                        {{ $item->start_at->format('d M Y H:i') }}
                                    </strong>

                                    @if ($item->end_at)
                                        <span class="agenda-date-end">
                                            s/d {{ $item->end_at->format('d M Y H:i') }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $item->location ?: '—' }}
                                </td>

                                <td>
                                    <span class="agenda-event-status is-{{ $eventStateItem }}">
                                        {{ $eventLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="agenda-publication-status is-{{ $publicationState }}">
                                        {{ $publicationLabel }}
                                    </span>
                                </td>

                                <td>
                                    @if ($item->is_featured)
                                        <span class="agenda-featured">
                                            Unggulan
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    <div class="agenda-actions">
                                        <x-ui.button
                                            :href="route(
                                                'admin.agendas.edit',
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
                                                'admin.agendas.destroy',
                                                $item
                                            ) }}"
                                            data-confirm="Hapus agenda ini?"
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
                                    class="agendas-empty"
                                >
                                    Belum ada agenda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($agendas->hasPages())
            <div class="agendas-pagination">
                {{ $agendas->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/agendas-index.css') }}"
    >
@endpush