@extends('layouts.app')

@section('title', 'Select Portal')

@section('content')
    <h1>Portals</h1>
    <p><a href="{{ route('login') }}">Login</a> to access your Admin / Teacher / Parent / Student dashboard.</p>
@endsection
