@extends('layouts.app')

@section('title', 'Promotion Review — Batch #' . $batch->id)

@section('content')
    <h1>Promotion Review — Batch #{{ $batch->id }} ({{ $batch->academic_year }})</h1>
    <p><a href="{{ route('admin.promotions.history') }}">&larr; Promotion History</a></p>

    @if (session('status'))
        <p><strong>{{ session('status') }}</strong></p>
    @endif

    <p>In batch mein {{ $flagged->count() }} student(s) flag hue hain (missing result ya criteria pura nahi hua). Neeche dekh kar manually result/class theek karen, phir zaroorat ho to individually undo karen.</p>

    @if ($flagged->isEmpty())
        <p>Is batch mein koi flagged student baaqi nahi (sab already resolved ya undone).</p>
    @else
        <table border="1" cellpadding="6">
            <tr>
                <th>GR Number</th>
                <th>Name</th>
                <th>Current Class</th>
                <th>Reason</th>
                <th>Overall %</th>
                <th>Action</th>
            </tr>
            @foreach ($flagged as $log)
                @php $student = $students->get($log->student_id); @endphp
                <tr>
                    <td>{{ $student->gr_number ?? 'N/A' }}</td>
                    <td>{{ $student->name ?? '(deleted student)' }}</td>
                    <td>{{ $student->class ?? '-' }}</td>
                    <td>
                        @if ($log->outcome === 'skipped_missing_result')
                            Result entered nahi hua
                        @elseif ($log->outcome === 'skipped_did_not_meet_criteria')
                            Pass criteria pura nahi hua
                        @elseif ($log->outcome === 'skipped_unknown_class')
                            Class value samajh nahi aayi ({{ $log->previous_class }})
                        @else
                            {{ $log->outcome }}
                        @endif
                    </td>
                    <td>{{ $log->overall_percentage !== null ? $log->overall_percentage . '%' : '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.promotions.undoStudent', [$batch, $log->student_id]) }}" style="display:inline">
                            @csrf
                            <button type="submit">Is student ka log clear karen</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
@endsection
