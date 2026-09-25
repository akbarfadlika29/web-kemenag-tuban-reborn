{{-- PPID_REMOVE_PLACEHOLDER_COPY --}}
@php
    $ppidHasMeaningfulText = static function ($value): bool {
        $text = html_entity_decode(
            strip_tags((string) $value),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = preg_replace('/[\s\x{00A0}]+/u', ' ', $text);
        $text = mb_strtolower(trim((string) $text));

        return $text !== ''
            && !in_array($text, ['informasi publik', 'informasi publik.'], true);
    };
@endphp
@extends('frontend.layouts.app')

@section('title', $information->meta_title ?: $information->title)

@section('content')
    <article class="public-detail">
        <div class="container public-detail-narrow">
            <x-frontend.breadcrumb :title="$information->title"
    parent-label="Informasi Publik"
    :parent-url="route('ppid.index')" />

            <header class="public-detail-header">
                <span class="section-kicker">
                    {{ $information->classification_label }}
                </span>

                <h1>
                    {{ $information->title }}
                </h1>
            </header>

            <div class="ppid-metadata-grid">
                <div>
                    <span>Kategori</span>
                    <strong>
                        {{ $information->category?->name ?? '—' }}
                    </strong>
                </div>

                <div>
                    <span>Unit Pengelola</span>
                    <strong>
                        {{ $information->unit?->name ?? '—' }}
                    </strong>
                </div>

                <div>
                    <span>Tahun</span>
                    <strong>
                        {{ $information->year ?: '—' }}
                    </strong>
                </div>

                <div>
                    <span>Akses</span>
                    <strong>
                        {{ $information->access_level_label }}
                    </strong>
                </div>

                <div>
                    <span>Ketersediaan</span>
                    <strong>
                        {{ $information->availability_label }}
                    </strong>
                </div>

                <div>
                    <span>Pemegang Informasi</span>
                    <strong>
                        {{ $information->information_holder ?: '—' }}
                    </strong>
                </div>
            </div>

            @if ($information->excerpt)
                <p class="public-detail-lead">
                    @if ($ppidHasMeaningfulText($information->excerpt))
{{ $information->excerpt }}
@endif
                </p>
            @endif

            <div class="public-prose">
                @if ($ppidHasMeaningfulText($information->description))
{!! $information->description !!}
@endif
            </div>

            @if ($information->legal_basis)
                <section class="public-detail-section">
                    <h2>Dasar Hukum</h2>

                    <p>
                        {!! nl2br(e($information->legal_basis)) !!}
                    </p>
                </section>
            @endif

            <section class="public-detail-section">
                <h2>Dokumen Informasi</h2>

                <div class="public-documents">
                    @forelse ($information->documents as $document)
                        @if ($document->media)
                            <div class="public-document-card">
                                <div>
                                    <strong>
                                        {{ $document->title ?: $document->media->original_name }}
                                    </strong>

                                    <span>
                                        @if ($document->version)
                                            Versi {{ $document->version }}
                                        @endif

                                        @if ($document->document_date)
                                            · {{ $document->document_date->translatedFormat('d F Y') }}
                                        @endif
                                    </span>
                                </div>

                                <a
                                    href="{{ Storage::disk(
                                        $document->media->disk
                                    )->url(
                                        $document->media->path
                                    ) }}"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Buka
                                </a>
                            </div>
                        @endif
                    @empty
                        <div class="empty-public-state">
                            Tidak ada dokumen.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </article>

@include('frontend.partials.page-interactions', ['interactionModel' => $information])
@endsection