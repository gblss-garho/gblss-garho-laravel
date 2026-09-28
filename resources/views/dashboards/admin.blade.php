@extends('layouts.app')

@section('title', ucfirst('admin') . ' Dashboard')

@section('content')
    <h1>Admin Dashboard</h1>
    <p>Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
    <p><a href="{{ route('admin.promotions.history') }}">Promotion History</a></p>
    <p><a href="{{ route('admin.leaving-certificates.index', [], false) }}">Leaving Certificates</a></p>
    <p><a href="{{ route('admin.id-cards.index', [], false) }}">ID Cards</a></p>
    <p><a href="{{ route('admin.admit-cards.index', [], false) }}">Admit Cards</a></p>
    <p><a href="{{ route('admin.staff-id-cards.index', [], false) }}">Staff ID Cards</a></p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
@endsection
