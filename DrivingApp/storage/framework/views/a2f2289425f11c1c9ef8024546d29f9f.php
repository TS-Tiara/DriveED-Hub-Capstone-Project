<form action="<?php echo e($route); ?>" method="POST" style="display:inline;">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PATCH'); ?>
    <button type="submit"><?php echo e($label); ?></button>
</form>
<?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\partials\_toggle-button.blade.php ENDPATH**/ ?>