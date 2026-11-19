@extends('layouts.portal')

@section('title', 'Student Leave Applications')

@section('content')
    <h1>Student Leave Applications</h1>
    <p><a href="{{ $back }}">&larr; Dashboard</a></p>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    <p>
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'Sab'] as $k => $l)
            <a class="btn {{ $status === $k ? '' : 'ghost' }}" href="{{ route($p.'.student-leave.index', ['status' => $k], false) }}">{{ $l }}</a>
        @endforeach
    </p>
    <div class="scroll">
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
        <tr><th>Leave date</th><th>Student</th><th>Class</th><th>Purpose</th><th>Wajah</th><th>File</th><th>Status</th><th></th></tr>
        @forelse ($items as $i)
            <tr>
                <td>{{ substr($i->leave_date, 0, 10) }}</td>
                <td>{{ $i->student_name }}</td>
                <td>{{ $i->class_name }}</td>
                <td>{{ $i->purpose }}</td>
                <td>{{ $i->reason }}</td>
                <td>@if ($i->attachment_path)<a href="{{ route($p.'.student-leave.file', $i->id, false) }}">{{ $i->attachment_name ?: 'File' }}</a>@endif</td>
                <td>{{ ucfirst($i->status) }}</td>
                <td>
                    @foreach (['approved' => 'Approve', 'rejected' => 'Reject'] as $st => $label)
                        @if ($i->status !== $st)
                            <form method="POST" action="{{ route($p.'.student-leave.update', $i->id, false) }}" style="display:inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="{{ $st }}">
                                <button type="submit">{{ $label }}</button>
                            </form>
                        @endif
                    @endforeach
                </td>
            </tr>
        @empty
            <tr><td colspan="8">Koi arzi nahi.</td></tr>
        @endforelse
    </table>
    </div>
@endsection
