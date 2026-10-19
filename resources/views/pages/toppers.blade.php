@extends('layouts.app')

@section('title', 'Toppers')

@section('content')
@include('pages._nav')
<div class="wrap">
    <h1>Toppers</h1>
    <p>Annual Exam &mdash; Session {{ $year }}</p>
    @foreach ($byClass as $cls => $list)
        <h2>Class {{ $cls }}</h2>
        @forelse ($list as $i => $t)
            <div class="card flex">
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
            <div class="card">Is class ka annual result abhi publish nahi hua.</div>
        @endforelse
    @endforeach
</div>
<footer>{{ $info->name ?? '' }}</footer>
@endsection
