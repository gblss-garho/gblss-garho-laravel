@extends('layouts.app')

@section('title', 'Promotion History')

@section('content')
    <h1>Promotion History</h1>
    <p><a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a></p>

    @if (session('status'))
        <p><strong>{{ session('status') }}</strong></p>
    @endif

    @if ($batches->isEmpty())
        <p>Koi promotion batch abhi tak nahi chala.</p>
    @else
        <table border="1" cellpadding="6">
            <tr>
                <th>Batch #</th>
                <th>Academic Year</th>
                <th>Run At</th>
                <th>Promoted</th>
                <th>Passed Out</th>
                <th>Skipped/Flagged</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            @foreach ($batches as $batch)
                <tr>
                    <td>{{ $batch->id }}</td>
                    <td>{{ $batch->academic_year }}</td>
                    <td>{{ $batch->run_at->format('d M Y, h:i A') }}</td>
                    <td>{{ $batch->promoted_count }}</td>
                    <td>{{ $batch->passed_out_count }}</td>
                    <td>{{ $batch->skipped_count }}</td>
                    <td>
                        @if ($batch->undone_at)
                            Undone ({{ $batch->undone_at->format('d M Y, h:i A') }})
                        @else
                            Active
                        @endif
                    </td>
                    <td>
                        @if ($batch->skipped_count > 0)
                            <a href="{{ route('admin.promotions.review', $batch) }}">Review flagged</a>
                        @endif
                        @if (! $batch->undone_at)
                            <form method="POST" action="{{ route('admin.promotions.undo', $batch) }}" style="display:inline" onsubmit="return confirm('Poora batch #{{ $batch->id }} undo karna hai? Yeh sab promoted/passed-out students ko wapas kar dega.');">
                                @csrf
                                <button type="submit">Undo poora batch</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
@endsection
