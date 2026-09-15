@extends('frontend.layouts.app')
@section('title', $regulation->meta_title ?: $regulation->title)
@section('meta_description', $regulation->meta_description ?: ($regulation->summary ?? ''))

@section('content')
<article class="public-detail">
    <div class="container public-detail-narrow">
        <x-frontend.breadcrumb :title="$regulation->title"
    parent-label="Regulasi"
    :parent-url="route('regulations.index')" />

        <header class="public-detail-header">
            <a class="reg-type"
                href="{{ route('regulations.index', ['type' => $regulation->type->slug]) }}">
                {{ $regulation->type->name }}
            </a>
            <h1>{{ $regulation->title }}</h1>
            <p class="public-detail-lead">{{ $regulation->summary }}</p>
        </header>

        <dl class="reg-metadata">
            @foreach([
                'Nomor' => $regulation->number,
                'Tahun' => $regulation->year,
                'Penerbit' => $regulation->issuing_authority,
                'Bidang' => $regulation->subject,
                'Ditetapkan' => $regulation->issued_at?->translatedFormat('d F Y'),
                'Mulai berlaku' => $regulation->effective_at?->translatedFormat('d F Y'),
                'Status keberlakuan' => $regulation->legal_label,
            ] as $label => $value)
                <div><dt>{{ $label }}</dt><dd>{{ $value ?: '—' }}</dd></div>
            @endforeach
        </dl>

        @if($regulation->legal_status_note)
            <section class="reg-public-card reg-space">
                <h2>Catatan keberlakuan</h2>
                <p class="reg-text">{{ $regulation->legal_status_note }}</p>
            </section>
        @endif

        @if($regulation->description)
            <section class="public-detail-section">
                <h2>Keterangan</h2>
                <p class="reg-text">{{ $regulation->description }}</p>
            </section>
        @endif

        @if($regulation->source_url)
            <p class="reg-space">
                <a href="{{ $regulation->source_url }}" target="_blank"
                    rel="noopener noreferrer">Lihat sumber resmi ↗</a>
            </p>
        @endif

        <section class="public-detail-section">
            <h2>Naskah regulasi</h2>

            @foreach($regulation->documents->where('document_role', 'main') as $doc)
                @if(\App\Services\Regulation\RegulationService::usable($doc->media))
                    <div class="public-download-card">
                        <div>
                            <strong>{{ $doc->label }}</strong>
                            <span>
                                {{ $doc->document_role === 'main' ? 'Naskah utama' : 'Lampiran' }}
                                · PDF ·
                                {{ number_format($doc->media->size / 1048576, 2, ',', '.') }} MB
                            </span>
                        </div>
                        <div class="reg-actions">
                            <a target="_blank" rel="noopener"
                                href="{{ route('regulations.document', [$regulation->slug, $doc->id]) }}">
                                Lihat PDF
                            </a>
                            <a href="{{ route('regulations.document', [$regulation->slug, $doc->id, 'download' => 1]) }}">
                                Unduh
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </section>
    </div>
</article>

@include('frontend.partials.page-interactions', ['interactionModel' => $regulation])
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/regulations.css') }}">
@endpush
