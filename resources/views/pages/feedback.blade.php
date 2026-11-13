@extends('layouts.portal')

@section('title', 'Feedback')

@section('content')
    <div class="hero-lk">
        <div class="ring"><img src="{{ file_exists(public_path('images/logo-glow.png')) ? asset('images/logo-glow.png') : (file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg')) }}" alt=""></div>
        <h2>Government Boys Lower Secondary School Garho</h2>
        <span class="pill">Feedback &mdash; Suggestions &amp; Reviews</span>
        <span class="dev">Designed &amp; Developed by Sir Waris Ghafoor</span>
    </div>
    @if (session('ok'))<div class="ok">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <div class="lk-card">
        <div class="lk-title sd">پنهنجو فيڊبيڪ ڏيو</div>
        <form method="POST" action="{{ route('public.feedback.store', [], false) }}">
            @csrf
            <label for="name">Naam (optional) <span class="sd">/ توهان جو نالو (اختياري)</span></label>
            <input id="name" name="name" maxlength="80" value="{{ old('name') }}" placeholder="Parent / Student">
            <label>Rating</label>
            <div class="stars">
                @for ($i = 5; $i >= 1; $i--)
                    <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" @checked(old('rating') == $i)>
                    <label for="star{{ $i }}" title="{{ $i }} star">&#9733;</label>
                @endfor
            </div>
            <label for="message">Suggestion / Review</label>
            <textarea id="message" name="message" rows="4" dir="auto" required placeholder="پنهنجي راءِ يا تجويز لکو…">{{ old('message') }}</textarea>
            <div class="btn-row"><button type="submit" class="gold">Submit Feedback</button></div>
        </form>
    </div>

    @if (!empty($reviews) && count($reviews))
        <div class="fb-sum"><span class="st">{{ str_repeat('★', (int) round($avg)) }}</span><span>{{ number_format($avg, 1) }} / 5 &middot; {{ count($reviews) }} reviews</span></div>
        @foreach ($reviews as $f)
            <div class="fb-item">
                <b>{{ $f->name ?: 'Anonymous' }}</b>
                @if ($f->rating)<span class="st">{{ str_repeat('★', (int) $f->rating) }}</span>@endif
                <p dir="auto" style="margin:6px 0">{{ $f->message }}</p>
                <small>{{ substr((string) $f->created_at, 0, 10) }}</small>
            </div>
        @endforeach
    @endif
@endsection
