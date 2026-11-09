@extends('layouts.portal')

@section('title', 'Parents Corner')

@section('content')
    <div class="hero-card">
        <img src="{{ file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg') }}" alt="">
        <h2>Parents Corner</h2>
        <span class="pill">GR number aur date of birth</span>
    </div>

    <div class="card narrow">
        <p class="sd">پنهنجي ٻار جي تفصيل ڏسو</p>
        <p>Apne bachay ka GR number aur date of birth likh kar result dekhein.</p>

        @if ($error)<div class="err"><p>{{ $error }}</p></div>@endif

        <form method="POST" action="{{ route('public.lookup.check') }}">
            @csrf
            <label for="gr_number">GR number <span class="sd">(GR نمبر داخل ڪريو)</span></label>
            <input id="gr_number" name="gr_number" value="{{ old('gr_number') }}" required inputmode="numeric">

            <label for="dob">Date of birth <span class="sd">(رڪارڊ موجب)</span> &mdash; jaise 2013-05-21</label>
            <input id="dob" name="dob" value="{{ old('dob') }}" required>

            <button type="submit">Dekhein</button>
        </form>
    </div>

    @if ($student)
        <div class="card">
            <h2>{{ $student->name }}</h2>
            <p>Walid: {{ $student->father_name }} &middot; Class: {{ $student->class }}{{ $student->section ? '-'.$student->section : '' }} &middot; GR: {{ $student->gr_number }}</p>
        </div>

        @forelse ($exams as $exam => $rows)
            <div class="card">
                <h3>{{ ucfirst($exam) }}</h3>
                <div class="scroll">
                    <table>
                        <tr><th>Subject</th><th>Marks</th></tr>
                        @foreach ($rows as $r)
                            <tr><td>{{ ucfirst($r->subject) }}</td><td>{{ $r->score }}</td></tr>
                        @endforeach
                        <tr><td><b>Total</b></td><td><b>{{ $rows->sum('score') }}</b></td></tr>
                    </table>
                </div>
            </div>
        @empty
            <div class="card"><p class="sd">نتيجو اڃا شايع نه ٿيو آهي</p></div>
        @endforelse

        <div class="card">
            <h3>Homework</h3>
            @forelse ($homework as $h)
                <p><b>{{ $h->date->format('d-m-Y') }} | {{ ucfirst($h->subject) }}</b><br>{{ $h->text }}
                @if ($h->attachment_path)<br><a href="{{ asset('storage/' . $h->attachment_path) }}" target="_blank">{{ $h->attachment_name ?: 'File' }}</a>@endif</p>
            @empty
                <p class="sd">اڄ لاءِ هن ڪلاس جو ڪو Homework/Diary داخل نه ٿيو آهي.</p>
            @endforelse
        </div>

        @php
            $p = (int) ($attMonth['P'] ?? 0); $a = (int) ($attMonth['A'] ?? 0); $l = (int) ($attMonth['L'] ?? 0);
            $tot = $p + $a + $l;
        @endphp
        <div class="card">
            <h3>Is mahine ki attendance ({{ now()->format('F Y') }})</h3>
            @if ($tot > 0)
                <p>Present: {{ $p }} | Absent: {{ $a }} | Leave: {{ $l }} | Fi sad: {{ round((($p + $l) / $tot) * 100) }}%</p>
            @else
                <p class="sd">هن دوري لاءِ ڪو رڪارڊ نه مليو.</p>
            @endif
        </div>

        <div class="card">
            <h3>Taaza notices</h3>
            @forelse ($notices as $n)
                <p><b>{{ substr($n->date, 0, 10) }}</b> &mdash; {{ $n->text }}</p>
            @empty
                <p>Koi notice nahi.</p>
            @endforelse
        </div>

        <div class="card">
            <h3>Aane wale events</h3>
            @forelse ($events as $e)
                <p><b>{{ substr($e->date, 0, 10) }}</b> &mdash; {{ $e->title }}</p>
            @empty
                <p>Koi event nahi.</p>
            @endforelse
        </div>
    @endif
@endsection
