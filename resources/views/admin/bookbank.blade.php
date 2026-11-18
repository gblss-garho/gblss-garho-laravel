@extends('layouts.portal')

@section('title', 'Book Bank')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/bookbank.css') }}">
    @php
        $nm = fn ($c) => preg_replace('/^class|\s+/i', '', mb_strtolower((string) $c));
        $schoolName = \Illuminate\Support\Facades\DB::table('school_info')->value('name') ?? 'Government Boys Lower Secondary School Garho';
        $short = fn ($c) => trim(preg_replace('/^class\s*/i', '', (string) $c));
        $s = $stock;
        $pct = $s['total'] > 0 ? round($s['issued'] / $s['total'] * 100, 1) : 0;
        $circ = 2 * M_PI * 52;
    @endphp
    <div class="bb">
        <p class="bb-noprint" style="margin:0"><a href="{{ $back }}">&larr; Dashboard</a></p>
        <h1 class="bb-noprint bb-title">Book Bank &mdash; Sindh Textbook Board Distribution</h1>

        @if (session('ok'))<div class="bb-ok bb-noprint">{{ session('ok') }}</div>@endif
        @foreach ($errors->all() as $e)<div class="bb-er bb-noprint">{{ $e }}</div>@endforeach

        @if (count($classes) === 0)
            <div class="bb-er">Koi class assign nahi hai.</div>
        @else
            <div class="bb-top bb-noprint">
                <div class="bb-seg">
                    <a href="{{ $base }}?class={{ urlencode($class) }}" @class(['on' => $view === 'register'])>Student Register</a>
                    <a href="{{ $base }}?class={{ urlencode($class) }}&view=catalog" @class(['on' => $view === 'catalog'])>Book Catalog</a>
                </div>
            </div>
            <div class="bb-bar bb-noprint">
                <form method="GET" action="{{ $base }}">
                    <input type="hidden" name="view" value="{{ $view }}">
                    <select name="class" onchange="this.form.submit()" aria-label="Class">
                        @foreach ($classes as $c)<option value="{{ $c }}" @selected($nm($c) === $nm($class))>{{ str_starts_with(strtolower($c), 'class') ? $c : 'Class '.$c }}</option>@endforeach
                    </select>
                </form>
                @if ($view === 'register')
                    <button type="button" class="bb-btn" id="bbOpen">+ Issue / Update Record</button>
                @elseif ($isAdmin)
                    <button type="button" class="bb-btn" id="bbOpenCat">+ Add Book to Catalog</button>
                @endif
                <button type="button" class="bb-btn out" onclick="window.print()">&#128424; Print / Save as PDF</button>
            </div>

            <div class="bb-pr">
                <h2>{{ $schoolName }}</h2>
                <p><b>Book Bank &mdash; {{ $view === 'catalog' ? 'Stock' : 'Student Register' }} &mdash; Class {{ $short($class) }}</b></p>
                <p>Print date: {{ now()->format('d M Y') }}</p>
            </div>

            @if ($view === 'register')
                <div class="bb-panel bb-noprint" id="bbPanel" hidden>
                    <div class="bb-ph"><b>Issue / Update Record</b><button type="button" class="bb-x" id="bbClose">&times; Close</button></div>
                    @if ($classCatalog->isEmpty())
                        <p class="bb-muted">Is class ke liye catalog mein koi kitaab nahi.@if ($isAdmin) &ldquo;Book Catalog&rdquo; mein add karein.@else Admin se catalog mein add karwayein.@endif</p>
                    @else
                    <form method="POST" action="{{ route($prefix.'.bookbank.matrix', [], false) }}" id="bbForm">
                        @csrf
                        <label class="bb-l">Student</label>
                        <select name="student_id" id="bbStu" required>
                            <option value="">&mdash; Student chunein &mdash;</option>
                            @foreach ($students as $st2)<option value="{{ $st2->id }}">{{ $st2->gr_number }} &middot; {{ $st2->name }}</option>@endforeach
                        </select>
                        <div class="bb-sub">
                            <div class="bb-sh"><span>Subject</span><span>Distributed</span><span>Collected</span></div>
                            @foreach ($subjects as $sub)
                                @php $has = in_array(mb_strtolower($sub), $catalogSubjects, true); @endphp
                                <div class="bb-sr @if(! $has) off @endif">
                                    <span>{{ $sub }}@if (! $has) <small>(catalog mein nahi)</small>@endif</span>
                                    <label><input type="checkbox" name="dist[]" value="{{ $sub }}" data-d="{{ $sub }}" @disabled(! $has)><i></i></label>
                                    <label><input type="checkbox" name="coll[]" value="{{ $sub }}" data-c="{{ $sub }}" @disabled(! $has)><i></i></label>
                                </div>
                            @endforeach
                        </div>
                        <button class="bb-btn gold" type="submit">Save Record</button>
                    </form>
                    @endif
                </div>

                <div class="bb-card">
                    <div class="bb-scroll">
                        <table class="bb-m">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="bb-sn">Student</th><th rowspan="2">GR#</th>
                                    <th colspan="{{ count($subjects) }}" class="grp">Books Distributed</th>
                                    <th colspan="{{ count($subjects) }}" class="grp">Books Collected</th>
                                </tr>
                                <tr>
                                    @foreach (['D', 'C'] as $_)@foreach ($subjects as $sub)<th class="sb @if($loop->first) bl @endif">{{ $sub }}</th>@endforeach @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $st2)
                                    <tr>
                                        <td class="bb-sn">{{ $st2->name }}</td><td>{{ $st2->gr_number }}</td>
                                        @foreach (['D', 'C'] as $col)
                                            @foreach ($subjects as $sub)
                                                @php $v = $matrix[$st2->id][$sub] ?? null; $on = $col === 'D' ? $v !== null : $v === 'C'; @endphp
                                                <td class="c @if($loop->first) bl @endif">{!! $on ? '<b class="ok">&#10003;</b>' : '<span class="no">&ndash;</span>' !!}</td>
                                            @endforeach
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ 2 + count($subjects) * 2 }}"><span class="sd">هن ڪلاس ۾ ڪو شاگرد ناهي.</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                    (function () {
                        var M = @json($matrix), panel = document.getElementById('bbPanel');
                        var open = document.getElementById('bbOpen'), close = document.getElementById('bbClose'), stu = document.getElementById('bbStu');
                        if (open) open.onclick = function () { panel.hidden = !panel.hidden; if (!panel.hidden) panel.scrollIntoView({behavior: 'smooth', block: 'start'}); };
                        if (close) close.onclick = function () { panel.hidden = true; };
                        if (!stu) return;
                        var D = [].slice.call(document.querySelectorAll('[data-d]')), C = [].slice.call(document.querySelectorAll('[data-c]'));
                        function load() {
                            var m = M[stu.value] || {};
                            D.forEach(function (b) { b.checked = !!m[b.dataset.d]; });
                            C.forEach(function (b) { b.checked = m[b.dataset.c] === 'C'; });
                        }
                        stu.onchange = load;
                        C.forEach(function (b) { b.onchange = function () { if (b.checked) D.filter(function (d) { return d.dataset.d === b.dataset.c; })[0].checked = true; }; });
                        D.forEach(function (b) { b.onchange = function () { if (!b.checked) C.filter(function (c) { return c.dataset.c === b.dataset.d; })[0].checked = false; }; });
                    })();
                </script>
            @else
                @if ($isAdmin)
                <div class="bb-panel bb-noprint" id="bbCat" hidden>
                    <div class="bb-ph"><b>Add Book to Catalog</b><button type="button" class="bb-x" onclick="document.getElementById('bbCat').hidden=true">&times; Close</button></div>
                    <form method="POST" action="{{ route('admin.bookbank.catalog.add', [], false) }}" class="bb-form">
                        @csrf
                        <input name="class" placeholder="Class (VI)" value="{{ $short($class) }}" required style="flex:0 0 90px">
                        <input name="subject" placeholder="Subject" required list="bbSubj">
                        <datalist id="bbSubj">@foreach (\App\Http\Controllers\AdminBookBankController::SUBJECTS as $x)<option value="{{ $x }}">@endforeach</datalist>
                        <input name="book_title" placeholder="Book title" required>
                        <input type="number" name="available_qty" placeholder="Total quantity" min="0" required style="flex:0 0 130px">
                        <button type="submit">Save Catalog Entry</button>
                    </form>
                    <p class="bb-muted">&ldquo;Total quantity&rdquo; = abhi school ke paas maujood copies. Issued khud update hoti hai.</p>
                </div>
                <script>document.getElementById('bbOpenCat').onclick=function(){var p=document.getElementById('bbCat');p.hidden=!p.hidden;};</script>
                @endif

                <div class="bb-card bb-stock">
                    <div class="bb-sh2"><h2>Stock Overview</h2><span class="bb-chip">Class {{ $short($class) }}</span></div>
                    <div class="bb-ov">
                        <div class="bb-donut">
                            <svg viewBox="0 0 120 120" width="150" height="150" role="img" aria-label="Stock">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="#0F5132" stroke-width="14"/>
                                @if ($s['issued'] > 0)<circle cx="60" cy="60" r="52" fill="none" stroke="#DBB43C" stroke-width="14" stroke-dasharray="{{ max(3, $circ * $s['issued'] / max(1, $s['total'])) }} {{ $circ }}" transform="rotate(-90 60 60)"/>@endif
                            </svg>
                            <div><b>{{ $s['total'] }}</b><small>Total Books</small></div>
                        </div>
                        <ul class="bb-leg"><li><i style="background:#0F5132"></i>Available (unissued) <b>{{ $s['remaining'] }}</b></li><li><i style="background:#DBB43C"></i>Currently Issued <b>{{ $s['issued'] }}</b></li></ul>
                    </div>
                    <div class="bb-k"><div><b>{{ $s['total'] }}</b>Total Received</div><div><b class="g">{{ $s['issued'] }}</b>Issued</div><div><b class="b">{{ $s['remaining'] }}</b>Remaining</div></div>
                </div>

                <div class="bb-card">
                    <div class="bb-scroll">
                        <table class="bb-t">
                            <tr><th>Class</th><th>Subject</th><th>Book Title</th><th>Total</th><th>Issued</th><th>Remaining</th>@if ($isAdmin)<th class="bb-noprint">Actions</th>@endif</tr>
                            @foreach ($classCatalog as $b)
                                <tr>
                                    <td>{{ $b->class }}</td><td>{{ $b->subject }}</td><td>{{ $b->book_title }}</td>
                                    <td>{{ $b->available_qty + $b->issued_qty }}</td><td>{{ $b->issued_qty }}</td>
                                    <td>
                                        @if ($isAdmin)
                                            <form method="POST" action="{{ route('admin.bookbank.catalog.qty', $b->id, false) }}" class="bb-form bb-noprint" style="flex-wrap:nowrap">
                                                @csrf <input type="hidden" name="class" value="{{ $class }}">
                                                <input type="number" name="available_qty" value="{{ $b->available_qty }}" min="0" style="flex:0 0 70px">
                                                <button type="submit">Save</button>
                                            </form>
                                            <span class="bb-pr">{{ $b->available_qty }}</span>
                                        @else {{ $b->available_qty }} @endif
                                    </td>
                                    @if ($isAdmin)
                                        <td class="bb-noprint">
                                            <form method="POST" action="{{ route('admin.bookbank.catalog.delete', $b->id, false) }}" onsubmit="return confirm('Hata dein?')">
                                                @csrf @method('DELETE') <input type="hidden" name="class" value="{{ $class }}">
                                                <button type="submit" class="bb-btn red">Delete</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            @if ($classCatalog->isEmpty())<tr><td colspan="7">Is class ke liye catalog khali hai.</td></tr>@endif
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
