@extends('layouts.portal')

@section('title', 'Textbooks')

@section('content')
    <div class="page-title"><h1>Textbooks</h1></div>
    <p class="sd sd-note">سنڌ ٽيڪسٽ بڪ بورڊ جا ڪتاب — ڪلاس مطابق.</p>
    @forelse ($items as $class => $books)
        <h2>Class {{ $class }}</h2>
        <div class="card">
            @foreach ($books as $b)
                <div class="row">{{ $b->subject }} &mdash; <a href="{{ $b->book_url }}" target="_blank" rel="noopener">{{ $b->book_name }}</a> <a class="sd" href="{{ $b->book_url }}" target="_blank" rel="noopener">📖 ڪتاب کوليو</a></div>
            @endforeach
        </div>
    @empty
        <div class="card sd">اڃا ڪا ڪتاب شامل نه ڪئي وئي.</div>
    @endforelse
@endsection
