<?php $view->extend('S2bBundle:Repo:search') ?>

<?php $view->slots->set('search_query', $query) ?>
<?php $view->slots->set('title', 'Search '.$query) ?>
<?php $view->slots->set('slogan', count($repos).' Repositor'.(count($repos) > 1 ? 'ies' : 'y').' found for "<strong>'.$query.'</strong>"') ?>
<?php $view->slots->set('description', count($repos).' Repositories found for '.$query) ?>

<?php if(count($bundles)): ?>
<h2 class="section-title"><?php echo count($bundles) ?> Bundle<?php echo count($bundles) > 1 ? 's' : '' ?></h2>
<?php $view->output('S2bBundle:Bundle:bigList', array('repos' => $bundles)) ?>
<?php endif; ?>

<?php if(count($projects)): ?>
<h2 class="section-title"><?php echo count($projects) ?> Project<?php echo count($projects) > 1 ? 's' : '' ?></h2>
<?php $view->output('S2bBundle:Project:bigList', array('repos' => $projects)) ?>
<?php endif; ?>
