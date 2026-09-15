@extends('frontend.layouts.app')

@section('title', $news->meta_title ?: $news->title)

@section(
    'meta_description',
    $news->meta_description ?: ($news->excerpt ?? '')
)

@section('content')
    <article class="public-detail">
        <div class="container public-detail-narrow">
            <x-frontend.breadcrumb :title="$news->title"
    parent-label="Berita"
    :parent-url="route('news.index')" />

            <header class="public-detail-header">
                {{-- news-category-link:section-kicker --}}
@if ($news->category?->is_active)
    <a class="section-kicker news-category-link" href="{{ route('news.index', ['category' => $news->category->slug]) }}">
        {{ $news->category->name }}
    </a>
@else
    <span class="section-kicker">{{ $news->category?->name ?? 'Berita' }}</span>
@endif

                <h1>
                    {{ $news->title }}
                </h1>

                <div class="public-detail-meta">
                    <span>
                        {{ optional($news->published_at)->translatedFormat('d F Y H:i') }}
                    </span>

                    @if ($news->unit)
                        <span>
                            {{ $news->unit->name }}
                        </span>
                    @endif
                </div>
            </header>

            @if ($news->coverMedia)
                <img
                    class="public-detail-cover"
                    src="{{ Storage::disk(
                        $news->coverMedia->disk
                    )->url(
                        $news->coverMedia->path
                    ) }}"
                    alt="{{ $news->coverMedia->alt_text ?? $news->title }}"
                >
            @endif

            <div class="public-prose">
                {!! $news->content !!}
            </div>

            @if ($news->tags->isNotEmpty())
                <div class="public-tags">
                    @foreach ($news->tags as $tag)
                        <a
    class="news-tag-link"
    href="{{ route('news.index', ['tag' => $tag->slug]) }}"
>
    #{{ $tag->name }}
</a>
                    @endforeach
                </div>
            @endif
        </div>
    </article>

    @if ($relatedNews->isNotEmpty())
        <section class="section section-soft">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <span class="section-kicker">
                            Berita Lainnya
                        </span>

                        <h2>
                            Baca Juga
                        </h2>
                    </div>
                </div>

                <div class="public-related-grid">
                    @foreach ($relatedNews as $item)
                        <a
                            href="{{ route('news.show', $item->slug) }}"
                            class="public-related-card"
                        >
                            <strong>
                                {{ $item->title }}
                            </strong>

                            <span>
                                {{ optional($item->published_at)->translatedFormat('d F Y') }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@include('frontend.partials.page-interactions', ['interactionModel' => $news])
@endsection