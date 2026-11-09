@extends('layouts.portal')

@section('content')
<div class="container">
    <h2>Staff ID Cards</h2>
    <p class="sd sd-note">ID ڪارڊ ٺاهڻ لاءِ ڪو ٽيچر چونڊيو. Front ۽ Back — ٻئي پاسا ڪٽي هڪ ٻئي پويان لڳايو (double-sided card).</p>
    <form method="GET" action="{{ route('admin.staff-id-cards.index') }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or PID">
        <button type="submit">Search</button>
    </form>
    <table class="table">
        <thead>
            <tr><th>Naam</th><th>Designation</th><th>PID</th><th>PDF</th></tr>
        </thead>
        <tbody>
        @forelse($teachers as $teacher)
            <tr>
                <td>{{ $teacher->name }}</td>
                <td>{{ $teacher->designation }}</td>
                <td>{{ $teacher->pid }}</td>
                <td><a href="{{ route('admin.staff-id-cards.pdf', $teacher->id) }}" target="_blank">PDF</a></td>
            </tr>
        @empty
            <tr><td colspan="4"><span class="sd">ڪو ٽيچر نه مليو.</span></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
