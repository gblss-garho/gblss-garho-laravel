/* GBLSS face scanner: round camera frame with a status ring.
   idle = soft gold, scan = amber light running round the ring, ok = green, fail = red. */
(function () {
    var MODELS = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';
    var SCAN_MS = 7000;

    function sleep(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

    function init(root) {
        var video = root.querySelector('video');
        var msg = root.querySelector('.fs-msg');
        var btn = root.querySelector('.fs-btn');
        var form = root.querySelector('form');
        var field = form.querySelector('input[name=descriptor_json]');
        var idleLabel = btn.textContent;
        var busy = false;

        function state(s, text) {
            root.setAttribute('data-state', s);
            if (text) { msg.textContent = text; }
        }

        async function boot() {
            state('loading', 'Camera aur face model load ho rahe hain...');
            try {
                if (!window.faceapi) { throw new Error('Face library load nahi hui. Internet check karen.'); }
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODELS);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODELS);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODELS);
                var stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 640 } }, audio: false
                });
                video.srcObject = stream;
                await video.play();
                btn.disabled = false;
                var first = root.getAttribute('data-initial-fail');
                if (first) { state('fail', first); btn.textContent = 'Dobara Try Karen'; }
                else { state('idle', 'Camera taiyar hai. Chehra gol frame ke beech mein rakhen.'); }
            } catch (e) {
                state('fail', 'Camera ya model shuru nahi hua: ' + (e && e.message ? e.message : e));
            }
        }

        var challenges = (root.getAttribute('data-challenges') || '').split(',').filter(Boolean);
        var liveField = form.querySelector('input[name=liveness]');
        var FAST = function () { return new faceapi.TinyFaceDetectorOptions({ inputSize: 192, scoreThreshold: 0.45 }); };
        var act = root.querySelector('.fs-act');

        function dist(a, b) { return Math.hypot(a.x - b.x, a.y - b.y); }
        function ear(eye) { // eye aspect ratio from the 6 eye landmarks
            return (dist(eye[1], eye[5]) + dist(eye[2], eye[4])) / (2 * dist(eye[0], eye[3]));
        }
        function metricsOf(lm) {
            var jaw = lm.getJawOutline(), nose = lm.getNose();
            var w = jaw[16].x - jaw[0].x;
            return {
                ear: (ear(lm.getLeftEye()) + ear(lm.getRightEye())) / 2,
                yaw: w > 0 ? (nose[3].x - jaw[0].x) / w : 0.5 // 0.5 = facing the camera
            };
        }
        async function frame() {
            var d = await faceapi.detectSingleFace(video, FAST()).withFaceLandmarks();
            return d ? metricsOf(d.landmarks) : null;
        }

        // Sindhi instructions (the user's own right/left; the preview is mirrored like a mirror)
        var SD = {
            blink: '\u0627\u06a9\u064a\u0648\u0646 \u0647\u06aa \u0680\u064a\u0631\u0648 \u0687\u0646\u0680\u064a\u0648',
            turn_left: '\u067e\u0646\u06be\u0646\u062c\u064a \u0686\u06be\u0631\u064a \u06a9\u064a \u06a9\u0627\u067b\u064a \u067e\u0627\u0633\u064a \u06af\u06be\u0645\u0627\u064a\u0648',
            turn_right: '\u067e\u0646\u06be\u0646\u062c\u064a \u0686\u06be\u0631\u064a \u06a9\u064a \u0633\u0627\u0684\u064a \u067e\u0627\u0633\u064a \u06af\u06be\u0645\u0627\u064a\u0648'
        };

        // Resolves true when the action is seen within `ms`. Blink = eyes visibly closing against the open-eye baseline.
        async function doChallenge(name, ms) {
            var t0 = Date.now(), base = 0, n = 0;
            while (Date.now() - t0 < ms) {
                var m = await frame();
                if (!m) { continue; }
                if (name === 'blink') {
                    n++;
                    if (n <= 3 || m.ear > base) { base = Math.max(base, m.ear); }
                    if (n > 3 && base > 0.18 && m.ear < base * 0.8) { return true; }
                } else if (name === 'turn_left') {
                    if (m.yaw > 0.6) { return true; }
                } else if (name === 'turn_right') {
                    if (m.yaw < 0.4) { return true; }
                }
            }
            return false;
        }

        function fail(text) {
            state('fail', text);
            btn.textContent = 'Dobara Try Karen';
            btn.disabled = false;
            busy = false;
        }

        async function scan() {
            if (busy) { return; }
            busy = true;
            btn.disabled = true;
            act.textContent = '';
            root.removeAttribute('data-act');

            state('scan', 'Chehra dhoondha ja raha hai... seedha dekhen.');
            var first = null, t0 = Date.now();
            while (Date.now() - t0 < 6000 && !first) {
                first = await frame();
                if (!first) { await sleep(120); }
            }
            if (!first) { return fail('Chehra nahi mila. Roshni mein, seedha camera ki taraf dekhen aur dobara try karen.'); }

            for (var i = 0; i < challenges.length; i++) {
                act.textContent = SD[challenges[i]] || '';
                root.setAttribute('data-act', challenges[i]);
                state('scan', 'Liveness check: upar likha kaam karen.');
                var ok = await doChallenge(challenges[i], 9000);
                act.textContent = '';
                root.removeAttribute('data-act');
                if (!ok) { return fail('Liveness check pass nahi hua. Dobara try karen aur hidayat par amal karen.'); }
            }

            // Take the face fingerprint as soon as the face is roughly frontal again.
            state('scan', 'Theek hai! Ab seedha dekhen...');
            var found = null;
            t0 = Date.now();
            while (Date.now() - t0 < 5000) {
                var d = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                if (d) {
                    var yaw = metricsOf(d.landmarks).yaw;
                    if (yaw > 0.4 && yaw < 0.6) { found = d; break; }
                }
            }
            if (!found) { return fail('Seedha dekhna zaroori hai. Dobara try karen.'); }

            field.value = JSON.stringify(Array.prototype.slice.call(found.descriptor));
            liveField.value = JSON.stringify({ c: challenges, ok: true });
            state('ok', 'Asli insan ki tasdeeq ho gayi. Save ho raha hai...');
            await sleep(700);
            form.submit();
        }

        btn.addEventListener('click', scan);
        boot();
    }

    document.addEventListener('DOMContentLoaded', function () {
        var nodes = document.querySelectorAll('[data-face-scan]');
        for (var i = 0; i < nodes.length; i++) { init(nodes[i]); }
    });
})();
