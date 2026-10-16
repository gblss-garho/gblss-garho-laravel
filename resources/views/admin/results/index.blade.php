@extends('layouts.app')

@section('title', 'Results')

@section('content')
    <p><a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a></p>
    <h1>Results</h1>

    <form method="GET" action="{{ route('admin.results.index') }}">
        <select name="year">
            @foreach ($years as $y)<option value="{{ $y }}" @selected((string) $y === $year)>{{ $y }}</option>@endforeach
        </select>
        <select name="exam">
            @foreach ($exams as $e)<option value="{{ $e }}" @selected($e === $exam)>{{ $e }}</option>@endforeach
        </select>
        <select name="class">
            <option value="">-- Class chunein --</option>
            @foreach ($classes as $c)<option value="{{ $c }}" @selected($c === $class)>{{ $c }}</option>@endforeach
        </select>
        <button type="submit">Dikhayen</button>
    </form>

    @if ($class !== '')
        @php
            $ps = \Illuminate\Support\Facades\DB::table('result_publish_status')->where('class_name', $class)->where('exam_type', $exam)->value('status');
        @endphp
        <form method="POST" action="{{ route('admin.results.toggle') }}" style="margin-top:8px">
            @csrf
            <input type="hidden" name="class" value="{{ $class }}">
            <input type="hidden" name="exam" value="{{ $exam }}">
            <input type="hidden" name="year" value="{{ $year }}">
            Status: <b>{{ $ps === 'published' ? 'Published (parents ko nazar aata hai)' : 'Draft (chhupa hua)' }}</b>
            <button type="submit">{{ $ps === 'published' ? 'Unpublish karein' : 'Publish karein' }}</button>
        </form>
    @endif

    @if ($class === '')
        <p>Class chun kar "Dikhayen" dabayein.</p>
    @elseif ($rows->isEmpty())
        <p>Is class ka result nahi mila.</p>
    @else
        <div style="overflow-x:auto">
        <table border="1" cellpadding="6" style="border-collapse:collapse;margin-top:12px">
            <tr>
                <th>#</th><th>GR</th><th>Name</th>
                @foreach ($subjects as $s)<th>{{ $s }}</th>@endforeach
                <th>Total</th>
            </tr>
            @foreach ($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['student']->gr_number ?? '' }}</td>
                    <td>{{ $r['student']->name ?? '' }}</td>
                    @foreach ($subjects as $s)<td>{{ $r['scores'][$s] ?? '-' }}</td>@endforeach
                    <td><b>{{ $r['total'] }}</b></td>
                </tr>
            @endforeach
        </table>
        </div>
    @endif
@endsection
