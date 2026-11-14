@extends('layouts.portal')

@section('title', 'Contact Us')

@section('content')
    @php
        $logo = file_exists(public_path('images/logo-glow.png')) ? asset('images/logo-glow.png') : (file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg'));
        $phone = trim((string) ($info->phone ?? ''));
        $email = trim((string) ($info->email ?? ''));
        $loc = trim((string) (($info->map_location ?? '') ?: ($info->address ?? '')));
    @endphp
    <div class="hero-lk">
        <div class="ring"><img src="{{ $logo }}" alt=""></div>
        <h2>Government Boys Lower Secondary School Garho</h2>
        <span class="pill">Contact Us</span>
        <span class="dev">Designed &amp; Developed by Sir Waris Ghafoor</span>
    </div>

    @if (session('ok'))<div class="ok">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif

    @if ($phone !== '' || $email !== '' || $loc !== '')
        <div class="lk-card">
            <div class="btn-row" style="margin:0">
                @if ($phone !== '')<a class="btn" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">&#128222; Call: {{ $phone }}</a>@endif
                @if ($email !== '')<a class="btn ghost" href="mailto:{{ $email }}">&#9993;&#65039; Email: {{ $email }}</a>@endif
                @if ($loc !== '')<a class="btn gold" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($loc) }}" target="_blank" rel="noopener">&#128205; Get Directions</a>@endif
            </div>
        </div>
        @if ($loc !== '')
            <div class="lk-card" style="padding:0;overflow:hidden">
                <iframe title="School Location" src="https://www.google.com/maps?q={{ urlencode($loc) }}&amp;output=embed" width="100%" height="280" style="border:0;display:block" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        @endif
    @endif

    <div class="lk-card">
        <div class="lk-title">Send us a message</div>
        <form method="POST" action="{{ route('public.contact.store', [], false) }}">
            @csrf
            <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
            <label for="name">FULL NAME</label>
            <input id="name" name="name" maxlength="80" required value="{{ old('name') }}" placeholder="Your name">
            <label for="phone">PHONE / WHATSAPP</label>
            <input id="phone" name="phone" maxlength="30" required value="{{ old('phone') }}" placeholder="03XX-XXXXXXX">
            <label for="email">EMAIL (OPTIONAL)</label>
            <input id="email" type="email" name="email" maxlength="120" value="{{ old('email') }}" placeholder="you@example.com">
            <label for="subject">REASON FOR CONTACT</label>
            <select id="subject" name="subject" required>
                <option value="">Select one</option>
                @foreach ($reasons as $r)<option value="{{ $r }}" @selected(old('subject') === $r)>{{ $r }}</option>@endforeach
            </select>
            <label for="message">MESSAGE</label>
            <textarea id="message" name="message" rows="4" dir="auto" maxlength="2000" required placeholder="Write your message…">{{ old('message') }}</textarea>
            <div class="btn-row"><button type="submit" class="gold">Send Message</button></div>
        </form>
    </div>
@endsection
