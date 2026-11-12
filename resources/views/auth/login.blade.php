@extends('layouts.portal')

@section('title', 'Login')

@section('content')
    <div class="hero-lk">
        <div class="ring"><img src="{{ file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg') }}" alt=""></div>
        <h2>Staff Login</h2>
        <span class="pill">Teachers &amp; Admin</span>
        <span class="dev">Designed &amp; Developed by Sir Waris Ghafoor</span>
    </div>

    <div class="lk-card">
        <div class="lk-title sd">Admin يا Teacher اڪائونٽ سان لاگ ان ڪريو.</div>
        @if ($errors->any())
            <div class="err">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login', [], false) }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

            <label for="password">Password / PIN</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">

            <label><input type="checkbox" name="remember"> Remember me</label>

            <div class="btn-row"><button type="submit">Login</button></div>
        </form>
    </div>
@endsection
