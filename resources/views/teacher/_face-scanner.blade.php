{{-- Round camera frame with a status ring. Params: $action, $btn, $title, $sub, $challenges (liveness), $initialFail (optional) --}}
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
<div class="fs" data-face-scan data-challenges="{{ implode(',', $challenges ?? []) }}" data-state="loading" @if (!empty($initialFail)) data-initial-fail="{{ $initialFail }}" @endif>
    <h2 class="fs-title">{{ $title }}</h2>
    <p class="fs-sub">{{ $sub }}</p>
    <div class="fs-stage">
        <div class="fs-cam"><video autoplay muted playsinline></video></div>
        <svg class="fs-svg" viewBox="0 0 100 100" aria-hidden="true">
            <circle class="fs-ticks" cx="50" cy="50" r="46"/>
            <circle class="fs-arc" cx="50" cy="50" r="46"/>
        </svg>
        <div class="fs-check" aria-hidden="true">&#10003;</div>
    </div>
    <p class="fs-act sd" dir="rtl" aria-live="assertive"></p>
    <p class="fs-msg" role="status" aria-live="polite">Camera aur face model load ho rahe hain...</p>
    <button type="button" class="fs-btn" disabled>{{ $btn }}</button>
    <div class="fs-tips"><span>&#9728; Achi roshni</span><span>&#128065; Palak jhapkayen</span><span>&#8596; Sir ghumayen</span><span>&#128083; Chashma/mask hata den</span></div>
    <form method="POST" action="{{ $action }}">
        @csrf
        <input type="hidden" name="descriptor_json">
        <input type="hidden" name="liveness">
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="{{ asset('js/face-scan.js') }}"></script>
