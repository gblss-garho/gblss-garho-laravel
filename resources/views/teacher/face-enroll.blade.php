@extends('layouts.app')

@section('title', 'Enroll Face')

@section('content')
    <h1>Chehra Enroll Karen</h1>

    @if (session('status'))
        <p style="color:green">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div style="color:red">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($isEnrolled)
        <p>Aapka chehra pehle se enroll hai. Dobara enroll karne se purana overwrite ho jayega.</p>
    @endif

    <p id="status-text">Camera load ho rahi hai...</p>

    <video id="video" width="320" height="240" autoplay muted playsinline style="background:#000"></video>
    <br>
    <button id="capture-btn" type="button" disabled>Chehra Capture Karen</button>

    <form id="enroll-form" method="POST" action="{{ route('teacher.face.enroll.store', [], false) }}">
        @csrf
        <input type="hidden" name="descriptor_json" id="descriptor_json">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';
        const video = document.getElementById('video');
        const statusText = document.getElementById('status-text');
        const captureBtn = document.getElementById('capture-btn');

        async function setup() {
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

                const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                video.srcObject = stream;

                statusText.textContent = 'Camera taiyar hai. Seedha camera mein dekhen aur capture karen.';
                captureBtn.disabled = false;
            } catch (err) {
                statusText.textContent = 'Camera ya models load nahi ho sake: ' + err.message;
            }
        }

        captureBtn.addEventListener('click', async () => {
            statusText.textContent = 'Chehra dhoonda ja raha hai...';
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (! detection) {
                statusText.textContent = 'Koi chehra nazar nahi aaya. Seedha camera mein dekhen aur dobara try karen.';
                return;
            }

            document.getElementById('descriptor_json').value = JSON.stringify(Array.from(detection.descriptor));
            statusText.textContent = 'Chehra mil gaya, save ho raha hai...';
            document.getElementById('enroll-form').submit();
        });

        setup();
    </script>
@endsection
