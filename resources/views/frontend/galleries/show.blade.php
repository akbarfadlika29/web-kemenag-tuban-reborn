@extends('frontend.layouts.app')

@section('title', $gallery->meta_title ?: $gallery->title)

@section('content')
    <article class="public-detail">
        <div class="container">
            <x-frontend.breadcrumb :title="$gallery->title"
    parent-label="Galeri"
    :parent-url="route('galleries.index')" />

            <header class="public-detail-header public-detail-narrow">
                <span class="section-kicker">
                    Galeri
                </span>

                <h1>
                    {{ $gallery->title }}
                </h1>

                @if ($gallery->excerpt)
                    <p class="public-detail-lead">
                        {{ $gallery->excerpt }}
                    </p>
                @endif
            </header>

            <div class="gallery-detail-grid">
                @foreach ($gallery->items as $item)
                    @if ($item->media)
                        <figure>
                            <img
                                src="{{ Storage::disk(
                                    $item->media->disk
                                )->url(
                                    $item->media->path
                                ) }}"
                                alt="{{ $item->alt_text ?: $item->media->alt_text ?: $gallery->title }}"
                            >

                            @if ($item->caption)
                                <figcaption>
                                    {{ $item->caption }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                @endforeach
            </div>

            @if ($gallery->description)
                <div class="public-prose public-detail-narrow">
                    {!! $gallery->description !!}
                </div>
            @endif
        </div>
    </article>

@include('frontend.partials.page-interactions', ['interactionModel' => $gallery])
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/pages/galleries.css') }}?v={{ filemtime(public_path('css/frontend/pages/galleries.css')) }}"
    >
@endpush
