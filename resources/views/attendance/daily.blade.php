@extends('layouts.portal')

@section('title', 'Daily Attendance')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/daily-attendance.css') }}">
    @php $circ = 2 * M_PI * 52; @endphp
    <div class="da">
        <p style="margin:0"><a href="{{ $back }}">&larr; Dashboard</a></p>
        <h1 class="da-title">Daily Attendance</h1>
        <p class="sd sd-note" style="margin:0">روزاني حاضري: Present، Absent يا Leave چونڊيو.</p>

        @if (count($classes) === 0)
            <div class="da-err">Koi class assign nahi hai.</div>
        @else
            <form method="GET" action="{{ $base }}" class="da-form">
                <label>Date<input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()"></label>
                <label>Class<select name="class" onchange="this.form.submit()">@foreach ($classes as $c)<option value="{{ $c }}" @selected($c === $class)>{{ $c }}</option>@endforeach</select></label>
            </form>

            @if ($scanUrl)<a class="da-scan" href="{{ $scanUrl }}">&#128247; Scan ID Card to Mark Present</a>@endif

            <section class="da-sum">
                <div class="da-donut">
                    <svg viewBox="0 0 120 120" width="130" height="130"><circle cx="60" cy="60" r="52" fill="none" stroke="#E3DDC9" stroke-width="12"/>
                        <circle id="daArc" cx="60" cy="60" r="52" fill="none" stroke="#0F5132" stroke-width="12" stroke-linecap="round" stroke-dasharray="{{ $circ * $t['percent'] / 100 }} {{ $circ }}" transform="rotate(-90 60 60)"/></svg>
                    <div><b id="daPct">{{ $t['percent'] }}%</b><small>Present</small></div>
                </div>
                <div class="da-k">
                    <div><small>TOTAL</small><b id="kT">{{ $t['total'] }}</b></div>
                    <div><small>PRESENT</small><b class="g" id="kP">{{ $t['present'] }}</b></div>
                    <div><small>ABSENT</small><b class="r" id="kA">{{ $t['absent'] }}</b></div>
                    <div><small>LEAVE</small><b class="y" id="kL">{{ $t['leave'] }}</b></div>
                    <div><small>PERCENTAGE</small><b class="y" id="kC">{{ $t['percent'] }}%</b></div>
                </div>
            </section>

            <div class="da-bar">
                <span id="daLeft">{{ $t['unmarked'] > 0 ? $t['unmarked'].' students not yet marked' : 'Sab students mark ho gaye' }}</span>
                <button type="button" id="daAll">Sab Present</button>
            </div>
            <div class="da-err" id="daMsg" hidden></div>

            <div class="da-list" id="daList">
                @forelse ($students as $s)
                    @php $m = $t['marks'][$s->id] ?? null; @endphp
                    <div class="da-row" data-id="{{ $s->id }}">
                        <div class="n"><i>{{ $s->gr_number }}</i> {{ $s->name }}</div>
                        <div class="b">
                            @foreach (['P' => 'Present', 'A' => 'Absent', 'L' => 'Leave'] as $k => $lab)
                                <button type="button" data-s="{{ $k }}" class="{{ $m === $k ? 'on k'.$k : '' }}">{{ $lab }}</button>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="da-err">Is class mein koi student nahi.</div>
                @endforelse
            </div>

            <script>
                (function () {
                    var URL = @json($saveUrl), CSRF = @json(csrf_token()), CLASS = @json($class), DATE = @json($date), CIRC = {{ $circ }};
                    var list = document.getElementById('daList'), msg = document.getElementById('daMsg');
                    function paint(t) {
                        ['kT:total', 'kP:present', 'kA:absent', 'kL:leave'].forEach(function (p) { var a = p.split(':'); document.getElementById(a[0]).textContent = t[a[1]]; });
                        document.getElementById('kC').textContent = t.percent + '%';
                        document.getElementById('daPct').textContent = t.percent + '%';
                        document.getElementById('daArc').setAttribute('stroke-dasharray', (CIRC * t.percent / 100) + ' ' + CIRC);
                        document.getElementById('daLeft').textContent = t.unmarked > 0 ? t.unmarked + ' students not yet marked' : 'Sab students mark ho gaye';
                        var marks = t.marks || {};
                        [].forEach.call(list.querySelectorAll('.da-row'), function (row) {
                            [].forEach.call(row.querySelectorAll('button'), function (b) {
                                var on = marks[row.dataset.id] === b.dataset.s;
                                b.className = on ? 'on k' + b.dataset.s : '';
                            });
                        });
                    }
                    function send(body) {
                        msg.hidden = true;
                        body.class = CLASS; body.date = DATE;
                        return fetch(URL, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) })
                            .then(function (r) { return r.json().then(function (j) { if (!r.ok) throw new Error(j.message || 'Save nahi hua'); return j; }); })
                            .then(paint)
                            .catch(function (e) { msg.textContent = e.message; msg.hidden = false; });
                    }
                    list.addEventListener('click', function (e) {
                        var b = e.target.closest('button'); if (!b) return;
                        var row = b.closest('.da-row');
                        send({ student_id: row.dataset.id, status: b.classList.contains('on') ? 'clear' : b.dataset.s });
                    });
                    document.getElementById('daAll').onclick = function () { if (confirm('Sab students Present mark karein? (Leave wale same rahenge)')) send({ status: 'all_present' }); };
                })();
            </script>
        @endif
    </div>
@endsection
