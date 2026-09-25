@extends('frontend.layouts.app')

@section('title', $pageTitle)

@section('content')
    @include('frontend.ppid.partials.directory')
@endsection

@push('styles')
<link rel="stylesheet"
      href="{{ asset('css/frontend/pages/ppid-directory.css') }}?v={{ filemtime(public_path('css/frontend/pages/ppid-directory.css')) }}">
@endpush