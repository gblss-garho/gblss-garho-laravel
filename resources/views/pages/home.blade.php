@extends('layouts.portal')

@section('title', ($info->name ?? 'GBLSS Garho'))

@section('content')
<style>
    .hm-hero { position:relative; overflow:hidden; border-radius:24px; margin: 0 0 40px; min-height:440px; display:flex; flex-direction:column; justify-content:center; color:#F8FAF8; box-shadow:0 22px 55px rgba(6,54,50,.38); background: radial-gradient(ellipse at 25% 15%, #0B7A5B 0%, #075C42 55%, #032E24 100%); }
    .hm-hero.photo { background-size:cover; background-position:center; }
    .hm-hero .in { position:relative; text-align:center; padding:48px 20px; }
    .hm-pill { display:inline-flex; align-items:center; gap:8px; padding:6px 16px; border-radius:999px; background:rgba(255,255,255,.08); border:1px solid #D9A91A88; font-family:'JetBrains Mono',ui-monospace,monospace; font-size:11px; font-weight:700; letter-spacing:1.5px; color:#D9A91A; backdrop-filter:blur(6px); }
    .hm-pill:before { content:""; width:6px; height:6px; border-radius:99px; background:#D9A91A; box-shadow:0 0 8px #D9A91A; }
    .hm-hero h1 { font-family:'Baloo 2','Noto Serif',serif; font-weight:800; font-size:clamp(30px,6vw,52px); line-height:1.12; text-shadow:0 4px 20px rgba(0,0,0,.35); max-width:760px; margin:18px auto 0; color:#fff; }
    .hm-tag { max-width:520px; margin:16px auto 0; font-family:'Baloo 2',serif; font-weight:600; font-size:16px; background:linear-gradient(90deg,#D9A91A,#3AA982,#D9A91A); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
    .hm-motto { max-width:460px; margin:12px auto 0; color:#F8FAF8CC; font-size:15px; }
    .hm-btns { display:flex; flex-wrap:wrap; justify-content:center; gap:12px; margin-top:30px; }
    .hm-btns a { padding:10px 24px; border-radius:999px; font-size:14px; font-weight:700; text-decoration:none; transition:transform .15s; }
    .hm-btns a:hover { transform: translateY(-2px); }
    .hm-b1 { background:linear-gradient(135deg,#D9A91A,#B98D08); color:#F8FAF8; box-shadow:0 6px 18px rgba(185,141,8,.42); }
    .hm-b2 { background:rgba(255,255,255,.08); color:#F8FAF8; border:1px solid rgba(255,249,242,.35); backdrop-filter:blur(6px); }
    .hm-b3 { background:rgba(217,169,26,.18); color:#D9A91A; border:1px solid #D9A91A66; }
    .hm-strip { height:5px; background:linear-gradient(90deg,#D9A91A,#0B7A5B,#D9A91A); position:relative; }
    .hm-stats { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; margin-bottom:40px; }
    @media (min-width:700px){ .hm-stats { grid-template-columns:repeat(4,1fr); } }
    .hm-stat { position:relative; overflow:hidden; background:#fff; border:1px solid #E4EBE7; border-radius:16px; padding:20px 10px; text-align:center; box-shadow:0 4px 16px rgba(11,79,74,.08); }
    .hm-stat:before { content:""; position:absolute; top:0; left:0; right:0; height:3px; background:var(--c); }
    .hm-stat .ic { font-size:20px; }
    .hm-stat b { display:block; font-family:'Baloo 2',serif; font-weight:800; font-size:28px; color:#075C42; }
    .hm-stat span { font-size:12px; color:#66736D; font-family:'JetBrains Mono',ui-monospace,monospace; }
    .hm-head { text-align:center; margin: 0 0 22px; }
    .hm-head small { display:block; font-family:'JetBrains Mono',ui-monospace,monospace; font-size:11px; letter-spacing:2px; color:#B98D08; font-weight:700; }
    .hm-head h2 { font-family:'Baloo 2',serif; font-weight:700; font-size:24px; color:#075C42; margin:4px 0 0; }
    .hm-sec { margin-bottom:40px; }
    .hm-two { display:grid; grid-template-columns:1fr; gap:20px; } @media (min-width:800px){ .hm-two { grid-template-columns:1fr 1fr; } }
    .hm-green { border-radius:16px; padding:24px; background:linear-gradient(160deg,#0B7A5B,#063F31); color:#F8FAF8; box-shadow:0 10px 28px rgba(6,54,50,.28); }
    .hm-green h3 { color:#fff; margin:0; font-family:'Baloo 2',serif; font-size:16px; }
    .hm-green .tot { padding:3px 12px; border-radius:999px; font-size:12px; font-weight:700; background:rgba(217,169,26,.2); color:#D9A91A; border:1px solid #D9A91A66; }
    .hm-bar { display:flex; align-items:center; gap:10px; margin:14px 0; font-size:13px; }
    .hm-bar b { width:58px; color:#9be0c6; font-weight:600; }
    .hm-bar i { display:block; height:14px; border-radius:7px; background:linear-gradient(90deg,#D9A91A,#3AA982); }
    .hm-bar span { color:#D9A91A; font-weight:700; }
    .hm-white { border-radius:16px; padding:24px; background:#fff; border:1px solid #E4EBE7; box-shadow:0 6px 18px rgba(11,79,74,.07); }
    .hm-white h3 { margin:0 0 6px; font-family:'Baloo 2',serif; font-size:16px; }
    .hm-feat { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; } @media (min-width:800px){ .hm-feat { grid-template-columns:repeat(4,1fr); } }
    .hm-fc { position:relative; overflow:hidden; background:#fff; border:1px solid #E4EBE7; border-radius:18px; padding:20px; box-shadow:0 6px 18px rgba(11,79,74,.07); }
    .hm-fc:before { content:""; position:absolute; top:0; left:0; right:0; height:4px; background:var(--c); }
    .hm-fc .ico { width:48px; height:48px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:14px; background:var(--t); }
    .hm-fc b { display:block; font-family:'Baloo 2',serif; font-size:15px; color:#075C42; margin-bottom:4px; }
    .hm-fc div.sd { font-size:13px; color:#17231F; line-height:1.7; }
    .hm-label { font-family:'JetBrains Mono',ui-monospace,monospace; font-size:11px; font-weight:700; letter-spacing:2px; color:#D9A91A; }
    .hm-mod { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-top:18px; } @media (min-width:800px){ .hm-mod { grid-template-columns:repeat(4,1fr); } }
    .hm-m { display:flex; align-items:center; gap:12px; padding:14px; background:#fff; border:1px solid #E4EBE7; border-radius:18px; box-shadow:0 10px 30px rgba(6,63,49,.07); text-decoration:none; color:#075C42; font-size:14px; font-weight:600; transition:transform .2s, box-shadow .2s; }
    .hm-m:hover { transform:translateY(-3px); box-shadow:0 14px 30px rgba(6,63,49,.1); }
    .hm-m i { font-style:normal; width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; background:#EAF3EF; flex:none; }
    .hm-m i.g { background:rgba(217,169,26,.12); }
    .hm-dark { border-radius:24px; padding:28px; background:linear-gradient(145deg,#063F31,#075C42); color:#fff; box-shadow:0 18px 42px rgba(6,63,49,.18); }
    .hm-dark h2, .hm-soft h2 { margin:8px 0 0; font-size:25px; font-weight:800; line-height:1.2; }
    .hm-dark h2 { color:#fff; } .hm-soft h2 { color:#075C42; }
    .hm-dark p { color:#C8DAD3; font-size:14px; } .hm-soft p { color:#66736D; font-size:14px; }
    .hm-chip { display:inline-block; padding:8px 12px; border-radius:12px; font-size:12px; font-weight:600; margin:0 6px 8px 0; }
    .hm-dark .hm-chip { background:rgba(255,255,255,.08); color:#E7F0EC; } .hm-soft .hm-chip { background:#EAF3EF; color:#075C42; }
    .hm-soft { border-radius:24px; padding:28px; background:#fff; border:1px solid #E4EBE7; box-shadow:0 10px 30px rgba(6,63,49,.07); }
    .hm-soft a.go { display:inline-block; margin-top:14px; padding:12px 20px; border-radius:12px; background:#D9A91A; color:#032E24; font-weight:700; font-size:14px; text-decoration:none; }
    .hm-team { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; } @media (min-width:800px){ .hm-team { grid-template-columns:repeat(3,1fr); } }
    .hm-t { position:relative; overflow:hidden; text-align:center; background:linear-gradient(#fff,#F8FAF8); border:1px solid #E4EBE7; border-radius:22px; padding:22px 12px 18px; box-shadow:0 6px 18px rgba(11,79,74,.07); }
    .hm-t:before { content:""; position:absolute; top:0; left:0; right:0; height:5px; background:linear-gradient(90deg,#C9A227,#0B7A5B,#C9A227); }
    .hm-t .av { width:76px; height:76px; margin:4px auto 10px; border-radius:50%; padding:3px; background:linear-gradient(135deg,#C9A227,#0F5132); }
    .hm-t .av img, .hm-t .av .ph { width:100%; height:100%; border-radius:50%; object-fit:cover; background:#fff; display:flex; align-items:center; justify-content:center; font-size:26px; color:#075C42; }
    .hm-t b { display:block; font-family:'Noto Serif',serif; font-size:15px; color:#075C42; }
    .hm-t .sub { display:inline-block; margin:6px 0; padding:3px 14px; border-radius:999px; background:#FDF1E1; color:#B98D08; font-size:13px; font-weight:600; }
    .hm-t p { font-style:italic; font-size:13px; color:#1F2A24; margin:6px 0 10px; }
    .hm-t a { display:inline-block; padding:6px 18px; border-radius:999px; border:1px solid #0B7A5B44; background:#E4F3F1; color:#0B7A5B; font-weight:700; font-size:13px; text-decoration:none; }
    .hm-all { display:inline-block; padding:11px 22px; border-radius:999px; background:linear-gradient(135deg,#D9A91A,#C9A227); color:#032E24; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(201,162,39,.35); }
    .hm-lead { background:#fff; border:1px solid #E4EBE7; border-top:4px solid #0B7A5B; border-radius:18px; padding:22px; box-shadow:0 6px 18px rgba(11,79,74,.07); }
    .hm-tabs { display:flex; gap:8px; margin:10px 0 18px; flex-wrap:wrap; }
    .hm-tabs button { margin:0; padding:7px 18px; border-radius:999px; background:#F1F4F2; color:#66736D; font-weight:600; font-size:14px; border:1px solid #E4EBE7; }
    .hm-tabs button.on { background:#0B7A5B; color:#fff; border-color:#0B7A5B; }
    .hm-tabs button:hover { background:#E4F3F1; }
    .hm-tabs button.on:hover { background:#0B7A5B; }
    .hm-lp { display:none; gap:16px; align-items:flex-start; } .hm-lp.on { display:flex; }
    .hm-lp .ph { width:70px; height:70px; flex:none; border-radius:50%; overflow:hidden; border:3px solid #fff; box-shadow:0 0 0 2px #0B7A5B; background:#E4F3F1; display:flex; align-items:center; justify-content:center; font-size:26px; }
    .hm-lp .ph img { width:100%; height:100%; object-fit:cover; }
    .hm-lp q { display:block; font-family:'Noto Serif',serif; font-style:italic; font-size:16px; line-height:1.7; color:#17231F; quotes:"\201C" "\201D"; }
    .hm-lp .who { color:#075C42; font-weight:700; margin-top:10px; font-size:15px; }
    .hm-lp details summary { cursor:pointer; color:#0B7A5B; font-weight:700; font-size:13px; margin-top:6px; }
</style>

@if (count($notices))
    <div class="notice-bar"><span class="lbl">&#128226; NOTICE</span><div class="run"><div>@foreach ($notices as $n)&#8505;&#65039; {{ str($n->text ?? '')->limit(160) }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;@endforeach</div></div></div>
@endif

<div class="hm-hero{{ !empty($info->school_photo_url) ? ' photo' : '' }}"@if (!empty($info->school_photo_url)) style="background-image:linear-gradient(180deg,rgba(6,54,50,.5) 0%,rgba(6,54,50,.68) 45%,rgba(4,36,32,.95) 100%),url('{{ $info->school_photo_url }}')"@endif>
    <div class="in">
        <span class="hm-pill">GOVERNMENT SCHOOL &middot; GARHO</span>
        <h1>{{ $info->name ?? 'GBLSS Garho' }}</h1>
        <p class="hm-tag">Building Knowledge. Shaping Character. Creating Futures.</p>
        @if (!empty($info->urdu_name))<div class="sd" style="margin-top:10px;font-size:20px">{{ $info->urdu_name }}</div>@endif
        @if (!empty($info->motto))<p class="hm-motto sd">{{ $info->motto }}</p>@endif
        <div class="hm-btns">
            <a class="hm-b1" href="{{ route('public.lookup', [], false) }}">Parent Portal &rarr;</a>
            <a class="hm-b2" href="{{ route('public.about', [], false) }}">About the School</a>
            <a class="hm-b3" href="{{ route('public.toppers', [], false) }}">Toppers &rarr;</a>
        </div>
    </div>
    <div class="hm-strip"></div>
</div>

<div class="hm-stats">
    <div class="hm-stat" style="--c:#D9A91A"><div class="ic">🎓</div><b>{{ $students }}</b><span>Students Enrolled</span></div>
    <div class="hm-stat" style="--c:#0B7A5B"><div class="ic">🧑‍🏫</div><b>{{ $teachers }}</b><span>Teaching Staff</span></div>
    <div class="hm-stat" style="--c:#D9A91A"><div class="ic">🏫</div><b>{{ !empty($info->total_classes) ? $info->total_classes : '6 - 8' }}</b><span>Classes</span></div>
    <div class="hm-stat" style="--c:#075C42"><div class="ic">🏛️</div><b>{{ $info->established_year ?? '—' }}</b><span>Established</span></div>
</div>

<div class="hm-sec">
    <div class="hm-head"><small>SNAPSHOT</small><h2>Student Strength &amp; Toppers</h2></div>
    <div class="hm-two">
        <div class="hm-green">
            <div style="display:flex;justify-content:space-between;align-items:center"><h3>Class-Wise Strength</h3><span class="tot">{{ $classCounts->sum() }} Total</span></div>
            @php $max = max(1, $classCounts->max()); @endphp
            @foreach ($classCounts as $cls => $n)
                <div class="hm-bar"><b>Class {{ $cls }}</b><i style="width: {{ max(4, round($n / $max * 62)) }}%"></i><span>{{ $n }}</span></div>
            @endforeach
        </div>
        <div class="hm-white">
            <h3>Class Toppers 🏆</h3>
            @if (!empty($info->current_exam_title) || !empty($info->current_exam_year))
                <div style="font-family:'JetBrains Mono',monospace;font-size:12px;color:#B98D08;margin-bottom:12px">{{ $info->current_exam_title ?: 'Annual Examination' }} {{ $info->current_exam_year }} Result</div>
            @endif
            <p class="sd" style="color:#66736D">ڪلاس وار ٽاپرز ڏسڻ لاءِ هيٺ ڏنل بٽڻ دٻايو.</p>
            <a class="hm-all" href="{{ route('public.toppers', [], false) }}">View Toppers &rarr;</a>
        </div>
    </div>
</div>

<div class="hm-sec">
    <div class="hm-head"><small>WHY CHOOSE US</small><h2 class="sd">هر ٻار لاءِ بهترين ماحول</h2></div>
    <div class="hm-feat">
        <div class="hm-fc" style="--c:#D9A91A;--t:#FCEAE1"><div class="ico">📚</div><b>Quality Education</b><div class="sd">سنڌ ٽيڪسٽ بڪ بورڊ نصاب سان تجربيڪار استاد</div></div>
        <div class="hm-fc" style="--c:#0B7A5B;--t:#E4F3F1"><div class="ico">🛡️</div><b>Safe Environment</b><div class="sd">هر ٻار لاءِ محفوظ ۽ دوستيءَ ڀريو ماحول</div></div>
        <div class="hm-fc" style="--c:#D9A91A;--t:#FDF1E1"><div class="ico">🏆</div><b>Co-Curricular Activities</b><div class="sd">راند روند، فن ۽ اضافي سرگرميون</div></div>
        <div class="hm-fc" style="--c:#075C42;--t:#E7EEEC"><div class="ico">🎓</div><b>Free Government Education</b><div class="sd">سنڌ حڪومت جي تحت مفت تعليم</div></div>
    </div>
</div>

<div class="hm-sec">
    <div class="hm-label">DIGITAL SCHOOL</div>
    <h2 style="font-family:'Baloo 2',serif;font-size:28px;font-weight:800;color:#075C42;margin:4px 0 0">A School Powered by Technology</h2>
    <p style="color:#66736D;font-size:14px;max-width:650px">A complete digital school experience — connecting teachers, students and parents through practical tools.</p>
    <div class="hm-mod">
        <a class="hm-m" href="{{ route('login', [], false) }}"><i class="g">✓</i>Smart Attendance</a>
        <a class="hm-m" href="{{ route('login', [], false) }}"><i>👥</i>Student Register</a>
        <a class="hm-m" href="{{ route('login', [], false) }}"><i>📚</i>E-Homework</a>
        <a class="hm-m" href="{{ route('login', [], false) }}"><i>📖</i>Book Bank</a>
        <a class="hm-m" href="{{ route('public.toppers', [], false) }}"><i>🏆</i>Results</a>
        <a class="hm-m" href="{{ route('public.lookup', [], false) }}"><i>🪪</i>ID Cards</a>
        <a class="hm-m" href="{{ route('public.lookup', [], false) }}"><i>🎫</i>Admit Cards</a>
        <a class="hm-m" href="{{ route('public.lookup', [], false) }}"><i class="g">📱</i>Parent Portal</a>
    </div>
</div>

<div class="hm-two hm-sec">
    <div class="hm-dark">
        <span class="hm-label">SMART ATTENDANCE</span>
        <h2>Attendance, made effortless.</h2>
        <p>QR ID Scan · Face Time In/Out · Leave Requests</p>
        <div style="margin-top:18px">
            <span class="hm-chip">Face Check-In</span><span class="hm-chip">QR Scan</span><span class="hm-chip">Absentees</span><span class="hm-chip">Leave</span>
        </div>
    </div>
    <div class="hm-soft">
        <span class="hm-label">PARENT PORTAL</span>
        <h2>Parents Are Always Connected</h2>
        <p>Attendance, today's homework, academic progress and leave applications in one place.</p>
        <div style="margin-top:14px">
            <span class="hm-chip">Attendance</span><span class="hm-chip">Today's Homework</span><span class="hm-chip">Academic Progress</span><span class="hm-chip">Results</span>
        </div>
        <a class="go" href="{{ route('public.lookup', [], false) }}">Open Parent Portal &rarr;</a>
    </div>
</div>

@if (count($team))
<div class="hm-sec">
    <div class="hm-head"><small>MEET THE TEAM</small><h2 class="sd">اسان جا استاد</h2></div>
    <div class="hm-team">
        @foreach ($team as $t)
            <div class="hm-t">
                <div class="av">@if (!empty($t->photo_url))<img src="{{ $t->photo_url }}" alt="{{ $t->name }}" loading="lazy">@else<div class="ph">{{ mb_substr($t->name, 0, 1) }}</div>@endif</div>
                <b>{{ $t->name }}</b>
                @if (!empty($t->subject))<span class="sub">{{ $t->subject }}</span>@endif
                @if (!empty($t->intro_line))<p>&ldquo;{{ $t->intro_line }}&rdquo;</p>@endif
                <a href="{{ route('public.staff', [], false) }}">View Details &rarr;</a>
            </div>
        @endforeach
    </div>
    @if ($teachers > 6)<div style="text-align:center;margin-top:22px"><a class="hm-all" href="{{ route('public.staff', [], false) }}">View All Teachers</a></div>@endif
</div>
@endif

@php
    $leaders = [];
    foreach ([['principal', 'Head Master', 'Head Master'], ['teo', 'TEO', 'Taluka Education Officer'], ['deo', 'DEO', 'District Education Officer']] as [$k, $tab, $full]) {
        $m = $info->{$k.'_message'} ?? null;
        if (!empty($m)) { $leaders[] = ['tab' => $tab, 'name' => $info->{$k.'_name'} ?? '', 'photo' => $info->{$k.'_photo_url'} ?? '', 'msg' => $m]; }
    }
@endphp
@if (count($leaders))
<div class="hm-sec">
    <div class="hm-lead">
        <div class="hm-label" style="color:#B98D08">MESSAGES FROM LEADERSHIP</div>
        <div class="hm-tabs">
            @foreach ($leaders as $i => $l)<button type="button" data-lt="{{ $i }}" class="{{ $i === 0 ? 'on' : '' }}">{{ $l['tab'] }}</button>@endforeach
        </div>
        @foreach ($leaders as $i => $l)
            <div class="hm-lp{{ $i === 0 ? ' on' : '' }}" data-lp="{{ $i }}">
                <div class="ph">@if ($l['photo'])<img src="{{ $l['photo'] }}" alt="">@else 👤 @endif</div>
                <div>
                    @php $short = \Illuminate\Support\Str::limit($l['msg'], 170); @endphp
                    @if (mb_strlen($l['msg']) > 170)
                        <details><summary style="list-style:none"><q>{{ $short }}</q><span style="color:#0B7A5B;font-weight:700;font-size:13px">Read more &#9660;</span></summary><q>{{ $l['msg'] }}</q></details>
                    @else
                        <q>{{ $l['msg'] }}</q>
                    @endif
                    @if ($l['name'])<div class="who">&mdash; {{ $l['name'] }}</div>@endif
                </div>
            </div>
        @endforeach
    </div>
</div>
<script>
document.querySelectorAll('[data-lt]').forEach(function (b) {
    b.addEventListener('click', function () {
        var i = b.getAttribute('data-lt');
        document.querySelectorAll('[data-lt]').forEach(function (x) { x.classList.toggle('on', x === b); });
        document.querySelectorAll('[data-lp]').forEach(function (p) { p.classList.toggle('on', p.getAttribute('data-lp') === i); });
    });
});
</script>
@endif

@if (count($events))
    <h2>Aane Wale Events</h2>
    <div class="card">
        @foreach ($events as $e)
            <div class="row"><b>{{ $e->title ?? ($e->name ?? '') }}</b><br><small>{{ substr($e->date ?? '', 0, 10) }}</small></div>
        @endforeach
    </div>
@endif

@if (count($photos))
    <h2>Gallery</h2>
    <div class="photos">
        @foreach ($photos as $p)
            <img src="{{ $p->url ?? ($p->image_url ?? ($p->photo_url ?? '')) }}" alt="{{ $p->caption ?? '' }}" loading="lazy">
        @endforeach
    </div>
    <p><a href="{{ route('public.gallery', [], false) }}">Poori gallery &rarr;</a> | <a href="{{ route('public.textbooks', [], false) }}">Textbooks</a> | <a href="{{ route('public.feedback', [], false) }}">Feedback</a></p>
@endif
@endsection
