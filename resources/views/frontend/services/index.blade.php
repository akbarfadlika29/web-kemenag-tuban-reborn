@extends('frontend.layouts.app')

@section('title', 'Layanan Publik')

@section('content')
    <x-frontend.page-header class="services-header">
        <x-slot:title>Layanan Publik</x-slot:title>
        <x-slot:description>
            Temukan layanan berdasarkan nama, kategori, dan kanal pelayanan.
        </x-slot:description>
    </x-frontend.page-header>

    <section class="section services-directory">
        <div class="container">
            <x-frontend.filter-panel label="Cari dan saring layanan publik">
                <form method="GET"
                      action="{{ route('services.index') }}"
                      class="services-directory-filters">
                    <label class="services-directory-search">
                        Cari layanan
                        <input type="search"
                               name="search"
                               value="{{ $search }}"
                               maxlength="200"
                               placeholder="Nama atau ringkasan layanan">
                    </label>

                    <label>
                        Kategori
                        <select name="category_id">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected((string) $categoryId === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Kanal
                        <select name="channel">
                            <option value="">Semua kanal</option>
                            <option value="online"
                                @selected($channel === 'online')>
                                Online
                            </option>
                            <option value="offline"
                                @selected($channel === 'offline')>
                                Offline
                            </option>
                            <option value="hybrid"
                                @selected($channel === 'hybrid')>
                                Online & Offline
                            </option>
                        </select>
                    </label>

                    <button type="submit" class="services-directory-apply">
                        Terapkan
                    </button>

                    <a href="{{ route('services.index') }}"
                       class="services-directory-reset">
                        Atur ulang
                    </a>
                </form>
            </x-frontend.filter-panel>

            <div class="services-directory-panel">
                <div class="services-directory-heading">
                    <div>
                        <h2>Daftar Layanan</h2>
                        <p>Informasi layanan yang tersedia untuk masyarakat.</p>
                    </div>

                    <span class="services-directory-count">
                        {{ number_format($services->total(), 0, ',', '.') }}
                        layanan
                    </span>
                </div>

                <p class="services-directory-scroll-hint">
                    Geser tabel ke samping pada layar kecil untuk melihat
                    seluruh informasi.
                </p>

                <x-frontend.table-scroll label="Daftar layanan publik">
                    <table class="services-directory-table">
                        <caption class="services-directory-caption">
                            Daftar layanan publik
                        </caption>

                        <colgroup>
                            <col style="width:31%">
                            <col style="width:16%">
                            <col style="width:18%">
                            <col style="width:13%">
                            <col style="width:12%">
                            <col style="width:10%">
                        </colgroup>

                        <thead>
                            <tr>
                                <th scope="col">Layanan</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Unit Pengelola</th>
                                <th scope="col">Kanal</th>
                                <th scope="col">Biaya</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($services as $service)
                                <tr>
                                    <th scope="row">
                                        <a href="{{ route('services.show', $service->slug) }}"
                                           class="services-directory-name">
                                            {{ $service->title }}
                                        </a>

                                        @if ($service->excerpt || $service->description)
                                            <span class="services-directory-excerpt">
                                                {{ Str::limit(
                                                    $service->excerpt
                                                        ?: strip_tags($service->description ?? ''),
                                                    110
                                                ) }}
                                            </span>
                                        @endif
                                    </th>

                                    <td>
                                        {{ $service->category?->name ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $service->unit?->name ?? '—' }}
                                    </td>

                                    <td>
                                        <span class="services-directory-channel">
                                            {{ $service->service_channel_label }}
                                        </span>
                                    </td>

                                    <td>
                                        @if ($service->is_free)
                                            <span class="services-directory-free">
                                                Gratis
                                            </span>
                                        @else
                                            {{ $service->fee_description ?: 'Lihat rincian' }}
                                        @endif
                                    </td>

                                    <td class="services-directory-action-cell">
                                        <a href="{{ route('services.show', $service->slug) }}"
                                           class="services-directory-view"
                                           aria-label="Lihat layanan {{ $service->title }}">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="services-directory-empty">
                                        Tidak ada layanan yang sesuai dengan filter Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-frontend.table-scroll>

                <div class="services-directory-footer">
                    <p>
                        Menampilkan {{ $services->firstItem() ?? 0 }}–{{ $services->lastItem() ?? 0 }}
                        dari {{ number_format($services->total(), 0, ',', '.') }}
                        layanan
                    </p>

                    @if ($services->hasPages())
                        {{ $services->links() }}
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('page-styles')
    <link rel="stylesheet"
          href="{{ asset('css/frontend/pages/services-directory-table.css') }}?v={{ filemtime(public_path('css/frontend/pages/services-directory-table.css')) }}">
@endpush