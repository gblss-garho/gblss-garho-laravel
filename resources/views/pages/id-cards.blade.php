@extends('layouts.portal')

@section('title', 'ID Card')

@section('content')
    <div class="page-title"><h1>Student ID Card</h1></div>
    <p class="sd sd-note">GR نمبر ۽ تاريخ پيدائش سان پنهنجو ID ڪارڊ ڊائونلوڊ ڪريو.</p>
    <div class="lk-card">
        <div class="lk-title">Student ID Card</div>
        @if ($error)<div class="err"><p>{{ $error }}</p></div>@endif
        <form method="POST" action="{{ route('public.id-cards.download', [], false) }}">
            @csrf
            <label for="gr_number">GR / Roll number</label>
            <input id="gr_number" name="gr_number" value="{{ old('gr_number') }}" required inputmode="numeric" placeholder="1024">
            <label for="dob">Date of birth <span class="sd">(رڪارڊ موجب)</span></label>
            <input id="dob" name="dob" type="date" value="{{ old('dob') }}" required>
            <div class="btn-row">
                <button type="submit">Search &amp; Download</button>
                <a class="btn ghost" href="{{ route('public.id-cards', [], false) }}">Reset</a>
            </div>
        </form>
    </div>
    <p class="sd sd-note">GR نمبر ۽ تاريخ پيدائش داخل ڪريو.</p>
@endsection
