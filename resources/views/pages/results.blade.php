@extends('layouts.portal')

@section('title', 'Exam Results')

@section('content')
    <style>
        .rs-title { text-align:center; margin:28px 0 6px; font-family:'Noto Serif',Georgia,serif; font-weight:800; font-size:42px; color:#C9A227; }
        .rs-sub { text-align:center; font-size:20px; color:#0F5132; margin:0 0 22px; }
        .rs-tabs { display:flex; gap:6px; background:#fff; border:1px solid #E9E2CC; border-radius:18px; padding:8px; flex-wrap:wrap; margin:16px 0; }
        .rs-tabs button { margin:0; flex:1; min-width:90px; padding:11px 10px; border-radius:12px; background:transparent; color:#0F5132; font-weight:600; box-shadow:none; }
        .rs-tabs button:hover { background:#EAF3EF; }
        .rs-tabs button.on { background:#0F5132; color:#fff; }
        .rs-pane { display:none; } .rs-pane.on { display:block; }
    </style>
    <h1 class="rs-title" style="background:none;-webkit-text-fill-color:#C9A227">Exam Results 🏆</h1>
    <p class="rs-sub sd">پنهنجو GR نمبر داخل ڪريو ۽ نتيجو ڏسو</p>

    <div class="lk-card">
        @if ($error)<div class="err"><p>{{ $error }}</p></div>@endif
        <form method="POST" action="{{ route('public.results.check', [], false) }}">
            @csrf
            <label for="gr_number">GR / Roll number <span class="sd">(GR نمبر داخل ڪريو)</span></label>
            <input id="gr_number" name="gr_number" value="{{ old('gr_number') }}" required inputmode="numeric" placeholder="GR number">
            <label for="dob">Date of birth <span class="sd">(رڪارڊ موجب)</span></label>
            <input id="dob" name="dob" type="date" value="{{ old('dob') }}" required>
            <button type="submit" style="width:100%;margin-top:14px">Search</button>
        </form>
    </div>

    @php
        $tabs = ['annual' => 'Annual', 'midterm' => 'Mid Term', 'monthly' => 'Monthly Test', 'classtest' => 'Class Test'];
        $norm = fn ($k) => match (true) {
            str_contains(strtolower($k), 'annual') => 'annual',
            str_contains(strtolower($k), 'mid') => 'midterm',
            str_contains(strtolower($k), 'month') => 'monthly',
            str_contains(strtolower($k), 'class') => 'classtest',
            default => strtolower($k),
        };
        $byTab = [];
        foreach ($exams as $k => $rows) { $byTab[$norm($k)] = $rows; }
        foreach ($byTab as $k => $_) { if (! isset($tabs[$k])) { $tabs[$k] = ucfirst($k); } }
    @endphp

    <div class="rs-tabs">
        @foreach ($tabs as $k => $label)<button type="button" data-rt="{{ $k }}" class="{{ $loop->first ? 'on' : '' }}">{{ $label }}</button>@endforeach
    </div>

    @if ($student)
        <div class="lk-card">
            <h2 style="margin-top:0">{{ $student->name }}</h2>
            <p>Walid: {{ $student->father_name }} &middot; Class: {{ $student->class }}{{ $student->section ? '-'.$student->section : '' }} &middot; GR: {{ $student->gr_number }}</p>
        </div>
    @endif

    @foreach ($tabs as $k => $label)
        <div class="rs-pane{{ $loop->first ? ' on' : '' }}" data-rp="{{ $k }}">
            @if ($student && isset($byTab[$k]))
                <div class="lk-card">
                    <h3 style="margin-top:0">{{ $label }}</h3>
                    <div class="scroll">
                        <table>
                            <tr><th>Subject</th><th>Marks</th></tr>
                            @foreach ($byTab[$k] as $r)
                                <tr><td>{{ ucfirst($r->subject) }}</td><td>{{ $r->score }}</td></tr>
                            @endforeach
                            <tr><td><b>Total</b></td><td><b>{{ $byTab[$k]->sum('score') }}</b></td></tr>
                        </table>
                    </div>
                </div>
            @elseif ($student)
                <div class="lk-card"><p class="sd">نتيجو اڃا شايع نه ٿيو آهي</p></div>
            @endif
        </div>
    @endforeach
    <script>
    document.querySelectorAll('[data-rt]').forEach(function (b) {
        b.addEventListener('click', function () {
            var k = b.getAttribute('data-rt');
            document.querySelectorAll('[data-rt]').forEach(function (x) { x.classList.toggle('on', x === b); });
            document.querySelectorAll('[data-rp]').forEach(function (p) { p.classList.toggle('on', p.getAttribute('data-rp') === k); });
        });
    });
    </script>
@endsection
