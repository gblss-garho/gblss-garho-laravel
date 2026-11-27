{{-- Round camera frame with a status ring. Params: $action, $btn, $title, $sub, $challenges (liveness), $initialFail (optional) --}}
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@500;700&display=swap" rel="stylesheet">
<div class="fs" data-face-scan data-challenges="{{ implode(',', $challenges ?? []) }}" @if (!empty($geo)) data-geo-lat="{{ $geo['lat'] }}" data-geo-lng="{{ $geo['lng'] }}" data-geo-radius="{{ $geo['radius'] }}" @endif data-state="loading" @if (!empty($initialFail)) data-initial-fail="{{ $initialFail }}" @endif>
    <h2 class="fs-title">{{ $title }}</h2>
    <p class="fs-sub">{{ $sub }}</p>
    <div class="fs-stage">
        <div class="fs-cam"><video autoplay muted playsinline></video></div>
        <svg class="fs-svg" viewBox="0 0 100 100" aria-hidden="true">
            <circle class="fs-ticks" cx="50" cy="50" r="46"/>
            <circle class="fs-arc" cx="50" cy="50" r="46"/>
        </svg>
        <div class="fs-arrow l" aria-hidden="true"><svg viewBox="0 0 40 24"><path d="M20 3L9 12l11 9M33 3L22 12l11 9"/></svg></div>
        <div class="fs-arrow r" aria-hidden="true"><svg viewBox="0 0 40 24"><path d="M20 3l11 9-11 9M7 3l11 9-11 9"/></svg></div>
        <div class="fs-eye" aria-hidden="true"><svg viewBox="0 0 40 24"><path d="M2 12C8 4 15 2 20 2s12 2 18 10c-6 8-13 10-18 10S8 20 2 12z"/><circle cx="20" cy="12" r="4.5"/></svg></div>
        <div class="fs-check" aria-hidden="true">&#10003;</div>
    </div>
    <p class="fs-act" lang="sd" dir="rtl" aria-live="assertive"></p>
    <p class="fs-msg" role="status" aria-live="polite">Camera aur face model load ho rahe hain...</p>
    <button type="button" class="fs-btn" disabled>{{ $btn }}</button>
    <div class="fs-tips"><span>&#9728; Achi roshni</span><span>&#128065; Palak jhapkayen</span><span>&#8596; Sir ghumayen</span><span>&#128083; Chashma/mask hata den</span></div>
    <form method="POST" action="{{ $action }}">
        @csrf
        <input type="hidden" name="descriptor_json">
        <input type="hidden" name="liveness">
        <input type="hidden" name="lat">
        <input type="hidden" name="lng">
        <input type="hidden" name="acc">
        @if (!empty($geo))<input type="hidden" name="snapshot">@endif
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="{{ asset('js/face-scan.js') }}?v={{ @filemtime(public_path('js/face-scan.js')) }}"></script>
