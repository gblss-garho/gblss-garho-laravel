@extends('layouts.portal')

@section('title', 'School Calendar')

@section('content')
    @php
        $logo = file_exists(public_path('images/logo-glow.png')) ? asset('images/logo-glow.png') : (file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg'));
        $badge = fn ($t) => $t === 'celebration' ? ['🎉 Celebration Day', '#FDF3E0', '#8B7B1E'] : ($t === 'event' ? ['📌 Event', '#E8F1EA', '#0F5132'] : ['🏖️ Holiday', '#FCEBE6', '#8B2E2E']);
    @endphp
    <div class="hero-lk">
        <div class="ring"><img src="{{ $logo }}" alt=""></div>
        <h2>Government Boys Lower Secondary School Garho</h2>
        <span class="pill">School Calendar &mdash; Holidays &amp; Celebration Days</span>
        <span class="dev">Designed &amp; Developed by Sir Waris Ghafoor</span>
    </div>

    @if ($upcoming->isEmpty() && $past->isEmpty())
        <div class="lk-card sd">اڃا ڪئلينڊر ۾ ڪا تاريخ شامل نه ڪئي وئي.</div>
    @endif

    @foreach ([['Aane wali tareekhein', $upcoming], ['Guzri hui tareekhein', $past]] as [$heading, $rows])
        @if ($rows->isNotEmpty())
            <h2>{{ $heading }}</h2>
            <div class="lk-card cal-list" style="padding:0;overflow:hidden">
                @foreach ($rows as $e)
                    @php [$lbl, $bg, $fg] = $badge($e->type); @endphp
                    <div class="cal-row">
                        <div class="cal-left">
                            <span class="cal-date">{{ \Carbon\Carbon::parse($e->date)->format('d M Y') }}</span>
                            <span class="cal-title" dir="auto">{{ $e->title }}</span>
                        </div>
                        <span class="cal-badge" style="background:{{ $bg }};color:{{ $fg }}">{{ $lbl }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach

    <style>
        .cal-row { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; padding:12px 16px; border-top:1px solid #EDE6D6; }
        .cal-row:first-child { border-top:0; }
        .cal-left { display:flex; align-items:center; gap:12px; flex-wrap:wrap; min-width:0; }
        .cal-date { font-family:'JetBrains Mono',ui-monospace,monospace; font-size:12px; font-weight:600; background:#F5F1E6; color:#0F5132; padding:4px 10px; border-radius:999px; }
        .cal-title { font-size:14px; font-weight:500; color:#1F2A24; }
        .cal-badge { font-size:12px; font-weight:600; padding:4px 10px; border-radius:999px; }
    </style>
@endsection
