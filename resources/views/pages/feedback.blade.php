@extends('layouts.app')

@section('title', 'Feedback')

@section('content')
    <p><a href="{{ route('home') }}">&larr; Home</a></p>
    <h1>Feedback</h1>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    @if ($errors->any())<p style="color:red">{{ $errors->first() }}</p>@endif
    <form method="POST" action="{{ route('public.feedback.store') }}">
        @csrf
        <p>Naam (optional)<br><input name="name" maxlength="80"></p>
        <p>Rating (1-5)<br><select name="rating"><option value="">--</option>@for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }}</option>@endfor</select></p>
        <p>Aap ka paigham<br><textarea name="message" rows="4" cols="40" required></textarea></p>
        <p><button type="submit">Bhejein</button></p>
    </form>
@endsection
