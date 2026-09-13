<?php $__env->startSection('title', 'Teacher Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <h1>Teacher Dashboard</h1>
    <p>Logged in as: <?php echo e(auth()->user()->name); ?> (<?php echo e(auth()->user()->email); ?>)</p>
    <p><a href="<?php echo e(route('teacher.attendance.index', [], false)); ?>">Attendance (Check In / Check Out)</a></p>
    <form method="POST" action="<?php echo e(route('logout', [], false)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit">Logout</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /workspaces/gblss-garho-laravel/resources/views/dashboards/teacher.blade.php ENDPATH**/ ?>