@php
    $logo = file_exists(public_path('images/logo-glow.png')) ? asset('images/logo-glow.png') : (file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg'));
@endphp
<div class="hero-lk">
    <div class="ring"><img src="{{ $logo }}" alt=""></div>
    <h2>Government Boys Lower Secondary School Garho</h2>
    <span class="pill">{{ $pill }}</span>
    <span class="dev">Designed &amp; Developed by Sir Waris Ghafoor</span>
</div>
