@extends('frontend.layouts.app')

@section('title', $pageTitle)

@section('content')
    @include('frontend.ppid.partials.exempt-directory')
@endsection

@push('styles')
<link rel="stylesheet"
      href="{{ asset('css/frontend/pages/ppid-directory.css') }}?v={{ filemtime(public_path('css/frontend/pages/ppid-directory.css')) }}">
<link rel="stylesheet"
      href="{{ asset('css/frontend/pages/ppid-exempt.css') }}?v={{ filemtime(public_path('css/frontend/pages/ppid-exempt.css')) }}">
@endpush