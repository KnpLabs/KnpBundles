<?php $view->extend('S2bBundle::layout') ?>

<h1>All <?php echo count($bundles) ?> Bundles</h1>

<ol class="list_all">
<?php foreach($bundles as $bundle): ?>
    <li>
        <a href="<?php echo $view->router->generate('bundle_show', array('username' => $bundle->getUsername(), 'name' => $bundle->getName())) ?>">
            <?php echo $bundle->getFullName() ?>
        </a>
    </li>
<?php endforeach; ?>
</ol>
