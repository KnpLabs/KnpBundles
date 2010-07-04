<?php $view->extend('S2bBundle::layout') ?>
<?php $view->slots->set('current_menu_item', 'search') ?>

<?php $view->slots->output('search_results', 'You should enter a query in the search input.') ?>

<?php $view->slots->set('h1', 'Search Bundles') ?>
<?php $view->slots->set('slogan', '') ?>
