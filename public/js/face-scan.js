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
        var FAST = function () { return new faceapi.TinyFaceDetectorOptions({ inputSize: 128, scoreThreshold: 0.4 }); };
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
        // One small camera picture, kept (admin only) so a check-in made away from school is visible in the background.
        function snapshot() {
            try {
                var w = video.videoWidth || 320, h = video.videoHeight || 240;
                var c = document.createElement('canvas');
                c.width = 320;
                c.height = Math.max(1, Math.round(h * 320 / w));
                c.getContext('2d').drawImage(video, 0, 0, c.width, c.height);
                return c.toDataURL('image/jpeg', 0.6);
            } catch (e) { return ''; }
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
            var t0 = Date.now(), hist = [], n = 0;
            while (Date.now() - t0 < ms) {
                var m = await frame();
                if (!m) { continue; }
                if (name === 'blink') {
                    // A blink lasts only ~150 ms, so compare each frame with the best open-eye value of the last few frames.
                    n++;
                    var top = hist.length ? Math.max.apply(null, hist) : 0;
                    if (n > 2 && top > 0.17 && (m.ear < top * 0.85 || top - m.ear > 0.04)) { return true; }
                    hist.push(m.ear);
                    if (hist.length > 8) { hist.shift(); }
                } else if (name === 'turn_left') {
                    if (m.yaw > 0.6) { return true; }
                } else if (name === 'turn_right') {
                    if (m.yaw < 0.4) { return true; }
                }
            }
            return false;
        }

        // ---- School location (geofence) ----
        var GEO = null;
        if (root.getAttribute('data-geo-lat')) {
            GEO = {
                lat: parseFloat(root.getAttribute('data-geo-lat')),
                lng: parseFloat(root.getAttribute('data-geo-lng')),
                radius: parseInt(root.getAttribute('data-geo-radius'), 10) || 200
            };
        }
        var GEO_TXT = {
            denied: '\u067e\u0646\u06be\u0646\u062c\u064a \u0641\u0648\u0646 \u062c\u064a \u0644\u0648\u06aa\u064a\u0634\u0646 (GPS) \u062c\u064a \u0627\u062c\u0627\u0632\u062a \u068f\u064a\u0648 \u06fd \u067b\u064a\u0647\u0631 \u06aa\u0648\u0634\u0634 \u06aa\u0631\u064a\u0648.',
            weak: 'GPS \u062c\u064a \u062f\u0631\u0633\u062a\u06af\u064a \u06aa\u0645\u0632\u0648\u0631 \u0622\u0647\u064a. \u06a9\u0644\u064a\u0644 \u062c\u0627\u0621\u0650 \u062a\u064a \u0627\u0686\u064a \u067b\u064a\u0647\u0631 \u06aa\u0648\u0634\u0634 \u06aa\u0631\u064a\u0648.',
            outA: '\u0627\u0648\u0647\u0627\u0646 \u0647\u0646 \u0648\u0642\u062a \u0627\u0633\u06aa\u0648\u0644 \u062c\u064a \u062c\u06b3\u0647\u0647 \u06a9\u0627\u0646 \u067b\u0627\u0647\u0631 \u0622\u0647\u064a\u0648. \u062d\u0627\u0636\u0631\u064a \u0631\u06b3\u0648 \u0627\u0633\u06aa\u0648\u0644 \u062c\u064a ',
            outB: ' \u0645\u064a\u067d\u0631\u0646 \u0627\u0646\u062f\u0631 \u0644\u06b3\u064a \u0633\u06af\u0647\u064a \u067f\u064a (\u0627\u0648\u0647\u0627\u0646 \u0627\u0633\u06aa\u0648\u0644 \u06a9\u0627\u0646 \u062a\u0642\u0631\u064a\u0628\u0646 ',
            outC: ' \u0645\u064a\u067d\u0631 \u067e\u0631\u064a \u0622\u0647\u064a\u0648).'
        };
        function metersBetween(a1, o1, a2, o2) {
            var R = 6371000, rad = Math.PI / 180;
            var dp = (a2 - a1) * rad, dl = (o2 - o1) * rad;
            var h = Math.sin(dp / 2) * Math.sin(dp / 2) + Math.cos(a1 * rad) * Math.cos(a2 * rad) * Math.sin(dl / 2) * Math.sin(dl / 2);
            return 2 * R * Math.asin(Math.min(1, Math.sqrt(h)));
        }
        function getPosition() {
            return new Promise(function (resolve, reject) {
                if (!navigator.geolocation) { reject({ code: 1 }); return; }
                navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
            });
        }
        // Returns true when allowed (and fills the hidden fields); otherwise shows the Sindhi message and returns false.
        async function checkLocation() {
            if (!GEO) { return true; }
            state('scan', 'School ki location check ho rahi hai...');
            var pos;
            try { pos = await getPosition(); }
            catch (e) {
                act.textContent = GEO_TXT.denied;
                fail('Location nahi mili. Phone ki location (GPS) on karen aur browser ko ijazat den.');
                return false;
            }
            var c = pos.coords;
            form.querySelector('input[name=lat]').value = c.latitude;
            form.querySelector('input[name=lng]').value = c.longitude;
            form.querySelector('input[name=acc]').value = Math.round(c.accuracy || 0);
            if (c.accuracy > 150) {
                act.textContent = GEO_TXT.weak;
                fail('GPS ki darstagi kam hai. Khuli jagah par aa kar dobara try karen.');
                return false;
            }
            var d = Math.round(metersBetween(c.latitude, c.longitude, GEO.lat, GEO.lng));
            if (d > GEO.radius) {
                act.textContent = GEO_TXT.outA + GEO.radius + GEO_TXT.outB + d + GEO_TXT.outC;
                fail('Aap abhi school ki location se bahar hain (' + d + ' m door).');
                return false;
            }
            return true;
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

            if (!(await checkLocation())) { return; }

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
                var ok = await doChallenge(challenges[i], 12000);
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
            var snapField = form.querySelector('input[name=snapshot]');
            if (snapField) { snapField.value = snapshot(); }
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
