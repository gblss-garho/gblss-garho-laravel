@extends('layouts.portal')

@section('title', 'Toppers')

@section('content')
    <div class="page-title"><h1>Toppers</h1></div>
    <p class="sd sd-note">هر ڪلاس جا اعلي نمبر وٺندڙ شاگرد.</p>
    <p>Annual Exam &mdash; Session {{ $year }}</p>
    @foreach ($byClass as $cls => $list)
        <h2>Class {{ $cls }}</h2>
        @forelse ($list as $i => $t)
            <div class="tp">
                @if (!empty($t->photo_url))
                    <img class="pic" src="{{ $t->photo_url }}" alt="{{ $t->name }}" loading="lazy">
                @else
                    <div class="pic">{{ mb_substr($t->name, 0, 1) }}</div>
                @endif
                <div>
                    <b>#{{ $i + 1 }} &nbsp;{{ $t->name }}</b><br>
                    <small>Kul numbers: {{ $t->total }} ({{ $t->subjects }} subjects)</small>
                </div>
            </div>
        @empty
            <div class="lk-card">Is class ka annual result abhi publish nahi hua.</div>
        @endforelse
    @endforeach
@endsection
