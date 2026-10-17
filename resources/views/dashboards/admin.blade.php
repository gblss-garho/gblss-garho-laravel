@extends('layouts.app')

@section('title', ucfirst('admin') . ' Dashboard')

@section('content')
    <h1>Admin Dashboard</h1>
    <p>Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
    <p><a href="{{ route('admin.promotions.history') }}">Promotion History</a></p>
    <p><a href="{{ route('admin.students.index', [], false) }}">Students</a></p>
    <p><a href="{{ route('admin.teachers.index', [], false) }}">Teachers</a></p>
    <p><a href="{{ route('admin.results.index', [], false) }}">Results</a></p>
    <p><a href="{{ route('admin.content.index', [], false) }}">Notices &amp; Calendar</a></p>
    <p><a href="{{ route('admin.leaving-certificates.index', [], false) }}">Leaving Certificates</a></p>
    <p><a href="{{ route('admin.id-cards.index', [], false) }}">ID Cards</a></p>
    <p><a href="{{ route('admin.admit-cards.index', [], false) }}">Admit Cards</a></p>
    <p><a href="{{ route('admin.staff-id-cards.index', [], false) }}">Staff ID Cards</a></p>
    <p><a href="{{ route('admin.settings.edit', [], false) }}">School Settings</a></p>
    <p><a href="{{ route('admin.leave.index', [], false) }}">Teacher Leave Requests</a></p>
    <p><a href="{{ route('admin.media.index', [], false) }}">Gallery, Textbooks &amp; Feedback</a></p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
@endsection
