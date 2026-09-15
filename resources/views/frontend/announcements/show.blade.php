@extends('frontend.layouts.app')

@section('title', $announcement->meta_title ?: $announcement->title)

@section('content')
    <article class="public-detail">
        <div class="container public-detail-narrow">
            <x-frontend.breadcrumb :title="$announcement->title"
    parent-label="Pengumuman"
    :parent-url="route('announcements.index')" />

            <header class="public-detail-header">
                <span class="section-kicker">
                    Pengumuman
                </span>

                <h1>
                    {{ $announcement->title }}
                </h1>

                <div class="public-detail-meta">
                    <span>
                        {{ optional($announcement->published_at)->translatedFormat('d F Y') }}
                    </span>

                    @if ($announcement->unit)
                        <span>
                            {{ $announcement->unit->name }}
                        </span>
                    @endif
                </div>
            </header>

            @if ($announcement->coverMedia)
                <img
                    class="public-detail-cover"
                    src="{{ Storage::disk(
                        $announcement->coverMedia->disk
                    )->url(
                        $announcement->coverMedia->path
                    ) }}"
                    alt="{{ $announcement->title }}"
                >
            @endif

            <div class="public-prose">
                {!! $announcement->content !!}
            </div>

            @if (
                isset($announcement->attachmentMedia)
                && $announcement->attachmentMedia
            )
                <div class="public-download-card">
                    <div>
                        <strong>
                            Dokumen Lampiran
                        </strong>

                        <span>
                            {{ $announcement->attachmentMedia->original_name }}
                        </span>
                    </div>

                    <a
                        href="{{ Storage::disk(
                            $announcement->attachmentMedia->disk
                        )->url(
                            $announcement->attachmentMedia->path
                        ) }}"
                        target="_blank"
                        rel="noopener"
                    >
                        Buka Dokumen
                    </a>
                </div>
            @endif
        </div>
    </article>

@include('frontend.partials.page-interactions', ['interactionModel' => $announcement])
@endsection