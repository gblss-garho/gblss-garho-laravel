@extends('layouts.app')

@section('title', 'School Settings')

@section('content')
    @php
        $labels = [
            'name' => 'School ka naam (English)', 'urdu_name' => 'School ka naam (Urdu/Sindhi)',
            'motto' => 'Motto', 'address' => 'Address', 'phone' => 'Phone', 'email' => 'Email',
            'semis_code' => 'SEMIS code', 'established_year' => 'Qaim hone ka saal',
            'total_classes' => 'Total classes', 'current_exam_title' => 'Maujooda exam ka naam',
            'current_exam_year' => 'Exam year (jaise 2026-2027)',
            'school_lat' => 'School latitude', 'school_lng' => 'School longitude',
            'attendance_radius' => 'Attendance radius (meter)',
            'time_in_window_start' => 'Check-in shuru (HH:MM)', 'time_in_window_end' => 'Check-in khatam (HH:MM)',
            'time_out_window_start' => 'Check-out shuru (HH:MM)', 'time_out_window_end' => 'Check-out khatam (HH:MM)',
            'friday_time_out_window_start' => 'Friday check-out shuru (HH:MM)', 'friday_time_out_window_end' => 'Friday check-out khatam (HH:MM)',
            'weekly_off_days' => 'Haftawar chhutti (0=Sunday ... 6=Saturday, comma se, jaise 0,6)',
        ];
    @endphp
    <h1>School Settings</h1>
    <p><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    @if ($errors->any())<p style="color:red">{{ $errors->first() }}</p>@endif

    <form method="POST" action="{{ route('admin.settings.update', [], false) }}">
        @csrf
        @foreach ($fields as $f)
            <p>{{ $labels[$f] ?? $f }}<br>
                <input name="{{ $f }}" value="{{ old($f, $s->$f ?? '') }}" size="40"></p>
        @endforeach
        <p><button type="submit">Save karein</button></p>
    </form>
@endsection
