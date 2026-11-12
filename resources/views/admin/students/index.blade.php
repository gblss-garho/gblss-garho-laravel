@extends('layouts.portal')

@section('title', 'Students')

@section('content')
    <p><a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a></p>
    <div class="page-title" data-n="02"><h1>Register / Students</h1></div>
    <p class="sd sd-note">جنرل رجسٽر مطابق اسٽوڊنٽ رڪارڊ. GR نمبر سان ڳوليو.</p>
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

    <div class="scroll"><table>
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
            <tr><td colspan="6"><span class="sd">اڃا ڪو اسٽوڊنٽ رڪارڊ ناهي.</span></td></tr>
        @endforelse
    </table></div>

    <p>
        @if ($students->previousPageUrl())<a href="{{ $students->previousPageUrl() }}">&larr; Pichla</a>@endif
        @if ($students->nextPageUrl())<a href="{{ $students->nextPageUrl() }}">Agla &rarr;</a>@endif
    </p>
@endsection
