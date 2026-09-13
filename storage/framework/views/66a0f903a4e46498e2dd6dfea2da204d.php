<?php $__env->startSection('title', 'Attendance'); ?>

<?php $__env->startSection('content'); ?>
    <h1>Teacher Attendance</h1>

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

    <?php if(! $isSchoolDay): ?>
        <p>Aaj school off hai (Saturday/Sunday).</p>
    <?php elseif(! $isFaceEnrolled): ?>
        <p>Attendance ke liye pehle apna chehra enroll karna zaroori hai.</p>
        <p><a href="<?php echo e(route('teacher.face.enroll', [], false)); ?>">Chehra Enroll Karen</a></p>
    <?php else: ?>
        <p>Check-in window: 8:00–8:30 AM. Check-out window: school khatam hone se 15 minute pehle.</p>

        <p>
            Check-in status:
            <?php echo e($record && $record->check_in_at ? $record->check_in_at->format('h:i A') : 'Abhi nahi hua'); ?>

        </p>

        <?php if($canCheckIn && ! ($record && $record->check_in_at)): ?>
            <div id="checkin-section">
                <video id="checkin-video" width="320" height="240" autoplay muted playsinline style="background:#000"></video>
                <br>
                <button id="checkin-capture-btn" type="button" disabled>Face se Check In Karen</button>
                <p id="checkin-status"></p>
                <form id="checkin-form" method="POST" action="<?php echo e(route('teacher.attendance.checkin', [], false)); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="descriptor_json" id="checkin-descriptor">
                </form>
            </div>
        <?php endif; ?>

        <p>
            Check-out status:
            <?php echo e($record && $record->check_out_at ? $record->check_out_at->format('h:i A') : 'Abhi nahi hua'); ?>

        </p>

        <?php if($canCheckOut && $record && $record->check_in_at && ! $record->check_out_at): ?>
            <div id="checkout-section">
                <video id="checkout-video" width="320" height="240" autoplay muted playsinline style="background:#000"></video>
                <br>
                <button id="checkout-capture-btn" type="button" disabled>Face se Check Out Karen</button>
                <p id="checkout-status"></p>
                <form id="checkout-form" method="POST" action="<?php echo e(route('teacher.attendance.checkout', [], false)); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="descriptor_json" id="checkout-descriptor">
                </form>
            </div>
        <?php endif; ?>

        <?php if(($canCheckIn && ! ($record && $record->check_in_at)) || ($canCheckOut && $record && $record->check_in_at && ! $record->check_out_at)): ?>
            <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
            <script>
                const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';

                async function loadModels() {
                    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                }

                async function setupCamera(videoId, btnId, statusId) {
                    const video = document.getElementById(videoId);
                    const btn = document.getElementById(btnId);
                    const status = document.getElementById(statusId);
                    if (! video || ! btn) return;

                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                        video.srcObject = stream;
                        btn.disabled = false;
                        status.textContent = 'Camera taiyar hai.';
                    } catch (err) {
                        status.textContent = 'Camera nahi khul saka: ' + err.message;
                    }
                }

                function wireCapture(videoId, btnId, statusId, descriptorFieldId, formId) {
                    const video = document.getElementById(videoId);
                    const btn = document.getElementById(btnId);
                    const status = document.getElementById(statusId);
                    if (! video || ! btn) return;

                    btn.addEventListener('click', async () => {
                        status.textContent = 'Chehra dhoonda ja raha hai...';
                        const detection = await faceapi
                            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                            .withFaceLandmarks()
                            .withFaceDescriptor();

                        if (! detection) {
                            status.textContent = 'Koi chehra nazar nahi aaya. Seedha camera mein dekhen.';
                            return;
                        }

                        document.getElementById(descriptorFieldId).value = JSON.stringify(Array.from(detection.descriptor));
                        status.textContent = 'Chehra mil gaya, submit ho raha hai...';
                        document.getElementById(formId).submit();
                    });
                }

                (async () => {
                    await loadModels();
                    await setupCamera('checkin-video', 'checkin-capture-btn', 'checkin-status');
                    await setupCamera('checkout-video', 'checkout-capture-btn', 'checkout-status');
                    wireCapture('checkin-video', 'checkin-capture-btn', 'checkin-status', 'checkin-descriptor', 'checkin-form');
                    wireCapture('checkout-video', 'checkout-capture-btn', 'checkout-status', 'checkout-descriptor', 'checkout-form');
                })();
            </script>
        <?php endif; ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /workspaces/gblss-garho-laravel/resources/views/teacher/attendance.blade.php ENDPATH**/ ?>