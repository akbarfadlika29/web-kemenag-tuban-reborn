@extends('frontend.layouts.app')
@section('title', $selectedType ? 'Regulasi: '.$selectedType->name : 'Regulasi')

@section('content')
<x-frontend.page-header>
    <x-slot:title>{{ $selectedType?->name ?? 'Regulasi' }}</x-slot:title>
    <x-slot:description>Temukan naskah regulasi dan dokumen pendukung.</x-slot:description>
</x-frontend.page-header>

<section class="section">
    <div class="container">
        <x-frontend.filter-panel>
<form class="public-search reg-filter" method="GET"
            action="{{ route('regulations.index') }}">
            <input name="search" value="{{ request('search') }}"
                placeholder="Cari judul atau nomor" aria-label="Cari regulasi">

            <select name="type" aria-label="Jenis regulasi">
                <option value="">Semua jenis</option>
                @foreach($types as $type)
                    <option value="{{ $type->slug }}" @selected(request('type') === $type->slug)>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>

            <input type="number" name="year" value="{{ request('year') }}"
                min="1900" max="2200" placeholder="Tahun" aria-label="Tahun">

            <button class="button button-primary">Cari</button>
            <a href="{{ route('regulations.index') }}">Atur ulang</a>
        </form>
</x-frontend.filter-panel>

        <p>{{ number_format($items->total(), 0, ',', '.') }} regulasi ditemukan.</p>

        <div class="reg-public-list">
            @forelse($items as $item)
                <article class="reg-public-card">
                    <a class="reg-type"
                        href="{{ route('regulations.index', ['type' => $item->type->slug]) }}">
                        {{ $item->type->name }}
                    </a>

                    <h2>
                        <a href="{{ route('regulations.show', $item->slug) }}">
                            {{ $item->title }}
                        </a>
                    </h2>

                    <p>Nomor {{ $item->number }} · Tahun {{ $item->year }}</p>
                    <p>{{ $item->summary }}</p>

                    <div class="reg-actions">
                        <span>{{ $item->legal_label }}</span>
                        <a href="{{ route('regulations.show', $item->slug) }}">
                            Detail dan dokumen →
                        </a>
                    </div>
                </article>
            @empty
                <div class="empty-public-state">Belum ada regulasi yang sesuai.</div>
            @endforelse
        </div>

        <div class="public-pagination">{{ $items->links() }}</div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/regulations.css') }}">
@endpush
