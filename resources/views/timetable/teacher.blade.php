@extends('layouts.portal')

@section('title', 'My Timetable')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/timetable.css') }}">
    <div class="tt">
        <p style="margin:0" class="no-print"><a href="{{ route('teacher.dashboard', [], false) }}">&larr; Dashboard</a></p>
        <h1 style="margin:4px 0 2px">Mera Timetable</h1>
        @if (! $teacher)
            <div class="tt-err">Aap ka teacher record nahi mila.</div>
        @else
            <p class="tt-note">{{ $teacher->name }} &middot; Somwar se Jumma</p>
            @include('timetable._grid', ['mode' => 'teacher'])
            <div class="tt-actions no-print"><button class="tt-btn alt" type="button" onclick="window.print()">Print</button></div>
        @endif
    </div>
@endsection
