<?php $view->extend('S2bBundle::layout') ?>

<?php $view->main_menu['Api']->setIsCurrent(true) ?>
<?php $view->slots->set('title', 'Developer HTTP API') ?>
<?php $view->slots->set('h1', '<span>Developer</span> API') ?>
<?php $view->slots->set('description', 'Programmatic HTTP API to access Bundles and developers data') ?>
<?php $view->slots->set('slogan', 'Programmatic HTTP API to access Bundles and developers data') ?>
<?php $view->slots->set('sidemenu', $view->actions->render('S2bBundle:Main:timeline')) ?>

<div class="doc_text markdown">
    <?php echo $view->markdown->transform(html_entity_decode($text, ENT_COMPAT, 'UTF-8')) ?>
</div>
