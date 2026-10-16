@extends('layouts.app')

@section('title', 'Notices & Calendar')

@section('content')
    <p><a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a></p>
    <h1>Notices &amp; Calendar</h1>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    @if ($errors->any())
        <ul style="color:red">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    @endif

    <h2>Notices</h2>
    <form method="POST" action="{{ route('admin.content.notice.add') }}">
        @csrf
        <p><textarea name="text" rows="3" style="width:90%" placeholder="Notice likhein" required></textarea></p>
        <input name="type" list="ntypes" placeholder="Type (info)" value="info">
        <datalist id="ntypes"><option value="info"><option value="urgent"><option value="holiday"></datalist>
        <input type="date" name="date">
        <button type="submit">Notice add karein</button>
    </form>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;margin-top:10px">
        <tr><th>Date</th><th>Type</th><th>Notice</th><th></th></tr>
        @forelse ($notices as $n)
            <tr>
                <td>{{ substr($n->date, 0, 10) }}</td>
                <td>{{ $n->type }}</td>
                <td>{{ $n->text }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.content.notice.delete', $n->id) }}" onsubmit="return confirm('Ye notice delete karna hai?')">
                        @csrf @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Koi notice nahi.</td></tr>
        @endforelse
    </table>

    <h2>Calendar events</h2>
    <form method="POST" action="{{ route('admin.content.event.add') }}">
        @csrf
        <input name="title" placeholder="Event ka naam" required>
        <input type="date" name="date" required>
        <input name="type" list="etypes" placeholder="Type (event)" value="event">
        <datalist id="etypes"><option value="event"><option value="holiday"><option value="exam"></datalist>
        <button type="submit">Event add karein</button>
    </form>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;margin-top:10px">
        <tr><th>Date</th><th>Type</th><th>Event</th><th></th></tr>
        @forelse ($events as $e)
            <tr>
                <td>{{ substr($e->date, 0, 10) }}</td>
                <td>{{ $e->type }}</td>
                <td>{{ $e->title }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.content.event.delete', $e->id) }}" onsubmit="return confirm('Ye event delete karna hai?')">
                        @csrf @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">Koi event nahi.</td></tr>
        @endforelse
    </table>
@endsection
