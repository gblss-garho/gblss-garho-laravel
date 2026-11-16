@extends('layouts.portal')

@section('title', 'Attendance')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
    <h1>Teacher Attendance</h1>
    <p class="sd sd-note">Time In/Out لاءِ پهريان اڄ جو پنهنجو چهرو enroll ڪريو.</p>

    @if (session('status'))<div class="at-ok">{{ session('status') }}</div>@endif

    {{-- ===== Today ===== --}}
    @php($errMsg = $errors->first('attendance') ?: $errors->first())
    @if ($mode === 'checkin' || $mode === 'checkout')
        @php($isIn = $mode === 'checkin')
        <div class="at-today">
            <h2>Aaj: {{ $isIn ? 'Time In' : 'Time Out' }}</h2>
            @if ($isIn)
                <p>On-time window: {{ $checkInLabel }}.
                    @if ($late > 0)<span class="wk-late">Aap {{ $late }} min late hain.</span> Phir bhi check-in ho jayega, late darj hoga.@endif
                </p>
            @else
                <p>Check-in: <b>{{ $record->check_in_at->format('h:i A') }}</b>@if ($late > 0) <span class="wk-late">(Late {{ $late }} min)</span>@endif. Check-out window: {{ $checkOutLabel }}.</p>
            @endif
        </div>
        @include('teacher._face-scanner', [
            'action' => route($isIn ? 'teacher.attendance.checkin' : 'teacher.attendance.checkout', [], false),
            'btn' => $isIn ? 'Face se Check In Karen' : 'Face se Check Out Karen',
            'title' => $isIn ? 'Check In' : 'Check Out',
            'sub' => 'Chehra scan aur liveness check (palak, sir ghumana) ke baad attendance lagegi.',
            'challenges' => $challenges,
            'initialFail' => $errMsg,
        ])
    @else
        @if ($errMsg)<div class="at-err">{{ $errMsg }}</div>@endif
        <div class="at-today">
            @if ($mode === 'off')
                <h2>Aaj school off hai</h2><p>Aaj Time In/Out ki zaroorat nahi.</p>
            @elseif ($mode === 'enroll')
                <h2>Pehle chehra enroll karen</h2>
                <p>Face se attendance ke liye ek dafa chehra enroll karna zaroori hai.</p>
                <p><a href="{{ route('teacher.face.enroll', [], false) }}"><b>Chehra Enroll Karen &rarr;</b></a></p>
            @elseif ($mode === 'done')
                <h2>Aaj ki attendance mukammal <span class="at-pill {{ $late > 0 ? 'p-late' : 'p-present' }}">{{ $late > 0 ? 'Late '.$late.' min' : 'Waqt par' }}</span></h2>
                <p>In: <b>{{ $record->check_in_at->format('h:i A') }}</b> &middot; Out: <b>{{ $record->check_out_at->format('h:i A') }}</b></p>
            @elseif ($mode === 'waiting_out')
                <h2>Check-in ho chuka hai <span class="at-pill {{ $late > 0 ? 'p-late' : 'p-present' }}">{{ $late > 0 ? 'Late '.$late.' min' : 'Waqt par' }}</span></h2>
                <p>In: <b>{{ $record->check_in_at->format('h:i A') }}</b>. Check-out {{ $checkOutLabel }} ke darmiyan khulega.</p>
            @elseif ($mode === 'early')
                <h2>Abhi school shuru nahi hua</h2><p>Check-in {{ $checkInLabel }} se shuru hota hai.</p>
            @else
                <h2>Aaj check-in nahi hua <span class="at-pill p-absent">Absent</span></h2>
                <p>Check-in ka waqt guzar chuka hai. Agar aap chhutti par the to "Chhutti" tab se darkhwast den.</p>
            @endif
        </div>
    @endif

    {{-- ===== Week ===== --}}
    @if ($week)
        @php($end = $weekStart->copy()->addDays(6))
        <div class="wk-head">
            <h2>Hafta: {{ $weekStart->format('j M') }} &ndash; {{ $end->format('j M Y') }}</h2>
            <span class="wk-nav">
                <a href="{{ route('teacher.attendance.index', ['week' => $prevWeek], false) }}">&larr; Pichla</a>
                @unless ($isCurrentWeek)<a href="{{ route('teacher.attendance.index', [], false) }}">Is hafte</a>
                <a href="{{ route('teacher.attendance.index', ['week' => $nextWeek], false) }}">Agla &rarr;</a>@endunless
            </span>
        </div>
        <div class="wk-stats">
            <div><b>{{ $week['sum']['present'] }}</b><span>Present</span></div>
            <div><b style="color:#B45309">{{ $week['sum']['late'] }}</b><span>Late{{ $week['sum']['late_minutes'] ? ' ('.$week['sum']['late_minutes'].' min)' : '' }}</span></div>
            <div><b style="color:#3730A3">{{ $week['sum']['leave'] }}</b><span>Leave</span></div>
            <div><b style="color:#991B1B">{{ $week['sum']['absent'] }}</b><span>Absent</span></div>
        </div>
        <div class="wk-list">
            @foreach ($week['days'] as $d)
                <div class="wk-day s-{{ $d['status'] }} {{ $d['date']->isToday() ? 'is-today' : '' }}">
                    <div class="wk-date"><small>{{ $d['date']->format('D') }}</small><b>{{ $d['date']->format('j') }}</b></div>
                    <div>
                        @if ($d['in'])
                            <div class="wk-times"><em>IN</em> {{ $d['in'] }} &nbsp; <em>OUT</em> {{ $d['out'] ?? '--:--' }}</div>
                            <div class="wk-meta">
                                @if ($d['late'] > 0)<span class="wk-late">Late {{ $d['late'] }} min</span> &middot; @endif
                                @if ($d['hours']){{ $d['hours'] }} school mein @endif
                                @if ($d['note']){{ $d['note'] }}@endif
                            </div>
                            @if ($d['bar'])<div class="wk-bar"><i style="left:{{ $d['bar']['left'] }}%;width:{{ $d['bar']['width'] }}%"></i></div>@endif
                        @else
                            <div class="wk-times"><em>{{ $d['status'] === 'off' ? 'Haftawar chhutti' : ($d['status'] === 'leave' ? 'Chhutti manzoor' : 'Koi record nahi') }}</em></div>
                            @if ($d['note'])<div class="wk-meta">{{ $d['note'] }}</div>@endif
                        @endif
                    </div>
                    <span class="at-pill p-{{ $d['status'] }}">{{ $d['label'] }}</span>
                </div>
            @endforeach
        </div>
        <p class="wk-meta" style="margin-top:10px">Late = {{ $checkInLabel }} ke baad. Timeline bar school ke din mein aap ke maujood rehne ka hissa dikhati hai.</p>
    @else
        <p>Aapke account se koi teacher record linked nahi hai.</p>
    @endif
@endsection
