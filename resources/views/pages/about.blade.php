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
        @php
            $shift = trim((string) ($info->shifts ?? ''), " \t\n\r\"[]");
            $shift = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', fn ($m) => mb_convert_encoding(pack('H*', $m[1]), 'UTF-8', 'UTF-16BE'), $shift);
            $off = array_filter(array_map('trim', explode(',', (string) ($info->weekly_off_days ?? '0,6'))), 'strlen');
            $dn = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $summer = now()->month >= 4 && now()->month <= 10;
            $work = array_values(array_diff(range(0, 6), array_map('intval', $off)));
            $normal = array_values(array_filter($work, fn ($d) => $d !== 5));
            $normalLbl = count($normal) ? implode(', ', array_map(fn ($d) => $dn[$d], $normal)) : '';
        @endphp
        @if (!empty($info->name))<div class="row"><b>School Name:</b> {{ $info->name }}</div>@endif
        @if (!empty($info->urdu_name))<div class="row"><b>School Name (Sindhi):</b> <span class="sd">{{ $info->urdu_name }}</span></div>@endif
        @if (!empty($info->established_year))<div class="row"><b>Qayam:</b> {{ $info->established_year }}</div>@endif
        @if (!empty($info->semis_code))<div class="row"><b>SEMIS Code:</b> {{ $info->semis_code }}</div>@endif
        @if (!empty($info->total_classes))<div class="row"><b>Classes Offered:</b> {{ $info->total_classes }}</div>@endif
        @if ($shift !== '')<div class="row"><b>Shift:</b> {{ $shift }}</div>@endif
        @if (!empty($info->principal_name))<div class="row"><b>Head Master:</b> {{ $info->principal_name }}</div>@endif
        @php
            $fri = in_array(5, $work);
            $timing = $summer
                ? $normalLbl.': 8:00 AM – 1:30 PM'.($fri ? ', Friday: 8:00 AM – 12:00 PM' : '').' (1 April – 31 October)'
                : $normalLbl.': 8:30 AM – 2:00 PM'.($fri ? ', Friday: 8:30 AM – 12:30 PM' : '').' (1 November – 31 March)';
        @endphp
        <div class="row"><b>School ka waqt:</b> {{ $timing }}</div>
        @if (!empty($info->address))<div class="row"><b>Pata:</b> {{ $info->address }}</div>@endif
        @if (!empty($info->phone))<div class="row"><b>Phone:</b> {{ $info->phone }}</div>@endif
        @if (!empty($info->email))<div class="row"><b>Email:</b> {{ $info->email }}</div>@endif
        @if (!empty($info->map_location) && str_starts_with($info->map_location, 'http'))
            <div class="row"><a href="{{ $info->map_location }}" target="_blank" rel="noopener">Nakshe par dekhein &rarr;</a></div>
        @endif
    </div>

    @php
        $mapQ = trim((string) ($info->map_location ?? ''));
        if ($mapQ === '' && !empty($info->school_lat) && !empty($info->school_lng)) { $mapQ = $info->school_lat.','.$info->school_lng; }
    @endphp
    @if ($mapQ !== '' && !str_starts_with($mapQ, 'http'))
        <h2>Location</h2>
        <div class="card" style="padding:0;overflow:hidden">
            <iframe title="School location" src="https://www.google.com/maps?q={{ urlencode($mapQ) }}&output=embed" style="border:0;width:100%;height:260px" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    @endif

    @foreach ([['principal_name', 'principal_message', 'principal_photo_url', 'Head Master'], ['teo_name', 'teo_message', 'teo_photo_url', 'TEO'], ['deo_name', 'deo_message', 'deo_photo_url', 'DEO']] as [$n, $m, $p, $label])
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
