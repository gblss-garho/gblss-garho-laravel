@extends('layouts.portal')

@section('title', 'Admit Cards')

@section('content')
    <h1>Student Admit Cards</h1>
    <p class="sd sd-note">امتحان لاءِ ايڊمٽ ڪارڊ ڏسو ۽ پرنٽ ڪريو. پوري ڪلاس لاءِ "Print All" استعمال ڪريو.</p>

    <form method="GET" action="{{ route('admin.admit-cards.index', [], false) }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="Naam ya GR number" style="width:70%">
        <button type="submit">Talaash</button>
    </form>

    <link rel="stylesheet" href="{{ asset('css/card-preview.css') }}">
    @if ($classes->isNotEmpty())
        <p class="pc-noprint" style="margin-top:12px"><b>Class chunein (preview aur Print All):</b><br>
            @foreach ($classes as $c)
                <a class="btn {{ $selected === $c ? '' : 'ghost' }}" href="{{ route('admin.admit-cards.index', ['class' => $c], false) }}">Class {{ $c }}</a>
            @endforeach
        </p>
    @endif

    @if ($grid->isNotEmpty())
        <p class="pc-noprint">
            <a class="btn" href="{{ route('admin.admit-cards.class-pdf', ['class' => $selected], false) }}">&#128424; Print All ({{ $grid->count() }}) &mdash; PDF</a>
            <a class="btn ghost" href="#" onclick="window.print();return false">Page se print</a>
        </p>
        <div class="pc-cards">
            @foreach ($grid as $st)
                @include('pages._admit-preview', ['student' => $st, 'school' => $school])
            @endforeach
        </div>
        <script src="{{ asset('js/card-fit.js') }}"></script>
    @endif

    @if ($grid->isEmpty())
    @if ($students->isEmpty())
        <p class="sd">هن ڪلاس لاءِ ڪو اسٽوڊنٽ نه مليو.</p>
    @else
        <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;margin-top:12px">
            <tr><th>GR</th><th>Name</th><th>Class</th><th>Admit Card</th></tr>
            @foreach ($students as $s)
                <tr>
                    <td>{{ $s->gr_number }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->class }}{{ $s->section ? '-'.$s->section : '' }}</td>
                    <td><a href="{{ route('admin.admit-cards.pdf', ['student' => $s->id], false) }}">PDF</a></td>
                </tr>
            @endforeach
        </table>
        @if ($students->count() === 50)
            <p>Sirf pehle 50 dikhaye gaye — talaash se chhota karen.</p>
        @endif
    @endif
    @endif
@endsection
