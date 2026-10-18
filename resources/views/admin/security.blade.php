@extends('layouts.app')

@section('title', 'Security Logs')

@section('content')
    <h1>Security Logs</h1>
    <p><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>

    <form method="GET" action="{{ route('admin.security.index', [], false) }}">
        Pichle <input type="number" name="hours" value="{{ $hours }}" min="1" max="720" style="width:70px"> ghante
        <button type="submit">Dekhein</button>
    </form>

    <h2 style="color:#b00">Shak wale (24 ghante mein 3+ naakaami)</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Teacher</th><th>Naakaami</th><th>Aakhri</th></tr>
        @forelse ($flags as $f)
            <tr><td>{{ $f->teacher_name ?? 'Unknown' }}</td><td>{{ $f->cnt }}</td><td>{{ substr($f->last_at, 0, 16) }}</td></tr>
        @empty
            <tr><td colspan="3">Koi nahi.</td></tr>
        @endforelse
    </table>

    <h2>Khulasa (pichle {{ $hours }} ghante)</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Teacher</th><th>Wajah</th><th>Tadaad</th><th>Aakhri</th></tr>
        @forelse ($summary as $s)
            <tr><td>{{ $s->teacher_name ?? 'Unknown' }}</td><td>{{ $s->reason }}</td><td>{{ $s->cnt }}</td><td>{{ substr($s->last_at, 0, 16) }}</td></tr>
        @empty
            <tr><td colspan="4">Is muddat mein koi naakaami nahi.</td></tr>
        @endforelse
    </table>

    <h2>Taaza 30 naakaam koshishein</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Waqt</th><th>Teacher</th><th>Wajah</th><th>Qism</th></tr>
        @foreach ($recent as $x)
            <tr><td>{{ substr($x->occurred_at, 0, 16) }}</td><td>{{ $x->teacher_name ?? 'Unknown' }}</td><td>{{ $x->reason }}</td><td>{{ $x->failure_type }}</td></tr>
        @endforeach
    </table>
@endsection
