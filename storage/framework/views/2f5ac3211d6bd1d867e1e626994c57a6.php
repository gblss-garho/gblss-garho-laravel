<?php $__env->startSection('title', 'Enroll Face'); ?>

<?php $__env->startSection('content'); ?>
    <h1>Chehra Enroll Karen</h1>

    <?php if(session('status')): ?>
        <p style="color:green"><?php echo e(session('status')); ?></p>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div style="color:red">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($error); ?></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php if($isEnrolled): ?>
        <p>Aapka chehra pehle se enroll hai. Dobara enroll karne se purana overwrite ho jayega.</p>
    <?php endif; ?>

    <p id="status-text">Camera load ho rahi hai...</p>

    <video id="video" width="320" height="240" autoplay muted playsinline style="background:#000"></video>
    <br>
    <button id="capture-btn" type="button" disabled>Chehra Capture Karen</button>

    <form id="enroll-form" method="POST" action="<?php echo e(route('teacher.face.enroll.store', [], false)); ?>">
        <?php echo csrf_field(); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /workspaces/gblss-garho-laravel/resources/views/teacher/face-enroll.blade.php ENDPATH**/ ?>