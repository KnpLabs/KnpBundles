<?php $view->extend('S2bBundle::layout') ?>
<?php $view->slots->set('current_menu_item', 'all') ?>

<?php $view->output('S2bBundle:Bundle:bigList', array('bundles' => $bundles)) ?>

<?php $view->slots->set('h1', '<span>'.count($bundles).'</span> Bundles') ?>
<?php $view->slots->set('slogan', 'Sorted by '.$sort) ?>

<?php $view->slots->start('sidemenu') ?>

<h3>Sort by</h3>

<?php $fields = array(
    'name' => 'Name',
    'username' => 'Author',
    'createdAt' => 'Last created',
    'lastCommitAt' => 'Last updated',
    'followers' => 'Followers',
    'forks' => 'Forks',
    'score' => 'Score'
); ?>
<ul>
<?php foreach($fields as $field => $text): ?>
    <li<?php $field == $sort && print ' class="current"' ?>>
        <a href="<?php echo $view->router->generate('all', array('sort' => $field)) ?>"><?php echo $text ?></a>
    </li>
<?php endforeach ?>
</ul>
<?php $view->slots->stop() ?>
