@extends('layouts.app')

@section('title', 'Gallery')

@section('content')
    <p><a href="{{ route('home') }}">&larr; Home</a></p>
    <h1>Gallery</h1>
    <div style="display:flex;flex-wrap:wrap;gap:12px">
        @forelse ($items as $g)
            <div style="width:160px">
                <a href="{{ $g->url }}" target="_blank"><img src="{{ $g->url }}" alt="" style="width:160px;height:120px;object-fit:cover"></a>
                <div style="font-size:13px">{{ $g->caption }}</div>
            </div>
        @empty
            <p>Abhi koi photo nahi.</p>
        @endforelse
    </div>
@endsection
