<ol>
<?php foreach($bundles as $bundle): ?>
    <li>
        <a title="<?php echo $bundle->getDescription() ?>" href="<?php echo $view->router->generate('bundle_show', array('username' => $bundle->getUsername(), 'name' => $bundle->getName())) ?>">
            <?php echo $bundle->getName(); ?>
        </a>
    </li>
<?php endforeach; ?>
</ol>
