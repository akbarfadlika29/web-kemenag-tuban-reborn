@extends('frontend.layouts.app')

@section('title', $service->meta_title ?: $service->title)
@section('meta_description', $service->meta_description ?: ($service->excerpt ?? ''))

@section('content')
<article class="public-detail service-detail-page">
    <div class="container">
        <x-frontend.breadcrumb
            :title="$service->title"
            parent-label="Layanan Publik"
            :parent-url="route('services.index')"
        />

        <header class="service-detail-heading public-detail-header">
            <span class="service-detail-category">
                {{ $service->category?->name ?? 'Layanan Publik' }}
            </span>
            <h1>{{ $service->title }}</h1>

        </header>

        @if ($service->coverMedia)
            <img
                class="service-detail-cover"
                src="{{ Storage::disk($service->coverMedia->disk)->url($service->coverMedia->path) }}"
                alt="{{ $service->coverMedia->alt_text ?: $service->title }}"
                loading="eager"
            >
        @endif

        <div class="service-detail-sections">
            <section class="service-detail-section" aria-labelledby="service-requirements">
                <span class="service-detail-number" aria-hidden="true">01</span>
                <div>
                    <h2 id="service-requirements">Persyaratan</h2>
                    <div class="service-detail-value">
                        @if ($service->requirements)
                            {!! nl2br(e($service->requirements)) !!}
                        @else
                            <span class="service-detail-empty">Belum dicantumkan.</span>
                        @endif
                    </div>
                </div>
            </section>

            <section class="service-detail-section" aria-labelledby="service-procedure">
                <span class="service-detail-number" aria-hidden="true">02</span>
                <div>
                    <h2 id="service-procedure">Prosedur</h2>
                    <div class="service-detail-value">
                        @if ($service->procedure)
                            {!! nl2br(e($service->procedure)) !!}
                        @else
                            <span class="service-detail-empty">Belum dicantumkan.</span>
                        @endif
                    </div>
                </div>
            </section>

            <section class="service-detail-section" aria-labelledby="service-time">
                <span class="service-detail-number" aria-hidden="true">03</span>
                <div>
                    <h2 id="service-time">Waktu Pelayanan</h2>
                    <div class="service-detail-value">
                        @if ($service->completion_time)
                            <p><strong>Waktu penyelesaian:</strong> {{ $service->completion_time }}</p>
                        @endif
                        @if ($service->service_hours)
                            <p><strong>Jam pelayanan:</strong> {{ $service->service_hours }}</p>
                        @endif
                        @unless ($service->completion_time || $service->service_hours)
                            <span class="service-detail-empty">Belum dicantumkan.</span>
                        @endunless
                    </div>
                </div>
            </section>

            <section class="service-detail-section" aria-labelledby="service-fee">
                <span class="service-detail-number" aria-hidden="true">04</span>
                <div>
                    <h2 id="service-fee">Biaya / Tarif</h2>
                    <div class="service-detail-value">
                        @if ($service->is_free)
                            <span class="service-detail-free">Gratis</span>
                        @else
                            {{ $service->fee_description ?: 'Sesuai ketentuan yang berlaku.' }}
                        @endif
                    </div>
                </div>
            </section>

            <section class="service-detail-section" aria-labelledby="service-output">
                <span class="service-detail-number" aria-hidden="true">05</span>
                <div>
                    <h2 id="service-output">Produk Layanan</h2>
                    <div class="service-detail-value">
                        {{ $service->service_output ?: 'Belum dicantumkan.' }}
                    </div>
                </div>
            </section>

            <section class="service-detail-section" aria-labelledby="service-complaint">
                <span class="service-detail-number" aria-hidden="true">06</span>
                <div>
                    <h2 id="service-complaint">Pengelolaan Pengaduan</h2>
                    <div class="service-detail-value">
                        @if ($service->contact_name || $service->contact_phone || $service->contact_email)
                            <dl class="service-detail-contacts">
                                @if ($service->contact_name)
                                    <div>
                                        <dt>Kontak</dt>
                                        <dd>{{ $service->contact_name }}</dd>
                                    </div>
                                @endif
                                @if ($service->contact_phone)
                                    <div>
                                        <dt>Telepon</dt>
                                        <dd>{{ $service->contact_phone }}</dd>
                                    </div>
                                @endif
                                @if ($service->contact_email)
                                    <div>
                                        <dt>Email</dt>
                                        <dd>{{ $service->contact_email }}</dd>
                                    </div>
                                @endif
                            </dl>
                        @else
                            <span class="service-detail-empty">Kontak pengaduan belum dicantumkan.</span>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        @if (in_array($service->service_channel, ['online', 'hybrid'], true) && $service->service_url)
            <div class="service-detail-action">

                <a href="{{ $service->service_url }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="button button-primary">
                    Buka Layanan Online <span aria-hidden="true">↗</span>
                </a>
            </div>
        @endif

        <a class="service-detail-back" href="{{ route('services.index') }}">
            <span aria-hidden="true">←</span> Kembali ke daftar layanan
        </a>
    </div>
</article>

@include('frontend.partials.page-interactions', ['interactionModel' => $service])
@endsection

@push('page-styles')
<link rel="stylesheet"
      href="{{ asset('css/frontend/pages/service-detail.css') }}?v={{ filemtime(public_path('css/frontend/pages/service-detail.css')) }}">
@endpush

@push('page-styles')
<style>
/* Sembunyikan metadata layanan; tombol interaksi tetap mengikuti komponen global. */

</style>
@endpush