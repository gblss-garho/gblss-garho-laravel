@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
    <h1>Teacher Attendance</h1>

    @if (session('status'))
        <p style="color:green">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div style="color:red">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (! $isSchoolDay)
        <p>Aaj school off hai (Saturday/Sunday).</p>
    @else
        <p>Aaj check-in window: 8:00–8:30 AM. Check-out window: school khatam hone se 15 minute pehle.</p>

        <p>
            Check-in status:
            {{ $record && $record->check_in_at ? $record->check_in_at->format('h:i A') : 'Abhi nahi hua' }}
        </p>
        <form method="POST" action="{{ route('teacher.attendance.checkin', [], false) }}">
            @csrf
            <button type="submit" @disabled(! $canCheckIn || ($record && $record->check_in_at))>Check In</button>
        </form>

        <p>
            Check-out status:
            {{ $record && $record->check_out_at ? $record->check_out_at->format('h:i A') : 'Abhi nahi hua' }}
        </p>
        <form method="POST" action="{{ route('teacher.attendance.checkout', [], false) }}">
            @csrf
            <button type="submit" @disabled(! $canCheckOut || ! $record || ! $record->check_in_at || $record->check_out_at)>Check Out</button>
        </form>
    @endif
@endsection
