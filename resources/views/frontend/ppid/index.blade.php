@extends('frontend.layouts.app')

@section('title', $pageTitle)

@section('content')
    @if ($classification === 'dikecualikan')
        @include('frontend.ppid.partials.exempt-directory')
    @else
        @include('frontend.ppid.partials.directory')
    @endif
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/frontend/pages/ppid/directory.css') }}?v={{ filemtime(public_path('css/frontend/pages/ppid/directory.css')) }}"
    >

    @if ($classification === 'dikecualikan')
        <link
            rel="stylesheet"
            href="{{ asset('css/frontend/pages/ppid/exempt.css') }}?v={{ filemtime(public_path('css/frontend/pages/ppid/exempt.css')) }}"
        >
    @endif
@endpush
