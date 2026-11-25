@extends('layouts.portal')

@section('title', 'Class Timetable')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/timetable.css') }}">
    <div class="tt">
        <h1 style="margin:4px 0 2px">Class Timetable</h1>
        <p class="tt-note sd">ڪلاس جو هفتيوار ٽائم ٽيبل.</p>
        <form method="GET" action="{{ route('public.timetable', [], false) }}" class="tt-pick no-print">
            <label>Class
                <select name="class" onchange="this.form.submit()">
                    @foreach ($classes as $c)<option value="{{ $c }}" @selected($c === $class)>{{ $c }}</option>@endforeach
                </select>
            </label>
            <button class="tt-btn alt" type="button" onclick="window.print()">Print</button>
        </form>
        <h3 class="tt-title">Class {{ $class }}</h3>
        @include('timetable._grid', ['mode' => 'class'])
    </div>
@endsection
