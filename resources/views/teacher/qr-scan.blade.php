@extends('layouts.app')

@section('title', 'QR Attendance')

@section('content')
    <h1>QR Attendance</h1>
    <p><a href="{{ route('teacher.dashboard', [], false) }}">Dashboard</a></p>

    @if (! $teacher)
        <p style="color: red;">Aapke account se koi teacher record linked nahi hai.</p>
    @elseif (! $canScan)
        <p style="color: red;">QR attendance sirf school ke din aur waqt mein ho sakti hai.</p>
    @else
        <p>Student ka QR code camera ke saamne rakhein.</p>
        <video id="video" playsinline muted style="width: 100%; max-width: 420px;"></video>
        <canvas id="canvas" hidden></canvas>
        <p id="result" style="font-weight: bold;"></p>

        <p>Camera na chale to code khud likhein:</p>
        <input type="text" id="manual_code" maxlength="32" autocapitalize="characters">
        <button type="button" id="manual_submit">Mark Present</button>

        <p>Ya gallery/screenshot se QR image select karein:</p>
        <input type="file" id="qr_file" accept="image/*">

        <h2>Abhi ki scans</h2>
        <ul id="log"></ul>

        <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
        <script>
            const SCAN_URL = @json(route('teacher.qr.scan.store', [], false));
            const CSRF = @json(csrf_token());
            const SAME_CODE_MS = 5000;
            const TICK_MS = 250;
            const MAX_WIDTH = 640;
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            const result = document.getElementById('result');
            const log = document.getElementById('log');
            let busy = false, lastCode = '', lastAt = 0, lastTick = 0;

            function show(message, ok) {
                result.textContent = message;
                result.style.color = ok ? 'green' : 'red';
            }

            function addLog(message) {
                const li = document.createElement('li');
                li.textContent = new Date().toLocaleTimeString() + ' - ' + message;
                log.prepend(li);
                while (log.children.length > 20) { log.lastChild.remove(); }
            }

            async function submitCode(code) {
                if (busy) { return; }
                busy = true;
                try {
                    const res = await fetch(SCAN_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                        },
                        body: JSON.stringify({ qr_code: code }),
                    });
                    let data = null;
                    try { data = await res.json(); } catch (e) { data = null; }
                    const message = (data && data.message) ? data.message : 'Server error (' + res.status + ')';
                    show(message, res.ok);
                    addLog(message);
                } catch (e) {
                    show('Network masla hai, dobara try karein.', false);
                } finally {
                    busy = false;
                }
            }

            function tick(ts) {
                if (ts - lastTick >= TICK_MS && !busy && video.readyState === video.HAVE_ENOUGH_DATA) {
                    lastTick = ts;
                    const scale = Math.min(1, MAX_WIDTH / video.videoWidth);
                    canvas.width = Math.round(video.videoWidth * scale);
                    canvas.height = Math.round(video.videoHeight * scale);
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const found = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });
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
                    show('QR reader load nahi hua. Internet check karein ya code khud likhein.', false);
                    return;
                }
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    show('Yeh browser camera support nahi karta (HTTPS zaroori hai).', false);
                    return;
                }
                try {
                    video.srcObject = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                    await video.play();
                    requestAnimationFrame(tick);
                } catch (e) {
                    show('Camera nahi khul saka. Permission dein ya code khud likhein.', false);
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
                const found = jsQR(data.data, data.width, data.height, { inversionAttempts: 'dontInvert' });
                if (found && found.data) {
                    submitCode(found.data.trim().toUpperCase());
                } else {
                    show('QR image mein code nahi mila.', false);
                }
            };
            img.onerror = function () { show('Image load nahi ho saki.', false); };
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
