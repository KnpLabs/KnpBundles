<?php $view->extend('S2bBundle::layout') ?>
<?php $view->slots->set('current_menu_item', 'search') ?>
<?php $view->slots->set('h1', 'Search <span>Bundles</span>') ?>
<?php $view->slots->set('title', $view->slots->get('title', 'Search Bundles')) ?>
<?php $view->slots->set('slogan', $view->slots->get('slogan', 'Find the Bundle you need')) ?>
<?php $view->slots->set('description', $view->slots->get('description', 'Find the Bundle you need')) ?>

<?php $view->slots->output('_content', 'Please use the search input at the top right.<br />The engine will search in Bundle name, Bundle description and developer name.') ?>
