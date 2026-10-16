@extends('layouts.app')

@section('title', 'Homework')

@section('content')
    <h1>Homework</h1>
    <p><a href="{{ route('teacher.dashboard', [], false) }}">&larr; Dashboard</a></p>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    @if ($errors->any())<p style="color:red">{{ $errors->first() }}</p>@endif

    @if (count($classes) === 0)
        <p>Aap ko abhi koi class assign nahi hai.</p>
    @else
    <form method="POST" action="{{ route('teacher.homework.store', [], false) }}" enctype="multipart/form-data">
        @csrf
        <p><select name="class_name" required>
            @foreach ($classes as $c)<option value="{{ $c }}">Class {{ $c }}</option>@endforeach
        </select></p>
        <p><input name="subject" placeholder="Subject" required></p>
        <p><input type="date" name="date" value="{{ now()->toDateString() }}" required></p>
        <p><textarea name="text" rows="4" cols="40" placeholder="Homework likhein" required></textarea></p>
        <p>File (optional, 5MB tak): <input type="file" name="file"></p>
        <p><button type="submit">Homework save karein</button></p>
    </form>
    @endif

    <h2>Pichla homework</h2>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
        <tr><th>Date</th><th>Class</th><th>Subject</th><th>Homework</th><th></th></tr>
        @forelse ($items as $h)
        <tr>
            <td>{{ $h->date->format('d-m-Y') }}</td>
            <td>{{ $h->class_name }}</td>
            <td>{{ $h->subject }}</td>
            <td>{{ $h->text }}
                @if ($h->attachment_path)<br><a href="{{ asset('storage/' . $h->attachment_path) }}" target="_blank">{{ $h->attachment_name ?: 'File' }}</a>@endif
            </td>
            <td>
                <form method="POST" action="{{ route('teacher.homework.destroy', $h->id, false) }}" onsubmit="return confirm('Ye homework delete karna hai?')">
                    @csrf @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">Koi homework nahi.</td></tr>
        @endforelse
    </table>
@endsection
