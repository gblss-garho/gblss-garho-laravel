@extends('layouts.app')

@section('title', 'Results History')

@section('content')
    <h1>Results History</h1>
    <p><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>

    <form method="GET" action="{{ route('admin.results.history', [], false) }}">
        <input name="gr" value="{{ $gr }}" placeholder="Student GR number" required>
        <button type="submit">Dekhein</button>
    </form>

    @if ($notFound)<p style="color:red">Is GR number ka student nahi mila.</p>@endif

    @if ($student)
        <h2>{{ $student->name }} (GR {{ $student->gr_number }})</h2>
        <p>Father: {{ $student->father_name }} | Maujooda class: {{ $student->class }}{{ $student->passed_out ? ' | Passed out' : '' }}</p>

        @forelse ($history as $year => $classes)
            <h3>Session {{ $year }}</h3>
            @foreach ($classes as $class => $exams)
                @foreach ($exams as $exam => $rows)
                    <p><b>Class {{ $class }} &mdash; {{ ucfirst($exam) }}</b> (kul: {{ $rows->sum('score') }})</p>
                    <table border="1" cellpadding="6" cellspacing="0">
                        <tr><th>Subject</th><th>Marks</th></tr>
                        @foreach ($rows as $row)
                            <tr><td>{{ config('subjects.' . $row->subject, $row->subject) }}</td><td>{{ $row->score }}</td></tr>
                        @endforeach
                    </table>
                @endforeach
            @endforeach
        @empty
            <p>Is student ke abhi koi results darj nahi.</p>
        @endforelse
    @endif
@endsection
