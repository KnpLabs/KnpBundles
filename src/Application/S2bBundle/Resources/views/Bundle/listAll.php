<?php $fields = array(
    'name' => 'name',
    'username' => 'author',
    'createdAt' => 'last created',
    'lastCommitAt' => 'last updated',
    'followers' => 'followers',
    'forks' => 'forks',
    'score' => 'score'
); ?>

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
<h3>Add a Bundle</h3>
Are we missing a Bundle?<br />
You can point it out here:
<form class="add_bundle" action="<?php echo $view->router->generate('bundle_add') ?>" method="POST">
    <input class="hint" value="http://github.com/xxx/yyyBundle" name="url" />
</form>
<?php $view->slots->stop() ?>
