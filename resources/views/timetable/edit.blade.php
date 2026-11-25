@extends('layouts.portal')

@section('title', 'Timetable')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/timetable.css') }}">
    <div class="tt">
        <p style="margin:0"><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
        <h1 style="margin:4px 0 2px">Timetable &middot; Class {{ $class }}</h1>
        <p class="tt-note">Somwar se Jumma, 7 periods. Hafta aur Itwar chhutti.</p>

        @if (session('ok'))<div class="tt-ok">{{ session('ok') }}</div>@endif
        @if ($errors->any())<div class="tt-err">{{ $errors->first() }}</div>@endif

        <form method="GET" action="{{ route('admin.timetable.edit', [], false) }}" class="tt-pick">
            <label>Class
                <select name="class" onchange="this.form.submit()">
                    @foreach ($classes as $c)<option value="{{ $c }}" @selected($c === $class)>{{ $c }}</option>@endforeach
                </select>
            </label>
            <a class="tt-btn alt" href="{{ route('admin.teaching.index', ['class' => $class], false) }}">Teaching Assignment</a>
        </form>

        @if ($assign->isEmpty())
            <div class="tt-err">Pehle is class ke subjects aur teachers <a href="{{ route('admin.teaching.index', ['class' => $class], false) }}">Teaching Assignment</a> mein daalein.</div>
        @else
            <form method="POST" action="{{ route('admin.timetable.save', [], false) }}">
                @csrf
                <input type="hidden" name="class_name" value="{{ $class }}">
                <div class="tt-scroll"><table class="tt-t tt-edit">
                    <thead><tr>
                        <th class="tt-p">Period<small>waqt (sab classes ke liye)</small></th>
                        @foreach (\App\Services\TimetableService::DAYS as $name)<th>{{ $name }}</th>@endforeach
                    </tr></thead>
                    <tbody>
                    @for ($p = 1; $p <= \App\Services\TimetableService::PERIODS; $p++)
                        <tr>
                            <th class="tt-p">{{ $p }}
                                <input type="time" name="times[{{ $p }}][starts_at]" value="{{ old("times.$p.starts_at", $periods[$p]['starts_at']) }}">
                                <input type="time" name="times[{{ $p }}][ends_at]" value="{{ old("times.$p.ends_at", $periods[$p]['ends_at']) }}">
                            </th>
                            @foreach (\App\Services\TimetableService::DAYS as $d => $name)
                                @php
                                    $cur = $grid[$d][$p] ?? null;
                                    $sel = old("cell.$d.$p", $cur ? optional($assign->firstWhere('subject', $cur->subject))->id : '');
                                @endphp
                                <td>
                                    <select name="cell[{{ $d }}][{{ $p }}]">
                                        <option value="">—</option>
                                        @foreach ($assign as $a)
                                            <option value="{{ $a->id }}" @selected((string) $sel === (string) $a->id)>{{ $a->subject }} · {{ $a->teacher?->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            @endforeach
                        </tr>
                    @endfor
                    </tbody>
                </table></div>
                <div class="tt-actions">
                    <button class="tt-btn" type="submit">Timetable save karein</button>
                    <a class="tt-btn alt" href="{{ route('public.timetable', ['class' => $class], false) }}" target="_blank">Dekhein / Print</a>
                </div>
                <p class="tt-note">Agar koi teacher usi waqt kisi aur class mein hai to save nahi hoga aur wajah dikhai jayegi.</p>
            </form>
        @endif
    </div>
@endsection
