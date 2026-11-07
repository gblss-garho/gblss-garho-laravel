@extends('layouts.portal')

@section('title', 'Gallery')

@section('content')
    <div class="page-title"><h1>Gallery</h1></div>
    @if (count($items))
        <div class="photos">
            @foreach ($items as $g)
                <figure>
                    <a href="{{ $g->url }}" target="_blank" rel="noopener"><img src="{{ $g->url }}" alt="{{ $g->caption }}" loading="lazy"></a>
                    @if (!empty($g->caption))<figcaption>{{ $g->caption }}</figcaption>@endif
                </figure>
            @endforeach
        </div>
    @else
        <div class="card">Abhi koi photo nahi.</div>
    @endif
@endsection
