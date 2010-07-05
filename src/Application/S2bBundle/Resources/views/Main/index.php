<?php $view->extend('S2bBundle::layout') ?>

<?php $view->slots->set('current_menu_item', 'homepage') ?>
<?php $view->slots->set('title', 'Home') ?>
<?php $view->slots->set('description', 'Comprehensive list of Symfony2 Bundles ordered by relevance and integrated with GitHub') ?>
<?php $view->slots->set('slogan', 'Comprehensive list of Symfony2 Bundles ordered by relevance and integrated with GitHub') ?>

As for now, <strong><?php echo $nbBundles ?></strong> Symfony2 Bundles are indexed.
