@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <p><a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a></p>
    <h1>Students</h1>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif

    <form method="GET" action="{{ route('admin.students.index') }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="Naam, father ya GR number" style="width:55%">
        <select name="class">
            <option value="">Sab classes</option>
            @foreach ($classes as $c)
                <option value="{{ $c }}" @selected($c === $class)>{{ $c }}</option>
            @endforeach
        </select>
        <button type="submit">Talaash</button>
        <a href="{{ route('admin.students.create') }}">+ Naya student</a>
    </form>

    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;margin-top:12px">
        <tr><th>GR</th><th>Name</th><th>Father</th><th>Class</th><th>Phone</th><th></th></tr>
        @forelse ($students as $s)
            <tr>
                <td>{{ $s->gr_number }}</td>
                <td>{{ $s->name }}</td>
                <td>{{ $s->father_name }}</td>
                <td>{{ $s->class }}{{ $s->section ? '-'.$s->section : '' }}</td>
                <td>{{ $s->guardian_phone }}</td>
                <td>
                    <a href="{{ route('admin.students.edit', $s) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.students.destroy', $s) }}" style="display:inline" onsubmit="return confirm('Is student ko delete karna hai? Iski attendance aur results bhi chale jayenge.')">
                        @csrf @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Koi student nahi mila.</td></tr>
        @endforelse
    </table>

    <p>
        @if ($students->previousPageUrl())<a href="{{ $students->previousPageUrl() }}">&larr; Pichla</a>@endif
        @if ($students->nextPageUrl())<a href="{{ $students->nextPageUrl() }}">Agla &rarr;</a>@endif
    </p>
@endsection
