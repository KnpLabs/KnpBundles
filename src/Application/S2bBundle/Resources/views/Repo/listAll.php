<?php $view->extend('S2bBundle::layout') ?>
<?php $view->slots->set('current_menu_item', 'bundle_list') ?>

<?php $view->output('S2bBundle:Bundle:bigList', array('bundles' => $bundles)) ?>

<?php $view->slots->set('h1', '<span>'.count($bundles).'</span> Bundles') ?>
<?php $view->slots->set('title', 'All '.count($bundles).' Bundles') ?>
<?php $view->slots->set('slogan', 'All Bundles sorted by '.$fields[$sort]) ?>
<?php $view->slots->set('description', 'All Symfony2 Bundles sorted by '.$sort) ?>

<?php $view->slots->start('sidemenu') ?>
<h3>Sort by</h3>
<div class="sidemenu uppercase">
    <ul>
    <?php foreach($fields as $field => $text): ?>
        <li<?php $field == $sort && print ' class="current"' ?>>
            <a href="<?php echo $view->router->generate('bundle_list', array('sort' => $field)) ?>"><?php echo $text ?></a>
        </li>
    <?php endforeach ?>
    </ul>
</div>
<?php $view->output('S2bBundle:Bundle:add') ?>
<?php $view->slots->stop() ?>
