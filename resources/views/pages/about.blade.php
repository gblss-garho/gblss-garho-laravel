@extends('layouts.portal')

@section('title', 'About the School')

@section('content')
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
        @php($summer = now()->month >= 4 && now()->month <= 10)
        <div class="row"><b>School ka waqt:</b>
            {{ $summer ? 'Monday–Thursday & Saturday: 8:00 AM – 1:30 PM, Friday: 8:00 AM – 12:00 PM (1 April – 31 October)' : 'Monday–Thursday & Saturday: 8:30 AM – 2:00 PM, Friday: 8:30 AM – 12:30 PM (1 November – 31 March)' }}
        </div>
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
@endsection
