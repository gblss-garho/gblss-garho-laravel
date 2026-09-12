@extends('layouts.app')

@section('title', ucfirst('student') . ' Dashboard')

@section('content')
    <h1>Student Dashboard</h1>
    <p>Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
@endsection
