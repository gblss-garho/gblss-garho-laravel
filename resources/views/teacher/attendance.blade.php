@extends('layouts.portal')

@section('title', 'Attendance')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
    @php
        $errMsg = $errors->first('attendance') ?: $errors->first();
        $scanning = in_array($mode, ['checkin', 'checkout'], true);
        $isIn = $mode === 'checkin';
        $now = \Carbon\Carbon::now();
    @endphp

    <div class="hz">
        <h1 style="margin-bottom:2px">Meri Hazri</h1>
        <p class="sd sd-note" style="margin:0">Time In/Out لاءِ پهريان اڄ جو پنهنجو چهرو enroll ڪريو.</p>

        @if (session('status'))<div class="hz-ok">{{ session('status') }}</div>@endif
        @if ($errMsg && ! $scanning)<div class="hz-err">{{ $errMsg }}</div>@endif

        {{-- ===== Aaj ===== --}}
        <section class="hz-hero">
            <div class="hz-ajrak"></div>
            <div class="hz-hero-in">
                <div class="hz-date">
                    <b>{{ $now->format('l, j M') }}</b>
                    <span>SCHOOL {{ $dayBar ? strtoupper($dayBar['from']).' – '.strtoupper($dayBar['to']) : 'OFF' }}</span>
                </div>

                @if ($mode === 'done' || $mode === 'waiting_out')
                    <span class="hz-state {{ $late > 0 ? 'warn' : 'ok' }}">{{ $late > 0 ? 'Late '.$late.' min' : 'Waqt par' }}</span>
                @elseif ($mode === 'missed')
                    <span class="hz-state bad">Absent</span>
                @elseif ($mode === 'checkin' && $late > 0)
                    <span class="hz-state warn">Aap {{ $late }} min late hain</span>
                @elseif ($mode === 'off')
                    <span class="hz-state">Aaj chhutti</span>
                @else
                    <span class="hz-state">{{ $mode === 'enroll' ? 'Chehra enroll baqi' : 'Time In ka intezar' }}</span>
                @endif

                <div class="hz-times">
                    <div><small>TIME IN</small><strong>{{ $record && $record->check_in_at ? $record->check_in_at->format('h:i') : '--:--' }}</strong>
                        @if ($record && $record->check_in_at)<em>{{ $record->check_in_at->format('A') }}</em>@endif</div>
                    <div><small>TIME OUT</small><strong>{{ $record && $record->check_out_at ? $record->check_out_at->format('h:i') : '--:--' }}</strong>
                        @if ($record && $record->check_out_at)<em>{{ $record->check_out_at->format('A') }}</em>@endif</div>
                </div>

                @if ($dayBar)
                    <div class="hz-track" title="School ka din">
                        <span class="ot" style="width:{{ $dayBar['ontime'] }}%"></span>
                        @if (! is_null($dayBar['in']))
                            <span class="sp {{ $late > 0 ? 'late' : '' }}" style="left:{{ $dayBar['in'] }}%;width:{{ max(1.5, ($dayBar['out'] ?? $dayBar['now']) - $dayBar['in']) }}%"></span>
                        @endif
                        <span class="nw" style="left:calc({{ $dayBar['now'] }}% - 1px)"></span>
                    </div>
                    <div class="hz-ends"><span>{{ $dayBar['from'] }}</span><span>on-time: {{ $checkInLabel }}</span><span>{{ $dayBar['to'] }}</span></div>
                @endif

                @if ($mode === 'enroll')
                    <p class="hz-note">Face se hazri ke liye ek dafa chehra enroll karna zaroori hai. <a href="{{ route('teacher.face.enroll', [], false) }}">Chehra Enroll Karen &rarr;</a></p>
                @elseif ($mode === 'waiting_out')
                    <p class="hz-note">Check-out {{ $checkOutLabel }} ke darmiyan khulega.</p>
                @elseif ($mode === 'early')
                    <p class="hz-note">Check-in {{ $checkInLabel }} se shuru hota hai.</p>
                @elseif ($mode === 'missed')
                    <p class="hz-note">Check-in ka waqt guzar chuka hai. Agar aap chhutti par the to <a href="{{ route('teacher.leave.index', [], false) }}">Chhutti ki darkhwast</a> den.</p>
                @elseif ($mode === 'checkin')
                    <p class="hz-note">Neeche chehra scan karen. 9:00 ke baad ka waqt late gina jayega.</p>
                @elseif ($mode === 'checkout')
                    <p class="hz-note">Check-out window: {{ $checkOutLabel }}. Neeche chehra scan karen.</p>
                @endif
            </div>
        </section>

        @if ($scanning)
            @if ($errMsg)<div class="hz-err">{{ $errMsg }}</div>@endif
            @include('teacher._face-scanner', [
                'action' => route($isIn ? 'teacher.attendance.checkin' : 'teacher.attendance.checkout', [], false),
                'btn' => $isIn ? 'Face se Check In Karen' : 'Face se Check Out Karen',
                'title' => $isIn ? 'Check In' : 'Check Out',
                'sub' => 'Chehra scan aur ek chhote liveness kaam ke baad hazri lagegi.',
                'challenges' => $challenges,
                'initialFail' => $errMsg,
            ])
        @endif

        {{-- ===== Hafta ===== --}}
        @if ($week)
            @php
                $sum = $week['sum'];
                $den = $sum['present'] + $sum['absent'];
                $score = $den > 0 ? (int) round($sum['present'] / $den * 100) : null;
                $end = $weekStart->copy()->addDays(6);
                $sel = 0;
                foreach ($week['days'] as $i => $dd) {
                    if ($dd['date']->isToday()) { $sel = $i; break; }
                    if ($dd['in']) { $sel = $i; }
                }
            @endphp
            <div class="hz-wk-head">
                <h2>{{ $weekStart->format('j M') }} &ndash; {{ $end->format('j M Y') }}</h2>
                <span class="hz-nav">
                    <a href="{{ route('teacher.attendance.index', ['week' => $prevWeek], false) }}">&larr; Pichla</a>
                    @unless ($isCurrentWeek)<a href="{{ route('teacher.attendance.index', [], false) }}">Is hafte</a><a href="{{ route('teacher.attendance.index', ['week' => $nextWeek], false) }}">Agla &rarr;</a>@endunless
                </span>
            </div>

            <div class="hz-summary">
                <div class="hz-score" style="--p:{{ $score ?? 0 }}"><span><b>{{ $score === null ? '—' : $score.'%' }}</b><small>HAZRI</small></span></div>
                <div class="hz-chips">
                    <div><i style="background:#16A34A"></i><span><b>{{ $sum['present'] - $sum['late'] }}</b>Waqt par</span></div>
                    <div><i style="background:#F59E0B"></i><span><b>{{ $sum['late'] }}</b>Late{{ $sum['late_minutes'] ? ' ('.$sum['late_minutes'].'m)' : '' }}</span></div>
                    <div><i style="background:#6366F1"></i><span><b>{{ $sum['leave'] }}</b>Leave</span></div>
                    <div><i style="background:#DC2626"></i><span><b>{{ $sum['absent'] }}</b>Absent</span></div>
                </div>
            </div>

            <div class="hz-wkx" id="hzwk">
                <div class="hz-days">
                    @foreach ($week['days'] as $i => $d)
                        <button type="button" class="dd s-{{ $d['status'] }} {{ $d['date']->isToday() ? 'is-today' : '' }} {{ $i === $sel ? 'on' : '' }}" data-i="{{ $i }}" aria-label="{{ $d['date']->format('l j M') }}: {{ $d['label'] }}">
                            <span class="dc">{{ $d['date']->format('j') }}</span><small>{{ $d['date']->format('D') }}</small>
                        </button>
                    @endforeach
                </div>

                @foreach ($week['days'] as $i => $d)
                    <div class="dp s-{{ $d['status'] }} {{ $i === $sel ? 'on' : '' }}" data-i="{{ $i }}">
                        <div class="dp-top"><b>{{ $d['date']->format('l, j M') }}</b><span class="pill p-{{ $d['status'] }}">{{ $d['label'] }}</span></div>
                        @if ($d['in'])
                            <div class="dp-io">
                                <div><small>IN</small><strong>{{ $d['in'] }}</strong></div>
                                <div><small>OUT</small><strong>{{ $d['out'] ?? '--:--' }}</strong></div>
                                @if ($d['hours'])<div><small>SCHOOL MEIN</small><strong>{{ $d['hours'] }}</strong></div>@endif
                            </div>
                            <div class="dp-meta">
                                @if ($d['late'] > 0)<span class="lt">Late {{ $d['late'] }} min</span>@else Waqt par pohanche @endif
                                @if ($d['note']) &middot; {{ $d['note'] }}@endif
                            </div>
                            @if ($d['bar'])<div class="dp-bar"><i style="left:{{ $d['bar']['left'] }}%;width:{{ $d['bar']['width'] }}%"></i></div>@endif
                        @else
                            <div class="dp-meta">
                                @if ($d['status'] === 'off') Haftawar chhutti
                                @elseif ($d['status'] === 'leave') Chhutti manzoor{{ $d['note'] ? ': '.$d['note'] : '' }}
                                @elseif ($d['status'] === 'upcoming') Yeh din abhi aana baqi hai
                                @elseif ($d['status'] === 'pending') Abhi check-in ka waqt hai
                                @else Koi hazri darj nahi{{ $d['note'] ? ' · '.$d['note'] : '' }}
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <p class="hz-foot">Late = {{ $checkInLabel }} ke baad. Din ke gol nishan par dabayen to us din ki tafseel khulegi.</p>

            <script>
                (function () {
                    var box = document.getElementById('hzwk');
                    if (!box) { return; }
                    box.classList.add('hz-js');
                    var btns = box.querySelectorAll('.dd'), panes = box.querySelectorAll('.dp');
                    btns.forEach(function (b) {
                        b.addEventListener('click', function () {
                            var i = b.getAttribute('data-i');
                            btns.forEach(function (x) { x.classList.toggle('on', x === b); });
                            panes.forEach(function (p) { p.classList.toggle('on', p.getAttribute('data-i') === i); });
                        });
                    });
                })();
            </script>
        @else
            <p>Aapke account se koi teacher record linked nahi hai.</p>
        @endif
    </div>
@endsection
