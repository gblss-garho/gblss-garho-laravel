@extends('layouts.portal')

@section('title', 'Attendance Overview')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/class-report.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-overview.css') }}">
    @php
        $nav = fn ($v) => $mode === 'week' ? ['week' => $v] : ['month' => $v];
        $keep = ['mode' => $mode, 'tab' => $tab];
        $label = $mode === 'week' ? $from->format('j M').' – '.$to->format('j M Y') : $from->format('F Y');
        $own = $mode === 'week' ? ['week' => $from->toDateString()] : ['month' => $from->format('Y-m')];
    @endphp
    <div class="cr ao">
        <p style="margin:0"><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
        <h1 style="margin:4px 0 2px">Attendance Overview</h1>
        <p class="sd sd-note" style="margin:0">اسٽاف ۽ شاگردن جي حاضري: ڪنهن به هفتي يا مهيني جو پورو رڪارڊ.</p>

        <form method="GET" action="{{ $base }}" class="cr-form">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="cr-seg">
                <button name="mode" value="month" @class(['on' => $mode === 'month'])>Mahina</button>
                <button name="mode" value="week" @class(['on' => $mode === 'week'])>Hafta</button>
            </div>
            @if ($mode === 'month')
                <label class="cr-wide">Mahina <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"></label>
            @else
                <label class="cr-wide">Hafta (koi bhi din) <input type="date" name="week" value="{{ $from->toDateString() }}" onchange="this.form.submit()"></label>
            @endif
        </form>

        <div class="ao-tabs">
            <a href="{{ $base }}?{{ http_build_query(['mode' => $mode, 'tab' => 'students'] + $own) }}" @class(['on' => $tab === 'students'])>Students</a>
            <a href="{{ $base }}?{{ http_build_query(['mode' => $mode, 'tab' => 'staff'] + $own) }}" @class(['on' => $tab === 'staff'])>Staff</a>
        </div>

        <div class="ao-period">
            <a href="{{ $base }}?{{ http_build_query($keep + $nav($prev)) }}">&larr;</a>
            <b>{{ $label }}</b>
            <a href="{{ $base }}?{{ http_build_query($keep + $nav($next)) }}">&rarr;</a>
        </div>

        @if ($tab === 'students')
            @php $t = $students['total']; @endphp
            <div class="cr-stats ao-stats">
                <div><strong>{{ $t['pct'] !== null ? $t['pct'].'%' : '—' }}</strong><small>HAZRI</small></div>
                <div><strong>{{ $t['p'] }}</strong><small>PRESENT</small></div>
                <div><strong>{{ $t['a'] }}</strong><small>ABSENT</small></div>
                <div><strong>{{ $t['l'] }}</strong><small>LEAVE</small></div>
            </div>
            @if (count($students['rows']) === 0)
                <p class="cr-note">Koi active student nahi mila.</p>
            @else
                <div class="ao-scroll"><table class="ao-t">
                    <thead><tr><th>Class</th><th>Students</th><th>Din</th><th>Present</th><th>Absent</th><th>Leave</th><th>%</th></tr></thead>
                    <tbody>
                    @foreach ($students['rows'] as $r)
                        <tr>
                            <td><a href="{{ route('admin.class-attendance', ['class' => $r['class'], 'mode' => $mode] + $own, false) }}"><b>{{ $r['class'] }}</b></a></td>
                            <td>{{ $r['strength'] }}</td>
                            <td>{{ $r['days'] }}</td>
                            <td class="g">{{ $r['p'] }}</td>
                            <td class="r">{{ $r['a'] }}</td>
                            <td class="v">{{ $r['l'] }}</td>
                            <td><span @class(['ao-pct', 'hi' => $r['pct'] !== null && $r['pct'] >= 85, 'mid' => $r['pct'] !== null && $r['pct'] >= 70 && $r['pct'] < 85, 'lo' => $r['pct'] !== null && $r['pct'] < 70])>{{ $r['pct'] !== null ? $r['pct'].'%' : '—' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
                <p class="cr-note">Class ke naam par dabayen: us class ke har bachay ka din-ba-din record khulega. "Din" = jitne din us class ki hazri lagi.</p>
            @endif
        @else
            @php $s = $staff['sum']; @endphp
            <div class="cr-stats ao-stats">
                <div><strong>{{ $s['pct'] !== null ? $s['pct'].'%' : '—' }}</strong><small>HAZRI</small></div>
                <div><strong>{{ $s['present'] }}</strong><small>PRESENT</small></div>
                <div><strong>{{ $s['absent'] }}</strong><small>ABSENT</small></div>
                <div><strong>{{ $s['late'] }}</strong><small>LATE</small></div>
            </div>
            @if ($staff['staff'] === 0)
                <p class="cr-note">Koi staff record nahi mila.</p>
            @else
                <div class="ao-scroll"><table class="ao-t ao-g">
                    <thead><tr>
                        <th class="nm">Staff</th>
                        @foreach ($staff['days'] as $d)
                            <th @class(['off' => ! $d['school']])><small>{{ $d['date']->format('D') }}</small>{{ $d['date']->format('j') }}</th>
                        @endforeach
                        <th>P</th><th>A</th><th>L</th><th>Late</th>
                    </tr></thead>
                    <tbody>
                    @foreach ($staff['rows'] as $r)
                        <tr>
                            <td class="nm">{{ $r['teacher']->name }}<small>{{ $r['teacher']->designation }}</small></td>
                            @foreach ($r['cells'] as $c)
                                <td class="cc c-{{ $c['code'] }}" @if ($c['late']) title="Late {{ $c['late'] }} min" @endif>{{ ['P' => 'P', 'T' => 'T', 'V' => 'L', 'A' => 'A', 'O' => '', '-' => '·'][$c['code']] }}</td>
                            @endforeach
                            <td class="g">{{ $r['present'] }}</td><td class="r">{{ $r['absent'] }}</td><td class="v">{{ $r['leave'] }}</td><td>{{ $r['late_minutes'] ? $r['late_minutes'].'m' : '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
                <div class="cr-legend">
                    <span><i class="p"></i>P = Present</span><span><i class="t"></i>T = Late</span>
                    <span><i class="l"></i>L = Chhutti manzoor</span><span><i class="a"></i>A = Absent</span>
                    <small>Late par ungli rakhein to minute dikhte hain. Jumma/Hafte ki chhutti wale din khali hain.</small>
                </div>
            @endif
        @endif
    </div>
@endsection
