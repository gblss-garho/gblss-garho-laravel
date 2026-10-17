@extends('layouts.app')

@section('title', 'Chhutti ki darkhwast')

@section('content')
    <h1>Chhutti ki darkhwast</h1>
    <p><a href="{{ route('teacher.dashboard', [], false) }}">&larr; Dashboard</a></p>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    @if ($errors->any())<p style="color:red">{{ $errors->first() }}</p>@endif

    <form method="POST" action="{{ route('teacher.leave.store', [], false) }}">
        @csrf
        <p>Kis din ki chhutti<br><input type="date" name="date" min="{{ now()->toDateString() }}" required></p>
        <p>Wajah<br><textarea name="reason" rows="3" cols="40" required></textarea></p>
        <p><button type="submit">Darkhwast bhejein</button></p>
    </form>

    <h2>Meri darkhwastein</h2>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
        <tr><th>Date</th><th>Wajah</th><th>Status</th></tr>
        @forelse ($items as $i)
            <tr><td>{{ substr($i->date, 0, 10) }}</td><td>{{ $i->reason }}</td><td>{{ ucfirst($i->status) }}</td></tr>
        @empty
            <tr><td colspan="3">Koi darkhwast nahi.</td></tr>
        @endforelse
    </table>
@endsection
