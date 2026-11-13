@extends('layouts.portal')

@section('title', 'ID Cards')

@section('content')
    <h1>Student ID Cards</h1>
    <p class="sd sd-note">ID ڪارڊ ٺاهڻ لاءِ ڪو اسٽوڊنٽ چونڊيو. Front ۽ Back — ٻئي پاسا ڪٽي هڪ ٻئي پويان لڳايو (double-sided card).</p>

    <form method="GET" action="{{ route('admin.id-cards.index', [], false) }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="Naam ya GR number" style="width:70%">
        <button type="submit">Talaash</button>
    </form>

    @if ($classes->isNotEmpty())
        <p style="margin-top:12px"><b>Poori class ke ID cards ek sath (Print All):</b><br>
            @foreach ($classes as $c)
                <a class="btn ghost" href="{{ route('admin.id-cards.class-pdf', ['class' => $c], false) }}" target="_blank">Class {{ $c }}</a>
            @endforeach
        </p>
        <p class="sd sd-note">پوري ڪلاس جا ڪارڊ هڪ PDF ۾ — هر صفحي تي ٽي ڪارڊ (Front ۽ Back). وڏي ڪلاس لاءِ PDF ٺهڻ ۾ ڪجهه وقت لڳندو.</p>
    @endif

    @if ($students->isEmpty())
        <p class="sd">ڪو اسٽوڊنٽ نه مليو.</p>
    @else
        <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;margin-top:12px">
            <tr><th>GR</th><th>Name</th><th>Class</th><th>ID Card</th></tr>
            @foreach ($students as $s)
                <tr>
                    <td>{{ $s->gr_number }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->class }}{{ $s->section ? '-'.$s->section : '' }}</td>
                    <td><a href="{{ route('admin.id-cards.pdf', ['student' => $s->id], false) }}" target="_blank">PDF</a></td>
                </tr>
            @endforeach
        </table>
        @if ($students->count() === 50)
            <p>Sirf pehle 50 dikhaye gaye — talaash se chhota karen.</p>
        @endif
    @endif
@endsection
