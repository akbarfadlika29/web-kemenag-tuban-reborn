@extends('frontend.layouts.app')

@section('title', $pageTitle)

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/ppid-directory.css') }}?v={{ filemtime(public_path('css/frontend/ppid-directory.css')) }}"
    >
@endpush

@section('content')
@php
    $descriptions = [
        '' => 'Temukan informasi publik berdasarkan klasifikasi, tahun, dan unit pengelola.',
        'berkala' => 'Telusuri informasi publik yang diumumkan secara berkala.',
        'serta_merta' => 'Telusuri informasi publik yang diumumkan segera untuk diketahui masyarakat.',
        'setiap_saat' => 'Telusuri informasi publik yang tersedia setiap saat.',
    ];

    $tabLabels = [
        '' => 'Semua Informasi',
        'berkala' => 'Berkala',
        'serta_merta' => 'Serta-merta',
        'setiap_saat' => 'Setiap Saat',
    ];

    $filterParams = array_filter([
        'search' => $search,
        'year' => $year,
        'unit_id' => $unitId,
    ], fn ($value) => $value !== '' && $value !== null);

    $hasFilters = $search !== '' || $year !== '' || $unitId !== '';
@endphp

<div class="dip-page">
    <div class="container">
        <x-frontend.breadcrumb
    :title="$pageTitle"
    :parent-label="$classification !== '' ? 'Informasi Publik' : null"
    :parent-url="$classification !== '' ? route('ppid.index') : null"
/>

        <header class="dip-heading">
            <span class="dip-eyebrow">LAYANAN PPID</span>
            <h1>{{ $pageTitle }}</h1>
            <p>{{ $descriptions[$classification] }}</p>
        </header>

        <nav class="dip-tabs" aria-label="Klasifikasi informasi publik">
            @foreach ($tabLabels as $value => $label)
                <a
                    href="{{ route('ppid.index', array_merge(
                        $filterParams,
                        $value !== '' ? ['classification' => $value] : []
                    )) }}"
                    class="dip-tab {{ $classification === $value ? 'is-active' : '' }}"
                    @if ($classification === $value) aria-current="page" @endif
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <section class="dip-panel" aria-label="Pencarian dan daftar informasi">
            <x-frontend.filter-panel>
<form
                method="GET"
                action="{{ route('ppid.index') }}"
                class="dip-filter"
                role="search"
                aria-label="Cari informasi publik"
            >
                @if ($classification !== '')
                    <input
                        type="hidden"
                        name="classification"
                        value="{{ $classification }}"
                    >
                @endif

                <div class="dip-field dip-field-search">
                    <label for="dip-search">Pencarian</label>
                    <input
                        id="dip-search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        maxlength="200"
                        placeholder="Cari judul atau ringkasan informasi…"
                    >
                </div>

                <div class="dip-field">
                    <label for="dip-year">Tahun</label>
                    <select id="dip-year" name="year">
                        <option value="">Semua tahun</option>
                        @foreach ($years as $optionYear)
                            <option
                                value="{{ $optionYear }}"
                                @selected((string) $year === (string) $optionYear)
                            >
                                {{ $optionYear }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="dip-field">
                    <label for="dip-unit">Unit pengelola</label>
                    <select id="dip-unit" name="unit_id">
                        <option value="">Semua unit</option>
                        @foreach ($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected((string) $unitId === (string) $unit->id)
                            >
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="dip-filter-actions">
                    <button class="dip-btn dip-btn-primary" type="submit">
                        Cari
                    </button>

                    @if ($hasFilters)
                        <a
                            class="dip-btn dip-btn-secondary"
                            href="{{ route('ppid.index', $classification !== ''
                                ? ['classification' => $classification]
                                : []) }}"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
</x-frontend.filter-panel>

            <div class="dip-result">
                <h2>Daftar informasi</h2>
                <span>{{ number_format($informations->total(), 0, ',', '.') }} informasi</span>
            </div>

            @if ($informations->count())
                <div class="dip-table-wrap">
                    <x-frontend.table-scroll label="Daftar informasi publik">
