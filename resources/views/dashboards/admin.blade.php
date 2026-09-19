@extends('layouts.app')

@section('title', ucfirst('admin') . ' Dashboard')

@section('content')
    <h1>Admin Dashboard</h1>
    <p>Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
    <p><a href="{{ route('admin.promotions.history') }}">Promotion History</a></p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
@endsection
