@extends('layouts.admin')

@section('title', 'Informasi Publik')

@section('content')
    <x-ui.page-header
        title="Informasi Publik"
        description="Kelola informasi publik beserta dokumen pendukung."
    >
        <x-slot:actions>
            <x-ui.button
                :href="route('admin.ppid-categories.index')"
                variant="secondary"
            >
                Kategori PPID
            </x-ui.button>

            <x-ui.button
                :href="route('admin.ppid-informations.create')"
                variant="primary"
            >
                Tambah Informasi
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @php
        $filtersActive =
            $search !== ''
            || $classification !== ''
            || $categoryId !== ''
            || $unitId !== ''
            || $status !== ''
            || $year !== ''
            || $availability !== ''
            || $accessLevel !== '';
    @endphp

    <div class="ppid-index">
        <div class="ppid-index-filter-card">
            <form
                method="GET"
                action="{{ route('admin.ppid-informations.index') }}"
                class="ppid-index-filter"
            >
                <div class="ppid-index-search">
                    <label>Cari</label>

                    <input
                        type="search"
                        name="search"
                        class="ui-control"
                        value="{{ $search }}"
                        placeholder="Judul, nomor dokumen, penguasa informasi..."
                    >
                </div>

                <div>
                    <label>Klasifikasi</label>

                    <select
                        name="classification"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
                        </option>

                        <option
                            value="berkala"
                            @selected($classification === 'berkala')
                        >
                            Berkala
                        </option>

                        <option
                            value="serta_merta"
                            @selected($classification === 'serta_merta')
                        >
                            Serta Merta
                        </option>

                        <option
                            value="setiap_saat"
                            @selected($classification === 'setiap_saat')
                        >
                            Setiap Saat
                        </option>
<option value="dikecualikan"
    @selected($classification === 'dikecualikan')>
    Informasi Dikecualikan
</option>
                    </select>
                </div>

                <div>
                    <label>Kategori</label>

                    <select
                        name="category_id"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
                        </option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(
                                    (string) $categoryId ===
                                    (string) $category->id
                                )
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Unit Kerja</label>

                    <select
                        name="unit_id"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
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
                    <label>Status</label>

                    <select
                        name="status"
                        class="ui-control"
                    >
                        <option value="">
                            Semua
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
                    <label>Tahun</label>

                    <input
                        type="number"
                        name="year"
                        class="ui-control"
                        min="1900"
                        max="2100"
                        value="{{ $year }}"
                    >
                </div>

                <div class="ppid-index-filter-actions">
                    <x-ui.button
                        type="submit"
                        variant="primary"
                    >
                        Terapkan
                    </x-ui.button>

                    @if ($filtersActive)
                        <x-ui.button
                            :href="route('admin.ppid-informations.index')"
                            variant="secondary"
                        >
                            Atur Ulang
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>

        <div class="ppid-index-result">
            <strong>
                {{ $informations->total() }} informasi
            </strong>

            @if ($filtersActive)
                <span>
                    Filter aktif
                </span>
            @endif
        </div>

        <div class="ppid-index-table-card">
            <div class="ppid-index-table-scroll">
                <table class="ppid-index-table">
                    <thead>
                        <tr>
                            <th>Informasi</th>
                            <th>Klasifikasi</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Tahun</th>
                            <th>Dokumen</th>
                            <th>Status</th>
                            <th>Akses</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($informations as $item)
                            @php
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
                                <td class="ppid-index-title-cell">
                                    <strong>
                                        {{ $item->title }}
                                    </strong>

                                    <span>
                                        {{ $item->document_number ?: $item->slug }}
                                    </span>

                                    @if ($item->is_featured)
                                        <small>
                                            Unggulan
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <span class="ppid-classification is-{{ $item->classification }}">
                                        {{ $item->classification_label }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->category?->name ?? '—' }}
                                </td>

                                <td>
                                    {{ $item->unit?->name ?? '—' }}
                                </td>

                                <td>
                                    {{ $item->year ?: '—' }}
                                </td>

                                <td>
                                    {{ $item->documents_count }}
                                </td>

                                <td>
                                    <span class="ppid-publication is-{{ $publicationState }}">
                                        {{ $publicationLabel }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->access_level === 'public'
                                        ? 'Publik'
                                        : 'Terbatas'
                                    }}
                                </td>

                                <td>
                                    <div class="ppid-index-actions">
                                        <x-ui.button
                                            :href="route(
                                                'admin.ppid-informations.edit',
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
                                                'admin.ppid-informations.destroy',
                                                $item
                                            ) }}"
                                            data-confirm="Hapus informasi PPID ini?"
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
                                    colspan="9"
                                    class="ppid-index-empty"
                                >
                                    Belum ada Informasi Publik.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($informations->hasPages())
            {{ $informations->links() }}
        @endif
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/admin/pages/ppid-informations-index.css') }}"
    >
@endpush