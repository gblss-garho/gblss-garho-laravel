@extends('layouts.app')

@section('title', ($info->name ?? 'GBLSS Garho'))

@section('content')
<style>
    :root { --g:#0F5132; --g2:#1B7A4D; --gold:#DBB43C; --bg:#F7F5EE; --ink:#2b2b2b; }
    * { box-sizing: border-box; }
    body { margin:0; background:var(--bg); color:var(--ink); font-family: Georgia, 'Times New Roman', serif; }
    .wrap { max-width: 980px; margin: 0 auto; padding: 0 16px; }
    .hero { background: linear-gradient(160deg, var(--g), #0a3a24); color:#fff; padding: 48px 0 40px; border-bottom: 5px solid var(--gold); text-align:center; }
    .hero h1 { margin:0 0 6px; font-size: 30px; }
    .hero .ur { font-size: 22px; opacity:.9; margin-bottom: 10px; }
    .hero .motto { color: var(--gold); font-style: italic; margin: 8px 0 22px; }
    .btn { display:inline-block; padding: 11px 20px; border-radius: 999px; text-decoration:none; margin: 4px; font-weight:bold; }
    .b1 { background: var(--gold); color:#222; } .b2 { border:1.5px solid var(--gold); color: var(--gold); }
    .stats { display:grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin: -24px 0 24px; }
    .stat { background:#fff; border-radius:14px; padding:18px 10px; text-align:center; box-shadow:0 2px 10px rgba(0,0,0,.08); border-top:4px solid var(--gold); }
    .stat b { display:block; font-size: 30px; color: var(--g); } .stat span { font-size: 13px; color:#666; }
    h2 { color: var(--g); margin: 28px 0 10px; font-size: 20px; }
    .card { background:#fff; border-radius:14px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,.06); margin-bottom: 12px; }
    .bar { display:flex; align-items:center; gap:8px; margin: 8px 0; } .bar i { display:block; height:12px; background: var(--g2); border-radius:6px; }
    .row { border-bottom:1px solid #eee; padding: 8px 0; } .row:last-child { border:0; } .row small { color:#777; }
    .photos { display:grid; grid-template-columns: repeat(3,1fr); gap:6px; } .photos img { width:100%; height:90px; object-fit:cover; border-radius:8px; }
    footer { background:#0a3a24; color:#ddd; text-align:center; padding: 22px 12px; margin-top: 34px; font-size: 14px; }
    @media (min-width: 700px) { .stats { grid-template-columns: repeat(4, 1fr); } .photos img { height:130px; } }
</style>

<div class="hero">
    <div class="wrap">
        <h1>{{ $info->name ?? 'GBLSS Garho' }}</h1>
        @if (!empty($info->urdu_name))<div class="ur">{{ $info->urdu_name }}</div>@endif
        @if (!empty($info->motto))<div class="motto">{{ $info->motto }}</div>@endif
        <a class="btn b1" href="{{ route('public.lookup', [], false) }}">Parents Corner</a>
        <a class="btn b2" href="{{ route('public.gallery', [], false) }}">Gallery</a>
        <a class="btn b2" href="{{ route('login', [], false) }}">Staff Login</a>
    </div>
</div>

<div class="wrap">
    <div class="stats">
        <div class="stat"><b>{{ $students }}</b><span>Students Enrolled</span></div>
        <div class="stat"><b>{{ $teachers }}</b><span>Teaching Staff</span></div>
        <div class="stat"><b>6 - 8</b><span>Classes</span></div>
        <div class="stat"><b>{{ $info->established_year ?? '—' }}</b><span>Established</span></div>
    </div>

    <h2>Class-wise Strength</h2>
    <div class="card">
        @php($max = max(1, $classCounts->max()))
        @foreach ($classCounts as $cls => $n)
            <div class="bar"><b style="width:60px">Class {{ $cls }}</b><i style="width: {{ round($n / $max * 60) }}%"></i><span>{{ $n }}</span></div>
        @endforeach
    </div>

    <h2>Taaza Notices</h2>
    <div class="card">
        @forelse ($notices as $n)
            <div class="row"><b>{{ str($n->text ?? '')->limit(140) }}</b><br><small>{{ substr($n->date ?? '', 0, 10) }}</small></div>
        @empty
            <div class="row">Abhi koi notice nahi.</div>
        @endforelse
    </div>

    <h2>Aane Wale Events</h2>
    <div class="card">
        @forelse ($events as $e)
            <div class="row"><b>{{ $e->title ?? ($e->name ?? '') }}</b><br><small>{{ substr($e->date ?? '', 0, 10) }}</small></div>
        @empty
            <div class="row">Abhi koi event nahi.</div>
        @endforelse
    </div>

    @if (count($photos))
        <h2>Gallery</h2>
        <div class="photos">
            @foreach ($photos as $p)
                <img src="{{ $p->url ?? ($p->image_url ?? ($p->photo_url ?? '')) }}" alt="{{ $p->caption ?? '' }}" loading="lazy">
            @endforeach
        </div>
        <p><a href="{{ route('public.gallery', [], false) }}">Poori gallery &rarr;</a> | <a href="{{ route('public.textbooks', [], false) }}">Textbooks</a> | <a href="{{ route('public.feedback', [], false) }}">Feedback</a></p>
    @endif
</div>

<footer>
    {{ $info->name ?? '' }}<br>
    {{ $info->address ?? '' }}<br>
    {{ $info->phone ?? '' }} {{ !empty($info->email) ? ' | ' . $info->email : '' }}
</footer>
@endsection
