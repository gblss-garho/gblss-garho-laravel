@extends('layouts.app')

@section('title', $teacher ? 'Edit Teacher' : 'Naya Teacher')

@section('content')
    <p><a href="{{ route('admin.teachers.index') }}">&larr; Teachers</a></p>
    <h1>{{ $teacher ? 'Teacher edit' : 'Naya teacher' }}</h1>

    @if ($errors->any())
        <ul style="color:red">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    @endif

    <form method="POST" action="{{ $teacher ? route('admin.teachers.update', $teacher) : route('admin.teachers.store') }}">
        @csrf
        @if ($teacher) @method('PUT') @endif
        @php
            $f = function ($k) use ($teacher) {
                $v = $teacher?->{$k};
                if ($v instanceof \Carbon\CarbonInterface) { $v = $v->format('Y-m-d'); }
                return old($k, $v);
            };
            $cls = old('classes', $teacher ? $teacher->classes->pluck('class_name')->implode(', ') : '');
        @endphp
        <p>Naam<br><input name="name" value="{{ $f('name') }}" required></p>
        <p>Login email<br><input type="email" name="auth_email" value="{{ $f('auth_email') }}" required></p>
        <p>Login PIN (4 digit){{ $teacher ? ' - khali chhorein to purana PIN rahega' : '' }}<br>
            <input name="pin" inputmode="numeric" maxlength="4" autocomplete="off" {{ $teacher ? '' : 'required' }}></p>
        <p>Classes (comma se, jaise VI, VII)<br><input name="classes" value="{{ $cls }}"></p>
        <p>Designation<br><input name="designation" value="{{ $f('designation') }}"></p>
        <p>Subject<br><input name="subject" value="{{ $f('subject') }}"></p>
        <p>Qualification<br><input name="qualification" value="{{ $f('qualification') }}"></p>
        <p>Experience<br><input name="experience" value="{{ $f('experience') }}"></p>
        <p>CNIC<br><input name="cnic" value="{{ $f('cnic') }}"></p>
        <p>PID<br><input name="pid" value="{{ $f('pid') }}"></p>
        <p>Date of birth<br><input type="date" name="dob" value="{{ $f('dob') }}"></p>
        <p>Service mein dakhla<br><input type="date" name="entry_in_service" value="{{ $f('entry_in_service') }}"></p>
        @if ($teacher)
            <p><label><input type="checkbox" name="reset_face" value="1"> Chehra reset karein (teacher dobara enroll karega)</label></p>
        @endif
        <button type="submit">Save</button>
    </form>
@endsection
