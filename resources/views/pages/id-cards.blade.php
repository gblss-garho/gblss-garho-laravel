@extends('layouts.portal')

@section('title', 'ID Cards')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/card-preview.css') }}">
    @include('pages._hero', ['pill' => 'ID Card Generator — Identity Verification'])
    <p class="sd sd-note">GR نمبر ۽ تاريخ پيدائش سان پنهنجو ID ڪارڊ ڏسو ۽ ڊائونلوڊ ڪريو.</p>
    <div class="pc-grid">
        <div class="pc-forms">
            <div class="lk-card">
                <div class="lk-title">Teacher ID Card</div>
                @if ($terror)<div class="err"><p>{{ $terror }}</p></div>@endif
                <form method="POST" action="{{ route('public.id-cards.teacher', [], false) }}">
                    @csrf
                    <label for="cnic_pid">CNIC / PID</label>
                    <input id="cnic_pid" name="cnic_pid" value="{{ old('cnic_pid') }}" required placeholder="PID ya CNIC likhein">
                    <div class="btn-row">
                        <button type="submit">Search</button>
                        <a class="btn ghost" href="{{ route('public.id-cards', [], false) }}">Reset</a>
                    </div>
                </form>
            </div>
            <div class="lk-card">
                <div class="lk-title">Student ID Card</div>
                @if ($error)<div class="err"><p>{{ $error }}</p></div>@endif
                <form method="POST" action="{{ route('public.id-cards.download', [], false) }}">
                    @csrf
                    <label for="gr_number">GR / Roll number</label>
                    <input id="gr_number" name="gr_number" value="{{ old('gr_number') }}" required inputmode="numeric" placeholder="GR number">
                    <label for="dob">Date of birth <span class="sd">(رڪارڊ موجب)</span></label>
                    <input id="dob" name="dob" type="date" value="{{ old('dob') }}" required>
                    <div class="btn-row">
                        <button type="submit">Search</button>
                        <a class="btn ghost" href="{{ route('public.id-cards', [], false) }}">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        <div>
            @if ($card)
                @include('pages._id-preview', ['type' => $card['type'], 'person' => $card['person'], 'idText' => $card['idText'], 'qr' => $card['qr'], 'public' => true])
                <p class="pc-note sd">اڳيان ۽ پويان — ٻنهي پاسن وارو ڪارڊ (double-sided card).</p>
                <div class="pc-actions"><a class="btn" href="{{ $card['pdfUrl'] }}" target="_blank" rel="noopener">&#128424; Print / Save as PDF</a></div>
            @else
                <p class="pc-note sd">ڪارڊ ڏسڻ لاءِ ڇنڊ ڇاڻ ڪريو (Search).</p>
            @endif
        </div>
    </div>
    <script src="{{ asset('js/card-fit.js') }}"></script>
@endsection
