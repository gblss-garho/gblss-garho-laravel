@extends('layouts.portal')

@section('title', 'Textbooks')

@section('content')
    <div class="page-title" data-n="09"><h1>Text Books</h1></div>
    <p class="sd sd-note">سنڌ ٽيڪسٽ بڪ بورڊ جا ڪتاب — ڪلاس مطابق.</p>
    @forelse ($items as $class => $books)
        <h2>Class {{ $class }}</h2>
        <div class="tb-grid">
            @foreach ($books as $b)
                <div class="tb">
                    <div class="cover">📖</div>
                    <div class="bd">
                        <small>{{ $b->subject ?: 'All' }}</small>
                        <b>{{ $b->book_name ?: 'All subjects' }}</b>
                        <a class="open sd" href="{{ $b->book_url }}" target="_blank" rel="noopener">📖 ڪتاب کوليو</a>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="lk-card sd">اڃا ڪا ڪتاب شامل نه ڪئي وئي.</div>
    @endforelse
@endsection
