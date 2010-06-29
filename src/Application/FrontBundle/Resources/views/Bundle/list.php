<?php $view->extend('FrontBundle::layout') ?>

Bundles list

<?php foreach($bundles as $bundle): ?>
    <?php echo $bundle->getName(); ?>
<?php endforeach; ?>
