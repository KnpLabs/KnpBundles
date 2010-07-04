<ul class="bundle-list">
<?php foreach($bundles as $bundle): ?>
    <li>
        <span class="score"><?php echo $bundle->getRoundScore() ?></span>
        <a href="<?php echo $view->router->generate('bundle_show', array('username' => $bundle->getUsername(), 'name' => $bundle->getName())) ?>">
            <?php echo $bundle->getShortName() ?><span>Bundle</span>
        </a>
        <div class="description"><?php echo $bundle->getDescription() ?></div>
    </li>
<?php endforeach; ?>
</ul>