<table class="dip-table">
                        <caption class="dip-sr-only">
                            {{ $pageTitle }} — daftar informasi dan unit pengelola
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="dip-col-number">No.</th>
                                <th scope="col">Informasi</th>
                                <th scope="col">Klasifikasi</th>
                                <th scope="col" class="dip-col-year">Tahun</th>
                                <th scope="col">Unit Pengelola</th>
                                <th scope="col" class="dip-col-action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($informations as $information)
                                <tr>
                                    <td class="dip-cell-number">
                                        {{ $informations->firstItem() + $loop->index }}
                                    </td>

                                    <th scope="row" class="dip-cell-title">
                                        <a
                                            class="dip-title-link"
                                            href="{{ route('ppid.show', $information->slug) }}"
                                        >
                                            {{ $information->title }}
                                        </a>

                                        @if ($information->category)
                                            <span class="dip-table-category">
                                                {{ $information->category->name }}
                                            </span>
                                        @endif

                                        @php
                                            $summary = trim(strip_tags(
                                                $information->excerpt
                                                    ?: ($information->description ?? '')
                                            ));
                                        @endphp

                                        @if ($summary !== '')
                                            <p class="dip-table-summary">
                                                {{ \Illuminate\Support\Str::limit($summary, 120) }}
                                            </p>
                                        @endif
                                    </th>

                                    <td data-label="Klasifikasi">
                                        <span class="dip-badge">
                                            {{ $classifications[$information->classification]
                                                ?? $information->classification_label }}
                                        </span>
                                    </td>

                                    <td data-label="Tahun" class="dip-cell-year">
                                        <span>{{ $information->year ?: '—' }}</span>
                                    </td>

                                    <td data-label="Unit Pengelola" class="dip-cell-unit">
                                        <span>{{ $information->unit?->name ?? '—' }}</span>
                                    </td>

                                    <td class="dip-cell-action">
                                        <a
                                            class="dip-btn dip-btn-secondary"
                                            href="{{ route('ppid.show', $information->slug) }}"
                                        >
                                            Lihat Detail
                                            <span class="dip-sr-only">: {{ $information->title }}</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
</x-frontend.table-scroll>
                </div>
            @else
                <div class="dip-empty">
                    <h3>{{ $hasFilters
                        ? 'Informasi tidak ditemukan'
                        : 'Belum ada informasi' }}</h3>
                    <p>{{ $hasFilters
                        ? 'Coba kata kunci lain atau reset filter pencarian.'
                        : 'Informasi yang diterbitkan akan ditampilkan di halaman ini.' }}</p>
                </div>
            @endif

            <footer class="dip-pagination">
                <p>
                    @if ($informations->count())
                        Menampilkan {{ $informations->firstItem() }}–{{ $informations->lastItem() }}
                        dari {{ $informations->total() }} informasi
                    @else
                        Menampilkan 0 dari {{ $informations->total() }} informasi
                    @endif
                </p>

                @if ($informations->hasPages())
                    <nav class="dip-page-links" aria-label="Halaman daftar informasi">
                        @if ($informations->onFirstPage())
                            <span class="dip-btn dip-btn-secondary is-disabled">Sebelumnya</span>
                        @else
                            <a class="dip-btn dip-btn-secondary"
                               href="{{ $informations->previousPageUrl() }}" rel="prev">
                                Sebelumnya
                            </a>
                        @endif

                        <span class="dip-page-number">
                            {{ $informations->currentPage() }} / {{ $informations->lastPage() }}
                        </span>

                        @if ($informations->hasMorePages())
                            <a class="dip-btn dip-btn-secondary"
                               href="{{ $informations->nextPageUrl() }}" rel="next">
                                Berikutnya
                            </a>
                        @else
                            <span class="dip-btn dip-btn-secondary is-disabled">Berikutnya</span>
                        @endif
                    </nav>
                @endif
            </footer>
        </section>
    </div>
</div>
@endsection
