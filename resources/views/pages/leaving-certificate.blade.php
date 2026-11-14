@extends('layouts.portal')

@section('title', 'Leaving Certificate')

@section('content')
    @include('pages._hero', ['pill' => 'Leaving Certificate — GR Number Verification'])
    <div class="lk-card">
        <div class="lk-title">Find Leaving Certificate</div>
        @if ($error)<div class="err"><p>{{ $error }}</p></div>@endif
        <form method="POST" action="{{ route('public.leaving-certificate.download', [], false) }}">
            @csrf
            <label for="gr_number">GR number</label>
            <input id="gr_number" name="gr_number" value="{{ old('gr_number') }}" required inputmode="numeric" placeholder="e.g. 1024">
            <label for="dob">Date of birth <span class="sd">(رڪارڊ موجب)</span></label>
            <input id="dob" name="dob" type="date" value="{{ old('dob') }}" required>
            <label for="year">Year (optional)</label>
            <input id="year" name="year" value="{{ old('year') }}" placeholder="e.g. 2024-2025">
            <div class="btn-row">
                <button type="submit">Search &amp; Download</button>
                <a class="btn ghost" href="{{ route('public.leaving-certificate', [], false) }}">Reset</a>
            </div>
        </form>
    </div>
@endsection
