@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('content')
    <h1>Teacher Dashboard</h1>
    <p>Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
    <p><a href="{{ route('teacher.attendance.index', [], false) }}">Attendance (Check In / Check Out)</a></p>
    <form method="POST" action="{{ route('logout', [], false) }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
@endsection
