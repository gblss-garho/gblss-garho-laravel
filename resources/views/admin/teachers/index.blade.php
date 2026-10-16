@extends('layouts.app')

@section('title', 'Teachers')

@section('content')
    <p><a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a></p>
    <h1>Teachers</h1>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    <p><a href="{{ route('admin.teachers.create') }}">+ Naya teacher</a></p>

    <div style="overflow-x:auto">
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
        <tr><th>Naam</th><th>Designation</th><th>Subject</th><th>Classes</th><th>Login email</th><th>Chehra</th><th></th></tr>
        @foreach ($teachers as $t)
            <tr>
                <td>{{ $t->name }}</td>
                <td>{{ $t->designation }}</td>
                <td>{{ $t->subject }}</td>
                <td>{{ $t->classes->pluck('class_name')->implode(', ') }}</td>
                <td>{{ $t->auth_email }}</td>
                <td>{{ $t->face_descriptor ? 'Enrolled' : 'Nahi' }}</td>
                <td>
                    <a href="{{ route('admin.teachers.edit', $t) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.teachers.destroy', $t) }}" style="display:inline" onsubmit="return confirm('Is teacher ko delete karna hai? Login bhi band ho jayega.')">
                        @csrf @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
    </div>
@endsection
