@extends('layouts.portal')

@section('title', 'Students')

@section('content')
    <p><a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a></p>
    <div class="page-title" data-n="02"><h1>Register / Students</h1></div>
    <p class="sd sd-note">جنرل رجسٽر مطابق اسٽوڊنٽ رڪارڊ. GR نمبر سان ڳوليو.</p>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif

    @php
        $palette = ['#0F5132', '#DBB43C', '#2E7D9A', '#B5523B', '#7A5BA6', '#4F8F3A', '#C98A1B', '#3A6EA5', '#8A8A8A'];
        $totalActive = max(1, (int) $byClass->sum());
        $acc = 0; $stops = []; $i = 0;
        foreach ($byClass as $cl => $n) {
            $from = $acc; $acc += $n / $totalActive * 100;
            $stops[] = $palette[$i % count($palette)].' '.round($from, 2).'% '.round($acc, 2).'%';
            $i++;
        }
    @endphp
    @if ($byClass->isNotEmpty())
        <div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;margin:10px 0 14px">
            <div style="width:120px;height:120px;border-radius:50%;background:conic-gradient({{ implode(',', $stops) }});position:relative;flex:none">
                <div style="position:absolute;inset:26px;border-radius:50%;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;line-height:1.1"><b style="font-size:20px">{{ $byClass->sum() }}</b><small>active</small></div>
            </div>
            <div style="font-size:13px;display:grid;grid-template-columns:repeat(2,auto);gap:2px 16px">
                @php $i = 0; @endphp
                @foreach ($byClass as $cl => $n)
                    <span><i style="display:inline-block;width:10px;height:10px;border-radius:2px;background:{{ $palette[$i % count($palette)] }}"></i> {{ $cl }}: <b>{{ $n }}</b></span>
                    @php $i++; @endphp
                @endforeach
                <span style="grid-column:1/-1;color:#666">Passed out / left: <b>{{ $passedOut }}</b></span>
            </div>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.students.index') }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="Naam, father ya GR number" style="width:55%">
        <select name="class">
            <option value="">Sab classes</option>
            @foreach ($classes as $c)
                <option value="{{ $c }}" @selected($c === $class)>{{ $c }}</option>
            @endforeach
        </select>
        <button type="submit">Talaash</button>
        <a href="{{ route('admin.students.create') }}">+ Naya student</a>
        <a href="{{ route('admin.gr-register', [], false) }}">General Register (GR 1 se)</a>
    </form>

    <div class="scroll"><table>
        <tr><th>GR</th><th>Name</th><th>Father</th><th>Class</th><th>Phone</th><th></th></tr>
        @forelse ($students as $s)
            <tr>
                <td>{{ $s->gr_number }}</td>
                <td>{{ $s->name }}</td>
                <td>{{ $s->father_name }}</td>
                <td>{{ $s->class }}{{ $s->section ? '-'.$s->section : '' }}</td>
                <td>{{ $s->guardian_phone }}</td>
                <td>
                    <a href="{{ route('admin.students.edit', $s) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.students.destroy', $s) }}" style="display:inline" onsubmit="return confirm('Is student ko delete karna hai? Iski attendance aur results bhi chale jayenge.')">
                        @csrf @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><span class="sd">اڃا ڪو اسٽوڊنٽ رڪارڊ ناهي.</span></td></tr>
        @endforelse
    </table></div>

    <p>
        @if ($students->previousPageUrl())<a href="{{ $students->previousPageUrl() }}">&larr; Pichla</a>@endif
        @if ($students->nextPageUrl())<a href="{{ $students->nextPageUrl() }}">Agla &rarr;</a>@endif
    </p>
@endsection
