@extends('layouts.portal')

@section('title', 'Students Attendance')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/class-report.css') }}">
    @php
        $sum = $data['summary'] ?? null;
        $lv = ['regular' => 'Regular', 'average' => 'Average', 'irregular' => 'Irregular', 'none' => 'Record nahi'];
        $lvSd = ['regular' => 'باقاعده', 'average' => 'اوسط', 'irregular' => 'بي قاعدو', 'none' => ''];
        $nav = fn ($v) => $mode === 'week' ? ['week' => $v] : ['month' => $v];
        $keep = ['class' => $class, 'mode' => $mode, 'q' => $q];
    @endphp

    <div class="cr">
        <p style="margin:0"><a href="{{ $back }}">&larr; Dashboard</a></p>
        <h1 style="margin:4px 0 2px">Students ka Attendance Record</h1>
        <p class="sd sd-note" style="margin:0">شاگردن جي حاضري: هفتيوار ۽ مهيناوار رڪارڊ.</p>

        @if (! $isAdmin && ! $teacher)
            <p class="cr-err">توهان جو لاگ ان ڪنهن ٽيچر رڪارڊ سان لنڪ ٿيل ناهي. ايڊمن کان پنهنجو AUTH EMAIL فيلڊ سيٽ ڪرايو.</p>
        @elseif (count($classes) === 0)
            <p class="cr-err">Koi class assign nahi hai.</p>
        @else
            <form method="GET" action="{{ $base }}" class="cr-form">
                <div class="cr-seg">
                    <button name="mode" value="month" @class(['on' => $mode === 'month'])>Mahina</button>
                    <button name="mode" value="week" @class(['on' => $mode === 'week'])>Hafta</button>
                </div>
                <label>Class
                    <select name="class" onchange="this.form.submit()">
                        @foreach ($classes as $c)<option value="{{ $c }}" @selected($c === $class)>{{ $c }}</option>@endforeach
                    </select>
                </label>
                @if ($mode === 'month')
                    <label>Mahina <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"></label>
                @else
                    <label>Hafta (koi bhi din) <input type="date" name="week" value="{{ $from->toDateString() }}" onchange="this.form.submit()"></label>
                @endif
                <label class="cr-wide">Search (naam, father ya GR)
                    <input type="search" name="q" value="{{ $q }}" placeholder="e.g. Ali ya 125">
                </label>
                <label>Kisi din ka haal
                    <input type="date" name="date" value="{{ $dayDate }}" min="{{ $from->toDateString() }}" max="{{ $to->toDateString() }}">
                </label>
                <label>Status
                    <select name="st">
                        <option value="all" @selected($dayStatus === 'all')>Sab</option>
                        <option value="present" @selected($dayStatus === 'present')>Present</option>
                        <option value="absent" @selected($dayStatus === 'absent')>Absent</option>
                        <option value="leave" @selected($dayStatus === 'leave')>Leave</option>
                    </select>
                </label>
                <button type="submit" class="cr-go">Dikhayein</button>
            </form>

            @if (! $data)
                <p class="cr-err">Is class ka record dekhne ki ijazat nahi.</p>
            @else
                <section class="cr-hero">
                    <div class="cr-ajrak"></div>
                    <div class="cr-hero-in">
                        <div class="cr-title">
                            <div>
                                <b>{{ $class }}</b>
                                <span>{{ $mode === 'week' ? $from->format('j M').' – '.$to->format('j M Y') : $from->format('F Y') }}</span>
                            </div>
                            <div class="cr-nav">
                                <a href="{{ $base }}?{{ http_build_query($keep + $nav($prev)) }}">&larr;</a>
                                <a href="{{ $base }}?{{ http_build_query($keep + $nav($next)) }}">&rarr;</a>
                            </div>
                        </div>
                        <div class="cr-stats">
                            <div><strong>{{ $sum['avg'] !== null ? $sum['avg'].'%' : '—' }}</strong><small>CLASS AVG</small></div>
                            <div><strong>{{ $sum['class_days'] }}</strong><small>ATTENDANCE DIN</small></div>
                            <div><strong>{{ $sum['regular'] }}</strong><small>REGULAR</small></div>
                            <div><strong>{{ $sum['irregular'] }}</strong><small>IRREGULAR</small></div>
                        </div>
                    </div>
                </section>

                @if ($sum['class_days'] === 0)
                    <p class="cr-note">Is {{ $mode === 'week' ? 'hafte' : 'mahine' }} mein is class ki attendance abhi nahi lagi.</p>
                @endif

                @if ($dayDate)
                    <p class="cr-note"><b>{{ \Carbon\Carbon::parse($dayDate)->format('l, j M Y') }}</b> &middot; {{ count($data['students']) }} students
                        @if ($dayStatus !== 'all') ({{ $dayStatus }}) @endif</p>
                @endif

                <div class="cr-legend">
                    <span><i class="p"></i>Present</span><span><i class="a"></i>Absent</span><span><i class="l"></i>Leave</span><span><i class="n"></i>Attendance nahi lagi</span>
                    <small>Regular &ge; {{ \App\Services\ClassAttendanceReport::REGULAR_AT }}% &middot; Average &ge; {{ \App\Services\ClassAttendanceReport::AVERAGE_AT }}% &middot; us se kam Irregular</small>
                </div>

                @forelse ($data['students'] as $r)
                    @php $s = $r['student']; @endphp
                    <article class="cr-card lv-{{ $r['level'] }}">
                        <div class="cr-top">
                            <div class="cr-who">
                                <b>{{ $s->name }}</b>
                                <small>GR {{ $s->gr_number }}@if ($s->father_name) &middot; {{ $s->father_name }}@endif</small>
                            </div>
                            <div class="cr-right">
                                @if ($dayDate)
                                    <span class="cr-day d-{{ $r['dayState'] ?? 'N' }}">{{ ['P' => 'Present', 'A' => 'Absent', 'L' => 'Leave'][$r['dayState']] ?? 'Nahi lagi' }}</span>
                                @endif
                                <span class="cr-badge b-{{ $r['level'] }}">{{ $lv[$r['level']] }}@if ($lvSd[$r['level']]) <em class="sd">{{ $lvSd[$r['level']] }}</em>@endif</span>
                            </div>
                        </div>
                        <div class="cr-meter"><span style="width:{{ $r['pct'] ?? 0 }}%"></span></div>
                        <div class="cr-nums">
                            <span><b>{{ $r['pct'] !== null ? $r['pct'].'%' : '—' }}</b> hazri</span>
                            <span class="p">P {{ $r['p'] }}</span><span class="a">A {{ $r['a'] }}</span><span class="l">L {{ $r['l'] }}</span>
                        </div>
                        @if (count($data['days']))
                            <div class="cr-strip" style="--n:{{ $mode === 'week' ? 7 : max(1, count($data['days'])) }}">
                                @foreach ($data['days'] as $i => $day)
                                    @php $c = $r['cells'][$i]; @endphp
                                    <div class="cell c-{{ $c ?? 'N' }}" title="{{ $day['date'] }}">
                                        <small>{{ $mode === 'week' ? $day['label'] : $day['num'] }}</small>
                                        <b>{{ $c ?? '–' }}</b>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @empty
                    <p class="cr-note">Koi student nahi mila.</p>
                @endforelse
            @endif
        @endif
    </div>
@endsection
