@extends('layouts.app')

@section('title', 'Leaving Certificates')

@section('content')
    <h1>Leaving Certificates</h1>
    <p><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>

    @if (session('status'))
        <p><strong>{{ session('status') }}</strong></p>
    @endif

    <h2>Certificate baaqi hai (passed out, certificate nahi bana)</h2>
    @if ($eligible->isEmpty())
        <p>Koi student baaqi nahi.</p>
    @else
        <table border="1" cellpadding="4">
            <tr><th>GR Number</th><th>Name</th><th>Class</th><th>Pass-out Year</th><th>Action</th></tr>
            @foreach ($eligible as $s)
                <tr>
                    <td>{{ $s->gr_number }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->class }}</td>
                    <td>{{ $s->pass_out_year }}</td>
                    <td><a href="{{ route('admin.leaving-certificates.create', ['student' => $s->id], false) }}">Certificate banayen</a></td>
                </tr>
            @endforeach
        </table>
    @endif

    <h2>Jari kiye gaye certificates</h2>
    @if ($certificates->isEmpty())
        <p>Abhi koi certificate jari nahi hua.</p>
    @else
        <table border="1" cellpadding="4">
            <tr><th>LC No.</th><th>Name</th><th>GR Number</th><th>Class Passed</th><th>Year</th><th>Issue Date</th><th>PDF</th></tr>
            @foreach ($certificates as $c)
                <tr>
                    <td>{{ $c->serial_no ? sprintf('LC-%04d', $c->serial_no) : '-' }}</td>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->gr_number }}</td>
                    <td>{{ $c->passed_class }}</td>
                    <td>{{ $c->year }}</td>
                    <td>{{ $c->issue_date?->format('d M Y') }}</td>
                    <td><a href="{{ route('admin.leaving-certificates.pdf', ['certificate' => $c->id], false) }}" target="_blank">PDF</a></td>
                </tr>
            @endforeach
        </table>
    @endif
@endsection
