@extends('layouts.portal')

@section('title', 'Teacher Dashboard')

@section('content')
    @php
        $me = auth()->user();
        $t = \Illuminate\Support\Facades\DB::table('teachers')->where('user_id', $me->id)->first();
        $tm = $t ? \App\Models\Teacher::find($t->id) : null;
        $classes = $tm ? implode(', ', $tm->assigned_classes_list) : '';
        $sub = collect([$t->designation ?? null, $t->subject ?? null, $classes ? 'Class '.$classes : null])->filter()->implode(' · ');
    @endphp
    <div class="page-title" data-n="00"><h1>Dashboard</h1></div>
    <div class="wel">
        <div class="l">
            <div class="av">@if (!empty($t->photo_url))<img src="{{ $t->photo_url }}" alt="">@else<div>{{ mb_substr($me->name, 0, 1) }}</div>@endif</div>
            <div><h2>{{ $t->name ?? $me->name }}</h2>@if ($sub)<div class="sub">{{ $sub }}</div>@endif</div>
        </div>
        <div class="r"><b>{{ now()->format('l') }}</b><span>{{ now()->format('M j, Y') }}</span></div>
    </div>
    <p class="sd sd-note">پنهنجي ڪلاس جي حاضري، Homework ۽ موڪل جون درخواستون هتان سنڀاليو.</p>

    <div class="pane">
        <div class="pane-h"><span class="ico">⏱️</span><div><b>Time Clock</b><small>Daily attendance &middot; face verified</small></div></div>
        <div class="pane-b">
            <div class="tiles">
                <a class="tile" href="{{ route('teacher.attendance.index', [], false) }}"><b>Attendance</b><span>Check In / Check Out (face se)</span></a>
                <a class="tile" href="{{ route('teacher.face.enroll', [], false) }}"><b>Chehra Enroll Karen</b><span>Apna chehra register karein</span></a>
                <a class="tile" href="{{ route('teacher.qr.scan', [], false) }}"><b>QR Attendance</b><span>Students ki attendance</span></a>
            </div>
        </div>
    </div>
    <div class="pane">
        <div class="pane-h"><span class="ico">📋</span><div><b>Class Work</b><small>Absentees &middot; homework &middot; leave</small></div></div>
        <div class="pane-b">
            <div class="tiles">
                <a class="tile" href="{{ route('teacher.absentees', [], false) }}"><b>Absentees</b><span>Ghair haazir students (WhatsApp)</span></a>
                <a class="tile" href="{{ route('teacher.homework.index', [], false) }}"><b>Homework</b><span>Daily diary / homework</span></a>
                <a class="tile" href="{{ route('teacher.leave.index', [], false) }}"><b>Chhutti ki darkhwast</b><span>Apni chhutti ke liye arzi</span></a>
            </div>
        </div>
    </div>
@endsection
