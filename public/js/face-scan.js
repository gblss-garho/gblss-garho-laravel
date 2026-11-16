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
        var FAST = function () { return new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.5 }); };

        function dist(a, b) { return Math.hypot(a.x - b.x, a.y - b.y); }
        function ear(eye) { // eye aspect ratio from the 6 eye landmarks
            return (dist(eye[1], eye[5]) + dist(eye[2], eye[4])) / (2 * dist(eye[0], eye[3]));
        }
        function metrics(lm) {
            var jaw = lm.getJawOutline(), nose = lm.getNose();
            var w = jaw[16].x - jaw[0].x;
            return {
                ear: (ear(lm.getLeftEye()) + ear(lm.getRightEye())) / 2,
                yaw: w > 0 ? (nose[3].x - jaw[0].x) / w : 0.5 // 0.5 = facing the camera
            };
        }
        async function frame() {
            var d = await faceapi.detectSingleFace(video, FAST()).withFaceLandmarks();
            return d ? metrics(d.landmarks) : null;
        }

        var TEXT = {
            blink: 'Ab ek dafa PALAK jhapkayen',
            turn_left: 'Sir ko screen ke BAAYEN taraf ghumayen',
            turn_right: 'Sir ko screen ke DAAYEN taraf ghumayen'
        };

        // Resolves true when the action is seen within `ms`.
        async function doChallenge(name, ms) {
            var t0 = Date.now(), open = 0, closedSeen = false;
            while (Date.now() - t0 < ms) {
                var m = await frame();
                if (!m) { continue; }
                if (name === 'blink') {
                    if (m.ear > open) { open = m.ear; }
                    if (open > 0.2 && m.ear < open * 0.74) { closedSeen = true; }
                    else if (closedSeen && m.ear > open * 0.86) { return true; }
                } else if (name === 'turn_left') { // nose moves toward the camera-left edge (raw image)
                    if (m.yaw > 0.64) { return true; }
                } else if (name === 'turn_right') {
                    if (m.yaw < 0.36) { return true; }
                }
            }
            return false;
        }

        async function waitFrontal(ms) {
            var t0 = Date.now();
            while (Date.now() - t0 < ms) {
                var m = await frame();
                if (m && m.yaw > 0.42 && m.yaw < 0.58) { return true; }
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

            state('scan', 'Chehra dhoondha ja raha hai... seedha dekhen.');
            var first = null, t0 = Date.now();
            while (Date.now() - t0 < 6000 && !first) {
                first = await frame();
                if (!first) { await sleep(150); }
            }
            if (!first) { return fail('Chehra nahi mila. Roshni mein, seedha camera ki taraf dekhen aur dobara try karen.'); }

            for (var i = 0; i < challenges.length; i++) {
                state('scan', 'Liveness ' + (i + 1) + '/' + challenges.length + ': ' + (TEXT[challenges[i]] || ''));
                var ok = await doChallenge(challenges[i], 7000);
                if (!ok) { return fail('Liveness check pass nahi hua (' + (TEXT[challenges[i]] || challenges[i]) + '). Dobara try karen.'); }
                state('scan', 'Theek hai! Ab seedha camera mein dekhen...');
                if (!(await waitFrontal(4000))) { return fail('Seedha dekhna zaroori hai. Dobara try karen.'); }
            }

            state('scan', 'Chehra pehchana ja raha hai... seedha dekhen.');
            var found = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptor();
            if (!found) { return fail('Chehra nahi mila. Dobara try karen.'); }

            field.value = JSON.stringify(Array.prototype.slice.call(found.descriptor));
            liveField.value = JSON.stringify({ c: challenges, ok: true });
            state('ok', 'Asli insan ki tasdeeq ho gayi. Save ho raha hai...');
            await sleep(900);
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
