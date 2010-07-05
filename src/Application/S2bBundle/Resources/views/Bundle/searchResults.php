<?php $view->extend('S2bBundle:Bundle:search') ?>

<?php $view->slots->set('search_query', $query) ?>
<?php $view->slots->set('title', 'Search '.$query) ?>
<?php $view->slots->set('slogan', count($bundles).' Bundles found for "<strong>'.$query.'</strong>"') ?>
<?php $view->slots->set('description', count($bundles).' Bundles found for '.$query) ?>

<?php $view->output('S2bBundle:Bundle:bigList', array('bundles' => $bundles)) ?>
