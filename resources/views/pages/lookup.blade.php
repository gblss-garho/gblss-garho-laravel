@extends('layouts.app')

@section('title', 'Parents Corner')

@section('content')
    <p><a href="{{ route('home') }}">&larr; Home</a></p>
    <h1>Parents Corner</h1>
    <p>Apne bachay ka GR number aur date of birth likh kar result dekhein.</p>

    @if ($error)<p style="color:red">{{ $error }}</p>@endif

    <form method="POST" action="{{ route('public.lookup.check') }}">
        @csrf
        <p>GR number<br><input name="gr_number" value="{{ old('gr_number') }}" required></p>
        <p>Date of birth (jaise 2013-05-21 ya 21-05-2013)<br><input name="dob" value="{{ old('dob') }}" required></p>
        <button type="submit">Dekhein</button>
    </form>

    @if ($student)
        <h2>{{ $student->name }}</h2>
        <p>Walid: {{ $student->father_name }} | Class: {{ $student->class }}{{ $student->section ? '-'.$student->section : '' }} | GR: {{ $student->gr_number }}</p>
        @forelse ($exams as $exam => $rows)
            <h3>{{ ucfirst($exam) }}</h3>
            <table border="1" cellpadding="6" style="border-collapse:collapse">
                <tr><th>Subject</th><th>Marks</th></tr>
                @foreach ($rows as $r)
                    <tr><td>{{ ucfirst($r->subject) }}</td><td>{{ $r->score }}</td></tr>
                @endforeach
                <tr><td><b>Total</b></td><td><b>{{ $rows->sum('score') }}</b></td></tr>
            </table>
        @empty
            <p>Abhi is class ka koi result publish nahi hua.</p>
        @endforelse
            <h3>Homework</h3>
        @forelse ($homework as $h)
            <p><b>{{ $h->date->format('d-m-Y') }} | {{ ucfirst($h->subject) }}</b><br>{{ $h->text }}
            @if ($h->attachment_path)<br><a href="{{ asset('storage/' . $h->attachment_path) }}" target="_blank">{{ $h->attachment_name ?: 'File' }}</a>@endif</p>
        @empty
            <p>Abhi koi homework nahi.</p>
        @endforelse
@endif
@endsection
