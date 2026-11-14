@extends('layouts.portal')

@section('title', 'Alumni')

@section('content')
    @include('pages._hero', ['pill' => 'Alumni — Former Students Archive'])
    <div class="lk-card">
        <div class="lk-title">Find a Former Student</div>
        <form method="GET" action="{{ route('public.alumni', [], false) }}">
            <label for="q">GR number, name or father name</label>
            <input id="q" name="q" value="{{ $q }}" required placeholder="e.g. 195 or student name">
            <div class="btn-row">
                <button type="submit">Search</button>
                <a class="btn ghost" href="{{ route('public.alumni', [], false) }}">Reset</a>
            </div>
        </form>
    </div>
    @if ($results !== null)
        @forelse ($results as $s)
            <div class="fb-item" style="display:flex;gap:14px;align-items:center">
                @if ($s->photo_url)<img src="{{ $s->photo_url }}" alt="" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid #D9A91A">@endif
                <div>
                    <b style="color:#0F5132">{{ $s->name }}</b><br>
                    <small>Father: {{ $s->father_name }} · GR {{ $s->gr_number }}</small><br>
                    <small>Passed Out: Class VIII · Session {{ $s->pass_out_year ?: '—' }}</small>
                    @if ($s->previous_school)<br><small>Previous School: {{ $s->previous_school }}</small>@endif
                </div>
            </div>
        @empty
            <p class="sd sd-note">هن نالي يا GR نمبر سان ڪو former student نه مليو.</p>
        @endforelse
    @endif
@endsection
