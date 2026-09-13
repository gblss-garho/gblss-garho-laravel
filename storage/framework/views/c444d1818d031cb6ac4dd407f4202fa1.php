<?php $__env->startSection('title', 'GBLSS Garho'); ?>

<?php $__env->startSection('content'); ?>
    <h1>GBLSS Garho — Laravel skeleton is live.</h1>
    <p>Phase 1 complete. Public site content migrates in Phase 7.</p>
    <p><a href="<?php echo e(route('login', [], false)); ?>">Go to Login</a></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /workspaces/gblss-garho-laravel/resources/views/pages/home.blade.php ENDPATH**/ ?>