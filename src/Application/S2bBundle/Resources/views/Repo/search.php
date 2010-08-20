<?php $view->extend('S2bBundle::layout') ?>
<?php $view['main_menu']['Search']->setIsCurrent(true) ?>
<?php $view['slots']->set('h1', 'Search') ?>
<?php $view['slots']->set('title', $view['slots']->get('title', 'Search Bundles and Projects')) ?>
<?php $view['slots']->set('slogan', $view['slots']->get('slogan', 'Search for Open Source Bundles and Projects')) ?>
<?php $view['slots']->set('description', $view['slots']->get('description', 'Search for Open Source Bundles and Projects')) ?>

<?php $view['slots']->start('sidemenu') ?>
<?php $view->output('S2bBundle:Repo:add') ?>
<?php $view['slots']->stop() ?>

<?php $view['slots']->output('_content', 'Please use the search input at the top right.<br />The engine will search in repository name, description and developer name.') ?>
