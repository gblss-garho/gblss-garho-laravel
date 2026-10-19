@extends('layouts.app')

@section('title', 'Our Staff')

@section('content')
@include('pages._nav')
<div class="wrap">
    <h1>Hamara Staff ({{ count($teachers) }})</h1>
    <div class="grid">
        @foreach ($teachers as $t)
            <div class="card flex">
                @if (!empty($t->photo_url))
                    <img class="pic" src="{{ $t->photo_url }}" alt="{{ $t->name }}" loading="lazy">
                @else
                    <div class="pic">{{ mb_substr($t->name, 0, 1) }}</div>
                @endif
                <div>
                    <b>{{ $t->name }}</b><br>
                    @if (!empty($t->designation)){{ $t->designation }}<br>@endif
                    @if (!empty($t->subject))<small>Subject: {{ $t->subject }}</small><br>@endif
                    @if (!empty($t->qualification))<small>{{ $t->qualification }}</small><br>@endif
                    @if (!empty($t->intro_line))<small><i>{{ $t->intro_line }}</i></small>@endif
                </div>
            </div>
        @endforeach
    </div>
</div>
<footer>{{ $info->name ?? '' }}</footer>
@endsection
