@extends('layouts.portal')

@section('title', 'Teaching Assignment')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/timetable.css') }}">
    <div class="tt">
        <p style="margin:0"><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
        <h1 style="margin:4px 0 2px">Teaching Assignment</h1>
        <p class="tt-note">Kaun teacher kis class ko kaun sa subject padhata hai. Timetable isi se banta hai.</p>

        @if (session('ok'))<div class="tt-ok">{{ session('ok') }}</div>@endif
        @if ($errors->any())<div class="tt-err">{{ $errors->first() }}</div>@endif

        <form method="GET" action="{{ route('admin.teaching.index', [], false) }}" class="tt-pick">
            <label>Class
                <select name="class" onchange="this.form.submit()">
                    @foreach ($classes as $c)<option value="{{ $c }}" @selected($c === $class)>{{ $c }}</option>@endforeach
                </select>
            </label>
            <a class="tt-btn alt" href="{{ route('admin.timetable.edit', ['class' => $class], false) }}">Is class ka Timetable &rarr;</a>
        </form>

        <div class="tt-card">
            <h3>Class {{ $class }}</h3>
            @forelse ($rows as $r)
                <form method="POST" action="{{ route('admin.teaching.destroy', $r->id, false) }}" class="tt-row" onsubmit="return confirm('Hata dein? Timetable se bhi ja\'ega.')">
                    @csrf @method('DELETE')
                    <span><b>{{ $r->subject }}</b> <small>{{ $r->teacher?->name }}</small></span>
                    <button class="tt-x" type="submit" aria-label="Hatayen">&times;</button>
                </form>
            @empty
                <p class="tt-note">Is class ka koi subject abhi nahi.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('admin.teaching.store', [], false) }}" class="tt-card tt-add">
            @csrf
            <input type="hidden" name="class_name" value="{{ $class }}">
            <h3>Subject shamil karein</h3>
            <label>Subject <input name="subject" list="tt-subj" required maxlength="60" placeholder="e.g. Mathematics"></label>
            <datalist id="tt-subj">@foreach ($subjects as $s)<option value="{{ $s }}">@endforeach</datalist>
            <label>Teacher
                <select name="teacher_id" required>
                    <option value="">— chunein —</option>
                    @foreach ($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}@if ($t->subject) ({{ $t->subject }})@endif</option>@endforeach
                </select>
            </label>
            <button class="tt-btn" type="submit">Save</button>
            <p class="tt-note">Wahi subject dobara dein to teacher badal jata hai (timetable bhi naye teacher ke saath).</p>
        </form>
    </div>
@endsection
