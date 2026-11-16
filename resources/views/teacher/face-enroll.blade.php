@extends('layouts.portal')

@section('title', 'Enroll Face')

@section('content')
    <h1>Chehra Enroll Karen</h1>
    <p class="sd sd-note">Time In/Out لاءِ پهريان اڄ جو پنهنجو چهرو enroll ڪريو.</p>

    @if (session('status'))<div class="at-ok">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="at-err">{{ $errors->first() }}</div>@endif

    @if ($isEnrolled)
        <p>Aapka chehra pehle se enroll hai. Dobara enroll karne se purana overwrite ho jayega.</p>
    @endif

    @include('teacher._face-scanner', [
        'action' => route('teacher.face.enroll.store', [], false),
        'btn' => 'Scan Shuru Karen',
        'title' => 'Chehra Enroll',
        'sub' => 'Ek dafa enroll karen. Asli insan ki tasdeeq ke liye ek chhota sa kaam (palak ya sir ghumana) hoga.',
        'challenges' => $challenges,
        'initialFail' => $errors->first(),
    ])

    <p style="text-align:center"><a href="{{ route('teacher.attendance.index', [], false) }}">&larr; Attendance par wapas</a></p>
@endsection
