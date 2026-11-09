@extends('layouts.portal')

@section('title', 'Teacher Dashboard')

@section('content')
    <div class="page-title"><h1>Teacher Dashboard</h1></div>
    <p class="sd sd-note">پنهنجي ڪلاس جي حاضري، Homework ۽ موڪل جون درخواستون هتان سنڀاليو.</p>
    <p class="who">Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>

    <div class="tiles">
        <a class="tile" href="{{ route('teacher.attendance.index', [], false) }}"><b>Attendance</b><span>Check In / Check Out (face se)</span></a>
        <a class="tile" href="{{ route('teacher.face.enroll', [], false) }}"><b>Chehra Enroll Karen</b><span>Apna chehra register karein</span></a>
        <a class="tile" href="{{ route('teacher.qr.scan', [], false) }}"><b>QR Attendance</b><span>Students ki attendance</span></a>
        <a class="tile" href="{{ route('teacher.absentees', [], false) }}"><b>Absentees</b><span>Ghair haazir students (WhatsApp)</span></a>
        <a class="tile" href="{{ route('teacher.homework.index', [], false) }}"><b>Homework</b><span>Daily diary / homework</span></a>
        <a class="tile" href="{{ route('teacher.leave.index', [], false) }}"><b>Chhutti ki darkhwast</b><span>Apni chhutti ke liye arzi</span></a>
    </div>
@endsection
