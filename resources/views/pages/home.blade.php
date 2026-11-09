@extends('layouts.portal')

@section('title', ($info->name ?? 'GBLSS Garho'))

@section('content')
@if (count($notices))
    <div class="ticker" style="margin:0 -16px"><div>@foreach ($notices as $n)&#9733; {{ str($n->text ?? '')->limit(120) }} &nbsp;&nbsp;@endforeach</div></div>
@endif

<div class="hero-card"@if (!empty($info->school_photo_url)) style="background-image:linear-gradient(160deg,rgba(15,81,50,.88),rgba(10,58,36,.92)),url('{{ $info->school_photo_url }}');background-size:cover;background-position:center"@endif>
    <img src="{{ file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg') }}" alt="" style="width:96px;height:96px;border:0;background:transparent">
    <h2>{{ $info->name ?? 'GBLSS Garho' }}</h2>
    @if (!empty($info->urdu_name))<div class="ur">{{ $info->urdu_name }}</div>@endif
    @if (!empty($info->motto))<div class="motto">{{ $info->motto }}</div>@endif
    <a class="btn gold" href="{{ route('public.lookup', [], false) }}">Parents Corner</a>
    <a class="btn ghost" href="{{ route('public.gallery', [], false) }}">Gallery</a>
    <a class="btn ghost" href="{{ route('public.about', [], false) }}">About</a>
    <a class="btn ghost" href="{{ route('public.toppers', [], false) }}">Toppers</a>
</div>

<div class="stats">
    <div class="stat"><b>{{ $students }}</b><span>Students Enrolled</span></div>
    <div class="stat"><b>{{ $teachers }}</b><span>Teaching Staff</span></div>
    <div class="stat"><b>6 - 8</b><span>Classes</span></div>
    <div class="stat"><b>{{ $info->established_year ?? '—' }}</b><span>Established</span></div>
</div>
    <div class="highlights sd">
        <div>سنڌ ٽيڪسٽ بڪ بورڊ نصاب سان تجربيڪار استاد</div>
        <div>هر ٻار لاءِ محفوظ ۽ دوستيءَ ڀريو ماحول</div>
        <div>راند روند، فن ۽ اضافي سرگرميون</div>
        <div>سنڌ حڪومت جي تحت مفت تعليم</div>
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
@endsection
