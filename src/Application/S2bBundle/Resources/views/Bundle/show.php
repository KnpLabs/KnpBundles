<?php $view->extend('S2bBundle:Repo:show') ?>

<?php $view->slots->set('h1', $repo->getShortName().'<span>Bundle</span>') ?>
<?php $view->slots->set('current_menu_item', 'bundle_list') ?>

<?php $view->slots->set('git_command', sprintf('git submodule add %s src/Bundle/%s', $repo->getGitUrl(), $repo->getName())) ?>
