@extends('layouts.app')

@section('title', 'Teacher Leave Requests')

@section('content')
    <h1>Teacher Leave Requests</h1>
    <p><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif

    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
        <tr><th>Date</th><th>Teacher</th><th>Wajah</th><th>Status</th><th></th></tr>
        @forelse ($items as $i)
            <tr>
                <td>{{ substr($i->date, 0, 10) }}</td>
                <td>{{ $i->teacher_name }}</td>
                <td>{{ $i->reason }}</td>
                <td>{{ ucfirst($i->status) }}</td>
                <td>
                    @foreach (['approved' => 'Approve', 'rejected' => 'Reject'] as $st => $label)
                        @if ($i->status !== $st)
                            <form method="POST" action="{{ route('admin.leave.update', $i->id, false) }}" style="display:inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="{{ $st }}">
                                <button type="submit">{{ $label }}</button>
                            </form>
                        @endif
                    @endforeach
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Koi darkhwast nahi.</td></tr>
        @endforelse
    </table>
@endsection
