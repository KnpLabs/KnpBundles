<?php $view->extend('S2bBundle:Repo:show') ?>

<?php $view->slots->set('h1', $repo->getName()) ?>
<?php $view->slots->set('current_menu_item', 'project_list') ?>

<?php $view->slots->set('git_command', sprintf('git clone %s', $repo->getGitUrl())) ?>
