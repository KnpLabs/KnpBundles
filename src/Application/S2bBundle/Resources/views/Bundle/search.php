<?php $view->extend('S2bBundle::layout') ?>
<?php $view->slots->set('current_menu_item', 'search') ?>
<?php $view->slots->set('h1', 'Search Bundles') ?>
<?php $view->slots->set('title', $view->slots->get('title', 'Search Bundles')) ?>
<?php $view->slots->set('slogan', $view->slots->get('slogan', 'Find the Bundle you need')) ?>
<?php $view->slots->set('description', $view->slots->get('description', 'Find the Bundle you need')) ?>

<?php $view->slots->output('_content', 'You should enter a query in the search input.') ?>
