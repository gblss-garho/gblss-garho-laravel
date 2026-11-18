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

            <div class="da-savebar"><span class="da-note" id="daNote"></span><button type="button" id="daSave" class="da-save">Save Attendance</button></div>

            <script>
                (function () {
                    var URL = @json($saveUrl), CSRF = @json(csrf_token()), CLASS = @json($class), DATE = @json($date), CIRC = {{ $circ }};
                    var saved = @json($t['marks']), cur = Object.assign({}, saved), TOTAL = {{ $t['total'] }};
                    var list = document.getElementById('daList'), msg = document.getElementById('daMsg'), btn = document.getElementById('daSave'), note = document.getElementById('daNote');
                    function dirty() { var ids = new Set(Object.keys(saved).concat(Object.keys(cur))); var out = {}; ids.forEach(function (id) { if (saved[id] !== cur[id]) out[id] = cur[id] || 'clear'; }); return out; }
                    function paint() {
                        var c = { P: 0, A: 0, L: 0 };
                        Object.keys(cur).forEach(function (k) { if (c[cur[k]] !== undefined) c[cur[k]]++; });
                        var pct = TOTAL ? Math.round(c.P / TOTAL * 100) : 0, un = TOTAL - c.P - c.A - c.L;
                        document.getElementById('kT').textContent = TOTAL; document.getElementById('kP').textContent = c.P;
                        document.getElementById('kA').textContent = c.A; document.getElementById('kL').textContent = c.L;
                        document.getElementById('kC').textContent = pct + '%'; document.getElementById('daPct').textContent = pct + '%';
                        document.getElementById('daArc').setAttribute('stroke-dasharray', (CIRC * pct / 100) + ' ' + CIRC);
                        document.getElementById('daLeft').textContent = un > 0 ? un + ' students not yet marked' : 'Sab students mark ho gaye';
                        [].forEach.call(list.querySelectorAll('.da-row'), function (row) {
                            [].forEach.call(row.querySelectorAll('button'), function (b) { b.className = cur[row.dataset.id] === b.dataset.s ? 'on k' + b.dataset.s : ''; });
                        });
                        var n = Object.keys(dirty()).length;
                        note.textContent = n ? n + ' tabdeeliyan save hona baqi hain' : 'Sab tabdeeliyan save hain';
                        note.className = n ? 'da-note warn' : 'da-note';
                        btn.disabled = !n;
                    }
                    list.addEventListener('click', function (e) {
                        var b = e.target.closest('button'); if (!b) return;
                        var id = b.closest('.da-row').dataset.id;
                        if (cur[id] === b.dataset.s) delete cur[id]; else cur[id] = b.dataset.s;
                        paint();
                    });
                    document.getElementById('daAll').onclick = function () {
                        list.querySelectorAll('.da-row').forEach(function (r) { if (cur[r.dataset.id] !== 'L') cur[r.dataset.id] = 'P'; });
                        paint();
                    };
                    btn.onclick = function () {
                        var changes = dirty(); if (!Object.keys(changes).length) return;
                        btn.disabled = true; btn.textContent = 'Save ho raha hai...'; msg.hidden = true;
                        fetch(URL, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ class: CLASS, date: DATE, marks: changes }) })
                            .then(function (r) { return r.json().then(function (j) { if (!r.ok) throw new Error(j.message || 'Save nahi hua'); return j; }); })
                            .then(function (j) { saved = Object.assign({}, j.marks || {}); cur = Object.assign({}, saved); btn.textContent = '\u2713 Save ho gaya'; setTimeout(function () { btn.textContent = 'Save Attendance'; paint(); }, 1500); paint(); })
                            .catch(function (e) { msg.textContent = e.message; msg.hidden = false; btn.textContent = 'Save Attendance'; paint(); });
                    };
                    window.addEventListener('beforeunload', function (e) { if (Object.keys(dirty()).length) { e.preventDefault(); e.returnValue = ''; } });
                    paint();
                })();
            </script>
        @endif
    </div>
@endsection
