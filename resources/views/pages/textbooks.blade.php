@extends('layouts.portal')

@section('title', 'Textbooks')

@section('content')
    <div class="page-title"><h1>Textbooks</h1></div>
    @forelse ($items as $class => $books)
        <h2>Class {{ $class }}</h2>
        <div class="card">
            @foreach ($books as $b)
                <div class="row">{{ $b->subject }} &mdash; <a href="{{ $b->book_url }}" target="_blank" rel="noopener">{{ $b->book_name }}</a></div>
            @endforeach
        </div>
    @empty
        <div class="card sd">اڃا ڪا ڪتاب شامل نه ڪئي وئي.</div>
    @endforelse
@endsection
