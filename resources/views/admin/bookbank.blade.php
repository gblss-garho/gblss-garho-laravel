@extends('layouts.app')

@section('title', 'Book Bank')

@section('content')
    <h1>Book Bank</h1>
    <p><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    @foreach ($errors->all() as $e)<p style="color:red">{{ $e }}</p>@endforeach

    <h2>Kitaab issue karein</h2>
    <form method="POST" action="{{ route('admin.bookbank.issue') }}">
        @csrf
        <select name="catalog_id" required>
            @foreach ($catalog as $b)
                @if ($b->available_qty > 0)
                    <option value="{{ $b->id }}">{{ $b->class }} - {{ $b->subject }} - {{ $b->book_title }} ({{ $b->available_qty }})</option>
                @endif
            @endforeach
        </select>
        <input name="gr_number" placeholder="Student GR number" required>
        <button type="submit">Issue</button>
    </form>

    <h2>Issue hui kitaabein ({{ count($open) }})</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Student</th><th>Class</th><th>Kitaab</th><th>Tareekh</th><th></th></tr>
        @forelse ($open as $i)
            <tr>
                <td>{{ $i->student_name }}</td><td>{{ $i->class }}</td><td>{{ $i->book_title }}</td>
                <td>{{ substr($i->issue_date, 0, 10) }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.bookbank.return', $i->id) }}">
                        @csrf
                        <select name="condition"><option>Good</option><option>Fair</option><option>Damaged</option></select>
                        <button type="submit">Wapas liya</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">Koi kitaab issue nahi.</td></tr>
        @endforelse
    </table>

    <h2>Catalog</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Class</th><th>Subject</th><th>Kitaab</th><th>Maujood</th><th>Issue</th><th></th></tr>
        @foreach ($catalog as $b)
            <tr>
                <td>{{ $b->class }}</td><td>{{ $b->subject }}</td><td>{{ $b->book_title }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.bookbank.catalog.qty', $b->id) }}">
                        @csrf
                        <input type="number" name="available_qty" value="{{ $b->available_qty }}" min="0" style="width:60px">
                        <button type="submit">Save</button>
                    </form>
                </td>
                <td>{{ $b->issued_qty }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.bookbank.catalog.delete', $b->id) }}" onsubmit="return confirm('Hata dein?')">
                        @csrf @method('DELETE')
                        <button type="submit">Hatayen</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>

    <h3>Nayi kitaab</h3>
    <form method="POST" action="{{ route('admin.bookbank.catalog.add') }}">
        @csrf
        <input name="class" placeholder="Class (VI)" required style="width:80px">
        <input name="subject" placeholder="Subject" required>
        <input name="book_title" placeholder="Kitaab ka naam" required>
        <input type="number" name="available_qty" placeholder="Tadaad" min="0" required style="width:80px">
        <button type="submit">Add</button>
    </form>

    <h2>Haal mein wapas hui</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr><th>Student</th><th>Kitaab</th><th>Issue</th><th>Wapsi</th><th>Halat</th></tr>
        @foreach ($returned as $i)
            <tr>
                <td>{{ $i->student_name }}</td><td>{{ $i->book_title }}</td>
                <td>{{ substr($i->issue_date, 0, 10) }}</td><td>{{ substr($i->return_date ?? '', 0, 10) }}</td><td>{{ $i->condition }}</td>
            </tr>
        @endforeach
    </table>
@endsection
