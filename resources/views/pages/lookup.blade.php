@extends('layouts.portal')

@section('title', 'Parents Corner')

@section('content')
    <div class="hero-lk">
        <div class="ring"><img src="{{ file_exists(public_path('images/logo-glow.png')) ? asset('images/logo-glow.png') : (file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg')) }}" alt=""></div>
        <h2>{{ $schoolName ?? 'Government Boys Lower Secondary School Garho' }}</h2>
        <span class="pill">Parent's Corner &mdash; <span class="sd">GR نمبر ۽ تاريخ پيدائش سان ڏسو</span></span>
        <span class="dev">Designed &amp; Developed by Sir Waris Ghafoor</span>
    </div>

    <div class="lk-card">
        <div class="lk-title sd">پنهنجي ٻار جي تفصيل ڏسو</div>
        <p>Apne bachay ka GR number aur date of birth likh kar result dekhein.</p>

        @if ($error)<div class="err"><p>{{ $error }}</p></div>@endif

        <form method="POST" action="{{ route('public.lookup.check') }}">
            @csrf
            <label for="gr_number">GR / Roll number <span class="sd">(GR نمبر داخل ڪريو)</span></label>
            <input id="gr_number" name="gr_number" value="{{ old('gr_number') }}" required inputmode="numeric" placeholder="GR number">

            <label for="dob">Date of birth <span class="sd">(رڪارڊ موجب)</span></label>
            <input id="dob" name="dob" type="date" value="{{ old('dob') }}" required>

            <div class="btn-row">
                <button type="submit">Search</button>
                <a class="btn ghost" href="{{ route('public.lookup', [], false) }}">Reset</a>
            </div>
        </form>
    </div>

    @if ($student)
        <link rel="stylesheet" href="{{ asset('css/parent-portal.css') }}">
        @if ($ok)<div class="pp-ok">{{ $ok }}</div>@endif
        <div class="lk-card pp-head">
            <div class="pp-name"><h2>{{ $student->name }}</h2><small>GR {{ $student->gr_number }} &middot; Class {{ $student->class }}{{ $student->section ? '-'.$student->section : '' }} &middot; Walid: {{ $student->father_name }}</small></div>
            <div class="pp-stats">
                <div><small>ATTENDANCE</small>
                    @if ($att['percent'] !== null)<b class="{{ $att['percent'] >= 85 ? 'g' : ($att['percent'] >= 70 ? 'y' : 'r') }}">{{ $att['percent'] }}%</b><em>{{ $att['present'] }} / {{ $att['days'] }} din</em>
                    @else<b class="y">&mdash;</b><em>abhi record nahi</em>@endif
                </div>
                <div><small>OVERALL PROGRESS</small>
                    @if ($progress['percent'] !== null)<b class="{{ $progress['percent'] >= 60 ? 'g' : ($progress['percent'] >= 40 ? 'y' : 'r') }}">{{ $progress['percent'] }}%</b><em>{{ $progress['got'] }} / {{ $progress['max'] }} marks</em>
                    @else<b class="y">&mdash;</b><em>result shaya nahi hua</em>@endif
                </div>
            </div>
        </div>

        @include('pages._ai-tutor')

        @forelse ($exams as $exam => $rows)
            <div class="lk-card">
                <h3 style="margin-top:0">{{ ucfirst($exam) }}</h3>
                <div class="scroll">
                    <table>
                        <tr><th>Subject</th><th>Marks</th></tr>
                        @foreach ($rows as $r)
                            <tr><td>{{ ucfirst($r->subject) }}</td><td>{{ $r->score }}</td></tr>
                        @endforeach
                        <tr><td><b>Total</b></td><td><b>{{ $rows->sum('score') }}</b></td></tr>
                    </table>
                </div>
            </div>
        @empty
            <div class="card"><p class="sd">نتيجو اڃا شايع نه ٿيو آهي</p></div>
        @endforelse

        @php
            $todayHw = $homework->filter(fn ($h) => $h->date->isToday());
            $subjects = $homework->pluck('subject')->filter()->map(fn ($x) => ucfirst($x))->unique()->sort()->values();
        @endphp
        <div class="lk-card">
            <h3 style="margin-top:0">&#128218; Today's Homework <span class="sd">&mdash; اڄ جو هوم ورڪ</span></h3>
            <small>Class {{ $student->class }}</small>
            @forelse ($todayHw as $h)
                <div class="pp-hw"><span class="tag">{{ ucfirst($h->subject) }}</span><p>{!! nl2br(e($h->text)) !!}</p>
                    @if ($h->attachment_path)<a href="{{ asset('storage/' . $h->attachment_path) }}" target="_blank">{{ $h->attachment_name ?: 'File' }}</a>@endif<small>&mdash; {{ $h->teacher_name }}</small></div>
            @empty
                <div class="pp-empty sd">اڄ لاءِ هن ڪلاس جو ڪو Homework/Diary داخل نه ٿيو آهي.</div>
            @endforelse

            <h4 style="margin:18px 0 8px">Previous Homework <span class="sd">&mdash; پويون Homework</span></h4>
            <div class="pp-filters">
                <label>Subject<select id="hwSub"><option value="">All Subjects</option>@foreach ($subjects as $sb)<option>{{ $sb }}</option>@endforeach</select></label>
                <label>Range<select id="hwRange"><option value="0">All Time</option><option value="7">Last 7 days</option><option value="30">Last 30 days</option><option value="90">Last 3 months</option></select></label>
            </div>
            <div id="hwList">
                @foreach ($homework->reject(fn ($h) => $h->date->isToday()) as $h)
                    <div class="pp-hw" data-sub="{{ ucfirst($h->subject) }}" data-age="{{ $h->date->diffInDays(now()->startOfDay()) }}">
                        <span class="tag">{{ ucfirst($h->subject) }}</span> <small>{{ $h->date->format('d M Y') }}</small>
                        <p>{!! nl2br(e($h->text)) !!}</p>
                        @if ($h->attachment_path)<a href="{{ asset('storage/' . $h->attachment_path) }}" target="_blank">{{ $h->attachment_name ?: 'File' }}</a>@endif
                        <small>&mdash; {{ $h->teacher_name }}</small>
                    </div>
                @endforeach
                <div class="pp-empty" id="hwNone" hidden>Is filter ke mutabiq koi Homework nahi.</div>
            </div>
        </div>
        <script>
            (function () {
                var sub = document.getElementById('hwSub'), rng = document.getElementById('hwRange'), none = document.getElementById('hwNone');
                function run() {
                    var shown = 0, days = +rng.value;
                    [].forEach.call(document.querySelectorAll('#hwList .pp-hw'), function (el) {
                        var ok = (!sub.value || el.dataset.sub === sub.value) && (!days || +el.dataset.age <= days);
                        el.hidden = !ok; if (ok) shown++;
                    });
                    none.hidden = shown > 0;
                }
                sub.onchange = rng.onchange = run; run();
            })();
        </script>

        <div class="lk-card">
            <h3 style="margin-top:0">Leave / Absence Application <span class="sd">&mdash; موڪل جي درخواست</span></h3>
            @if ($leaveError)<div class="err"><p>{{ $leaveError }}</p></div>@endif
            <form method="POST" action="{{ route('public.lookup.leave', [], false) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="gr_number" value="{{ $student->gr_number }}"><input type="hidden" name="dob" value="{{ $dob }}">
                <div class="pp-two">
                    <label>Date<input type="date" name="leave_date" value="{{ old('leave_date', now()->toDateString()) }}" required></label>
                    <label>Purpose<select name="purpose">@foreach (\App\Http\Controllers\PublicLeaveController::PURPOSES as $pu)<option>{{ $pu }}</option>@endforeach</select></label>
                </div>
                <label>Reason <span class="sd">(ڪلاس ٽيچر لاءِ درخواست)</span><textarea name="reason" rows="3" maxlength="1000" required>{{ old('reason') }}</textarea></label>
                <label>Attach application / media (optional, image or PDF, up to 5MB)<input type="file" name="attachment" accept="image/jpeg,image/png,application/pdf"></label>
                <div class="btn-row"><button type="submit">Submit Application</button></div>
            </form>
            @if ($leaves->isNotEmpty())
                <h4 style="margin:18px 0 6px">Pichli arziyan</h4>
                @foreach ($leaves as $lv)
                    <div class="pp-lv"><b>{{ substr($lv->leave_date, 0, 10) }}</b> &middot; {{ $lv->purpose }} <span class="st {{ $lv->status }}">{{ ucfirst($lv->status) }}</span></div>
                @endforeach
            @endif
        </div>

        @php
            $p = (int) ($attMonth['P'] ?? 0); $a = (int) ($attMonth['A'] ?? 0); $l = (int) ($attMonth['L'] ?? 0);
            $tot = $p + $a + $l;
        @endphp
        <div class="card">
            <h3>Is mahine ki attendance ({{ now()->format('F Y') }})</h3>
            @if ($tot > 0)
                <p>Present: {{ $p }} | Absent: {{ $a }} | Leave: {{ $l }} | Fi sad: {{ round((($p + $l) / $tot) * 100) }}%</p>
            @else
                <p class="sd">هن دوري لاءِ ڪو رڪارڊ نه مليو.</p>
            @endif
        </div>

        <div class="card">
            <h3>Taaza notices</h3>
            @forelse ($notices as $n)
                <p><b>{{ substr($n->date, 0, 10) }}</b> &mdash; {{ $n->text }}</p>
            @empty
                <p>Koi notice nahi.</p>
            @endforelse
        </div>

        <div class="card">
            <h3>Aane wale events</h3>
            @forelse ($events as $e)
                <p><b>{{ substr($e->date, 0, 10) }}</b> &mdash; {{ $e->title }}</p>
            @empty
                <p>Koi event nahi.</p>
            @endforelse
        </div>
    @endif
@endsection
