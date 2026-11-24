@extends('layouts.portal')

@section('title', ucfirst('admin') . ' Dashboard')

@section('content')
    <div class="page-title"><h1>Admin Dashboard</h1></div>
    <p class="sd sd-note">اسڪول جو رڪارڊ، نتيجا، ڪارڊ ۽ سيٽنگون هتان سنڀاليو.</p>
    <p class="who">Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>

    @if (! empty($att))
        <link rel="stylesheet" href="{{ asset('css/attendance-overview.css') }}">
        <section class="att-card">
            <h2><span>Aaj ki Hazri &middot; {{ $att['date']->format('D, j M') }}</span><a href="{{ route('admin.attendance-overview', [], false) }}">Poora record</a></h2>
            <div class="att-grid">
                <div class="att-box">
                    <h3>Students ({{ $att['students']['classes_marked'] }}/{{ $att['students']['classes_total'] }} class ki hazri lagi)</h3>
                    <div class="att-row">
                        <span class="g"><b>{{ $att['students']['present'] }}</b> present</span>
                        <span class="r"><b>{{ $att['students']['absent'] }}</b> absent</span>
                        <span class="v"><b>{{ $att['students']['leave'] }}</b> leave</span>
                    </div>
                </div>
                <div class="att-box">
                    <h3>Staff ({{ $att['staff']['total'] }})</h3>
                    @if ($att['school_day'])
                        <div class="att-row">
                            <span class="g"><b>{{ $att['staff']['present'] }}</b> present</span>
                            <span class="t"><b>{{ $att['staff']['late'] }}</b> late</span>
                            <span class="v"><b>{{ $att['staff']['leave'] }}</b> leave</span>
                            <span class="r"><b>{{ $att['staff']['absent'] }}</b> absent</span>
                        </div>
                    @else
                        <div class="att-row">Aaj school ki chhutti hai.</div>
                    @endif
                </div>
            </div>
            <p class="att-note">Staff ka "absent" din ke aakhri hisse (check-out ka waqt) se pehle nahi ginta.</p>
        </section>
    @endif

    <div class="tiles">
        <a class="tile" href="{{ route('admin.attendance-overview', [], false) }}"><b>Attendance Overview</b><span>Staff + students, koi bhi hafta ya mahina</span></a>
        <a class="tile" href="{{ route('admin.students.index', [], false) }}"><b>Students</b><span>Register, edit, add</span></a>
        <a class="tile" href="{{ route('admin.daily-attendance', [], false) }}"><b>Daily Attendance</b><span>Present / Absent / Leave hath se</span></a>
        <a class="tile" href="{{ route('admin.class-attendance', [], false) }}"><b>Students Attendance</b><span>Weekly / monthly record aur status</span></a>
        <a class="tile" href="{{ route('admin.gr-register', [], false) }}"><b>General Register (GR)</b><span>GR 1 se aakhri tak, purane bhi</span></a>
        <a class="tile" href="{{ route('admin.teachers.index', [], false) }}"><b>Teachers</b><span>Staff records aur PIN</span></a>
        <a class="tile" href="{{ route('admin.results.index', [], false) }}"><b>Results</b><span>Marks aur publish</span></a>
        <a class="tile" href="{{ route('admin.results.history', [], false) }}"><b>Results History</b><span>Purane saalon ke natije</span></a>
        <a class="tile" href="{{ route('admin.promotions.history') }}"><b>Promotion History</b><span>Class promotion ka record</span></a>
        <a class="tile" href="{{ route('admin.content.index', [], false) }}"><b>Notices &amp; Calendar</b><span>Notices, events</span></a>
        <a class="tile" href="{{ route('admin.leaving-certificates.index', [], false) }}"><b>Leaving Certificates</b><span>School leaving certificate</span></a>
        <a class="tile" href="{{ route('admin.id-cards.index', [], false) }}"><b>ID Cards</b><span>Students ke ID card</span></a>
        <a class="tile" href="{{ route('admin.admit-cards.index', [], false) }}"><b>Admit Cards</b><span>Imtihan ke slips</span></a>
        <a class="tile" href="{{ route('admin.staff-id-cards.index', [], false) }}"><b>Staff ID Cards</b><span>Teachers ke ID card</span></a>
        <a class="tile" href="{{ route('admin.settings.edit', [], false) }}"><b>School Settings</b><span>School ki maloomat</span></a>
        <a class="tile" href="{{ route('admin.bookbank.index', [], false) }}"><b>Book Bank</b><span>Kitabon ka stock</span></a>
        <a class="tile" href="{{ route('admin.student-leave.index', [], false) }}"><b>Student Leave Applications</b><span>Walidain ki arziyan</span></a>
        <a class="tile" href="{{ route('admin.leave.index', [], false) }}"><b>Teacher Leave Requests</b><span>Chhutti ki arziyan</span></a>
        <a class="tile" href="{{ route('admin.media.index', [], false) }}"><b>Gallery, Textbooks &amp; Feedback</b><span>Photos, kitabein, rai</span></a>
        <a class="tile" href="{{ route('admin.contact.index', [], false) }}"><b>Contact Messages</b><span>Parents ke paighamat</span></a>
        <a class="tile" href="{{ route('admin.security.index', [], false) }}"><b>Security Logs</b><span>Login aur attendance logs</span></a>
    </div>
@endsection
