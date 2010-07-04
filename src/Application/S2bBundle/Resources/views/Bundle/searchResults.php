<?php $view->extend('S2bBundle:Bundle:search') ?>

<?php $view->slots->set('search_query', $query) ?>

<?php $view->slots->start('search_results') ?>

<h2 class="section-title"><?php echo $query ?></h2>

<?php $view->output('S2bBundle:Bundle:bigList', array('bundles' => $bundles)) ?>

<?php $view->slots->stop() ?>
