@extends('layouts.portal')

@section('title', 'Admit Card')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/card-preview.css') }}">
    @include('pages._hero', ['pill' => 'Admit Card Slip — Roll Number Verification'])
    <div class="lk-card">
        <div class="lk-title">Check Your Admit Card</div>
        @if ($error)<div class="err"><p>{{ $error }}</p></div>@endif
        <form method="POST" action="{{ route('public.admit-card.download', [], false) }}">
            @csrf
            <label for="year">Year</label>
            <select id="year" disabled><option>{{ \App\Support\CardData::school()['exam_year'] ?: \App\Support\CardData::session() }}</option></select>
            <label for="class">Class</label>
            <select id="class" name="class">
                <option value="">All</option>
                @foreach (['VI', 'VII', 'VIII'] as $c)<option value="{{ $c }}" @selected(old('class') === $c)>{{ $c }}</option>@endforeach
            </select>
            <label for="gr_number">GR / Roll number</label>
            <input id="gr_number" name="gr_number" value="{{ old('gr_number') }}" required inputmode="numeric" placeholder="GR number">
            <label for="dob">Date of birth <span class="sd">(رڪارڊ موجب)</span></label>
            <input id="dob" name="dob" type="date" value="{{ old('dob') }}" required>
            <div class="btn-row">
                <button type="submit">Check Slip</button>
                <a class="btn ghost" href="{{ route('public.admit-card', [], false) }}">Reset</a>
            </div>
        </form>
    </div>
    @if ($card)
        <div style="max-width:380px;margin:0 auto">
            @include('pages._admit-preview', ['student' => $card['student'], 'school' => $card['school']])
            <div class="pc-actions"><a class="btn" href="{{ $card['pdfUrl'] }}" target="_blank" rel="noopener">&#128424; Print / Save as PDF</a></div>
        </div>
    @endif
    <script src="{{ asset('js/card-fit.js') }}"></script>
@endsection
