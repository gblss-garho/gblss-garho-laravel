@extends('layouts.portal')

@section('title', 'GR Status Edit')

@section('content')
    <p><a href="{{ route('admin.gr-register', [], false) }}">&larr; GR Register</a></p>
    <h1>GR {{ $reg }} &mdash; {{ $student->name ?? $gr->name }}</h1>
    <p>Father: {{ $student->father_name ?? $gr->father_name }}@if ($student) &middot; Class: {{ $student->class }}@endif</p>
    @if ($errors->any())<ul style="color:red">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
    <form method="POST" action="{{ route('admin.gr-register.update', $reg, false) }}">
        @csrf @method('PUT')
        @include('admin.partials.status-fields', ['student' => $student, 'gr' => $gr])
        <button type="submit">Save</button>
    </form>
@endsection
