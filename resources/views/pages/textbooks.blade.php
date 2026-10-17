@extends('layouts.app')

@section('title', 'Textbooks')

@section('content')
    <p><a href="{{ route('home') }}">&larr; Home</a></p>
    <h1>Textbooks</h1>
    @forelse ($items as $class => $books)
        <h2>Class {{ $class }}</h2>
        <ul>
            @foreach ($books as $b)
                <li>{{ $b->subject }} &mdash; <a href="{{ $b->book_url }}" target="_blank">{{ $b->book_name }}</a></li>
            @endforeach
        </ul>
    @empty
        <p>Abhi koi kitaab nahi.</p>
    @endforelse
@endsection
