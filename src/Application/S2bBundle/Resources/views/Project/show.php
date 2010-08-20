<?php $view->extend('S2bBundle:Repo:show') ?>

<?php $view['slots']->set('h1', $repo->getName()) ?>
<?php $view['main_menu']['Projects']->setIsCurrent(true) ?>

<?php $view['slots']->set('git_command', sprintf('git clone %s', $repo->getGitUrl())) ?>
