@extends('layouts.app')

@section('title', $student ? 'Edit Student' : 'Naya Student')

@section('content')
    <p><a href="{{ route('admin.students.index') }}">&larr; Students</a></p>
    <h1>{{ $student ? 'Student edit' : 'Naya student' }}</h1>

    @if ($errors->any())
        <ul style="color:red">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    @endif

    <form method="POST" action="{{ $student ? route('admin.students.update', $student) : route('admin.students.store') }}">
        @csrf
        @if ($student) @method('PUT') @endif
        @php
            $f = function ($k) use ($student) {
                $v = $student?->{$k};
                if ($v instanceof \Carbon\CarbonInterface) { $v = $v->format('Y-m-d'); }
                return old($k, $v);
            };
        @endphp
        <p>GR number<br><input name="gr_number" value="{{ $f('gr_number') }}" required></p>
        <p>Naam<br><input name="name" value="{{ $f('name') }}" required></p>
        <p>Walid ka naam<br><input name="father_name" value="{{ $f('father_name') }}"></p>
        <p>Date of birth<br><input type="date" name="dob" value="{{ $f('dob') }}"></p>
        <p>Class<br><input name="class" value="{{ $f('class') }}" required></p>
        <p>Section<br><input name="section" value="{{ $f('section') }}"></p>
        <p>Address<br><input name="address" value="{{ $f('address') }}"></p>
        <p>Guardian phone<br><input name="guardian_phone" value="{{ $f('guardian_phone') }}" placeholder="03xx xxxxxxx"></p>
        <p>Admission date<br><input type="date" name="admission_date" value="{{ $f('admission_date') }}"></p>
        <button type="submit">Save</button>
    </form>
@endsection
