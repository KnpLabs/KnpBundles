<?php $view->extend('S2bBundle::layout') ?>

<?php $view->slots->set('current_menu_item', 'homepage') ?>
<?php $view->slots->set('title', 'Home') ?>
<?php $view->slots->set('description', 'Comprehensive list of Symfony2 Bundles ordered by relevance and integrated with GitHub') ?>
<?php $view->slots->set('slogan', 'Comprehensive list of Symfony2 Bundles ordered by relevance and integrated with GitHub') ?>

<h2>Welcome to the community driven Symfony2 Bundles website!</h2>
As for now, <strong><?php echo $nbBundles ?></strong> Symfony2 Bundles are indexed. <a href="<?php echo $view->router->generate('all') ?>">View all bundles</a> or <a href="<?php echo $view->router->generate('search') ?>">search for bundles</a>
<br />
Symfony2Bundles is a spontaneous community initiative! Its purpose is to help us to find the best Bundles.<br />
It works by querying the GitHub API to discover new Bundles and update existing ones. Don't try to claim or describe your Bundles on this website! This task is done automatically.
