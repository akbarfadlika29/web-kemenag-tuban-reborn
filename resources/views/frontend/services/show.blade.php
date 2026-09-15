@extends('frontend.layouts.app')

@section('title', $service->meta_title ?: $service->title)

@section(
    'meta_description',
    $service->meta_description ?: ($service->excerpt ?? '')
)

@section('content')
    <article class="public-detail">
        <div class="container">
            <div class="public-breadcrumb public-detail-narrow">
                <a href="{{ route('home') }}">
                    Beranda
                </a>

                <span>/</span>

                <a href="{{ route('services.index') }}">
                    Layanan
                </a>
            </div>

            <div class="service-detail-layout">
                <main>
                    <header class="public-detail-header">
                        <span class="section-kicker">
                            {{ $service->category?->name ?? 'Layanan Publik' }}
                        </span>

                        <h1>
                            {{ $service->title }}
                        </h1>

                        @if ($service->excerpt)
                            <p class="public-detail-lead">
                                {{ $service->excerpt }}
                            </p>
                        @endif
                    </header>

                    @if ($service->coverMedia)
                        <img
                            class="public-detail-cover"
                            src="{{ Storage::disk(
                                $service->coverMedia->disk
                            )->url(
                                $service->coverMedia->path
                            ) }}"
                            alt="{{ $service->title }}"
                        >
                    @endif

                    <div class="public-prose">
                        {!! $service->description !!}
                    </div>

                    @if ($service->requirements)
                        <section class="public-detail-section">
                            <h2>Persyaratan</h2>

                            <div class="public-prose">
                                {!! nl2br(e($service->requirements)) !!}
                            </div>
                        </section>
                    @endif

                    @if ($service->procedure)
                        <section class="public-detail-section">
                            <h2>Prosedur / Alur Pelayanan</h2>

                            <div class="public-prose">
                                {!! nl2br(e($service->procedure)) !!}
                            </div>
                        </section>
                    @endif

                    @if ($service->legal_basis)
                        <section class="public-detail-section">
                            <h2>Dasar Hukum</h2>

                            <div class="public-prose">
                                {!! nl2br(e($service->legal_basis)) !!}
                            </div>
                        </section>
                    @endif
                </main>

                <aside class="service-detail-sidebar">
                    <div class="service-info-card">
                        <h2>
                            Informasi Layanan
                        </h2>

                        <dl>
                            <div>
                                <dt>Kanal</dt>

                                <dd>
                                    {{ $service->service_channel_label }}
                                </dd>
                            </div>

                            <div>
                                <dt>Biaya</dt>

                                <dd>
                                    @if ($service->is_free)
                                        Gratis
                                    @else
                                        {{ $service->fee_description ?: 'Sesuai ketentuan' }}
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>Waktu Penyelesaian</dt>

                                <dd>
                                    {{ $service->completion_time ?: '—' }}
                                </dd>
                            </div>

                            <div>
                                <dt>Produk Layanan</dt>

                                <dd>
                                    {{ $service->service_output ?: '—' }}
                                </dd>
                            </div>

                            <div>
                                <dt>Unit Kerja</dt>

                                <dd>
                                    {{ $service->unit?->name ?? '—' }}
                                </dd>
                            </div>

                            @if ($service->service_location)
                                <div>
                                    <dt>Lokasi</dt>

                                    <dd>
                                        {{ $service->service_location }}
                                    </dd>
                                </div>
                            @endif

                            @if ($service->service_hours)
                                <div>
                                    <dt>Jam Pelayanan</dt>

                                    <dd>
                                        {{ $service->service_hours }}
                                    </dd>
                                </div>
                            @endif

                            @if ($service->contact_phone)
                                <div>
                                    <dt>Telepon</dt>

                                    <dd>
                                        {{ $service->contact_phone }}
                                    </dd>
                                </div>
                            @endif

                            @if ($service->contact_email)
                                <div>
                                    <dt>Email</dt>

                                    <dd>
                                        {{ $service->contact_email }}
                                    </dd>
                                </div>
                            @endif
                        </dl>

                        @if (
                            in_array(
                                $service->service_channel,
                                ['online', 'hybrid'],
                                true
                            )
                            && $service->service_url
                        )
                            <a
                                href="{{ $service->service_url }}"
                                target="_blank"
                                rel="noopener"
                                class="button button-primary service-online-button"
                            >
                                Buka Layanan Online
                            </a>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </article>

@include('frontend.partials.page-interactions', ['interactionModel' => $service])
@endsection