@extends('layouts.portal')

@section('title', $t->name.' — Staff ID')

@section('content')
    <div class="card" style="max-width:560px;margin:18px auto;text-align:center">
        <div style="font-size:12px;letter-spacing:1px;color:#8B8371">VERIFIED STAFF · BIODATA</div>
        <h2 style="margin:6px 0 2px;color:#0F5132">{{ $school['name'] }}</h2>
        <div style="font-size:13px;color:#555">{{ $school['address'] }}
            @if (!empty($school['phone']))<br>Tel: {{ $school['phone'] }}@endif
            <br>SEMIS: {{ $school['code'] }}</div>
        <hr style="border:0;border-top:1px solid #E3DCC7;margin:14px 0">
        @if (!empty($t->photo_url))
            <img src="{{ $t->photo_url }}" alt="{{ $t->name }}" style="width:120px;height:120px;object-fit:cover;object-position:50% 15%;border-radius:12px;border:3px solid #DBB43C">
        @else
            <div style="width:120px;height:120px;margin:0 auto;border-radius:12px;border:3px solid #DBB43C;background:#EDE7D3;display:flex;align-items:center;justify-content:center;font-size:48px;color:#123B7A">{{ mb_strtoupper(mb_substr($t->name, 0, 1)) }}</div>
        @endif
        <h1 style="font-size:24px;margin:10px 0 2px">{{ $t->name }}</h1>
        @if (!empty($t->designation))<div style="color:#0F5132;font-weight:600">{{ $t->designation }}</div>@endif
        <table style="margin:12px auto 0;text-align:left;font-size:14px">
            @if (!empty($t->subject))<tr><td><b>Subject</b></td><td>{{ $t->subject }}</td></tr>@endif
            @if (!empty($t->qualification))<tr><td><b>Qualification</b></td><td>{{ $t->qualification }}</td></tr>@endif
            @if (!empty($t->experience))<tr><td><b>Experience</b></td><td>{{ $t->experience }}</td></tr>@endif
            @if (!empty($t->entry_in_service))<tr><td><b>In service since</b></td><td>{{ \Carbon\Carbon::parse($t->entry_in_service)->format('M d, Y') }}</td></tr>@endif
            <tr><td><b>Staff ID</b></td><td>{{ \App\Support\CardData::mask($t->pid) }}</td></tr>
        </table>
        @if (!empty($t->intro_line))<p style="font-style:italic;color:#555;margin-top:12px">{{ $t->intro_line }}</p>@endif
    </div>
@endsection
