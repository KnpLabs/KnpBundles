<ul class="bundle-list clickable-list">
<?php foreach($bundles as $bundle): ?>
    <li class="item">
        <span class="score"><?php echo $bundle->getScore() ?></span>
        <a class="item-link" href="<?php echo $view->router->generate('bundle_show', array('username' => $bundle->getUsername(), 'name' => $bundle->getName())) ?>">
            <img class="gravatar" src="<?php echo $view->assets->getUrl('bundles/s2b/images/lego32.png') ?>" />
            <?php echo $bundle->getShortName() ?><span>Bundle</span><em><?php echo $bundle->getLastTagName() ?></em>
        </a>
        <span class="description"><?php echo $bundle->getDescription() ?></span>
    </li>
<?php endforeach; ?>
</ul>
