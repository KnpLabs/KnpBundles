<?php $view->extend('S2bBundle::layout') ?>
<?php $view->slots->set('current_menu_item', 'user_list') ?>
<?php $view->slots->set('h1', '<span>'.count($users).'</span> Developers') ?>
<?php $view->slots->set('title', 'All '.count($users).' Developers') ?>
<?php $view->slots->set('slogan', 'List of Symfony2 Bundle developers') ?>
<?php $view->slots->set('description', 'Symfony2 Bundle developers') ?>

<ul class="user-list clickable-list">
<?php foreach($users as $user): ?>
    <li class="item">
        <a class="item-link" href="<?php echo $view->router->generate('user_show', array('name' => $user->getName())) ?>">
            <img class="gravatar" src="<?php echo Bundle\GravatarBundle\Api::getUrl($user->getEmail(), array('size' => 32, 'default' => 'mm')) ?>" width="32" height="32" />
            <?php echo $user->getName() ?>
            <?php if($user->getFullName()): ?>
                <span>(<?php echo $user->getFullName() ?>)</span>
            <?php endif; ?>
        </a>
        <ul class="bundles">
        <?php foreach($user->getBundles() as $bundle): ?>
            <li>
                <a href="<?php echo $view->router->generate('bundle_show', array('username' => $user->getName(), 'name' => $bundle->getName())) ?>">
                    <?php echo $bundle->getShortName() ?><span>Bundle</span><em><?php echo $bundle->getLastTagName() ?></em>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    </li>
<?php endforeach; ?>
</ul>
