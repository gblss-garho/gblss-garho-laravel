@extends('layouts.portal')

@section('title', 'Admit Cards')

@section('content')
    <h1>Student Admit Cards</h1>

    <form method="GET" action="{{ route('admin.admit-cards.index', [], false) }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="Naam ya GR number" style="width:70%">
        <button type="submit">Talaash</button>
    </form>

    @if ($classes->isNotEmpty())
        <p style="margin-top:12px"><b>Poori class ke admit cards (Print All):</b><br>
            @foreach ($classes as $c)
                <a class="btn ghost" href="{{ route('admin.admit-cards.class-pdf', ['class' => $c], false) }}" target="_blank">Class {{ $c }}</a>
            @endforeach
        </p>
    @endif

    @if ($students->isEmpty())
        <p>Koi student nahi mila.</p>
    @else
        <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;margin-top:12px">
            <tr><th>GR</th><th>Name</th><th>Class</th><th>Admit Card</th></tr>
            @foreach ($students as $s)
                <tr>
                    <td>{{ $s->gr_number }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->class }}{{ $s->section ? '-'.$s->section : '' }}</td>
                    <td><a href="{{ route('admin.admit-cards.pdf', ['student' => $s->id], false) }}" target="_blank">PDF</a></td>
                </tr>
            @endforeach
        </table>
        @if ($students->count() === 50)
            <p>Sirf pehle 50 dikhaye gaye — talaash se chhota karen.</p>
        @endif
    @endif
@endsection
