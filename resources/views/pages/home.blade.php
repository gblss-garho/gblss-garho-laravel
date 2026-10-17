@extends('layouts.app')

@section('title', 'GBLSS Garho')

@section('content')
    <h1>GBLSS Garho — Laravel skeleton is live.</h1>
    <p>Phase 1 complete. Public site content migrates in Phase 7.</p>
    <p><a href="{{ route('login', [], false) }}">Go to Login</a></p>
    <p><a href="{{ route('public.lookup') }}">Parents Corner</a> | <a href="{{ route('public.gallery') }}">Gallery</a> | <a href="{{ route('public.textbooks') }}">Textbooks</a> | <a href="{{ route('public.feedback') }}">Feedback</a></p>
@endsection
