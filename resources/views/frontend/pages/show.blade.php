@extends('frontend.layouts.app')

@section(
    'title',
    $page->meta_title ?: $page->title
)

@section(
    'meta_description',
    $page->meta_description
        ?: ($page->excerpt ?? '')
)

@section('content')
    <article class="public-detail">
        <div class="container public-detail-narrow">
            {{-- Server-rendered page heading --}}
            <x-frontend.breadcrumb :title="$page->title" />

            <header class="public-detail-header">
                <h1>{{ $page->title }}</h1>
            </header>

            <div class="public-prose">
                {!! $page->content !!}
            </div>
        </div>
    </article>

@include('frontend.partials.page-interactions', ['interactionModel' => $page])
@endsection
