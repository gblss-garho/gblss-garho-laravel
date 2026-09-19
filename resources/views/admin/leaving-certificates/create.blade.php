@extends('layouts.app')

@section('title', 'Leaving Certificate Banayen')

@php
    $fields = [
        'name' => 'Name',
        'father_name' => 'Father Name',
        'gr_number' => 'GR Number',
        'dob' => 'Date of Birth',
        'dob_words' => 'Date of Birth (in words)',
        'caste' => 'Caste',
        'religion' => 'Religion',
        'place_of_birth' => 'Place of Birth',
        'admission_date' => 'Date of Admission',
        'admitted_class' => 'Class at Admission',
        'passed_class' => 'Class Passed / Last Attended',
        'year' => 'Academic Year',
        'progress' => 'Progress',
        'conduct' => 'Conduct',
        'dues' => 'Dues',
        'reason' => 'Reason for Leaving',
        'last_school' => 'Last School Attended',
        'remarks' => 'Remarks',
        'leaving_date' => 'Date of Leaving',
        'issue_date' => 'Date of Issue',
    ];
    $dateKeys = ['dob', 'admission_date', 'leaving_date', 'issue_date'];
@endphp

@section('content')
    <h1>Leaving Certificate &mdash; {{ $student->name }}</h1>
    <p><a href="{{ route('admin.leaving-certificates.index', [], false) }}">&larr; Wapas</a></p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('admin.leaving-certificates.store', ['student' => $student->id], false) }}">
        @csrf
        @foreach ($fields as $key => $label)
            <p>
                <label for="{{ $key }}">{{ $label }}</label><br>
                <input id="{{ $key }}" type="{{ in_array($key, $dateKeys) ? 'date' : 'text' }}" name="{{ $key }}" value="{{ old($key, $defaults[$key] ?? '') }}" style="width:100%">
            </p>
        @endforeach
        <button type="submit">Certificate save karen</button>
    </form>
@endsection
