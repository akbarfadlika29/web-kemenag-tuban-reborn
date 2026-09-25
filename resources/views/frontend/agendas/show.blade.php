@extends('frontend.layouts.app')

@section(
    'title',
    $agenda->meta_title ?: $agenda->title
)

@section(
    'meta_description', 
    $agenda->meta_description
        ?: Str::limit(
            strip_tags($agenda->description ?? ''),
            155
        )
)

@section('content')
    

    <article class="public-detail">
        <div class="container public-detail-narrow">
            <x-frontend.breadcrumb :title="$agenda->title"
    parent-label="Agenda"
    :parent-url="route('agendas.index')" />

            <header class="public-detail-header">
                <span class="section-kicker">
                    Agenda
                </span>

                <h1>
                    {{ $agenda->title }}
                </h1>

                @if ($agenda->unit)
                    <div class="public-detail-meta">
                        <span>
                            {{ $agenda->unit->name }}
                        </span>
                    </div>
                @endif
            </header>

            <div class="agenda-detail-info">
                <div>
                    <span>
                        Tanggal
                    </span>

                    <strong>
                        {{ $agenda->start_at
                            ? $agenda->start_at->translatedFormat('d F Y')
                            : '—'
                        }}
                    </strong>
                </div>

                <div>
                    <span>
                        Waktu
                    </span>

                    <strong>
                        @if ($agenda->start_at)
                            {{ $agenda->start_at->format('H:i') }}

                            @if ($agenda->end_at)
                                – {{ $agenda->end_at->format('H:i') }}
                            @endif

                            WIB
                        @else
                            —
                        @endif
                    </strong>
                </div>

                <div>
                    <span>
                        Lokasi
                    </span>

                    <strong>
                        {{ $agenda->location ?: 'Belum ditentukan' }}
                    </strong>
                </div>
            </div>

            @if ($agenda->coverMedia)
                <figure class="agenda-detail-cover">
                    <img
                        class="public-detail-cover"
                        src="{{ Storage::disk(
                            $agenda->coverMedia->disk
                        )->url(
                            $agenda->coverMedia->path
                        ) }}"
                        alt="{{ $agenda->coverMedia->alt_text ?? $agenda->title }}"
                    >
                </figure>
            @endif

            @if ($agenda->description)
                <section class="public-detail-section agenda-description">
                    <h2>
                        Tentang Agenda
                    </h2>

                    <div class="public-prose">
                        {!! $agenda->description !!}
                    </div>
                </section>
            @endif

            @if ($agenda->unit)
                <section class="public-detail-section">
                    <h2>
                        Penyelenggara
                    </h2>

                    <div class="agenda-organizer-card">
                        <span>
                            Unit Kerja
                        </span>

                        <strong>
                            {{ $agenda->unit->name }}
                        </strong>
                    </div>
                </section>
            @endif

            <div class="agenda-detail-actions">
                <a
                    href="{{ route('agendas.index') }}"
                    class="button agenda-back-button"
                >
                    ← Kembali ke Daftar Agenda
                </a>
            </div>
        </div>
    </article>

@include('frontend.partials.page-interactions', ['interactionModel' => $agenda])
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/pages/agendas.css') }}?v={{ filemtime(public_path('css/frontend/pages/agendas.css')) }}"
    >
@endpush
