@extends('layouts.portal')

@section('title', 'Feedback')

@section('content')
    <div class="page-title"><h1>Feedback</h1></div>
    @if (session('ok'))<div class="ok">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    <div class="card narrow">
        <form method="POST" action="{{ route('public.feedback.store', [], false) }}">
            @csrf
            <label for="name">Naam (optional)</label>
            <input id="name" name="name" maxlength="80" value="{{ old('name') }}">
            <label>Rating</label>
            <div class="stars">
                @for ($i = 5; $i >= 1; $i--)
                    <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" @checked(old('rating') == $i)>
                    <label for="star{{ $i }}" title="{{ $i }} star">&#9733;</label>
                @endfor
            </div>
            <label for="message">Aap ka paigham</label>
            <textarea id="message" name="message" rows="4" required>{{ old('message') }}</textarea>
            <button type="submit">Bhejein</button>
        </form>
    </div>
@endsection
