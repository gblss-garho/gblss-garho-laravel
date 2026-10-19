@extends('layouts.app')

@section('title', 'About the School')

@section('content')
@include('pages._nav')
<div class="wrap">
    <h1>{{ $info->name ?? 'GBLSS Garho' }}</h1>
    @if (!empty($info->urdu_name))<div style="font-size:20px">{{ $info->urdu_name }}</div>@endif
    @if (!empty($info->motto))<p style="color:#8a6d00;font-style:italic">{{ $info->motto }}</p>@endif

    @if (!empty($info->about_text))
        <div class="card">{!! nl2br(e($info->about_text)) !!}</div>
    @endif

    <h2>School ki maloomat</h2>
    <div class="card">
        @if (!empty($info->established_year))<div class="row"><b>Qayam:</b> {{ $info->established_year }}</div>@endif
        @if (!empty($info->semis_code))<div class="row"><b>SEMIS Code:</b> {{ $info->semis_code }}</div>@endif
        @if (!empty($info->shifts))<div class="row"><b>Shifts:</b> {{ is_string($d = json_decode($info->shifts)) ? $d : $info->shifts }}</div>@endif
        @if (!empty($info->address))<div class="row"><b>Pata:</b> {{ $info->address }}</div>@endif
        @if (!empty($info->phone))<div class="row"><b>Phone:</b> {{ $info->phone }}</div>@endif
        @if (!empty($info->email))<div class="row"><b>Email:</b> {{ $info->email }}</div>@endif
        @if (!empty($info->map_location) && str_starts_with($info->map_location, 'http'))
            <div class="row"><a href="{{ $info->map_location }}" target="_blank" rel="noopener">Nakshe par dekhein &rarr;</a></div>
        @endif
    </div>

    @foreach ([['deo_name', 'deo_message', 'deo_photo_url', 'DEO'], ['teo_name', 'teo_message', 'teo_photo_url', 'TEO']] as [$n, $m, $p, $label])
        @if (!empty($info->$m))
            <h2>{{ $label }} ka paigham</h2>
            <div class="card">
                <div class="flex">
                    @if (!empty($info->$p))<img class="pic" src="{{ $info->$p }}" alt="">@endif
                    <b>{{ $info->$n ?? $label }}</b>
                </div>
                <p>{!! nl2br(e($info->$m)) !!}</p>
            </div>
        @endif
    @endforeach
</div>
<footer>{{ $info->name ?? '' }}<br>{{ $info->address ?? '' }}</footer>
@endsection
