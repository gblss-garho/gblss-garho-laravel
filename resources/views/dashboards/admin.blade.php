@extends('layouts.portal')

@section('title', ucfirst('admin') . ' Dashboard')

@section('content')
    <div class="page-title"><h1>Admin Dashboard</h1></div>
    <p class="who">Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>

    <div class="tiles">
        <a class="tile" href="{{ route('admin.students.index', [], false) }}"><b>Students</b><span>Register, edit, add</span></a>
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
        <a class="tile" href="{{ route('admin.leave.index', [], false) }}"><b>Teacher Leave Requests</b><span>Chhutti ki arziyan</span></a>
        <a class="tile" href="{{ route('admin.media.index', [], false) }}"><b>Gallery, Textbooks &amp; Feedback</b><span>Photos, kitabein, rai</span></a>
        <a class="tile" href="{{ route('admin.security.index', [], false) }}"><b>Security Logs</b><span>Login aur attendance logs</span></a>
    </div>
@endsection
