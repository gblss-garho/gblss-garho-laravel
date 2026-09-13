<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>
    <h1>Login</h1>

    <?php if($errors->any()): ?>
        <div style="color:red">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($error); ?></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('login', [], false)); ?>">
        <?php echo csrf_field(); ?>
        <div>
            <label>Email</label>
            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label><input type="checkbox" name="remember"> Remember me</label>
        </div>
        <button type="submit">Login</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /workspaces/gblss-garho-laravel/resources/views/auth/login.blade.php ENDPATH**/ ?>