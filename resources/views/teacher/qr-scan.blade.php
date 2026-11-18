@extends('layouts.portal')

@section('title', 'QR Attendance')

@section('content')
    <h1>QR Attendance</h1>
    <p class="sd sd-note">شاگرد جي ID ڪارڊ جو QR اسڪين ڪري حاضري لڳايو.</p>
    <p><a href="{{ route('teacher.dashboard', [], false) }}">Dashboard</a></p>

    @if (! $teacher)
        <p style="color: red;">توهان جو لاگ ان ڪنهن ٽيچر رڪارڊ سان لنڪ ٿيل ناهي. ايڊمن کان پنهنجو AUTH EMAIL فيلڊ سيٽ ڪرايو.</p>
    @elseif (! $canScan)
        <p style="color: red;">QR attendance sirf school ke din aur waqt mein ho sakti hai.</p>
    @else
        <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
        <style>
            .fs-qr .fs-cam video { transform: none; }
            .fs-qr .fs-aim { position: absolute; inset: 24%; border-radius: 22px; pointer-events: none; z-index: 2; opacity: .85;
                background:
                  linear-gradient(#FCD34D,#FCD34D) top left / 22px 3px no-repeat, linear-gradient(#FCD34D,#FCD34D) top left / 3px 22px no-repeat,
                  linear-gradient(#FCD34D,#FCD34D) top right / 22px 3px no-repeat, linear-gradient(#FCD34D,#FCD34D) top right / 3px 22px no-repeat,
                  linear-gradient(#FCD34D,#FCD34D) bottom left / 22px 3px no-repeat, linear-gradient(#FCD34D,#FCD34D) bottom left / 3px 22px no-repeat,
                  linear-gradient(#FCD34D,#FCD34D) bottom right / 22px 3px no-repeat, linear-gradient(#FCD34D,#FCD34D) bottom right / 3px 22px no-repeat; }
            .fs-qr[data-state=scan] .fs-aim, .fs-qr[data-state=ok] .fs-aim, .fs-qr[data-state=fail] .fs-aim { display: none; }
            .fs-qr .fs-who { font-weight: 700; font-size: 17px; min-height: 22px; margin: 10px 0 0; }
            .qr-more { max-width: 420px; margin: 12px auto; font-size: 14px; }
            .qr-more input[type=text] { padding: 9px 10px; border: 1px solid #D9D3BD; border-radius: 12px; font: inherit; }
            .qr-more button { font: inherit; padding: 9px 14px; border: 0; border-radius: 12px; background: #0F5132; color: #fff; }
        </style>

        <div class="fs fs-qr" id="qrfs" data-state="loading">
            <h2 class="fs-title">Student QR Attendance</h2>
            <p class="fs-sub">ID card ka QR gol frame ke andar rakhein.</p>
            <div class="fs-stage">
                <div class="fs-cam"><video id="video" autoplay muted playsinline></video></div>
                <div class="fs-aim" aria-hidden="true"></div>
                <svg class="fs-svg" viewBox="0 0 100 100" aria-hidden="true">
                    <circle class="fs-ticks" cx="50" cy="50" r="46"/>
                    <circle class="fs-arc" cx="50" cy="50" r="46"/>
                </svg>
                <div class="fs-check" aria-hidden="true">&#10003;</div>
            </div>
            <p class="fs-act" id="act" lang="sd" dir="rtl" aria-live="assertive"></p>
            <p class="fs-who" id="who"></p>
            <p class="fs-msg" id="result" role="status" aria-live="polite">Camera load ho raha hai...</p>
            <div class="fs-tips"><span>&#9728; Achi roshni</span><span>15-25 cm door</span><span>QR seedha rakhein</span></div>
        </div>
        <canvas id="canvas" hidden></canvas>

        <details class="qr-more">
            <summary>Camera na chale ya QR kharab ho</summary>
            <p>Code khud likhein:</p>
            <input type="text" id="manual_code" maxlength="64" autocapitalize="characters">
            <button type="button" id="manual_submit">Mark Present</button>
            <p>Ya gallery/screenshot se QR image select karein:</p>
            <input type="file" id="qr_file" accept="image/*">
        </details>

        <h2>Abhi ki scans</h2>
        <ul id="log"></ul>

        <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
        <script>
            const SCAN_URL = @json(route('teacher.qr.scan.store', [], false));
            const CSRF = @json(csrf_token());
            const SAME_CODE_MS = 5000;
            const TICK_MS = 250;
            const MAX_WIDTH = 960;
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            const result = document.getElementById('result');
            const box = document.getElementById('qrfs');
            const act = document.getElementById('act');
            const who = document.getElementById('who');
            const MIN_PROCESS_MS = 1100, HOLD_MS = 2400;
            const SD = { marked: 'اسڪين مڪمل ٿي وئي', already_present: 'هي شاگرد اڳ ۾ ئي حاضر آهي', error: 'اسڪين ناڪام ٿي', net: 'نيٽ ورڪ جو مسئلو آهي' };
            function setState(st) { box.setAttribute('data-state', st); }
            const log = document.getElementById('log');
            let busy = false, lastCode = '', lastAt = 0, lastTick = 0;

            function show(message) { result.textContent = message; }

            function addLog(message) {
                const li = document.createElement('li');
                li.textContent = new Date().toLocaleTimeString() + ' - ' + message;
                log.prepend(li);
                while (log.children.length > 20) { log.lastChild.remove(); }
            }

            const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

            async function submitCode(code) {
                if (busy) { return; }
                busy = true;
                setState('scan');
                act.textContent = 'اسڪين ٿي رهي آهي…';
                who.textContent = '';
                show('Scan ho raha hai, intezar karein...');
                const t0 = Date.now();
                let ok = false, state = 'error', message = '', name = '';
                try {
                    const res = await fetch(SCAN_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ qr_code: code }),
                    });
                    let data = null;
                    try { data = await res.json(); } catch (e) { data = null; }
                    message = (data && data.message) ? data.message : 'Server error (' + res.status + ')';
                    ok = res.ok;
                    state = ok ? ((data && data.status) || 'marked') : 'error';
                    name = ok && data && data.student ? data.student.name + ' (' + data.student.class + ')' : '';
                } catch (e) {
                    state = 'net'; message = 'Network masla hai, dobara try karein.';
                }
                const wait = MIN_PROCESS_MS - (Date.now() - t0);
                if (wait > 0) { await sleep(wait); }
                setState(ok ? 'ok' : 'fail');
                act.textContent = SD[state] || SD.error;
                who.textContent = name;
                show(message);
                addLog(message);
                await sleep(HOLD_MS);
                setState('idle');
                act.textContent = '';
                who.textContent = '';
                show('Agla QR card camera ke saamne rakhein.');
                lastAt = Date.now();
                busy = false;
            }

            function tick(ts) {
                if (ts - lastTick >= TICK_MS && !busy && video.readyState === video.HAVE_ENOUGH_DATA) {
                    lastTick = ts;
                    const scale = Math.min(1, MAX_WIDTH / video.videoWidth);
                    canvas.width = Math.round(video.videoWidth * scale);
                    canvas.height = Math.round(video.videoHeight * scale);
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const found = jsQR(img.data, img.width, img.height, { inversionAttempts: 'attemptBoth' });
                    if (found && found.data) {
                        const code = found.data.trim().toUpperCase();
                        const now = Date.now();
                        if (code !== lastCode || now - lastAt > SAME_CODE_MS) {
                            lastCode = code;
                            lastAt = now;
                            submitCode(code);
                        }
                    }
                }
                requestAnimationFrame(tick);
            }

            async function startCamera() {
                if (typeof jsQR === 'undefined') {
                    show('QR reader load nahi hua. Internet check karein ya code khud likhein.'); setState('fail');
                    return;
                }
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    show('Yeh browser camera support nahi karta (HTTPS zaroori hai).'); setState('fail');
                    return;
                }
                try {
                    video.srcObject = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } } });
                    await video.play();
                    setState('idle');
                    show('QR card gol frame ke andar rakhein.');
                    requestAnimationFrame(tick);
                } catch (e) {
                    show('Camera nahi khul saka. Permission dein ya code khud likhein.'); setState('fail');
                }
            }

            document.getElementById('manual_submit').addEventListener('click', function () {
                const input = document.getElementById('manual_code');
                const code = input.value.trim().toUpperCase();
                if (code) { submitCode(code); input.value = ''; }
            });

            document.getElementById('qr_file').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) { return; }
            const img = new Image();
            img.onload = function () {
                const scale = Math.min(1, MAX_WIDTH / img.width);
                canvas.width = Math.round(img.width * scale);
                canvas.height = Math.round(img.height * scale);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                const data = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const found = jsQR(data.data, data.width, data.height, { inversionAttempts: 'attemptBoth' });
                if (found && found.data) {
                    submitCode(found.data.trim().toUpperCase());
                } else {
                    show('QR image mein code nahi mila.');
                }
            };
            img.onerror = function () { show('Image load nahi ho saki.'); };
            img.src = URL.createObjectURL(file);
            e.target.value = '';
        });

        window.addEventListener('pagehide', function () {
                if (video.srcObject) { video.srcObject.getTracks().forEach(function (t) { t.stop(); }); }
            });

            startCamera();
        </script>
    @endif
@endsection
